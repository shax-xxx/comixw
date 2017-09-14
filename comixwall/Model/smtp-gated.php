<?php
/* $ComixWall: smtp-gated.php,v 1.18 2009/11/17 10:57:23 soner Exp $ */

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

class Smtpgated extends Model
{
	public $Name= 'smtp-gated';
	public $User= '_smtp-gated';
	
	public $NVPS= '\h';
	public $ConfFile= '/etc/smtp-gated.conf';
	public $LogFile= '/var/log/smtp-gated.log';
	
	public $VersionCmd= '/usr/local/sbin/smtp-gated -v';
	
	public $PidFile= '/var/run/smtp-gated/smtp-gated.pid';
	
	function Smtpgated()
	{
		global $TmpFile;
		
		parent::Model();
		
		$this->StartCmd= "/usr/local/sbin/smtp-gated /etc/smtp-gated.conf > $TmpFile 2>&1 &";
	}

	function GetVersion()
	{
		$version= explode("\n", $this->RunShellCommand($this->VersionCmd.' | /usr/bin/head -4'));
		return $version[1]."\n".$version[3];
	}
					
	function ParseLogLine($logline, &$cols)
	{
		global $Re_Ip;

		if ($this->ParseSyslogLine($logline, $cols)) {
			$re_scanner= '(SCAN|SPAM)';
			$re_result= '(\S+)';
			$re_nonempty= '(\S+)';
			$re_num= '(\d+)';
			$re_srcip= "($Re_Ip)";
			$re_result= '(.*|)';
		
			//SCAN:CLEAN size=1639, time=0, src=192.168.1.1, ident=
			//SPAM:CLEAN size=337, time=0, src=192.168.1.1, ident=, score=2.900000
			//SCAN:VIRUS size=1065, time=0, src=192.168.1.1, ident=, virus=Eicar-Test-Signature
			$re= "/$re_scanner:$re_result\s+size=$re_num,\s+time=$re_num,\s+src=$re_srcip,\s+ident=.*$/";
			if (preg_match($re, $logline, $match)) {
				$cols['Scanner']= $match[1];
				$cols['Result']= $match[2];
				$cols['Bytes']= $match[3];
				$cols['ScanSrcIP']= $match[5];
			}
		
			$re= "/$re_scanner:$re_result\s+size=$re_num,\s+src=$re_srcip,\s+ident=.*$/";
			if (preg_match($re, $logline, $match)) {
				$cols['Scanner']= $match[1];
				$cols['Result']= $match[2];
				$cols['Bytes']= $match[3];
				$cols['ScanSrcIP']= $match[4];
			}
		
			//CLOSE by=server, rcv=442/286, trns=1, rcpts=1, auth=0, time=140183437574146, src=192.168.1.1, ident=
			$re= "/CLOSE\s+by=$re_nonempty,\s+rcv=$re_num\/$re_num,\s+trns=$re_num,\s+rcpts=$re_num,\s+auth=$re_num,\s+time=$re_num,\s+src=$re_srcip,\s+ident=.*$/";
			if (preg_match($re, $logline, $match)) {
				$cols['ClosedBy']= $match[1];
				$cols['Xmted']= $match[2];
				$cols['Rcved']= $match[3];
				$cols['Trns']= $match[4];
				$cols['Rcpts']= $match[5];
				$cols['Seconds']= $match[7];
				$cols['SrcIP']= $match[8];
			}
		
			//SESSION TAKEOVER: src=192.168.1.1, ident=, trns=1, reason=Malware found (Eicar-Test-Signature)
			$re= "/SESSION\s+TAKEOVER:\s+src=$re_srcip,\s+\S+,\s+\S+,\s+reason=$re_result$/";
			if (preg_match($re, $logline, $match)) {
				$cols['SrcIP']= $match[1];
				$cols['STReason']= $match[2];
				$cols['ClosedBy']= 'proxy';
			}
		
			//LOCK:LOCKED src=192.168.1.1, ident=-
			$re= "/LOCK:LOCKED\s+src=$re_srcip,.*$/";
			if (preg_match($re, $logline, $match)) {
				$cols['SrcIP']= $match[1];
				$cols['LockedIP']= $cols['SrcIP'];
				$cols['STReason']= 'Locked';
				$cols['ClosedBy']= 'proxy';
			}

			$re_result= '(RCPT\s+TO:|rejected)';
			$re_sender= '(<\S*>|)';
			$re_recipient= '(<\S*>|)';
			$re_retcode= '(\[\d+\]|\d+)';
			//MAIL FROM <soner@comixwall.org> RCPT TO: 250<soner@comixwall.org>
			$re= "/MAIL\s+FROM\s+$re_sender\s+$re_result\s+$re_retcode$re_recipient$/";
			if (preg_match($re, $logline, $match)) {
				$cols['Sender']= trim($match[1], '<>');
				$cols['RResult']= $match[2].' '.$match[3];
				$cols['Recipient']= trim($match[4], '<>');
			}

			$re= "/^$re_result\s+$re_retcode$re_recipient.*$/";
			if (preg_match($re, $cols['Log'], $match)) {
				$cols['RResult']= $match[1].' '.$match[2];
				$cols['Recipient']= trim($match[3], '<>');
			}
			return TRUE;
		}
		return FALSE;
	}
}

$ModelConfig = array(
    'proxy_name' => array(
		),
    'lock_on' => array(
		),
    'lock_duration' => array(
        'type' => UINT,
		),
    'abuse' => array(
		),
    'priority' => array(
        'type' => UINT,
		),
    'max_connections' => array(
        'type' => UINT,
		),
    'max_per_host' => array(
        'type' => UINT,
		),
    'max_load' => array(
        'type' => UINT,
		),
    'scan_max_size' => array(
        'type' => UINT,
		),
    'spam_max_size' => array(
        'type' => UINT,
		),
    'spam_max_load' => array(
        'type' => UINT,
		),
    'spam_threshold' => array(
        'type' => UINT,
		),
    'ignore_errors' => array(
        'type' => UINT,
		),
    'spool_leave_on' => array(
		),
    'log_helo' => array(
		),
    'log_mail_from' => array(
		),
    'log_rcpt_to' => array(
		),
    'log_level' => array(
		),
    'nat_header_type' => array(
		),
    'locale' => array(
		),
);
?>
