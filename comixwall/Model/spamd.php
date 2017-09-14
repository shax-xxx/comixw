<?php
/* $ComixWall: spamd.php,v 1.9 2009/11/16 12:05:36 soner Exp $ */

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

/** @file
 * OpenBSD/spamd, spam deferral daemon.
 */

require_once($MODEL_PATH.'model.php');

class Spamd extends Model
{
	public $Name= 'spamd';
	public $User= '_spamd';
	
	public $LogFile= '/var/log/spamd.log';
	
	function Spamd()
	{
		global $TmpFile;
		
		parent::Model();
		
		$this->StartCmd= "/usr/libexec/spamd > $TmpFile 2>&1 &";
		
		$this->Commands= array_merge(
			$this->Commands,
			array(
				'GetWhitelist'	=> array(
					'argv'	=> array(),
					'desc'	=> _('Get spamd whitelist'),
					),
				
				'GetGreylist'	=> array(
					'argv'	=> array(),
					'desc'	=> _('Get spamd blacklist'),
					),
				
				'SetStartupIf'	=> array(
					'argv'	=> array(NAME),
					'desc'	=> _('Set startup if'),
					),
				)
			);
	}
	
	function Stop()
	{
		$this->Pkill('spamlogd');
		return parent::Stop();
	}

	function Start()
	{
		/// @attention Start spamd first, otherwise spamlogd starts only
		parent::Start();
		
		// spamlogd_flags="-i em1"
		if (($if= $this->GetNVP($this->rcConfLocal, 'spamlogd_flags', '"')) === FALSE) {
			if (($if= $this->GetDisabledNVP($this->rcConfLocal, 'spamlogd_flags', '"')) === FALSE) {
				return FALSE;
			}
		}
		$this->RunShellCommand("/usr/libexec/spamlogd $if");
	}

	function SetStartupIf($if)
	{
        $re= '/(\h*spamlogd_flags\h*=\h*"\h*-i\h+)(\w+\d+)(".*)/m';
		return $this->ReplaceRegexp($this->rcConfLocal, $re, '${1}'.$if.'${3}');
	}

	function GetWhitelist()
	{
		return $this->RunShellCommand('/usr/sbin/spamdb | grep WHITE');
	}

	function GetGreylist()
	{
		return $this->RunShellCommand('/usr/sbin/spamdb | grep GREY');
	}

	function ParseLogLine($logline, &$cols)
	{
		global $Re_Ip;

		if ($this->ParseSyslogLine($logline, $cols)) {
			$re_srcip= "($Re_Ip)";
			$re_num= '(\d+)';

			//87.109.52.69: disconnected after 466 seconds.
			$re= "/$re_srcip: disconnected after $re_num seconds\.$/";
			if (preg_match($re, $logline, $match)) {
				$cols['IP']= $match[1];
				$cols['Seconds']= $match[2];
			}

			//122.45.35.135: disconnected after 462 seconds. lists: korea
			$re= "/$re_srcip: disconnected after $re_num seconds\. lists: (.*)$/";
			if (preg_match($re, $logline, $match)) {
				$cols['IP']= $match[1];
				$cols['Seconds']= $match[2];
				$cols['List']= $match[3];
			}
			return TRUE;
		}
		return FALSE;
	}
}
?>
