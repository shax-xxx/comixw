<?php
/* $ComixWall: p3scan.php,v 1.16 2009/11/27 12:44:49 soner Exp $ */

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

class P3scan extends Model
{
	public $Name= 'p3scan';
	public $User= '_p3scan';
	
	const CONFIG_DIR= '/etc/p3scan';
	const RE_LC_SUFFIX= '/p3scan-(.*)\.mail$/';
	
	public $NVPS= '=';
	public $ConfFile= '/etc/p3scan/p3scan.conf';
	public $LogFile= '/var/log/p3scan.log';

	public $VersionCmd= '/usr/local/sbin/p3scan -v';

	public $PidFile= '/var/run/p3scan/p3scan.pid';
	
	function P3scan()
	{
		global $TmpFile;
		
		parent::Model();
		
		$this->StartCmd= "/usr/local/sbin/p3scan -f /etc/p3scan/p3scan.conf > $TmpFile 2>&1 &";
	
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
					'desc'	=> _('Change p3scan report language'),
					),
				)
			);
	}

	function GetVersion()
	{
		return $this->RunShellCommand($this->VersionCmd.' | /usr/bin/head -2 | /usr/bin/tail -1');
	}
					
	/** Gets current locale
	 */
	function GetCurrentLocale()
	{
		$localcf= self::CONFIG_DIR.'/p3scan.mail';
		
		if (is_link($localcf) && ($linkedfile= readlink($localcf))) {
			if (preg_match(self::RE_LC_SUFFIX, $linkedfile, $match)) {
				return $match[1];
			}
		}
		return FALSE;
	}
	
	/** Gets list of locale files
	 */
	function GetLocales()
	{
		$files= $this->GetFiles(self::CONFIG_DIR.'/p3scan-*.mail');
		$files= explode("\n", $files);

		$locales= array();
		foreach ($files as $file) {
			if (!is_dir($file) && !preg_match('/.*\.(bak|orig)$/', $file)) {
				if (preg_match(self::RE_LC_SUFFIX, $file, $match)) {
					$locales[]= $match[1];
				}
			}
		}
		return implode("\n", $locales);
	}
	
	/** Changes local.cf link to given locale
	 *
	 * @param[in]	$locale	string Locale name, e.g. Turkish.
	 */
	function ChangeLocal($locale)
	{
		return $this->RunShellCommand('/bin/ln -sf '.self::CONFIG_DIR."/p3scan-$locale.mail ".self::CONFIG_DIR."/p3scan.mail");
	}
	
	function ParseLogLine($logline, &$cols)
	{
		global $Re_Ip;

		if ($this->ParseSyslogLine($logline, $cols)) {
			$re= "/USER '(.*)'$/";
			if (preg_match($re, $logline, $match)) {
				$cols['User']= $match[1];
			}
			else {
				$re_clientip= "($Re_Ip)";
				$re_num= '(\d+)';

				$re= "/Connection from $re_clientip:$re_num$/";
				if (preg_match($re, $logline, $match)) {
					$cols['SrcIP']= $match[1];
				}
				else {
					$re= "/Real-server address is $re_clientip:$re_num$/";
					if (preg_match($re, $logline, $match)) {
						$cols['DstIP']= $match[1];
					}
					else {
						$re_result= '(.*)';

						$re= "/Session done \($re_result\). Mails: $re_num Bytes: $re_num$/";
						if (preg_match($re, $logline, $match)) {
							$cols['Result']= $match[1];
							$cols['Mails']= $match[2];
							$cols['Bytes']= $match[3];
						}
						else {
							// POP3 from 192.168.10.2:47845 to 10.0.0.10:110 from Soner Tari <soner@comixwall.org> to soner@comixwall.org user: soner virus: Eicar-Test-Signature file: /p3scan.8c0Ph
							$re= "/POP3 from $Re_Ip:\d+ to $Re_Ip:\d+ from (.+) to (.+) user: .+ virus: (.+) file:.*$/";
							if (preg_match($re, $logline, $match)) {
								$cols['From']= $match[1];
								$cols['To']= $match[2];
								$cols['Virus']= $match[3];
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
    'checkspam' => array(
        'type' => FALSE,
		),
    'justdelete' => array(
        'type' => FALSE,
		),
    'maxchilds' => array(
        'type' => UINT,
		),
    'bytesfree' => array(
        'type' => UINT,
		),
    'debug' => array(
        'type' => FALSE,
		),
    'quiet' => array(
        'type' => FALSE,
		),
);
?>
