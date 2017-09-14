<?php
/* $ComixWall: freshclam.php,v 1.16 2009/11/19 18:19:42 soner Exp $ */

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
 * ClamAV virus db updater.
 */

require_once($MODEL_PATH.'model.php');

class Freshclam extends Model
{
	public $Name= 'freshclam';
	public $User= '_clamav';
	
	public $NVPS= '\h';
	public $ConfFile= '/etc/freshclam.conf';
	public $LogFile= '/var/log/freshclam.log';
	
	public $PidFile= '/var/run/clamav/freshclam.pid';
	
	function Freshclam()
	{
		global $TmpFile;
		
		parent::Model();
		
		$this->StartCmd= "/usr/local/bin/freshclam -d > $TmpFile 2>&1 &";
	
		$this->Commands= array_merge(
			$this->Commands,
			array(
				'GetMirrors'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get database mirrors'),
					),
				
				'AddMirror'=>	array(
					'argv'	=>	array(URL),
					'desc'	=>	_('Add database mirror'),
					),
				
				'DelMirror'=>	array(
					'argv'	=>	array(URL),
					'desc'	=>	_('Delete database mirror'),
					),
				)
			);
	}
	
	/// @todo clamd and freshclam log lines contain number of virus defs.
	function ParseLogLine($logline, &$cols)
	{
		// Mon Oct 26 05:23:56 2009 -> ClamAV update process started at Mon Oct 26 05:23:56 2009
		$re_datetime= '\w+\s+(\w+\s+\d+)\s+(\d+:\d+:\d+)\s+\d+';
		$re_rest= '(.*)';

		$re= "/^$re_datetime\s+->\s+$re_rest$/";
		if (preg_match($re, $logline, $match)) {
			$cols['Date']= $match[1];
			$cols['Time']= $match[2];
			$cols['DateTime']= $cols['Date'].' '.$cols['Time'];
			$cols['Log']= $match[3];
			return TRUE;
		}
		else if ($this->ParseSyslogLine($logline, $cols)) {
			$cols['DateTime']= $cols['Date'].' '.$cols['Time'];
			return TRUE;
		}
		else {
			$cols['Log']= $logline;
			return TRUE;
		}
		return FALSE;		
	}

	function GetMirrors()
	{
		$mirrors= $this->SearchFileAll($this->ConfFile, "/^\h*DatabaseMirror\h*([\w.]+)\b.*\h*$/m");
		// Do not list the main server
		return preg_replace('/^(\s*database\.clamav\.net\s*)/m', '', $mirrors);
	}
	
	function AddMirror($mirror)
	{
		$this->DelMirror($mirror);
		return $this->ReplaceRegexp($this->ConfFile, "/(\h*DatabaseMirror\h+database\.clamav\.net.*)/m", "DatabaseMirror $mirror\n".'${1}');
	}

	function DelMirror($mirror)
	{
		// Do not delete the main server
		if ($mirror !== 'database.clamav.net') {
			$mirror= Escape($mirror, '.');
			return $this->ReplaceRegexp($this->ConfFile, "/^(\h*DatabaseMirror\h+$mirror\b.*(\s|))/m", '');
		}
		ViewError(_("Won't delete database.clamav.net entry."));
		return FALSE;
	}
}

$ModelConfig = array(
    'Checks' => array(
        'type' => UINT,
		),
    'MaxAttempts' => array(
        'type' => UINT,
		),
    'SafeBrowsing' => array(
        'type' => STR_yes_no,
		),
    'LogVerbose' => array(
        'type' => STR_yes_no,
		),
    'DNSDatabaseInfo' => array(
		),
    'HTTPProxyServer' => array(
		),
    'HTTPProxyPort' => array(
        'type' => PORT,
		),
    'HTTPProxyUsername' => array(
		),
    'HTTPProxyPassword' => array(
		),
    'LocalIPAddress' => array(
        'type' => IP,
		),
    'Debug' => array(
        'type' => STR_yes_no,
		),
);
?>
