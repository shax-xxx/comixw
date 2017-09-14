<?php
/* $ComixWall: snort.php,v 1.26 2009/11/25 23:43:18 soner Exp $ */

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

class Snort extends Model
{
	public $Name= 'snort';
	public $User= '_snort';
	
	public $NVPS= '\h';
	public $ConfFile= '/etc/snort/snort.conf';
	public $LogFile= '/var/log/snort/snort.log';
	
	public $VersionCmd= '/usr/local/bin/snort -V 2>&1';

	private $re_RulePrefix= 'include\h*\$(RULE_PATH|PREPROC_RULE_PATH)\/';

	function Snort()
	{
		parent::Model();
		
		$this->Commands= array_merge(
			$this->Commands,
			array(
				'Start'=>	array(
					'argv'	=>	array(NAME),
					'desc'	=>	_('Start snort'),
					),

				'StopProcess'=> array(
					'argv'	=> array(NAME),
					'desc'	=> _('Stop Snort instance'),
					),

				'GetRules'		=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get rules'),
					),
				
				'GetDisabledRules'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get disabled rules'),
					),

				'DisableRule'	=>	array(
					'argv'	=>	array(NAME),
					'desc'	=>	_('Disable rule'),
					),

				'EnableRule'	=>	array(
					'argv'	=>	array(NAME),
					'desc'	=>	_('Enable rule'),
					),

				'MoveRuleUp'=>	array(
					'argv'	=>	array(NAME),
					'desc'	=>	_('Move rule up'),
					),

				'MoveRuleDown'=>	array(
					'argv'	=>	array(NAME),
					'desc'	=>	_('Move rule down'),
					),

				'SetStartupIfs'	=>	array(
					'argv'	=>	array(NAME, NAME),
					'desc'	=>	_('Set startup ifs'),
					),
				)
			);
	}

	function GetVersion()
	{
		return $this->RunShellCommand($this->VersionCmd.' | /usr/bin/head -3 | /usr/bin/tail -2');
	}

	function Start($if)
	{
		global $TmpFile;

		$cmd= "/usr/local/bin/snort -i $if -D -d -c $this->ConfFile -u _snort -g _snort -b -l /var/snort/log";
		$count= 0;
		while ($count++ < self::PROC_STAT_TIMEOUT) {
			if ($this->IsInstanceRunning($if)) {
				return TRUE;
			}
			$this->RunShellCommand("$cmd > $TmpFile 2>&1");
			/// @todo Check $TmpFile for error messages, if so break out instead
			exec('/bin/sleep .1');
		}

		/// Start command is redirected to tmp file
		$output= file_get_contents($TmpFile);
		ViewError($output);
		cwc_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Start failed with: $output");

		// Check one last time due to the last sleep in the loop
		return $this->IsInstanceRunning($if);
	}

	function Stop()
	{
		global $TmpFile;

		$cmd= '/usr/bin/pkill -U_snort';

		$count= 0;
		while ($count++ < self::PROC_STAT_TIMEOUT) {
			if (!$this->IsInstanceRunning('\w+')) {
				return TRUE;
			}
			$this->RunShellCommand("$cmd > $TmpFile 2>&1");
			/// @todo Check $TmpFile for error messages, if so break out instead
			exec('/bin/sleep .1');
		}

		/// Kill command is redirected to tmp file
		$output= file_get_contents($TmpFile);
		ViewError($output);
		cwc_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Kill failed with: $output");

		// Check one last time due to the last sleep in the loop
		return !$this->IsInstanceRunning('\w+');
	}

	/** Stops snort process started with the given interface.
	 *
	 * @param[in]	$if	string Interface name.
	 */
	function StopProcess($if)
	{
		$pid= $this->FindPid($if);
		if ($pid > -1) {
			return $this->KillPid($pid);
		}
		return TRUE;
	}

	/** Finds the pid of snort process started with the given inf.
	 *
	 * @param[in]	$if	string Interface name.
	 * @return int Pid or -1 if not running
	 */
	function FindPid($if)
	{
		$pidcmd= "/bin/ps arwwx | /usr/bin/grep snort | /usr/bin/grep '$if' | /usr/bin/grep -v -e cwc.php -e grep";
		exec($pidcmd, $output, $retval);

		foreach ($output as $psline) {
			$re= '/\h+-i\h+(\w+)\b/';
			if (preg_match($re, $psline)) {
				$re= '/^\s*(\d+)\s+/';
				if (preg_match($re, $psline, $match)) {
					if ($match[1] !== '') {
						return $match[1];
					}
				}
			}
		}
		return -1;
	}

	/** Checks if the process(es) is running.
	 *
	 * Uses ps with -U option.
	 *
	 * @param[in]	$if	string Interface name
	 * @return boolean TRUE if running
	 */
	function IsInstanceRunning($if)
	{
		$re= "\/usr\/local\/bin\/snort\s+[^\n]*-i\s+$if\s+[^\n]*";

		$output= $this->RunShellCommand('/bin/ps arwwx -U_snort');
		if (preg_match("/$re/m", $output)) {
			return TRUE;
		}
		return FALSE;
	}

	function SetConfig($confname)
	{
		global $basicConfig, $advancedConfig;

		$this->Config= ${$confname};
	}

	/** Get list of enabled/uncommented rules.
	 * @return Rule or list of rules.
	 */
	function GetRules()
	{
		return $this->SearchFileAll($this->ConfFile, "/^\h*$this->re_RulePrefix([^#\s]+)\h*$/m", 2);
	}

	/** Gets list of disabled/commented rules.
	 * @return Rule or list of rules.
	 */
	function GetDisabledRules()
	{
		return $this->SearchFileAll($this->ConfFile, "/^\h*$this->COMC\h*$this->re_RulePrefix([^#\s]+)\b\h*$/m", 2);
	}

	function EnableRule($rule)
	{
		return $this->EnableName($this->ConfFile, "$this->re_RulePrefix$rule");
	}

	function DisableRule($rule)
	{
		return $this->DisableName($this->ConfFile, "$this->re_RulePrefix$rule");
	}

	function MoveRuleUp($rule)
	{
		$rule= Escape($rule, '/.');
		return $this->ReplaceRegexp($this->ConfFile, "/^\h*(($this->re_RulePrefix[^\n]+)\n+($this->COMC\h*$this->re_RulePrefix[^\n]+\n+)*)\h*($this->re_RulePrefix$rule)\n/m", '${6}'."\n".'${1}');
	}

	function MoveRuleDown($rule)
	{
		$rule= Escape($rule, '/.');
		return $this->ReplaceRegexp($this->ConfFile, "/^\h*($this->re_RulePrefix$rule)\n+\h*(($this->COMC\h*$this->re_RulePrefix[^\n]+\n+)*($this->re_RulePrefix[^\n]+)\n+)/m", '${3}'.'${1}'."\n");
	}

	function SetStartupIfs($lanif, $wanif)
	{
        $re= '|(\h*/usr/local/bin/snort\h+.*-i\h+)(\w+\d+)(\h+.*\h+/usr/local/bin/snort\h+.*-i\h+)(\w+\d+)(\h+.*)|ms';
		return $this->ReplaceRegexp('/etc/rc.local', $re, '${1}'.$lanif.'${3}'.$wanif.'${5}');
	}
}

