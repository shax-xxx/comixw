<?php
/* $ComixWall: clamd.php,v 1.13 2009/11/20 12:01:39 soner Exp $ */

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

class Clamd extends Model
{
	public $Name= 'clamd';
	public $User= '_clamav';
	
	public $NVPS= '\h';
	public $ConfFile= '/etc/clamd.conf';
	public $LogFile= '/var/log/clamd.log';
	
	public $VersionCmd= '/usr/local/sbin/clamd -V';
	
	public $PidFile= '/var/run/clamav/clamd.pid';
	
	function Clamd()
	{
		global $TmpFile;
		
		parent::Model();
		
		$this->StartCmd= "/usr/local/sbin/clamd -c /etc/clamd.conf > $TmpFile 2>&1 &";
	}
		
	/// @todo clamd and freshclam log lines contain the number of virus defs.
	function ParseLogLine($logline, &$cols)
	{
		//Thu Sep 24 14:20:02 2009 -> SelfCheck: Database status OK.
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
		return FALSE;
	}
}

$ModelConfig = array(
    'SelfCheck' => array(
        'type' => UINT,
		),
    'LeaveTemporaryFiles' => array(
        'type' => STR_yes_no,
		),
    'LogClean' => array(
        'type' => STR_yes_no,
		),
    'ScanMail' => array(
        'type' => STR_yes_no,
		),
    'ScanPE' => array(
        'type' => STR_yes_no,
		),
    'DetectBrokenExecutables' => array(
        'type' => STR_yes_no,
		),
    'ScanHTML' => array(
        'type' => STR_yes_no,
		),
    'ScanArchive' => array(
        'type' => STR_yes_no,
		),
    'ScanRAR' => array(
        'type' => STR_yes_no,
		),
    'ScanOLE2' => array(
        'type' => STR_yes_no,
		),
    'MailFollowURLs' => array(
        'type' => STR_yes_no,
		),
    'MaxDirectoryRecursion' => array(
        'type' => UINT,
		),
    'FollowDirectorySymlinks' => array(
        'type' => STR_yes_no,
		),
    'FollowFileSymlinks' => array(
        'type' => STR_yes_no,
		),
    'ArchiveMaxFileSize' => array(
		),
    'ArchiveMaxRecursion' => array(
        'type' => UINT,
		),
    'ArchiveMaxFiles' => array(
        'type' => UINT,
		),
    'ArchiveMaxCompressionRatio' => array(
        'type' => UINT,
		),
    'ArchiveLimitMemoryUsage' => array(
        'type' => STR_yes_no,
		),
    'ArchiveBlockEncrypted' => array(
        'type' => STR_yes_no,
		),
    'ArchiveBlockMax' => array(
        'type' => STR_yes_no,
		),
    'MaxThreads' => array(
        'type' => UINT,
		),
    'LogVerbose' => array(
        'type' => STR_yes_no,
		),
    'Debug' => array(
        'type' => STR_yes_no,
		),
);
?>
