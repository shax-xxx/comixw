<?php
/* $ComixWall: openssh.php,v 1.19 2009/11/23 17:52:50 soner Exp $ */

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

class Openssh extends Model
{
	public $Name= 'openssh';
	public $User= 'root';
	
	public $NVPS= '\h';
	public $ConfFile= '/etc/ssh/sshd_config';
	public $LogFile= '/var/log/authlog';
	
	public $VersionCmd= '/usr/bin/ssh -V 2>&1';
	
	public $PidFile= '/var/run/sshd.pid';
	
	function Openssh()
	{
		global $TmpFile;
		
		parent::Model();
		
		$this->Proc= 'sshd';
		$this->StartCmd= "/usr/sbin/sshd > $TmpFile 2>&1 &";
	}
	
	function ParseLogLine($logline, &$cols)
	{
		global $Re_Ip;

		if ($this->ParseSyslogLine($logline, $cols)) {
			$re_user= '((invalid user\s+|)(\S+))';
			$re_clientip= "($Re_Ip)";
			$re_num= '(\d+)';
			$re_type= '(.*)';

			// Failed password for invalid user soner from 81.215.105.114 port 27836 ssh2
			// Failed password for root from 81.215.105.114 port 29782 ssh2
			// Failed none for invalid user soner from 81.215.105.114 port 40401 ssh2
			$re= "/Failed\s+(.*)\s+for\s+$re_user\s+from\s+$re_clientip\s+port\s+$re_num\s+$re_type$/";
			if (preg_match($re, $logline, $match)) {
				$cols['Reason']= $match[1];
				$cols['User']= $match[4];
				$cols['IP']= $match[5];
				$cols['Type']= $match[7];
			}
			else {
				// Accepted password for root from 81.215.105.114 port 47179 ssh2
				// Accepted publickey for root from 81.215.105.114 port 58402 ssh2
				$re= "/Accepted\s+(.*)\s+for\s+$re_user\s+from\s+$re_clientip\s+port\s+$re_num\s+$re_type$/";
				if (preg_match($re, $logline, $match)) {
					$cols['User']= $match[4];
					$cols['IP']= $match[5];
					$cols['Type']= $match[7];
				}
			}
			return TRUE;
		}
		return FALSE;
	}
	
	function GetFileLineCount($file, $re= '')
	{
		$cmd= "/usr/bin/grep -a ' sshd\[' $file";
		if ($re !== '') {
			$re= escapeshellarg($re);
			$cmd.= " | /usr/bin/grep -a $re";
		}
		$cmd.= ' | /usr/bin/wc -l';
		
		// OpenBSD wc returns with leading blanks
		return trim($this->RunShellCommand($cmd));
	}

	function GetLogs($file, $end, $count, $re= '')
	{
		$cmd= "/usr/bin/grep -a ' sshd\[' $file";
		if ($re !== '') {
			$re= escapeshellarg($re);
			$cmd.= " | /usr/bin/grep -a $re";
		}
		$cmd.= " | /usr/bin/head -$end | /usr/bin/tail -$count";
		
		$lines= explode("\n", $this->RunShellCommand($cmd));
		
		$logs= array();
		foreach ($lines as $line) {
			unset($cols);
			if ($this->ParseLogLine($line, $cols)) {
				$logs[]= $cols;
			}
		}
		return serialize($logs);
	}
	
	function GetLiveLogs($file, $count, $re= '')
	{
		$cmd= "/usr/bin/grep -a ' sshd\[' $file";
		if ($re !== '') {
			$re= escapeshellarg($re);
			$cmd.= " | /usr/bin/grep -a $re";
		}
		$cmd.= " | /usr/bin/tail -$count";

		$lines= explode("\n", $this->RunShellCommand($cmd));
		
		$logs= array();
		foreach ($lines as $line) {
			if ($this->ParseLogLine($line, $cols)) {
				$logs[]= $cols;
			}
		}
		return serialize($logs);
	}
}

$ModelConfig = array(
    'Port' => array(
        'type' => PORT,
		),
    'Protocol' => array(
        'type' => UINT,
		),
    'AddressFamily' => array(
		),
    'ListenAddress' => array(
        'type' => IP,
		),
    'ServerKeyBits' => array(
        'type' => UINT,
		),
    'SyslogFacility' => array(
		),
    'LogLevel' => array(
		),
    'LoginGraceTime' => array(
		),
    'PermitRootLogin' => array(
        'type' => STR_yes_no,
		),
    'MaxAuthTries' => array(
        'type' => UINT,
		),
    'PermitEmptyPasswords' => array(
        'type' => STR_yes_no,
		),
    'PrintMotd' => array(
        'type' => STR_yes_no,
		),
    'PrintLastLog' => array(
        'type' => STR_yes_no,
		),
    'TCPKeepAlive' => array(
        'type' => STR_yes_no,
		),
    'UseDNS' => array(
        'type' => STR_yes_no,
		),
    'PidFile' => array(
		),
    'MaxStartups' => array(
		),
    'Banner' => array(
		),
    'Subsystem\s+sftp' => array(
		),
);
?>
