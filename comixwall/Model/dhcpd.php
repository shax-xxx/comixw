<?php
/* $ComixWall: dhcpd.php,v 1.11 2009/11/16 12:05:36 soner Exp $ */

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

class Dhcpd extends Model
{
	public $Name= 'dhcpd';
	public $User= '_dhcp';
	
	/// IPs distributed by DHCP server
	private $leasesFile= '/var/db/dhcpd.leases';
	
	public $ConfFile= '/etc/dhcpd.conf';
	
	public $LogFile= '/var/log/dhcpd.log';
						
	function Dhcpd()
	{
		parent::Model();
		
		$this->Commands= array_merge(
			$this->Commands,
			array(
				'GetIfs'	=> array(
					'argv'	=> array(),
					'desc'	=> _('Get DHCP interfaces'),
					),

				'GetOption'	=> array(
					'argv'	=> array(NAME),
					'desc'	=> _('Get DHCP option'),
					),

				'GetRange'	=>	array(
					'argv'	=> array(),
					'desc'	=> _('Get DHCP IP range'),
					),

				'AddIf'	=>	array(
					'argv'	=> array(NAME),
					'desc'	=> _('Add DHCP interface'),
					),

				'DelIf'	=>	array(
					'argv'	=> array(NAME),
					'desc'	=> _('Delete DHCP interface'),
					),

				'GetArpTable'	=>	array(
					'argv'	=> array(),
					'desc'	=> _('Get arp table'),
					),

				'GetLeases'	=>	array(
					'argv'	=> array(),
					'desc'	=> _('Get dhcp leases'),
					),

				'SetOptions'=>	array(
					'argv'	=> array(IPADR, IPADR, IPADR, IPADR, IPADR, IPADR),
					'desc'	=> _('Set configuration'),
					),

				'SetDhcpdConf'	=>	array(
					'argv'	=> array(IPADR, IPADR, IPADR, IPADR, IPADR, IPADR),
					'desc'	=> _('Set dhcpd conf'),
					),
				)
			);
	}
	
	function Start()
	{
		if (($ifs= $this->GetIfs()) !== FALSE) {
			$ifs= explode("\n", $ifs);
			$ifs= implode(' ', $ifs);
			return $this->RunShellCommand("/usr/sbin/dhcpd $ifs");
		}
		return FALSE;
	}
	
	/** Provides the list of dhcpd interfaces.
	 *
	 * @return List of interfaces.
	 */
	function GetIfs()
	{
		$ifs= $this->SearchFile($this->rcConfLocal, '/^\h*#*\h*dhcpd_flags\h*=\h*("[^#"]*"|)(\h*|\h*#.*)$/m', 1, '"');
		return implode ("\n", preg_split('/\h+/', $ifs));
	}

	/** Adds a dhcpd interface.
	 *
	 * Cleans up duplicates first
	 *
	 * @param[in]	$if	Interface name.
	 */
	function AddIf($if)
	{
		$this->DelIf($if);
		if (($ifs= $this->GetIfs()) !== FALSE) {
			$ifs= explode("\n", $ifs);
			$ifs= trim(implode(' ', $ifs)." $if");
			return $this->SetIfs($ifs);
		}
		return FALSE;
	}

	/** Deletes a dhcpd interface.
	 *
	 * @param[in]	$if	Interface name.
	 */
	function DelIf($if)
	{
		if (($ifs= $this->GetIfs()) !== FALSE) {
			$ifs= explode("\n", $ifs);

			// Don't use: unset($ifs[array_search($if, $ifs)]), check strictly
			if (($key= array_search($if, $ifs)) !== FALSE) {
				echo array_search($if, $ifs)."\n";
				unset($ifs[$key]);
				$ifs= implode(' ', $ifs);
				return $this->SetIfs($ifs);
			}
		}
		return FALSE;
	}

	/** Sets ifs.
	 *
	 * @param[in]	$ifs	List of ifs.
	 */
	function SetIfs($ifs)
	{
		return $this->ReplaceRegexp($this->rcConfLocal, '/^(\h*#*\h*dhcpd_flags\h*=\h*)("[^#"]*"|)(\h*|\h*#.*)$/m', '${1}"'.$ifs.'"${3}');
	}

	/** Reads a dhcpd option.
	 *
	 * DHCP server options are usually IPs. Range is read by GetRange().
	 *
	 * @param[in]	$option	Option name to get value of.
	 * @return Option value.
	 */
	function GetOption($option)
	{
		return $this->SearchFile($this->ConfFile, "/^\h*option\h*$option\h*([^#;]*)\h*\;\h*$/m");
	}

	/** Reads dhcpd range option.
	 *
	 * @return IP range.
	 */
	function GetRange()
	{
		global $Re_Ip;
		
		$re= "/^\h*range\h*(($Re_Ip)\h*($Re_Ip))\h*\;\h*$/m";
		if (($output= $this->SearchFile($this->ConfFile, $re)) !== FALSE) {
			return preg_replace("/\s+/", "\n", $output);
		}
		return FALSE;
	}

