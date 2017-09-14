<?php
/* $ComixWall: snortips.php,v 1.25 2009/11/26 20:51:35 soner Exp $ */

/*
 * Copyright (c) 2004-2009 Soner Tari.  All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 3. All advertising materials mentioning features or use of this
 *    software must display the following acknowledgement: This
 *    product includes software developed by Soner Tari
 *    and its contributors.
 * 4. Neither the name of Soner Tari nor the names of
 *    its contributors may be used to endorse or promote products
 *    derived from this software without specific prior written
 *    permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS OR
 * IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES
 * OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
 * NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF
 * THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

require_once($MODEL_PATH.'model.php');

class Snortips extends Model
{
	public $Name= 'snortips';
	public $User= 'root';

	private $sigMsgFile= '/var/tmp/snortips.sigmsg';

	public $NVPS= '\h';
	public $ConfFile= '/etc/snort/snortips.conf';
	public $LogFile= '/var/log/snortips.log';
	
	public $VersionCmd= 'unset LC_ALL; unset LANG; /usr/local/sbin/snortips -V';
	
	public $PidFile= '/var/run/snortips.pid';
	
	function Snortips()
	{
		global $TmpFile;
		
		parent::Model();
		
		$this->StartCmd= "unset LC_ALL; unset LANG; /usr/local/sbin/snortips > $TmpFile 2>&1 &";

		$this->Commands= array_merge(
			$this->Commands,
			array(
				'AddIPToList'	=> array(
					'argv'	=> array(NAME, IPADR|IPRANGE),
					'desc'	=> _('Add to IP list'),
					),

				'DelIPFromList'	=> array(
					'argv'	=> array(NAME, SERIALARRAY),
					'desc'	=> _('Delete IP from list'),
					),

				'UnblockAll'	=> array(
					'argv'	=> array(),
					'desc'	=> _('Unblock all'),
					),

				'UnblockIPs'	=> array(
					'argv'	=> array(SERIALARRAY),
					'desc'	=> _('Unblock IPs'),
					),

				'BlockIP'	=> array(
					'argv'	=> array(IPADR|IPRANGE, NUM|EMPTYSTR),
					'desc'	=> _('Block IPs'),
					),

				'GetInfo'	=> array(
					'argv'	=> array(),
					'desc'	=> _('Get IPS info'),
					),

				'GetListIPs'	=> array(
					'argv'	=> array(NAME),
					'desc'	=> _('Get listed ips'),
					),

				'GetKeywords'	=> array(
					'argv'	=> array(),
					'desc'	=> _('Get alert keywords'),
					),

				'AddKeyword'	=> array(
					/// @todo Is there any pattern or size for keywords?
					'argv'	=> array(STR),
					'desc'	=> _('Add alert keyword'),
					),

				'DelKeyword'	=> array(
					'argv'	=> array(STR),
					'desc'	=> _('Delete alert keywords'),
					),
			)
		);
	}

	function Stop()
	{
		return $this->Kill();
	}
	
	/** Searches for an IP or !IP.
	 *
	 * @param[in]	$ip	Search string.
	 * @return	Search string if successful.
	 */
	function GetIP($ip)
	{
		$ip= Escape($ip, '/.');
		return $this->SearchFile($this->ConfFile, "/^\h*($ip)\b\h*$/m");
	}

	function GetInfo()
	{
		global $Re_Ip, $Re_Net;

		// Clear the file if this function spawns too fast (do not read the same contents again)
		$this->PutFile('/var/db/snortips', '');
		
		if ($this->IsRunning()) {
			$this->RunShellCommand('/bin/kill -INFO $(/bin/cat /var/run/snortips.pid)');
			
			/// @todo Use communication over shared memory (e.g. pipes) with SnortIPS instead
			$eof= FALSE;
			$count= 0;
			while ($count++ < self::PROC_STAT_TIMEOUT) {
				if ($items= $this->GetFile('/var/db/snortips')) {
					// Check for EOF
					if (preg_match("/^\.$/m", $items)) {
						$eof= TRUE;
						break;
					}
				}
				exec('/bin/sleep .1');
			}
			
			if ($eof) {
				$info= array(
					'Whitelisted' => array(),
					'Blocked' => array(),
					'Blacklisted' => array(),
					);

				$items= explode("\n", $items);
				foreach ($items as $line) {
					if (preg_match("/^($Re_Net|$Re_Ip) \((\d+)\)$/", $line, $match)) {
						$host= $match[1];
						$expires= $match[2];
						$info['Blocked'][$host]= $expires.' ('. floor($expires / 60) .' '._('min').' '. $expires % 60 .' '._('sec').')';
					}
					else if (preg_match("/^!($Re_Net|$Re_Ip)/", $line, $match)) {
						$info['Whitelisted'][]= $match[1];
					}
					else if (preg_match("/^($Re_Net|$Re_Ip)/", $line)) {
						$info['Blacklisted'][]= $line;
					}
				}
				return serialize($info);
			}
		}
		return FALSE;
	}

	/** Deletes a list of IPs from a list.
	 *
	 * Does not allow system IPs to be deleted.
	 *
	 * @param[in]	$list	string List name
	 * @param[in]	$ips	array List of IPs to delete
	 */
	function DelIPFromList($list, $ips)
	{
		$systemips[]= '127.0.0.1';
		if ($list == 'whitelist') {
			$ifs= explode("\n", $this->GetPhyIfs());
			foreach ($ifs as $if) {
				if ($ifip= $this->GetIpAddr($if)) {
					$systemips[]= $ifip;
				}
			}
		}

		$ips= unserialize($ips);
		$retval= TRUE;
		foreach ($ips as $ip) {
			if (!in_array($ip, $systemips)) {
				$method= $list == 'whitelist' ? 'DelAllowedIp' : 'DelRestrictedIp';
				$retval&= $this->$method($ip);
			}
			else {
				ViewError(_('You cannot delete system IP address').": $ip");
				$retval= FALSE;
			}
		}
		return $retval;
	}

	/** Adds an IP to snortips white or black list.
	 *
	 * @param[in]	$list	string List name
	 * @param[in]	$ip		string IP or net
	 */
	function AddIPToList($list, $ip)
	{
		global $preIP, $preNet;

		/// Check the IP in the complement list
		$compip= $list == 'whitelist' ? $ip : "!$ip";
		
		$output= $this->GetIP($compip);
		if ($output !== $compip) {
			$output= $this->RunShellCommand("/sbin/pfctl -nv -t snortips -T add $ip 2>&1");
			$output= explode("\n", $output);
			// 0/1 addresses added (dummy).
			if (preg_match('/^(\d+)\/\d+ addresses added \(dummy\)\.$/', $output[0], $match)) {
				if ($match[1] > 0) {
					if (preg_match("/^A\s+($preIP|$preNet)$/", $output[1], $match)) {
						$actualadded= $match[1];
						// 192.168.1.1/32 and 192.168.1.1 are identical, but we miss it above
						$compip= $list == 'whitelist' ? $actualadded : "!$actualadded";
						$output= $this->GetIP($compip);
						if ($output !== $compip) {
							$method= $list == 'whitelist' ? 'AddAllowedIp' : 'AddRestrictedIp';
							$retval= $this->$method($actualadded);
							if ($actualadded == $ip) {
								return $retval;
							}
							ViewError(_('IP or network address fixed').": $ip -> $actualadded");
						}
						else {
							ViewError(_('White and black list entries should not be identical').": $actualadded");
						}
					}
				}
				else {
					ViewError(_('Cannot add').": $ip\n$output[0]");
				}
			}
			else {
				ViewError(_('Pfctl output does not match').": $ip\n$output[0]");
			}
		}
		else {
			ViewError(_('White and black list entries should not be identical').": $ip");
		}
		return FALSE;
	}

	/** Unblock all
	 */
	function UnblockAll()
	{
		return $this->RunShellCommand('/bin/kill -USR2 $(/bin/cat /var/run/snortips.pid)');
	}

	/** Unblock IPs
	 */
	function UnblockIPs($ips)
	{
		$ips= unserialize($ips);
		$contents= array();
		foreach ($ips as $ip) {
			$contents[]= "U $ip\n";
		}
		// file_put_contents() accepts array as data
		file_put_contents($this->sigMsgFile, $contents, LOCK_EX);
		return $this->RunShellCommand('/bin/kill -USR1 $(/bin/cat /var/run/snortips.pid)');
	}

	/** Unblock IPs
	 */
	function BlockIP($ip, $time= '')
	{
		file_put_contents($this->sigMsgFile, rtrim("B $ip $time"), LOCK_EX);
		return $this->RunShellCommand('/bin/kill -USR1 $(/bin/cat /var/run/snortips.pid)');
	}

	/** Provides a list of IPs.
	 */
	function GetListIPs($list)
	{
		global $Re_Ip, $Re_Net;
		
		return $this->SearchFileAll($this->LISTS[$list], "/^\h*($Re_Ip|$Re_Net)\h*$/m");
	}

	/** Get keywords.
	 */
	function GetKeywords()
	{
		return $this->SearchFileAll($this->ConfFile, "/^\h*Keyword\h+\"(.*)\"\h*$/m");
	}
	
	/** Add a keyword.
	 */
	function AddKeyword($keyword)
	{
		$this->DelKeyword($keyword);
		return $this->AppendToFile($this->ConfFile, "Keyword \"$keyword\"");
	}

	/** Delete a keyword.
	 */
	function DelKeyword($keyword)
	{
		$keyword= Escape($keyword, '/');
		return $this->ReplaceRegexp($this->ConfFile, "/^(\h*Keyword\h+\"$keyword\"\s*)/m", '');
	}

	/** Parses snortips IPS logs.
	 *
	 * @param[in]	$logline	Log line to parse.
	 * @param[out]	$cols		Parser output, parsed fields.
	 */
	function ParseLogLine($logline, &$cols)
	{
		global $Re_Ip, $Re_Net;

		if ($this->ParseSyslogLine($logline, $cols)) {
			// Unblocking host 10.0.1.13
			$re= "/^Unblocking host ($Re_Ip|$Re_Net)$/";
			if (preg_match($re, $cols['Log'], $match)) {
				$cols['Unblocking']= $match[1];
			}
			else {
				// Blocking host $host as blacklisted
				$re= "/^Blocking host ($Re_Ip|$Re_Net) as blacklisted$/";
				if (preg_match($re, $cols['Log'], $match)) {
					$cols['Blocked']= $match[1];
				}
				else {
					// Host $host is already blocked; blocking as blacklisted
					$re= "/^Host ($Re_Ip|$Re_Net) is already blocked; blocking as blacklisted$/";
					if (preg_match($re, $cols['Log'], $match)) {
						$cols['Blocked']= $match[1];
					}
					else {
						// Blocking host 11.11.11.12 for 3600 ticks
						$re= "/^Blocking host ($Re_Ip|$Re_Net) for (\d+) ticks$/";
						if (preg_match($re, $cols['Log'], $match)) {
							$cols['Blocked']= $match[1];
							$cols['BlockedTime']= $match[2];
						}
						else {
							// Host 11.11.11.11 is already blocked; extending amnesty to 7200 ticks
							$re= "/^Host ($Re_Ip|$Re_Net) is already blocked; extending block duration by (\d+) ticks$/";
							if (preg_match($re, $cols['Log'], $match)) {
								$cols['Extended']= $match[1];
								$cols['ExtendedTime']= $match[2];
							}
							else {
								// Soft init requested, unblocking and zeroing all
								$re= "/^Soft init requested, unblocking and zeroing all$/";
								if (preg_match($re, $cols['Log'], $match)) {
									$cols['Softinit']= $cols['Date'].' - '.substr($cols['Time'], 0, 2)._('h');
								}
							}
						}
					}
				}
			}
			return TRUE;
		}
		return FALSE;
	}
}

$ModelConfig = array(
    'Priority' => array(
        'type' => UINT,
		),
    'BlockDuration' => array(
        'type' => UINT,
		),
    'MaxBlockDuration' => array(
        'type' => UINT,
		),
);
?>
