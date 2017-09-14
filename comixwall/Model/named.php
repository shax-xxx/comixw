<?php
/* $ComixWall: named.php,v 1.7 2009/11/16 12:05:36 soner Exp $ */

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

class Named extends Model
{
	public $Name= 'named';
	public $User= 'root|named';
	
	public $ConfFile= '/var/named/etc/named.conf';
	public $LogFile= '/var/log/named.log';

	public $VersionCmd= '/usr/sbin/named -v';

	public $PidFile= '/var/run/named.pid';

	function Named()
	{
		global $TmpFile;
		
		parent::Model();
		
		$this->StartCmd= "/usr/sbin/named > $TmpFile 2>&1 &";
		
		$this->Commands= array_merge(
			$this->Commands,
			array(
				'GetForwarders'	=> array(
					'argv'	=> array(),
					'desc'	=> _('Get DNS forwarders'),
					),

				'SetForwarders'	=> array(
					'argv'	=> array(IPADRLIST),
					'desc'	=> _('Set DNS forwarders'),
					),
				)
			);
	}

	/** Gets name server forwarders.
	 *
	 * @return Forwarders IP, semi-colon separated.
	 * @todo Is semi-colon separated list fine?
	 */
	function GetForwarders()
	{
		return $this->SearchFile($this->ConfFile, "/^\h*forwarders\h*{\h*(.*)\h*\;\h*}\h*\;\h*$/m");
	}

	/** Sets name server forwarders.
	 *
	 * @param[in]	$forwarders	string semi-colon seperated list of IPs, forwarders.
	 * @return Replace result.
	 */
	function SetForwarders($forwarders)
	{
		return $this->ReplaceRegexp($this->ConfFile, "/^(\h*forwarders\h*{\h*)(.*)(\h*\;\h*}\h*\;\h*)$/m", '${1}'.$forwarders.'${3}');
	}
	
	function ParseLogLine($logline, &$cols)
	{
		global $Re_Ip;

		if ($this->ParseSyslogLine($logline, $cols)) {
			$re_clientip= "($Re_Ip)";
			$re_num= '(\d+)';
			$re_domain= '(\S+)';
			$re_type= '(.*)';

			// client 127.0.0.1#31874: query: www.openbsd.org IN A +
			$re= "/client\s+$re_clientip#$re_num:\s+query:\s+$re_domain\s+\S+\s+$re_type$/";
			if (preg_match($re, $logline, $match)) {
				$cols['IP']= $match[1];
				$cols['Domain']= $match[3];
				$cols['Type']= $match[4];
			}
			return TRUE;
		}
		return FALSE;
	}
}
?>
