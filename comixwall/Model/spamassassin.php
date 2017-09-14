<?php
/* $ComixWall: spamassassin.php,v 1.15 2009/11/23 17:53:18 soner Exp $ */

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

class Spamassassin extends Model
{
	public $Name= 'spamassassin';
	public $User= 'root|_spamdaemon';
	
	private $confDir= '/etc/mail/spamassassin';
	private $re_LcSuffix= '/local-(\w+)\.cf$/';
	
	public $NVPS= '\h';
	public $ConfFile= '/etc/mail/spamassassin/local.cf';
	public $LogFile= '/var/log/maillog';
	
	/// Have to unset LC_ALL and LANG, otherwise perl complains
	public $VersionCmd= 'unset LC_ALL; unset LANG; /usr/local/bin/spamd -V';

	public $PidFile= '/var/run/spamassassin.pid';

	public $StartCmd= 'unset LC_ALL; unset LANG; /usr/local/bin/spamd -L -d -x -u _spamdaemon -r /var/run/spamassassin.pid';

	function Spamassassin()
	{
		global $TmpFile;
		
		parent::Model();
		
		$this->Proc= 'spamd';

		$this->StartCmd= "unset LC_ALL; unset LANG; /usr/local/bin/spamd -L -d -x -u _spamdaemon -r /var/run/spamassassin.pid > $TmpFile 2>&1 &";
		
		$this->Commands= array_merge(
			$this->Commands,
			array(
				'GetCurrentLocale'	=> array(
					'argv'	=> array(),
					'desc'	=> _('Get current locale'),
					),
				
				'GetLocales'	=> array(
					'argv'	=> array(),
					'desc'	=> _('Get locales'),
					),
				
				'ChangeLocal'	=> array(
					'argv'	=> array(NAME),
					'desc'	=> _('Change spamassassin local'),
					),
				)
			);
	}

	function GetVersion()
	{
		return $this->RunShellCommand($this->VersionCmd.' | /usr/bin/head -2');
	}
	
	function Stop()
	{
		return $this->Kill();
	}
	
	/** Gets current locale
	 */
	function GetCurrentLocale()
	{
		$localcf= $this->confDir.'/local.cf';
		
		if (is_link($localcf) && ($linkedfile= readlink($localcf))) {
			if (preg_match($this->re_LcSuffix, $linkedfile, $match)) {
				return $match[1];
			}
		}
		return FALSE;
	}
	
	/** Gets locale files
	 */
	function GetLocales()
	{
		$files= $this->GetFiles($this->confDir.'/local-*.cf');
		$files= explode("\n", $files);

		$locales= array();
		foreach ($files as $file) {
			if (!is_dir($file) && !preg_match('/.*\.(bak|orig)$/', $file)) {
				if (preg_match($this->re_LcSuffix, $file, $match)) {
					$locales[]= $match[1];
				}
			}
		}
		return implode("\n", $locales);
	}
	
	/** Change local.cf link
	 */
	function ChangeLocal($locale)
	{
		return $this->RunShellCommand("cd $this->confDir; /bin/ln -sf local-$locale.cf local.cf");
	}
	
	function ParseLogLine($logline, &$cols)
	{
		if ($this->ParseSyslogLine($logline, $cols)) {
			if (stripos($cols['Log'], 'clean message ') !== FALSE) {
				$cols['Ham']= 1;
			}
			else if (stripos($cols['Log'], 'identified spam ') !== FALSE) {
				$cols['Spam']= 1;
			}

			$re= '/^.* for (\S+):\S+ in (\d+\.\d+) seconds, (\d+) bytes\.$/';
			if (preg_match($re, $logline, $match)) {
				$cols['User']= $match[1];
				$cols['Seconds']= $match[2];
				$cols['Bytes']= $match[3];
			}
			return TRUE;
		}
		return FALSE;
	}
	
	function GetFileLineCount($file, $re= '')
	{
		$cmd= "/usr/bin/grep -a ' spamd\[' $file";
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
		$cmd= "/usr/bin/grep -a ' spamd\[' $file";
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
		$cmd= "/usr/bin/grep -a ' spamd\[' $file";
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
    'rewrite_header Subject' => array(
		),
    'report_safe' => array(
        'type' => UINT_0_2,
		),
    'trusted_networks' => array(
        'type' => IP,
		),
    'lock_method' => array(
		),
    'required_score' => array(
        'type' => FLOAT,
		),
    'use_bayes' => array(
        'type' => UINT_0_1,
		),
    'bayes_path' => array(
		),
    'bayes_auto_learn' => array(
        'type' => UINT_0_1,
		),
    'bayes_ignore_header X-Bogosity' => array(
        'type' => FALSE,
		),
    'bayes_ignore_header X-Spam-Flag' => array(
        'type' => FALSE,
		),
    'bayes_ignore_header X-Spam-Status' => array(
        'type' => FALSE,
		),
);
?>