/** Basic configuration.
 */
$basicConfig = array(
    'var HOME_NET' => array(
		),
    'var EXTERNAL_NET' => array(
		),
    'var DNS_SERVERS' => array(
		),
    'var SMTP_SERVERS' => array(
		),
    'var HTTP_SERVERS' => array(
		),
    'var SQL_SERVERS' => array(
		),
    'var TELNET_SERVERS' => array(
		),
    'var SNMP_SERVERS' => array(
		),
    'portvar SSH_PORTS' => array(
		),
    'portvar HTTP_PORTS' => array(
		),
    'portvar SHELLCODE_PORTS' => array(
		),
    'var RULE_PATH' => array(
		),
);

/** Advanced configuration.
 */
$advancedConfig = array(
    'config disable_decode_alerts' => array(
        'type' => FALSE,
		),
    'config disable_tcpopt_experimental_alerts' => array(
        'type' => FALSE,
		),
    'config disable_tcpopt_obsolete_alerts' => array(
        'type' => FALSE,
		),
    'config disable_tcpopt_ttcp_alerts' => array(
        'type' => FALSE,
		),
    'config disable_tcpopt_alerts' => array(
        'type' => FALSE,
		),
    'config disable_ipopt_alerts' => array(
        'type' => FALSE,
		),
    'preprocessor frag3_global: max_frags' => array(
        'type' => UINT,
		),
    'preprocessor bo' => array(
        'type' => FALSE,
		),
    'preprocessor telnet_decode' => array(
        'type' => FALSE,
		),
    'include classification.config' => array(
        'type' => FALSE,
		),
    'include reference.config' => array(
        'type' => FALSE,
		),
);
?>