	/** Set dhcpd configuration.
	 *
	 * @param[in]	$dns	DNS server.
	 * @param[in]	$router	Gateway.
	 * @param[in]	$mask	Netmask.
	 * @param[in]	$bc		Broadcast address.
	 * @param[in]	$lr		Lower IP range.
	 * @param[in]	$ur		Upper IP range.
	 * @return TRUE on success.
	 */
	function SetOptions($dns, $router, $mask, $bc, $lr, $ur)
	{
		$retval=  $this->SetOption('domain-name-servers', $dns);
		$retval&= $this->SetOption('routers', $router);
		$retval&= $this->SetOption('subnet-mask', $mask);
		$retval&= $this->SetOption('broadcast-address', $bc);
		$retval&= $this->SetRange($lr, $ur);
		return $retval;
	}

	/** Changes a dhcpd option.
	 *
	 * @param[in]	$option	Option name to get value of.
	 * @param[in]	$value	Option value to set.
	 * @return Option value.
	 */
	function SetOption($option, $value)
	{
		global $Re_Ip;
		
		return $this->ReplaceRegexp($this->ConfFile, "/^(\h*option\h*$option\b\h*)($Re_Ip)(\h*\;\h*)$/m", '${1}'.$value.'${3}');
	}

	/** Changes dhcpd range option.
	 *
	 * @param[in]	$lower	Lower limit of IP range.
	 * @param[in]	$upper	Upper limit of IP range.
	 */
	function SetRange($lower, $upper)
	{
		global $Re_Ip;
		
		return $this->ReplaceRegexp($this->ConfFile, "/^(\h*range\h*)($Re_Ip)(\h+)($Re_Ip)(\h*\;\h*)$/m", '${1}'.$lower.'${3}'.$upper.'${5}');
	}

	function SetDhcpdConf($lanip, $lanmask, $lannet, $lanbc, $lanmin, $lanmax)
	{
		global $Re_Ip;
	
		$retval=  $this->ReplaceRegexp($this->ConfFile, "/^(\h*subnet\h+)($Re_Ip)(\h+netmask\h+$Re_Ip\h*\{.*)/m", '${1}'.$lannet.'${3}');
		$retval&= $this->ReplaceRegexp($this->ConfFile, "/^(\h*subnet\h+$Re_Ip\h+netmask\h+)($Re_Ip)(\h*\{.*)/m", '${1}'.$lanmask.'${3}');
		$retval&= $this->SetOptions($lanip, $lanip, $lanmask, $lanbc, $lanmin, $lanmax);
		return $retval;
	}

	/** Get arp table.
	 */
	function GetArpTable()
	{
		global $Re_Ip;
		$lines= $this->RunShellCommand('/usr/sbin/arp -an');

		// Host                                 Ethernet Address    Netif Expire    Flags
		// 192.168.1.1                          08:00:27:22:5a:71     em1 19m38s
		// test3.my.domain                      08:00:27:b4:5d:29     em1 permanent l

		$re_arp= "/($Re_Ip)\s+(\w+:\w+:\w+:\w+:\w+:\w+)\s+(\w+)/m";

		$logs= array();
		if (preg_match_all($re_arp, $lines, $match, PREG_SET_ORDER)) {
			foreach ($match as $fields) {
				$cols['IP']= $fields[1];
				$cols['MAC']= $fields[2];
				$cols['Interface']= $fields[3];
				$logs[]= $cols;
			}
		}
		return serialize($logs);
	}

	function GetLeases()
	{
		global $Re_Ip;

		//	lease 192.168.25.1 {
		//	    starts 4 2017/04/13 21:58:08 UTC;
		//	    ends 5 2017/04/14 09:58:08 UTC;
		//	    hardware ethernet 08:00:27:22:5a:71;
		//	    uid 01:08:00:27:22:5a:71;
		//	    client-hostname "win7";
		//	}

		$re_starts= '\s*starts\s+(\d+)\s+(\d+\/\d+\/\d+)\s+(\d+:\d+:\d+).*';
		$re_ends= '\s*ends\s+(\d+)\s+(\d+\/\d+\/\d+)\s+(\d+:\d+:\d+).*';
		$re_mac= '\s*hardware\s+\w+\s+(\w+:\w+:\w+:\w+:\w+:\w+)\s*';
		$re_uid= '\s*uid\s+(.+)\s*';
		$re_host= '\s*(client-hostname|hostname)\s+"(.+)"\s*';
		$re_abandoned= '(\s*(abandoned);\s*|)';
		
		$re_lease= "/\s*lease\s+($Re_Ip)\s*\{$re_starts;$re_ends;$re_mac;($re_uid;|)$re_host;$re_abandoned\s*\}\s*/m";
		$lines= $this->GetFile($this->leasesFile);
		$logs= array();

		if (preg_match_all($re_lease, $lines, $match, PREG_SET_ORDER)) {
			foreach ($match as $fields) {
				$cols['IP']= $fields[1];
				$cols['Starts (UTC)']= "$fields[3] $fields[4]";
				$cols['Ends (UTC)']= "$fields[6] $fields[7]";
				$cols['MAC']= $fields[8];
				$cols['Host']= "$fields[12]";
				$cols['Status']= $fields[14];
				$logs[]= $cols;
			}
		}
		return serialize($logs);
	}
}
?>
