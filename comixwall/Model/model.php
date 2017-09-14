<?php
/* $ComixWall: model.php,v 1.138 2009/11/28 17:06:51 soner Exp $ */

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
 * Base class for all models, and master configuration table.
 */

class Model
{
	public $Name= '';
	public $User= '';
	
	/// @attention Should be updated in constructors of children
	public $Proc= '';

	public $NVPS= '=';
	public $COMC= '#';
	public $ConfFile= '';
	public $Config= '';
	
	public $LogFile= '';
	
	protected $psCmd= '/bin/ps arwwx -o pid,start,%cpu,time,%mem,rss,vsz,stat,pri,nice,tty,user=Long_User_Name,group=Long_Group_Name,command | /usr/bin/grep -E <PROC>';

	/// Max number of iterations to try while starting or stopping processes.
	const PROC_STAT_TIMEOUT= 100;
	
	private $confDir= '/etc/';
	
	/// web server password file pathname.
	protected $passwdFile= '/var/www/conf/.htpasswd';

	public $CmdLogStart= '/usr/bin/head -1 <LF>';

	public $VersionCmd= '';

	public $StartCmd= '';
	public $PidFile= '';

	public $TmpLogsDir= '';

	protected $newSyslogConf= '/etc/newsyslog.conf';
	protected $rcConfLocal= '/etc/rc.conf.local';
	
	
	public $PfRulesFile= '/etc/pf.conf';
	
	/** Argument lists and descriptions of commands.
	 *
	 * @param[out] argv	Array of arg types in order
	 * @param[out] desc	Description of the shell function
	 *
	 * @todo $Commands should be implemented with Interfaces in OOP?
	 */
	public $Commands= array();

	function Model()
	{
		global $ModelConfig;
		
		$this->Proc= $this->Name;
		
		$this->Config= $ModelConfig;
		
		$this->TmpLogsDir= '/var/tmp/comixwall/logs/'.get_class($this).'/';
		
		$this->Commands= array_merge(
			$this->Commands,
			array(
				'GetIntIf'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get int_if'),
					),
				
				'GetExtIf'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get ext_if'),
					),
				
				'GetVersion'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get software version'),
					),

				'Reload'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Reload '.get_class($this)),
					),
				
				'Start'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Start '.get_class($this)),
					),
				
				'Restart'	=> array(
					'argv'	=> array(),
					'desc'	=> _('Restart '.get_class($this)),
					),

				'Stop'	=>	array(
					'argv'	=>	array(),
					'desc'	=> _('Stop '.get_class($this)),
					),
				
				'IsRunning'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Check if process running'),
					),

				'GetProcList'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get process list'),
					),

				'GetServiceStatus'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get service status'),
					),
				
				'GetAllStats'=>	array(
					'argv'	=>	array(FILEPATH, NAME|EMPTYSTR),
					'desc'	=>	_('Get all stats'),
					),
				
				'GetStats'=>	array(
					'argv'	=>	array(FILEPATH, SERIALARRAY, NAME|EMPTYSTR),
					'desc'	=>	_('Get stats'),
					),
				
				'GetPhyIfs'		=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('List physical interfaces'),
					),

				'GetFileCvsTag'=>	array(
					'argv'	=>	array(FILEPATH),
					'desc'	=>	_('Get source file CVS tag'),
					),

				'GetDefaultLogFile'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get log file'),
					),

				'SelectLogFile'	=>	array(
					'argv'	=>	array(FILEPATH|EMPTYSTR),
					'desc'	=>	_('Select log file'),
					),

				'GetLogFilesList'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get log files list'),
					),

				'GetProcStatLines'	=>	array(
					'argv'	=>	array(FILEPATH|NONE),
					'desc'	=>	_('Get stat lines'),
					),
							
				'GetSysCtl'	=>	array(
					'argv'	=>	array(NAME),
					'desc'	=>	_('Get sysctl values'),
					),

				'CheckAuthentication'	=>	array(
					'argv'	=>	array(NAME, SHA1STR),
					'desc'	=>	_('Check authentication'),
					),
				
				'GetConfigValues'	=>	array(
					'argv'	=>	array(NAME|EMPTYSTR, NUM|EMPTYSTR),
					'desc'	=>	_('Get config values'),
					),

				'SetConfValue'	=>	array(
					/// @todo Is there any pattern or size for new value, 2nd param?
					'argv'	=>	array(CONFNAME, STR, NAME|EMPTYSTR, NUM|EMPTYSTR),
					'desc'	=>	_('Set name value pair'),
					),

				'EnableConf'	=>	array(
					'argv'	=>	array(CONFNAME, NAME|EMPTYSTR, NUM|EMPTYSTR),
					'desc'	=>	_('Enable config'),
					),

				'DisableConf'	=>	array(
					'argv'	=>	array(CONFNAME, NAME|EMPTYSTR, NUM|EMPTYSTR),
					'desc'	=>	_('Disable config'),
					),

				'GetLogStartDate'	=>	array(
					'argv'	=>	array(FILEPATH),
					'desc'	=>	_('Get log start date'),
					),

				'GetFileLineCount'	=>	array(
					'argv'	=>	array(FILEPATH, REGEXP|NONE),
					'desc'	=>	_('Gets line count'),
					),

				'GetLogs'	=>	array(
					'argv'	=>	array(FILEPATH, NUM, NUM, REGEXP|NONE),
					'desc'	=>	_('Get lines'),
					),

				'GetLiveLogs'	=>	array(
					'argv'	=>	array(FILEPATH, NUM, REGEXP|NONE),
					'desc'	=>	_('Get tail'),
					),

				'PrepareFileForDownload'	=>	array(
					'argv'	=>	array(FILEPATH),
					'desc'	=>	_('Prepare file for download'),
					),
				
				'GetAllowedIps'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get PF allowed'),
					),

				'AddAllowedIp'	=>	array(
					'argv'	=>	array(IPADR|IPRANGE),
					'desc'	=>	_('Set PF allowed'),
					),

				'DelAllowedIp'	=>	array(
					'argv'	=>	array(IPADR|IPRANGE),
					'desc'	=>	_('Delete PF allowed'),
					),

				'GetRestrictedIps'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get PF restricted'),
					),

				'AddRestrictedIp'=>	array(
					'argv'	=>	array(IPADR|IPRANGE),
					'desc'	=>	_('Set PF restricted'),
					),

				'DelRestrictedIp'=>	array(
					'argv'	=>	array(IPADR|IPRANGE),
					'desc'	=>	_('Delete PF restricted'),
					),
				)
			);
	}
	
	/** Get int_if.
	 */
	function GetIntIf()
	{
		return $this->GetNVP($this->PfRulesFile, 'int_if');
	}

	/** Get ext_if.
	 */
	function GetExtIf()
	{
		return $this->GetNVP($this->PfRulesFile, 'ext_if');
	}

	/** Restart module processes.
	 */
	function Restart()
	{
	}

	/** Builds generic grep command to extract log lines.
	 *
	 * The purpose is to get the lines with dates in $re_datetime.
	 *
	 * @param[in]	$logfile	string Log file pathname
	 * @param[in]	$tail		int Tail len, new log lines to update stats with
	 */
	function GetStatsLogLines($logfile, $tail= -1)
	{
		global $Modules;

		$cmd= $Modules[$this->Name]['Stats']['Total']['Cmd'];

		if ($tail > -1) {
			$cmd.= " | /usr/bin/tail -$tail";
		}

		$cmd= preg_replace('/<LF>/', $logfile, $cmd);

		return $this->RunShellCommand($cmd);
	}
	
	/** Searches a given file with a given regexp.
	 *
	 * @param[in]	$file	string Config file
	 * @param[in]	$re		string Regexp to search the file with, should have end markers
	 * @param[in]	$set	int There may be multiple parentheses in $re, which one to return
	 * @param[in]	$trimchars If given, these chars are trimmed
	 * @return String found or FALSE if no match
	 */
	function SearchFile($file, $re, $set= 1, $trimchars= '')
	{
		/// @todo What to do multiple matching NVPs
		if (preg_match($re, file_get_contents($file), $match)) {
			$retval= $match[$set];
			if ($trimchars !== '') {
				$retval= trim($retval, $trimchars);
			}
			return rtrim($retval);
		}
		return FALSE;
	}

	/** Multi-searches a given file with a given regexp.
	 *
	 * @param[in]	$file	string Config file
	 * @param[in]	$re		string Regexp to search the file with, should have end markers
	 * @param[in]	$set	int There may be multiple parentheses in $re, which one to return
	 * @return		String found or FALSE if no match
	 */
	function SearchFileAll($file, $re, $set= 1)
	{
		/// @todo What to do multiple matching NVPs
		if (preg_match_all($re, file_get_contents($file), $match)) {
			return implode("\n", array_values($match[$set]));
		}
		return FALSE;
	}

	/** Search and replace.
	 *
	 * @param[in]	$file		string Config file.
	 * @param[in]	$matchre	string Match re.
	 * @param[in]	$replacere	string Replace re.
	 */
	function ReplaceRegexp($file, $matchre, $replacere)
	{
		if (copy($file, $file.'.bak')) {
			$contents= preg_replace($matchre, $replacere, file_get_contents($file), 1, $count);
			if ($contents !== NULL && $count === 1) {
				file_put_contents($file, $contents);
				return TRUE;
			}
			else {
				cwc_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "Cannot replace in $file");
			}
		}
		else {
			cwc_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Cannot copy file $file");
		}
		return FALSE;
	}

	/** Runs given shell command and returns its output as string.
	 *
	 * @param[in]	$cmd	Command line to run.
	 * @return Command result in a string.
	 */
	function RunShellCommand($cmd)
	{
		/// @attention Do not use shell_exec() here, because it is disabled when PHP is running in safe_mode
		/// @warning Not all shell commands return 0 on success, such as grep, date...
		/// Hence, do not check return value
		exec($cmd, $output);
		if (is_array($output)) {
			return implode("\n", $output);
		}
		return '';
	}

	/** Appends a string to a file.
	 *
	 * @param[in]	$file	Config file pathname
	 * @param[in]	$line	Line to add
	 */
	function AppendToFile($file, $line)
	{
		if (copy($file, $file.'.bak')) {
			$contents= file_get_contents($file).$line."\n";
			file_put_contents($file, $contents);
			return TRUE;
		}
		else {
			cwc_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Cannot copy file $file");
		}
		return FALSE;
	}

	/** Returns files with the given filepath pattern.
	 *
	 * $filepath does not have to be just directory path, and may contain wildcards.
	 *
	 * @param[in]	$filepath	string Pattern
	 * @return List of file names, without path
	 */
	function GetFiles($filepath)
	{
		return $this->RunShellCommand("ls -1 $filepath");
	}

	/** Returns all configuration for a given configuration type.
	 *
	 * @param[in]	$conf	string Config type
	 * @param[in]	$group	int E2guardian group
	 * @return serialized Array of config items
	 */
	function GetConfigValues($conf, $group)
	{
		$this->SetConfig($conf);

		$values= array();
		foreach ($this->Config as $name => $config) {
			if (($output= $this->GetValue($name, $conf, $group)) !== FALSE) {
				$values[$name]= array(
					'Value' => $output,
					'Enabled' => TRUE,
					);
			}
			else if (($output= $this->GetDisabledValue($name, $conf, $group)) !== FALSE) {
				$values[$name]= array(
					'Value' => $output,
					'Enabled' => FALSE,
					);
			}
		}
		return serialize($values);
	}

	/** Returns all enabled configuration for a given configuration type.
	 *
	 * @param[in]	$name	string Config name
	 * @param[in]	$conf	string Config type
	 * @param[in]	$group	int E2guardian group
	 * @return serialized Array of config items
	 */
	function GetValue($name, $conf, $group)
	{
		$file= $this->GetConfFile($conf, $group);
		
		if ((isset($this->Config[$name]['type'])) && ($this->Config[$name]['type'] === FALSE)) {
			return $this->GetName($file, $name);
		}
		
		$value= $this->GetNVP($file, $name);
		return $value !== FALSE ? "$name=$value" : $value;
	}

	/** Returns all disabled configuration for a given configuration type.
	 */
	function GetDisabledValue($name, $conf, $group)
	{
		$file= $this->GetConfFile($conf, $group);
		
		if ((isset($this->Config[$name]['type'])) && ($this->Config[$name]['type'] === FALSE)) {
			return $this->GetDisabledName($file, $name);
		}
		
		$value= $this->GetDisabledNVP($file, $name);
		return $value !== FALSE ? "$name=$value" : $value;
	}
	
	/** Reads value of NVP.
	 *
	 * @param[in]	$file		string Config file
	 * @param[in]	$name		string Name of NVP
	 * @param[in]	$trimchars	string Chars to trim in the results
	 * @return Value of NVP or NULL on failure
	 */
	function GetNVP($file, $name, $trimchars= '')
	{
		return $this->SearchFile($file, "/^\h*$name\b\h*$this->NVPS\h*([^$this->COMC'\"\n]*|'[^'\n]*'|\"[^\"\n]*\"|[^$this->COMC\n]*)(\h*|\h*$this->COMC.*)$/m", 1, $trimchars);
	}

	/** Reads value of commented-out NVP.
	 *
	 * @param[in]	$file	Config file
	 * @param[in]	$name	Name of NVP
	 * @return Value of commented NVP or NULL on failure
	 */
	function GetDisabledNVP($file, $name)
	{
		return $this->SearchFile($file, "/^\h*$this->COMC\h*$name\b\h*$this->NVPS\h*([^$this->COMC'\"\n]*|'[^'\n]*'|\"[^\"\n]*\"|[^$this->COMC\n]*)(\h*|\h*$this->COMC.*)$/m");
	}

	/** Checks if Name exists.
	 *
	 * @param[in]	$file	Config file
	 * @param[in]	$name	Name of NVP
	 * @return Name or NULL on failure
	 */
	function GetName($file, $name)
	{
		return $this->SearchFile($file, "/^\h*($name)(\h*$this->COMC.*|\h*)$/m");
	}

	/** Checks if commented-out Name exists.
	 *
	 * @param[in]	$file	Config file
	 * @param[in]	$name	Name of NVP
	 * @return Commented Name or NULL on failure
	 */
	function GetDisabledName($file, $name)
	{
		return $this->SearchFile($file, "/^\h*$this->COMC\h*($name)(\h*$this->COMC.*|\h*)$/m");
	}

	/** Sets the value of NVP configuration.
	 *
	 * @param[in]	$name	string Config name
	 * @param[in]	$newvalue	new Config value
	 * @param[in]	$conf	string Config type
	 * @param[in]	$group	int E2guardian group
	 */
	function SetConfValue($name, $newvalue, $conf, $group)
	{
		$this->SetConfig($conf);
		if (isset($this->Config[$name]['type'])) {
			$re= $this->Config[$name]['type'];
		}
		else {
			$re= '.*';
		}

		if (preg_match("/^($re)$/", $newvalue)) {
			$file= $this->GetConfFile($conf, $group);
			return $this->SetNVP($file, $name, $newvalue);
		}
		ViewError(_('Invalid value').": $name: $newvalue");
		cwc_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "Configuration change failed, invalid value: $name: $newvalue");
		return FALSE;
	}

	/** Changes value of NVP.
	 *
	 * @param[in]	$file		Config file
	 * @param[in]	$name		Name of NVP
	 * @param[in]	$newvalue	New value to set
	 * @return boolean Success or failure
	 */
	function SetNVP($file, $name, $newvalue)
	{
		if (copy($file, $file.'.bak')) {
			if (($value= $this->GetNVP($file, $name)) !== FALSE) {
				/// @warning Backslash should be escaped first, or causes double escapes
				$value= Escape($value, '\/$^*()."');
				$re= "^(\h*$name\b\h*$this->NVPS\h*)($value)(\h*$this->COMC.*|\h*)$";

				$contents= preg_replace("/$re/m", '${1}'.$newvalue.'${3}', file_get_contents($file), 1, $count);
				if ($contents !== NULL && $count == 1) {
					file_put_contents($file, $contents);
					return TRUE;
				}
				else {
					cwc_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Cannot set new value $file, $name, $newvalue");
				}
			}
			else {
				cwc_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Cannot find NVP: $file, $name");
			}
		}
		else {
			cwc_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Cannot copy file $file");
		}
		return FALSE;
	}

	/** Enables a configuration item in conf file.
	 *
	 * Certain modules have multiple configuration files,
	 * hence they override this method.
	 *
	 * @param[in]	$name	string Config name
	 * @param[in]	$conf	string Config type
	 * @param[in]	$group	int E2guardian group
	 * @return boolean Result of action
	 */
	function EnableConf($name, $conf, $group)
	{
		$file= $this->GetConfFile($conf, $group);
		
		$this->SetConfig($conf);
		if ((isset($this->Config[$name]['type'])) && ($this->Config[$name]['type'] === FALSE)) {
			return $this->EnableName($file, $name);
		}
		return $this->EnableNVP($file, $name);
	}

	/** Disables a configuration item in conf file.
	 */
	function DisableConf($name, $conf, $group)
	{
		$file= $this->GetConfFile($conf, $group);
		
		$this->SetConfig($conf);
		if ((isset($this->Config[$name]['type'])) && ($this->Config[$name]['type'] === FALSE)) {
			return $this->DisableName($file, $name);
		}
		return $this->DisableNVP($file, $name);
	}

	/** Enables an NVP: configuration item with value.
	 *
	 * @param[in]	$file	string Config file
	 * @param[in]	$name	string Config name
	 */
	function EnableNVP($file, $name)
	{
		return $this->ReplaceRegexp($file, "/^\h*$this->COMC(\s*$name\b\s*$this->NVPS\s*.*)$/m", '${1}');
	}

	/** Enables a Name: configuration item without value.
	 */
	function EnableName($file, $name)
	{
		return $this->ReplaceRegexp($file, "/^\h*$this->COMC(\h*$name(\h*$this->COMC.*|\h*))$/m", '${1}');
	}

	/** Disables an NVP.
	 */
	function DisableNVP($file, $name)
	{
		return $this->ReplaceRegexp($file, "/^(\h*$name\b\s*$this->NVPS\s*.*)$/m", $this->COMC.'${1}');
	}

	/** Disables a Name.
	 */
	function DisableName($file, $name)
	{
		return $this->ReplaceRegexp($file, "/^(\h*$name(\h*$this->COMC.*|\h*))$/m", $this->COMC.'${1}');
	}

	/** Returns configuration file of the module.
	 *
	 * Certain modules have configuration divided into multiple pages/files,
	 * hence they override this method.
	 *
	 * @param[in]	$conf	string Config type
	 * @param[in]	$group	int E2guardian group
	 * @return Config file pathname
	 */
	function GetConfFile($conf, $group)
	{
		return $this->ConfFile;
	}

	/** Sets configuration file based on config type provided.
	 *
	 * Certain modules have configuration divided into multiple pages/files,
	 * hence they override this method.
	 *
	 * @param[in]	$confname	string Config type
	 */
	function SetConfig($confname)
	{
	}
	
	/** Extracts IP address assigned to an interface.
	 *
	 * @param[in]	$if	Interface name.
	 * @return IP of the interface.
	 */
	function GetIpAddr($if)
	{
		global $Re_Ip;

		if (file_exists($this->confDir."hostname.$if")) {
			return $this->SearchFile($this->confDir."hostname.$if", "/^\h*inet\h*($Re_Ip)\h*$Re_Ip\b.*$/m");
		}
		return FALSE;
	}

	/** Checks user's password supplied against the one in htpasswd file.
	 *
	 * @param[in]	$user	User name.
	 * @param[in]	$passwd	Password.
	 * @return TRUE if passwd matches, FALSE otherwise.
	 */
	function CheckAuthentication($user, $passwd)
	{
		syslog(LOG_NOTICE, $passwd);	
		/// @warning Args should never be empty, htpasswd expects 2 args
		$passwd= $passwd == '' ? "''" : $passwd;
		// For debug purpose
		cwc_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "original:  $passwd");

		/// @warning Have to trim newline chars, or passwds do not match
		$passwdfile= file($this->passwdFile, FILE_IGNORE_NEW_LINES);
		
		// Do not use preg_match() here. If there is more than one line (passwd) for a user in passwdFile,
		// this array method ensures that only one password apply to each user, the last one in passwdFile.
		// This should never happen actually, but in any case.
		$passwdlist= array();
		foreach ($passwdfile as $nvp) {
			list($u, $p)= explode(':', $nvp, 2);
			$passwdlist[$u]= $p;
		}
		if (password_verify($passwd, $passwdlist[$user])) {
			return TRUE;
		} else {
			ViewError("Password mismatch");
			return FALSE;
		}
	}

	/** Extracts the datetime of the first line in the log file.
	 *
	 * Works only on uncompressed log files.
	 *
	 * @param[in]	$file	string Log file pathname
	 * @return	string DateTime or warning if archive is compressed
	 */
	function GetLogStartDate($file)
	{
		if (preg_match('/.*\.gz$/', $file)) {
			$tmpfile= $this->GetTmpLogFileName($file);
			// Log file may have been rotated, shifting compressed archive file numbers,
			// hence modification check
			if (file_exists($tmpfile) && !$this->IsLogFileModified($tmpfile)) {
				$file= $tmpfile;
			}
		}
		
		if (!preg_match('/.*\.gz$/', $file)) {
			$logline= $this->GetFileFirstLine($file);
			
			$this->ParseLogLine($logline, $cols);
			return $cols['Date'].' '.$cols['Time'];
		}
		return _CONTROL('Compressed');
	}

	/** Gets first line of file.
	 *
	 * Used to get the start date of log files.
	 *
	 * @param[in]	$file	string Log file pathname
	 * @return int First line in file
	 */
	function GetFileFirstLine($file)
	{
		$cmd= preg_replace('/<LF>/', $file, $this->CmdLogStart);
		return $this->RunShellCommand($cmd);
	}

	/** Gets line count of file.
	 *
	 * @param[in]	$file	string Log file pathname
	 * @param[in]	$re		string Regexp to get count of a restricted result set
	 * @return int Line count
	 */
	function GetFileLineCount($file, $re= '')
	{
		if ($re == '') {
			/// @warning Input redirection is necessary, otherwise wc adds file name to its output too
			$cmd= "/usr/bin/wc -l < $file";
		}
		else {
			$re= escapeshellarg($re);
			$cmd= "/usr/bin/grep -a -E $re $file | /usr/bin/wc -l";
		}
		// OpenBSD wc returns with leading blanks
		return trim($this->RunShellCommand($cmd));
	}

	/** Gets lines in file.
	 *
	 * @param[in]	$file	string Log file pathname
	 * @param[in]	$end	int Head option, start line
	 * @param[in]	$count	int Tail option, page line count
	 * @param[in]	$re		string Regexp to restrict the result set
	 * @return serialized Log lines
	 */
	function GetLogs($file, $end, $count, $re= '')
	{
		// Empty $re is not an issue for grep, greps all
		$re= escapeshellarg($re);
		$cmd= "/usr/bin/grep -a -E $re $file | /usr/bin/head -$end | /usr/bin/tail -$count";
		
		exec($cmd, $output, $retval);
		
		$logs= array();
		foreach ($output as $line) {
			unset($cols);
			if ($this->ParseLogLine($line, $cols)) {
				$logs[]= $cols;
			}
		}
		return serialize($logs);
	}

	/** Gets logs for live logs pages.
	 *
	 * Used to extract lines in last section of the log file or
	 * of the lines grep'd.
	 *
	 * Difference from the archives method is that this one always gets
	 * the tail of the log or grepped lines.
	 *
	 * @param[in]	$file	string Log file
	 * @param[in]	$count	int Tail lenght, page line count
	 * @param[in]	$re		string Regexp to restrict the result set
	 * @return serialized Log lines
	 */
	function GetLiveLogs($file, $count, $re= '')
	{
		// Empty $re is not an issue for grep, greps all
		$re= escapeshellarg($re);
		$cmd= "/usr/bin/grep -a -E $re $file | /usr/bin/tail -$count";

		exec($cmd, $output, $retval);
		
		$logs= array();
		foreach ($output as $line) {
			unset($cols);
			if ($this->ParseLogLine($line, $cols)) {
				$logs[]= $cols;
			}
		}
		return serialize($logs);
	}

	/** Sends HUP to the module pid.
	 */
	function Reload()
	{
		if ($this->PidFile !== '') {
			if (($pid= $this->GetFile($this->PidFile)) !== FALSE) {
				$this->RunShellCommand("/bin/kill -HUP $pid");
				return TRUE;
			}
		}
		ViewError(_('Cannot get pid'));
		return FALSE;
	}

	/** Start module process(es).
	 *
	 * Tries PROC_STAT_TIMEOUT times.
	 *
	 * @return boolean TRUE if the process starts
	 *
	 * @todo Actually should stop retying on some error conditions?
	 */
	function Start()
	{
		global $TmpFile;

		$this->RunShellCommand($this->StartCmd);
		
		$count= 0;
		while ($count++ < self::PROC_STAT_TIMEOUT) {
			if ($this->IsRunning()) {
				return TRUE;
			}
			/// @todo Check $TmpFile for error messages, if so break out instead
			exec('/bin/sleep .1');
		}

		// Check one last time due to the last sleep in the loop
		if ($this->IsRunning()) {
			return TRUE;
		}
		
		// Start command is redirected to tmp file
		$output= file_get_contents($TmpFile);
		ViewError($output);
		cwc_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Start failed with: $output");
		return FALSE;
	}

	/** Stops module process(es)
	 *
	 * Tries PROC_STAT_TIMEOUT times.
	 *
	 * @return boolean TRUE if the process killed successfully
	 *
	 * @todo Actually should stop retrying on some error conditions?
	 */
	function Stop()
	{
		return $this->Pkill($this->Proc);
	}
		
	/** Stops the given process.
	 *
	 * Used to kill processes without a model definition, hence $proc param.
	 *
	 * @param	$proc string Process name
	 */
	function Pkill($proc)
	{
		global $TmpFile;
		
		$cmd= '/usr/bin/pkill -x '.$proc;
		
		$count= 0;
		while ($count++ < self::PROC_STAT_TIMEOUT) {
			if (!$this->IsRunning($proc)) {
				return TRUE;
			}
			$this->RunShellCommand("$cmd > $TmpFile 2>&1");
			/// @todo Check $TmpFile for error messages, if so break out instead
			exec('/bin/sleep .1');
		}

		// Check one last time due to the last sleep in the loop
		if (!$this->IsRunning($proc)) {
			return TRUE;
		}
		
		// Pkill command is redirected to tmp file
		$output= file_get_contents($TmpFile);
		ViewError($output);
		cwc_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Pkill failed for $proc with: $output");
		return FALSE;
	}

	/** Stops module's parent process with its pid.
	 */
	function Kill()
	{
		global $TmpFile;
		
		if (($pid= $this->GetFile($this->PidFile)) !== FALSE) {
			$cmd= "/bin/kill $pid";
		
			$count= 0;
			while ($count++ < self::PROC_STAT_TIMEOUT) {
				if (!$this->IsRunning()) {
					return TRUE;
				}
				$this->RunShellCommand("$cmd > $TmpFile 2>&1");
				/// @todo Check $TmpFile for error messages, if so break out instead
				exec('/bin/sleep .1');
			}

			// Check one last time due to the last sleep in the loop
			if (!$this->IsRunning()) {
				return TRUE;
			}
			
			// Kill command is redirected to tmp file
			$output= file_get_contents($TmpFile);
			ViewError($output);
			cwc_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Kill failed with: $output");
			return FALSE;
		}
		/// @attention Missing pid file means success, proc is not running anyway
		return TRUE;
	}

	/** Kills process with the given pid
	 *
	 * Tries PROC_STAT_TIMEOUT times.
	 *
	 * @param[in]	$pid	int Pid
	 * @return boolean TRUE if the process killed successfully
	 *
	 * @todo Actually should stop retrying on some error conditions?
	 */
	function KillPid($pid)
	{
		global $TmpFile;

		$cmd= '/bin/kill '.$pid;

		$count= 0;
		while ($count++ < self::PROC_STAT_TIMEOUT) {
			if (!$this->IsModulePidRunning($pid)) {
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
		return !$this->IsModulePidRunning($pid);
	}

	/** Checks if the pid is running.
	 *
	 * @param[in]	$pid int Pid
	 * @return boolean TRUE if process is running
	 */
	function IsModulePidRunning($pid)
	{
		$pidcmd= "/bin/ps -o pid -p $pid | /usr/bin/grep '$pid'";

		$output= $this->RunShellCommand($pidcmd);

		return ($output !== '');
	}

	/** Checks if the process(es) is running.
	 *
	 * Uses ps with grep.
	 *
	 * @param[in]	$proc	string Module process name
	 * @return boolean TRUE if there is any process running
	 */
	function IsRunning($proc= '')
	{
		if ($proc == '') {
			$proc= $this->Proc;
		}
	
		/// @todo Should use pid files instead of ps, if possible at all
		$cmd= preg_replace('/<PROC>/', escapeshellarg($proc), $this->psCmd);
		exec($cmd, $output, $retval);
		if ($retval === 0) {
			return count($this->SelectProcesses($output)) > 0;
		}
		ViewError(implode("\n", $output));
		cwc_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "No such process: $proc");
		return FALSE;
	}
	
	/** Gets the list of processes running.
	 */
	function GetProcList()
	{
		$cmd= preg_replace('/<PROC>/', escapeshellarg($this->Proc), $this->psCmd);
		exec($cmd, $output, $retval);
		if ($retval === 0) {
			return serialize($this->SelectProcesses($output));
		}
		ViewError(implode("\n", $output));
		cwc_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "Process list failed for $this->Proc");
		return FALSE;
	}

	/** Selects user processes from ps output.
	 *
	 * @param[in]	$psout	arrary ps output
	 * @return array Parsed ps output of user processes
	 */
	function SelectProcesses($psout)
	{
		//   PID STARTED  %CPU      TIME %MEM   RSS   VSZ STAT  PRI  NI TTY      USER     GROUP    COMMAND
		//     1  5:10PM   0.0   0:00.03  0.0   388   412 Is     10   0 ??       root     wheel    /sbin/init
		// Skip processes running on terminals, e.g. vi, tail, man
		// Select based on daemon user
		$re= "/^\s*(\d+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\d+)\s+(\d+)\s+(\S+)\s+(\d+)\s+(\d+)\s+\?\?\s+($this->User)\s+(\S+)\s+(.+)$/";
		
		$processes= array();
		foreach ($psout as $line) {
			if (preg_match($re, $line, $match)) {
				// Skip processes initiated by this WUI
				if (!preg_match('/\b(cwc\.php|grep|kill|pkill)\b/', $match[13])) {
					$processes[]= array(
						$match[1],
						$match[2],
						$match[3],
						$match[4],
						$match[5],
						$match[6],
						$match[7],
						$match[8],
						$match[9],
						$match[10],
						$match[11],
						$match[12],
						$match[13],
						);
				}
			}
		}
		return $processes;
	}
	
	/** Gets software version string.
	 */
	function GetVersion()
	{
		if ($this->VersionCmd !== '') {
			return  $this->RunShellCommand($this->VersionCmd.' | /usr/bin/head -1');
		}
		return FALSE;
	}

	/** Gets service statuses.
	 */
	function GetServiceStatus()
	{
		global $MODEL_PATH, $ModelFiles, $Models, $ModelsToStat;

		$output= FALSE;
		foreach ($ModelsToStat as $m) {
			if (array_key_exists($m, $ModelFiles)) {
				require_once($MODEL_PATH.$ModelFiles[$m]);

				if (class_exists($Models[$m])) {
					$model= new $Models[$m]();
					
					if ($model->IsRunning()) {
						$output.= "$m=R\n";
					}
					else {
						$output.= "$m=S\n";
					}
				}
				else {
					cwc_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "Not in Models: $m");
				}
			}
			else {
				cwc_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "Not in ModelFiles: $m");
			}
		}
		return $output;
	}

	/** Collects text statistics for the proc stats general page.
	 *
	 * Builds the shell command to count with grep first.
	 * Just counts the number of lines in the grep output.
	 */
	function GetProcStatLines($logfile)
	{
		global $Modules;

		$stats= array();
		foreach ($Modules[$this->Name]['Stats'] as $stat => $conf) {
			if (isset($conf['Cmd'])) {
				$cmd= $conf['Cmd'];
				if (isset($conf['Needle'])) {
					$cmd.= ' | /usr/bin/grep -a -E <NDL>';
					$cmd= preg_replace('/<NDL>/', escapeshellarg($conf['Needle']), $cmd);
				}
				$cmd.= ' | /usr/bin/wc -l';
			}
			else if (isset($conf['Needle'])) {
				$cmd= '/usr/bin/grep -a -E <NDL> <LF> | /usr/bin/wc -l';
				$cmd= preg_replace('/<NDL>/', escapeshellarg($conf['Needle']), $cmd);
			}
			if ($logfile == '') {
				$logfile= $this->LogFile;
			}
			$cmd= preg_replace('/<LF>/', $logfile, $cmd);
			
			$stats[$conf['Title']]= trim($this->RunShellCommand($cmd));
		}
		return serialize($stats);		
	}

	/** Uncompresses gzipped log file to tmp dir.
	 */
	function CopyLogFileToTmp($file, $tmpdir)
	{
		exec("/bin/mkdir -p $tmpdir 2>&1", $output, $retval);
		if ($retval === 0) {
			exec("/bin/cp $file $tmpdir 2>&1", $output, $retval);
			if ($retval === 0) {
				$tmpfile= $tmpdir.basename($file);
				if (preg_match('/(.*)\.gz$/', $tmpfile, $match)) {
					// Delete the old uncompressed file, otherwise gunzip fails
					$this->DeleteFile($match[1]);
					
					exec("/usr/bin/gunzip $tmpfile 2>&1", $output, $retval);
					if ($retval === 0) {
						return TRUE;
					}
					else {
						cwc_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'gunzip failed: '.$tmpdir.basename($file));
					}
				}
				else {
					return TRUE;
				}
			}
			else {
				cwc_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'cp failed: '.$file);
			}
		}
		else {
			cwc_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'mkdir failed: '.$tmpdir);
		}
		ViewError(implode("\n", $output));
		return FALSE;
	}

	/** Returns ComixWall cvs tag in the given file, if any.
	 *
	 * @param[in]	$file string File pathname.
	 */
	function GetFileCvsTag($file)
	{
		if (($contents= $this->GetFile($file)) !== FALSE) {
			/// @warning Don't add $ to ComixWall tag, otherwise CVS changes $re during commit
			$re= '/ComixWall:\s+(.*\.php,v\s+\d+\.\d+\s+\d+\/\d+\/\d+\s+\d+:\d+:\d+)\s+\S+\s+Exp\s+\$/';
			if (preg_match($re, $contents, $match)) {
				return $match[1];
			}
		}
		return FALSE;
	}

	/** Gets sysctl output for the given arg.
	 *
	 * @param[in]	$option	sysctl arg, such as hw.sensors.
	 * @return sysctl output lines.
	 */
	function GetSysCtl($option)
	{       
//		$ret = $this->RunShellCommand("/sbin/sysctl $option");
	}

	/** Reads file contents.
	 *
	 * @param[in]	$file	Config file
	 * @return File contents
	 */
	function GetFile($file)
	{
		if (file_exists($file)) {
			return file_get_contents($file);
		}
		return FALSE;
	}

	function GetDefaultLogFile()
	{
		return $this->LogFile;
	}
	
	/** Returns log file under tmp location.
	 *
	 * Updates the tmp file if original file is modified.
	 * Updates the stat info of the file in the tmp statistics file,
	 * used to check file modificaton.
	 *
	 * @param[in]	$file Original file name
	 * @return File pathname
	 */
	function SelectLogFile($file)
	{
		if ($file === '') {
			$file= $this->LogFile;
		}
		
		$file= $this->GetTmpLogFileName($file);
		
		if (!file_exists($file) || $this->IsLogFileModified($file)) {
			if ($this->UpdateTmpLogFile($file)) {
				// Update stats to update file stat info only
				$this->UpdateStats($file, $stats, $briefstats);
			}
			else {
				$file= $this->LogFile;
				cwc_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "Logfile tmp copy update failed, defaulting to: $file");
			}
		}
		
		return $file;
	}

	/** Copies original log file to tmp location.
	 *
	 * @param[in]	$file File pathname
	 */
	function UpdateTmpLogFile($file)
	{
		$origfile= $this->GetOrigFileName($file);
		
		if ($this->CopyLogFileToTmp($origfile, $this->TmpLogsDir)) {
			return TRUE;
		}
		cwc_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "Copy failed: $file");
		return FALSE;
	}

	/** Returns the log file name in the tmp location.
	 *
	 * @param[in]	$file File pathname
	 */
	function GetTmpLogFileName($file)
	{
		if (preg_match('/(.*)\.gz$/', $file, $match)) {
			$file= $this->TmpLogsDir.basename($match[1]);
		}
		else {
			$file= $this->TmpLogsDir.basename($file);
		}
		return $file;
	}

	/** Gets log files list with start dates.
	 *
	 * Searches the logs directory for all possible archives according to
	 * the default file name.
	 */
	function GetLogFilesList()
	{
		$file= $this->LogFile;
		$filelist= explode("\n", $this->GetFiles("$file*"));
		asort($filelist);

		$result= array();
		foreach ($filelist as $filepath) {
			$result[$filepath]= $this->GetLogStartDate($filepath);
		}
		return serialize($result);
	}

	/** Deletes file or dir.
	 *
	 * @param[in]	$path	string File or dir.
	 */
	function DeleteFile($path)
	{
		if (file_exists($path)) {
			exec("/bin/rm -rf $path 2>&1", $output, $retval);
			if ($retval === 0) {
				return TRUE;
			}
			else {
				$errout= implode("\n", $output);
				ViewError($errout);
				cwc_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "Failed deleting: $path, $errout");
			}
		}
		else {
			cwc_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "File path does not exist: $path");
		}
		return FALSE;
	}

	/** Writes contents to file.
	 *
	 * @param[in]	$file		Config file.
	 * @param[in]	$contents	Contents to write.
	 */
	function PutFile($file, $contents)
	{
		if (file_exists($file)) {
			return file_put_contents($file, $contents, LOCK_EX);
		}
		return FALSE;
	}

	/** Extract physical interface names from ifconfig output.
	 *
	 * Removes non-physical interfaces from the output
	 * @return Names of physical interfaces.
	 */
	function GetPhyIfs()
	{
		return $this->RunShellCommand("/sbin/ifconfig -a | /usr/bin/grep ': flags=' | /usr/bin/sed 's/: flags=.*//g' | /usr/bin/grep -v -e lo -e pflog -e pfsync -e enc -e tun");
	}
	
	/** Returns both brief and full statistics.
	 *
	 * @param[in]	$logfile		string Log file pathname
	 * @param[in]	$collecthours	boolean Flag to get hour statistics also
	 * @return serialized Statistics in serialized arrays
	 */
	function GetAllStats($logfile, $collecthours= '')
	{
		$date= serialize(array('Month' => '', 'Day' => ''));
		/// @attention We need $stats return value of GetStats() because of $collecthours constraint
		$stats= $this->GetStats($logfile, $date, $collecthours);
		
		// Do not get $stats here, just $briefstats
		$this->GetSavedStats($logfile, $dummy, $briefstats);
		$briefstats= serialize($briefstats);
		
		// Use serialized stats as array elements to prevent otherwise extra unserialize() for $stats,
		// which is already serialized by GetStat() above.
		// They are ordinary strings now, this serialize() should be quite fast
		return serialize(
				array(
					'stats' 	=> $stats,
					'briefstats'=> $briefstats,
					)
			);
	}
	
	/** Main statistics collector, as module data set.
	 *
	 * @param[in]	$logfile		string Log file pathname
	 * @param[in]	$date			array Datetime struct
	 * @param[in]	$collecthours	boolean Flag to collect hour statistics also
	 * @return array Statistics data set collected, serialized
	 */
	function GetStats($logfile, $date, $collecthours= '')
	{
		global $Modules;
		
		$date= unserialize($date);

		$stats= array();
		$briefstats= array();
		$uptodate= FALSE;
		
		if ($this->IsLogFileModified($logfile)) {
			$this->UpdateTmpLogFile($logfile);
		}
		else {
			$uptodate= $this->GetSavedStats($logfile, $stats, $briefstats);
		}
			
		if (!$uptodate) {
			$this->UpdateStats($logfile, $stats, $briefstats);
		}
				
		if (isset($stats['Date'])) {
			if ($collecthours === '') {
				foreach ($stats['Date'] as $day => $daystats) {
					unset($stats['Date'][$day]['Hours']);
				}
			}

			$re= $this->GetDateRegexp($date);
			foreach ($stats['Date'] as $day => $daystats) {
				if (!preg_match("/$re/", $day)) {
					unset($stats['Date'][$day]);
				}
			}

			$re= $this->GetHourRegexp($date);
			foreach ($stats['Date'] as $day => $daystats) {
				if (isset($daystats['Hours'])) {
					foreach ($daystats['Hours'] as $hour => $hourstats) {
						if (!preg_match("/$re/", $hour)) {
							unset($stats['Date'][$day]['Hours'][$hour]);
						}
					}
				}
			}
		}
		return serialize($stats);
	}

	/** Gets the number of lines added to log files since last tmp update.
	 *
	 * @param[in]	$logfile	string Log file pathname
	 * @param[out]	$count		int Number of new log lines
	 */
	function CountDiffLogLines($logfile, &$count)
	{
		$count= -1;
			
		if ($this->GetStatsFileInfo($logfile, $oldlinecount, $oldfilestat)) {
			
			$newlinecount= $this->GetFileLineCount($logfile);
			$origfile= $this->GetOrigFileName($logfile);

			if (($newlinecount >= $oldlinecount) && !preg_match('/\.gz$/', $origfile)) {
				cwc_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "Logfile modified: $logfile, linecount $oldlinecount->$newlinecount");

				$count= $newlinecount - $oldlinecount;
				cwc_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "Logfile has grown by $count lines: $logfile");
				return TRUE;
			}
			else {
				// Logs probably rotated, recollect the stats
				// Also stats for compressed files are always recollected on rotation, otherwise stats would be merged with the old stats
				cwc_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "Assuming log file rotation: $logfile");
			}
		}
		else {
			cwc_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "Cannot get file info: $logfile");
		}

		return FALSE;
	}

	/** Updates statistics incrementally, both brief and full.
	 *
	 * @param[in]	$logfile		string Log file pathname
	 * @param[out]	$stats			array Full stats
	 * @param[out]	$briefstats		array Brief stats
	 */
	function UpdateStats($logfile, &$stats, &$briefstats)
	{
		global $Modules;

		$stats= array();
		$briefstats= array();

		// Line count should be obtained here, see SaveStats() for explanation
		$linecount= $this->GetFileLineCount($logfile);

		if ($this->CountDiffLogLines($logfile, $tail)) {
			$this->GetSavedStats($logfile, $stats, $briefstats);
		}
		
		$statsdefs= $Modules[$this->Name]['Stats'];
		
		if (isset($statsdefs)) {
			$lines= $this->GetStatsLogLines($logfile, $tail);
			
			if ($lines !== '') {
				$lines= explode("\n", $lines);

				foreach ($lines as $line) {
					unset($values);
					$this->ParseLogLine($line, $values);
	 				// Post-processing modifies link and/or datetime values.
					$this->PostProcessCols($values);

					$this->CollectDayStats($statsdefs, $values, $line, $stats);
				
					$briefstatsdefs= $statsdefs['Total']['BriefStats'];
					
					if (isset($briefstatsdefs)) {
						if (!isset($briefstatsdefs['Date'])) {
							// Always collect Date field
							$briefstatsdefs['Date'] = _('Requests by date');
						}

						// Collect the fields listed under BriefStats
						foreach ($briefstatsdefs as $name => $title) {
							$value= $values[$name];
							if (isset($value)) {
								$briefstats[$name][$value]+= 1;
							}
						}
					}
				}
			}
		}

		$this->SaveStats($logfile, $stats, $briefstats, $linecount);
	}

	/** Generates date regexp to be used by statistics functions.
	 *
	 * Used to match date indeces of stats array to get stats for date ranges.
	 *
	 * @param[in]	$date	array Date struct
	 * @return Regexp
	 */
	function GetDateRegexp($date)
	{
		global $MonthNames;

		if ($date['Month'] == '') {
			$re= '.*';
		}
		else {
			$re= $MonthNames[$date['Month']].'\s+';
			if ($date['Day'] == '') {
				$re.= '.*';
			}
			else {
				$re.= sprintf('% 2d', $date['Day']);
			}
		}
		return $re;
	}

	/** Generates hour regexp to be used by statistics functions.
	 *
	 * @param[in]	$date	array Date struct
	 * @return Regexp
	 */
	function GetHourRegexp($date)
	{
		if ($date['Hour'] == '') {
			$re= '.*';
		}
		else {
			$re= $date['Hour'];
		}
		return $re;
	}

	/** Gets saved statistics for the given log file.
	 *
	 * @param[in]	$logfile	string Log file
	 * @param[out]	$stats		array Statistics
	 * @param[out]	$briefstats	array Brief statistics
	 */
	function GetSavedStats($logfile, &$stats, &$briefstats)
	{
		$statsfile= $this->GetStatsFileName($logfile);
		if (($filecontents= $this->GetFile($statsfile)) !== FALSE) {
			if ($serialized_stats= preg_replace("|^(<filestat>.*</filestat>\s)|m", '', $filecontents)) {
				$allstats= unserialize($serialized_stats);
				if (isset($allstats['stats']) && isset($allstats['briefstats'])) {
					$stats= $allstats['stats'];
					$briefstats= $allstats['briefstats'];
					return TRUE;
				}
				else {
					cwc_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "Missing stats in file: $statsfile");
				}
			}
			else {
				cwc_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "filestat removal failed in file: $statsfile");
			}
		}
		return FALSE;
	}

	/** Checks if the given log file has been updated.
	 *
	 * Compares the full stat info, except for last access time,
	 * which is updated by the stat() call too.
	 *
	 * @param[in]	$logfile	string Log file
	 */
	function IsLogFileModified($logfile)
	{
		$origfile= $this->GetOrigFileName($logfile);
		
		if ($this->GetStatsFileInfo($logfile, $linecount, $filestat)) {
			if (file_exists($origfile)) {
				$newfilestat= stat($origfile);

				$diff= array_diff($newfilestat, $filestat);
				unset($diff['8']);
				unset($diff['atime']);
				if (count($diff) === 0) {
					cwc_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "Logfile not modified: $logfile, linecount $linecount");
					return FALSE;
				}
			}
		}
		return TRUE;
	}

	/** Gets previous line count and stat() from statistics file.
	 *
	 * @param[in]	$logfile	string Log file
	 * @param[out]	$linecount	int Previous line count
	 * @param[out]	$filestat	array Previous stat() output
	 */
	function GetStatsFileInfo($logfile, &$linecount, &$filestat)
	{
		/// @todo Should check file format too, and delete the stats file if corrupted
		
		$linecount= 0;
		$filestat= array();
		
		$statsfile= $this->GetStatsFileName($logfile);
		if (file_exists($statsfile)) {
			$filestatline= $this->RunShellCommand("/usr/bin/head -1 $statsfile");
			if (preg_match('|^<filestat>(.*)</filestat>$|', $filestatline, $match)) {
				$fileinfo= unserialize($match[1]);
				
				$linecount= $fileinfo['linecount'];
				$filestat= $fileinfo['stat'];
				return TRUE;
			}
			else {
				cwc_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "filestat missing in: $statsfile");
			}
		}
		else {
			cwc_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "No such file: $statsfile");
		}
		return FALSE;
	}

	/** Gets name of the tmp statistics file for the given log file.
	 *
	 * @param[in]	$logfile	string Log file
	 */
	function GetStatsFileName($logfile)
	{
		$origfilename= basename($this->GetOrigFileName($logfile));
		
		$statsdir= '/var/tmp/comixwall/stats/'.get_class($this);
		$statsfile= "$statsdir/$origfilename";

		return $statsfile;
	}

	/** Returns the original file name for the given log file.
	 *
	 * @param[in]	$logfile	string Log file
	 */
	function GetOrigFileName($logfile)
	{
		$origfilename= basename($logfile);
		if (basename($this->LogFile) !== $origfilename) {
			$origfilename.= '.gz';
		}
		$origfile= dirname($this->LogFile).'/'.$origfilename;
		
		return $origfile;
	}

	/** Saves collected statistics with current line count and stat() output.
	 *
	 * @warning Line count should be get before statistics collection, otherwise
	 * new lines appended during stats processing may be skipped,
	 * hence the $linecount param.
	 *
	 * @param[in]	$logfile	string Log file
	 * @param[in]	$stats		array Statistics
	 * @param[in]	$briefstats	array Brief statistics
	 * @param[in]	$linecount	int Line count
	 */
	function SaveStats($logfile, $stats, $briefstats, $linecount)
	{
		$origfile= $this->GetOrigFileName($logfile);
		$statsfile= $this->GetStatsFileName($logfile);
		
		$savestats=
			'<filestat>'.
			serialize(
				array(
					'linecount'	=> $linecount,
					'stat'		=> stat($origfile),
					)
			).
			'</filestat>'."\n".
			serialize(
				array(
					'stats' 	=> $stats,
					'briefstats'=> $briefstats,
					)
			);
		
		$statsdir= dirname($statsfile);
		if (!file_exists($statsdir)) {
			exec('/bin/mkdir -p '.$statsdir);
		}
		
		exec('/usr/bin/touch '.$statsfile);
		$this->PutFile($statsfile, $savestats);
		cwc_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "Saved stats to: $statsfile");
	}
	
	/** Day statistics collector.
	 *
	 * $statsdefs has all the information to collect what data.
	 *
	 * If parsed log time does not have an appropriate hour/min, then 12:00 is assumed.
	 *
	 * @param[in]	$statsdefs		array Module stats section of $Modules
	 * @param[in]	$values			array Log fields parsed by caller function
	 * @param[in]	$line			string Current log line needed to search for keywords
	 * @param[out]	$stats			array Statistics data set collected
	 *
	 * @todo How is it possible that Time does not have hour/min? Should have a module
	 * Time field processor as well?
	 */
	function CollectDayStats($statsdefs, $values, $line, &$stats)
	{
		$re= '/^(\d+):(\d+):(\d+)$/';
		if (preg_match($re, $values['Time'], $match)) {
			$hour= $match[1];
			$min= $match[2];
		}
		else {
			// Should be unreachable
			$hour= '12';
			$min= '00';
			cwc_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, 'There was no Time in log values, defaulting to 12:00');
		}

		$daystats= &$stats['Date'][$values['Date']];
		$this->IncStats($line, $values, $statsdefs, $daystats);

		$this->CollectHourStats($statsdefs, $hour, $min, $values, $line, $daystats);
	}

	/** Hour statistics collector.
	 *
	 * $statsdefs has all the information to collect what data.
	 *
	 * $daystats is the subsection of the main $stats array for the current date.
	 *
	 * @param[in]	$statsdefs		array Module stats section of $Modules
	 * @param[in]	$hour			string Hour to collect stats for
	 * @param[in]	$min			string Min to collect stats for, passed to CollectMinuteStats()
	 * @param[in]	$values			array Log fields parsed by caller function
	 * @param[in]	$line			string Current log line needed to search for keywords
	 * @param[out]	$daystats		array Statistics data set collected
	 */
	function CollectHourStats($statsdefs, $hour, $min, $values, $line, &$daystats)
	{
		$hourstats= &$daystats['Hours'][$hour];
		$this->IncStats($line, $values, $statsdefs, $hourstats);

		$this->CollectMinuteStats($statsdefs, $min, $values, $line, $hourstats);
	}
	
	/** Increments stats for the given values.
	 */
	function IncStats($line, $values, $statsdefs, &$stats)
	{
		$stats['Sum']+= 1;

		if (isset($statsdefs['Total']['Counters'])) {
			foreach ($statsdefs['Total']['Counters'] as $counter => $conf) {
				$value= $values[$conf['Field']];
				if (isset($value)) {
					$stats[$counter]['Sum']+= $value;

					foreach ($conf['NVPs'] as $name => $title) {
						if (isset($values[$name])) {
							$stats[$counter][$name][$values[$name]]+= $value;
						}
					}
				}
			}
		}

		foreach ($statsdefs as $stat => $conf) {
			if (isset($conf['Needle'])) {
				if (preg_match('/'.$conf['Needle'].'/', $line)) {
					$stats[$stat]['Sum']+= 1;

					foreach ($conf['NVPs'] as $name => $title) {
						if (isset($values[$name])) {
							$stats[$stat][$name][$values[$name]]+= 1;
						}
					}
				}
			}
		}
	}

	/** Minute statistics collector.
	 *
	 * $statsdefs has all the information to collect what data.
	 *
	 * $hourstats is the subsection of the $stats array for the current hour.
	 *
	 * @param[in]	$statsdefs	array Module stats section of $Modules
	 * @param[in]	$min		string Min to collect stats for, passed to CollectMinuteStats()
	 * @param[in]	$values		array Log fields parsed by caller function
	 * @param[in]	$line		string Current log line needed to search for keywords
	 * @param[out]	$hourstats	array Statistics data set collected
	 */
	function CollectMinuteStats($statsdefs, $min, $values, $line, &$hourstats)
	{
		$minstats= &$hourstats['Mins'][$min];
		$minstats['Sum']+= 1;

		if (isset($statsdefs['Total']['Counters'])) {
			foreach ($statsdefs['Total']['Counters'] as $counter => $conf) {
				if (isset($values[$conf['Field']])) {
					$minstats[$counter]+= $values[$conf['Field']];
				}
			}
		}

		foreach ($statsdefs as $stat => $conf) {
			if (isset($conf['Needle'])) {
				if (preg_match('/'.$conf['Needle'].'/', $line)) {
					$minstats[$stat]+= 1;
				}
			}
		}
	}

	/** Log parser.
	 *
	 * @param[in]	$logline	string Log line
	 * @param[out]	$cols		array Parsed fields
	 * @return TRUE if log line is parsed
	 */
	function ParseLogLine($logline, &$cols)
	{
		return $this->ParseSyslogLine($logline, $cols);
	}
	
	/** Parses standard syslog line.
	 *
	 * @param[in]	$logline	string Log line
	 * @param[out]	$cols		array Parsed fields
	 *
	 * @return	TRUE if log line is a syslog line, thus parsed
	 */
	function ParseSyslogLine($logline, &$cols)
	{
		$re_datetime= '(\w+\s+\d+)\s+(\d+:\d+:\d+)';
		$re_proc= '((\S+(\[\d+\]|)):|)';
		
		$re= "/^$re_datetime\s+(\S+|)\s+$re_proc\s*(.*|)$/";
		if (preg_match($re, $logline, $match)) {
			$cols['Date']= $match[1];
			$cols['Time']= $match[2];
			$cols['Process']= $match[5];
			$cols['Log']= $match[7];
			return TRUE;
		}
		return FALSE;
	}
	
	/** Further processes parser output fields.
	 *
	 * Used by statistics collector functions.
	 *
	 * @warning This cannot be handled in the parser. Because details of the Link
	 * field is lost, which are needed on log pages.
	 *
	 * @param[out]	$cols	array Updated parser output
	 */
	function PostProcessCols(&$cols)
	{
	}

	/** Prepares file for download over WUI.
	 */
	function PrepareFileForDownload($file)
	{
		$tmpdir= '/var/tmp/comixwall/downloads';
		$retval= 0;
		if (!file_exists($tmpdir)) {
			exec("/bin/mkdir -p $tmpdir 2>&1", $output, $retval);
		}
		
		if ($retval === 0) {
			exec("/bin/rm -f $tmpdir/* 2>&1", $output, $retval);
			if ($retval === 0) {
				$tmpfile= "$tmpdir/".basename($file);
				exec("/bin/cp $file $tmpfile 2>&1", $output, $retval);
				if ($retval === 0) {
					exec("/sbin/chown www:www $tmpfile 2>&1", $output, $retval);
					if ($retval === 0) {
						return $tmpfile;
					}
				}
			}
		}
		$errout= implode("\n", $output);
		ViewError($errout);
		cwc_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "FAILED: $errout");
		return FALSE;
	}
	
	/** Provides the list of allowed IPs.
	 *
	 * @return List of IPs.
	 */
	function GetAllowedIps()
	{
		global $Re_Ip, $Re_Net;
		
		return $this->SearchFileAll($this->ConfFile, "/^\h*!($Re_Ip|$Re_Net)\h*$/m");
	}

	/** Provides a list of restricted IPs.
	 *
	 * @return List of IPs.
	 */
	function GetRestrictedIps()
	{
		global $Re_Ip, $Re_Net;
		
		return $this->SearchFileAll($this->ConfFile, "/^\h*($Re_Ip|$Re_Net)\h*$/m");
	}

	/** Adds an IP or IP range to allowed list.
	 *
	 * @param[in]	$ip	IP or IP range.
	 */
	function AddAllowedIp($ip)
	{
		$this->DelAllowedIp($ip);
		return $this->AppendToFile($this->ConfFile, "!$ip");
	}

	/** Deletes an IP or IP range from allowed list.
	 *
	 * @param[in]	$ip	IP or IP range.
	 */
	function DelAllowedIp($ip)
	{
		$ip= Escape($ip, '/.');
		return $this->ReplaceRegexp($this->ConfFile, "/^(\h*!$ip\b.*(\s|))/m", '');
	}

	/** Adds an IP or IP range to restricted list.
	 *
	 * @param[in]	$ip	IP or IP range.
	 */
	function AddRestrictedIp($ip)
	{
		$this->DelRestrictedIp($ip);
		return $this->AppendToFile($this->ConfFile, $ip);
	}

	/** Deletes an IP or IP range from restricted list.
	 *
	 * @param[in]	$ip	IP or IP range.
	 */
	function DelRestrictedIp($ip)
	{
		$ip= Escape($ip, '/.');
		return $this->ReplaceRegexp($this->ConfFile, "/^(\h*$ip\b.*(\s|))/m", '');
	}

	/** Get newsyslog configuration for log file.
	 *
	 * Certain log files do not have model classes, hence the $model param.
	 *
	 * @param[in]	$model	string Index to $ModelsToLogConfig
	 */
	function GetNewsyslogConfig($model)
	{
		$output= FALSE;
		if (($contents= $this->GetFile($this->newSyslogConf)) !== FALSE) {
			$re_filepath= Escape($this->LogFile, '/');
			$re_owner= '([\w:]+|)';
			$re_mode= '(\d+)';
			$re_count= '(\d+)';
			$re_size= '(\d+|\*)';
			$re_when= '(\d+|\*)';

			$re= "/^\s*$re_filepath\s+$re_owner\s*$re_mode\s+$re_count\s+$re_size\s+$re_when\s+.*$/m";
			if (preg_match($re, $contents, $match)) {
				$output= array(
					$this->LogFile => array(
						'Model' => $model,
						'Count' => $match[3],
						'Size' => $match[4],
						'When' => $match[5],
						),
					);
			}
		}
		return $output;
	}

	/** Set newsyslog configuration for log file.
	 *
	 * @param[in]	$file	string Log file pathname
	 * @param[in]	$count	int How many archives to keep
	 * @param[in]	$size	int/* Max site to rotate, or not care
	 * @param[in]	$when	int/* Interval to rotate in hours, or not care
	 */
	function SetNewsyslogConfig($file, $count, $size, $when)
	{
		if (copy($this->newSyslogConf, $this->newSyslogConf.'.bak')) {
			if (($contents= $this->GetFile($this->newSyslogConf)) !== FALSE) {
				$re_filepath= Escape($file, '/');
				$re_owner= '([\w:]+|)';
				$re_mode= '(\d+)';
				$re_count= '(\d+)';
				$re_size= '(\d+|\*)';
				$re_when= '(\d+|\*)';

				$re= "/^(\s*$re_filepath\s+$re_owner\s*$re_mode\s+)$re_count(\s+)$re_size(\s+)$re_when(\s+.*)$/m";
				$re_replace= '${1}'.$count.'${5}'.$size.'${7}'.$when.'${9}';
				if (($newcontents= preg_replace($re, $re_replace, $contents)) !== FALSE) {
					$this->PutFile($this->newSyslogConf, $newcontents);
					return TRUE;
				}
				else {
					cwc_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Cannot set new value: $file, $count, $size, $when");
				}
			}
		}
		else {
			cwc_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Cannot copy file $this->newSyslogConf");
		}
		return FALSE;
	}
	
	/** Returns all partitions defined in fstab.
	 */
	function GetPartitions()
	{
		$cmd= "/bin/df -h";
		if (($contents= $this->RunShellCommand($cmd)) !== FALSE) {
			$contents= explode("\n", $contents);
			
			$partitions= array();
			foreach ($contents as $line) {
				if (preg_match('/^(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)/', $line, $match)) {
					$partitions[$match[1]]= $match[6];
				}
			}
			// remove unwanted title element
			unset($partitions['Filesystem']);
			return $partitions;
			}
		return FALSE;
	}
	/** Finds sysctl temp and fan sensors.
	 *
	 * There may be multiple sensors. And we don't know which is CPU sensor.
	 *
	 * @return array Sensors extracted from sysctl output, FALSE if error or no sensors.
	 */
	function GetSensors()
	{ 
		if (($hwsensors= $this->GetSysCtl('hw.sensors')) !== FALSE) {
			$hwsensors= explode("\n", $hwsensors);

			if (count($hwsensors) > 0) {
				$tempsensors= array();
				$fansensors= array();
				foreach ($hwsensors as $sensor) {
					if (preg_match("/^hw\.sensors\.(\w+\d+\.temp\d+)/", $sensor, $match)) {
						if (!in_array($match[1], $tempsensors)) {
							$tempsensors[]= $match[1];
						}
					}
					else if (preg_match("/^hw\.sensors\.(\w+\d+\.fan\d+)/", $sensor, $match)) {
						if (!in_array($match[1], $fansensors)) {
							$fansensors[]= $match[1];
						}
					}
				}
				
				return array(
					'temp'	=> $tempsensors,
					'fan'	=> $fansensors,
					);
			}
		}
		return FALSE; 
	}
}

/** Master module configuration struct.
 *
 * This array provides all the configuration parameters needed for each module.
 * Detailed behaviour of each module is defined thru the settings in this array.
 *
 * @param Name				Translatable name of the module.
 * @param Fields			Array of columns to show on Logs pages. Parsers may
 * 							produce more fields than those listed here, e.g. for
 * 							statistics functions.
 * @param SubMenus			Each module page may have its own submenus (pages).
 *							Sub-fields should be php file names.
 *							These sub-fields also point to another array. The indeces
 *							of this array are used for $_GET method url, and are
 *							associated with the translatable title of sub-page.
 * @param HighlightLogs		Used for coloring log lines, holds params for coloring function
 * @param HighlightLogs>Col	Column/field to search for keywords (to color the line)
 * @param HighlightLogs>Tag	Tag to use in the HTML style, usually 'class', but also 'id'
 * @param HighlightLogs>Keywords	Contains keywords for red, yellow, and green.
 * 									Precedence being in that order.
 * @param Stats				Parent field in configuration details used on
 *							statistics pages.
 * @param Stats>Total		Mandatory sub-field for each Stats field. Configures the
 *							the general settings for the basic stats for the module.
 * @param Stats>Total>Title	Title to display on top of the graph.
 * @param Stats>Total>Cmd	Command line to get log lines. Usually to get all lines.
 * @param Stats>Total>Needle To get only those lines that contain the Needle text among
 *							the lines obtained by Stats>Total>Cmd.
 * @param Stats>Total>Color	The color of the bars on the graph.
 * @param Stats>Total>NVPs	Name-Value-Pairs to print at the bottom of the graph.
 *							Usually top 5 of some of the more important stats.
 *							Displayed in 2 columns.
 * @param Stats>Total>BriefStats Statistics (parsed field names) to collect as
 *							BriefStats. Top 100 of collected data are
 *							shown on the left of General statistics page.
 * @param Stats>Total>Counters Statistics to collect and show as a graph over total
 *							data. The difference between these counters and Stats>\<StatName\>
 *							is that these are collected using the command line for
 *							the Total stats. So there is no separate Cmd field.
 *							Counters has one extra field, Divisor, which is used to
 *							divide the total count. Usually need to convert bytes to
 *							kilobytes.
 * @param Stats><StatName>	Custom statistics to be collected. The data for these
 *							graphs are collected using the Cmd and Needle fields.
 *							Stats>Total>Counters could have been merged with this one perhaps.
 *							The sub-fields for these custom stats is the same as the
 *							Total field described above.
 *
 * @todo How to separate this array in MVC/OOP design?
 */
$Modules = array(
    'system' => array(
        'Name' => _TITLE2('System'),
        'Path' => 'system',
        'Fields' => array(
            'Date',
            'Time',
            'Process',
            'Log',
    		),
        'SubMenus' => array(
            'logs.php' => array(
                'archives' => _MENU('Archives'),
                'live' => _MENU('Live'),
        		),
            'graphs.php' => array(
                'cpus' => _MENU('CPUs'),
                'sensors' => _MENU('Sensors'),
                'memory' => _MENU('Memory'),
                'disks' => _MENU('Disks'),
                'partitions' => _MENU('Partitions'),
        		),
            'conf.php' => array(
                'basic' => _MENU('Basic'),
                'net' => _MENU('Network'),
                'init' => _MENU('Init'),
                'startup' => _MENU('Startup'),
                'logs' => _MENU('Logs'),
                'wui' => _MENU('WUI'),
        		),
    		),
		),
    'pf' => array(
        'Name' => _TITLE2('Packet Filter'),
        'Path' => 'pf',
        'Fields' => array(
            'Date',
            'Time',
            'Rule',
            'Act',
            'Dir',
            'If',
            'SrcIP',
            'SPort',
            'DstIP',
            'DPort',
            'Type',
            'Log',
    		),
        'HighlightLogs' => array(
            'Col' => 'Act',
            'REs' => array(
                'red' => array('\bblock\b'),
                'yellow' => array('\bmatch\b'),
                'green' => array('\bpass\b'),
        		),
    		),
        'Stats' => array(
            'Total' => array(
                'Title' => _STATS('All requests'),
                'Cmd' => $TCPDUMP.' <LF>',
                'Needle' => '',
                'Color' => '#004a4a',
                'NVPs' => array(
                    'SrcIP' => _STATS('Source addresses'),
                    'DstIP' => _STATS('Destination addresses'),
                    'DPort' => _STATS('Destination ports'),
                    'Type' => _STATS('Packet types'),
            		),
                'BriefStats' => array(
                    'Date' => _STATS('Requests by date'),
                    'SrcIP' => _STATS('Source addresses'),
                    'DstIP' => _STATS('Destination addresses'),
                    'DPort' => _STATS('Destination ports'),
                    'Type' => _STATS('Packet types'),
            		),
                'Counters' => array(),
        		),
            'Pass' => array(
                'Title' => _STATS('Allowed requests'),
                'Cmd' => $TCPDUMP.' <LF>',
                'Needle' => ' pass ',
                'Color' => 'green',
                'NVPs' => array(
                    'SrcIP' => _STATS('Source addresses'),
                    'DstIP' => _STATS('Destination addresses'),
                    'DPort' => _STATS('Destination ports'),
                    'Type' => _STATS('Packet types'),
            		),
        		),
            'Block' => array(
                'Title' => _STATS('Blocked requests'),
                'Cmd' => $TCPDUMP.' <LF>',
                'Needle' => ' block ',
                'Color' => 'red',
                'NVPs' => array(
                    'SrcIP' => _STATS('Source addresses'),
                    'DstIP' => _STATS('Destination addresses'),
                    'DPort' => _STATS('Destination ports'),
                    'Type' => _STATS('Packet types'),
            		),
        		),
            'Match' => array(
                'Title' => _STATS('Matched requests'),
                'Cmd' => $TCPDUMP.' <LF>',
                'Needle' => ' match ',
                'Color' => '#FF8000',
                'NVPs' => array(
                    'SrcIP' => _STATS('Source addresses'),
                    'DstIP' => _STATS('Destination addresses'),
                    'DPort' => _STATS('Destination ports'),
                    'Type' => _STATS('Packet types'),
            		),
        		),
    		),
        'SubMenus' => array(
            'stats.php' => array(
                'general' => _MENU('General'),
                'daily' => _MENU('Daily'),
                'hourly' => _MENU('Hourly'),
                'live' => _MENU('Live'),
        		),
            'logs.php' => array(
                'archives' => _MENU('Archives'),
                'live' => _MENU('Live'),
        		),
            'graphs.php' => array(
                'ifs' => _MENU('Interfaces'),
                'transfer' => _MENU('Transfer'),
                'states' => _MENU('States'),
                'mbufs' => _MENU('Mbufs'),
                'hosts' => _MENU('Hosts'),
                'protocol' => _MENU('Protocols'),
        		),
    		),
		),
    'protograph' => array(
        'Name' => _TITLE2('Protograph'),
        'Path' => 'protograph',
        'SubMenus' => array(
            'graphs.php' => array(
                'ifs' => _MENU('Interfaces'),
                'transfer' => _MENU('Transfer'),
                'states' => _MENU('States'),
                'mbufs' => _MENU('Mbufs'),
                'hosts' => _MENU('Hosts'),
                'protocol' => _MENU('Protocols'),
        		),
    		),
		),
    'e2guardian' => array(
        'Name' => _TITLE2('Web Filter'),
        'Path' => 'e2guardian',
        'Fields' => array(
            'Date',
            'Time',
            'Process',
            'Log',
    		),
        'HighlightLogs' => array(
            'REs' => array(
                'red' => array('\berror\b'),
                'yellow' => array('\bnotice\b'),
                'green' => array('\bsuccess'),
        		),
    		),
        'SubMenus' => array(
            'logs.php' => array(
                'archives' => _MENU('Archives'),
                'live' => _MENU('Live'),
        		),
            'lists.php' => array(
                'sites' => _MENU('Sites'),
                'urls' => _MENU('URLs'),
                'exts' => _MENU('Extensions'),
                'mimes' => _MENU('Mimes'),
                'dm_exts' => _MENU('DM Exts'),
                'dm_mimes' => _MENU('DM Mimes'),
                'virus_sites' => _MENU('Virus Sites'),
                'virus_urls' => _MENU('Virus URLs'),
                'virus_exts' => _MENU('Virus Exts'),
                'virus_mimes' => _MENU('Virus Mimes'),
        		),
            'cats.php' => array(
                'sites' => _MENU('Domains'),
                'urls' => _MENU('URLs'),
                'phrases' => _MENU('Phrases'),
                'blacklists' => _MENU('Blacklists'),
        		),
            'conf.php' => array(
                'groups' => _MENU('General'),
                'basic' => _MENU('Basic'),
                'scan' => _MENU('Scan'),
                'blanket' => _MENU('Blanket'),
                'bypass' => _MENU('Bypass'),
                'email' => _MENU('Email'),
        		),
            'general.php' => array(
                'basic' => _MENU('Basic'),
                'filter' => _MENU('Filter'),
                'scan' => _MENU('Scan'),
                'logs' => _MENU('Logs'),
                'downloads' => _MENU('Downloads'),
                'advanced' => _MENU('Advanced'),
        		),
    		),
		),
    'e2guardianlogs' => array(
        'Name' => _TITLE2('Web Filter Access'),
        'Fields' => array(
            'Date',
            'Time',
            'IP',
            'Link',
            'Scan',
            'Mtd',
            'Size',
            'Log',
    		),
        'HighlightLogs' => array(
            'Col' => 'Scan',
            'REs' => array(
                'red' => array('\*DENIED\*'),
                'yellow' => array('Bypass cookie exception'),
                'green' => array('\*SCANNED\*'),
        		),
    		),
        'Stats' => array(
            'Total' => array(
                'Title' => _STATS('All requests'),
                'Cmd' => '/bin/cat <LF>',
                'Needle' => '',
                'Color' => '#004a4a',
                'NVPs' => array(
                    'Link' => _STATS('Requests'),
                    'IP' => _STATS('IPs'),
            		),
                'BriefStats' => array(
                    'Date' => _STATS('Requests by date'),
                    'IP' => _STATS('Requests by IP'),
                    'Link' => _STATS('Links visited'),
                    'Cat' => _STATS('Denied categories'),
            		),
                'Counters' => array(
                    'Sizes' => array(
                        'Field' => 'Size',
                        'Title' => _STATS('Downloaded (KB)'),
                        'Color' => '#FF8000',
                        'Divisor' => 1000,
                        'NVPs' => array(
                            'Link' => _STATS('Size by site (KB)'),
                            'IP' => _STATS('Size by IP (KB)'),
                    		),
                		),
            		),
        		),
            'Scanned' => array(
                'Title' => _STATS('Scanned requests'),
                'Needle' => 'SCANNED',
                'Color' => 'blue',
                'NVPs' => array(
                    'Link' => _STATS('Requests scanned'),
                    'IP' => _STATS('Scanned IPs'),
            		),
        		),
            'Exception' => array(
                'Title' => _STATS('Exception requests'),
                'Needle' => 'EXCEPTION',
                'Color' => '#FF8000',
                'NVPs' => array(
                    'Link' => _STATS('Exception requests'),
                    'IP' => _STATS('Exception IPs'),
            		),
        		),
            'Denied' => array(
                'Title' => _STATS('Denied requests'),
                'Needle' => 'DENIED',
                'Color' => 'red',
                'NVPs' => array(
                    'Link' => _STATS('Requests denied'),
                    'IP' => _STATS('Denied IPs'),
                    'Cat' => _STATS('Denied categories'),
            		),
        		),
            'Infected' => array(
                'Title' => _STATS('Infected requests'),
                'Needle' => 'INFECTED',
                'Color' => 'red',
                'NVPs' => array(
                    'Link' => _STATS('Requests infected'),
                    'IP' => _STATS('Infected IPs'),
            		),
        		),
            'Bypassed' => array(
                'Title' => _STATS('Bypassed denials'),
                'Needle' => 'GBYPASS| Bypass ',
                'Color' => '#FF8000',
                'NVPs' => array(
                    'Link' => _STATS('Requests bypassed'),
                    'IP' => _STATS('Bypassing IPs'),
            		),
        		),
    		),
        'SubMenus' => array(
            'accesslogs.php' => array(
                'archives' => _MENU('Archives'),
                'live' => _MENU('Live'),
        		),
            'stats.php' => array(
                'general' => _MENU('General'),
                'daily' => _MENU('Daily'),
                'hourly' => _MENU('Hourly'),
                'live' => _MENU('Live'),
        		),
    		),
		),
    'blacklists' => array(
        'Fields' => array(
            'Category',
            'Site',
    		),
        'HighlightLogs' => array(
            'REs' => array(
				// Do not highlight any blacklist search result
        		),
    		),
        'SubMenus' => array(
            'cats.php' => array(
                'sites' => _MENU('Domains'),
                'urls' => _MENU('URLs'),
                'phrases' => _MENU('Phrases'),
                'blacklists' => _MENU('Blacklists'),
        		),
    		),
		),
    'dhcpd' => array(
        'Name' => _TITLE2('DHCP Server'),
        'Path' => 'dhcpd',
        'Fields' => array(
            'Date',
            'Time',
            'Process',
            'Log',
    		),
        'SubMenus' => array(
            'stats.php' => array(
                'general' => _MENU('General'),
                'daily' => _MENU('Daily'),
                'hourly' => _MENU('Hourly'),
                'live' => _MENU('Live'),
        		),
            'logs.php' => array(
                'archives' => _MENU('Archives'),
                'live' => _MENU('Live'),
        		),
    		),
		),
    'arp' => array(
        'Fields' => array(
            'IP',
            'MAC',
            'Interface',
    		),
		),
    'lease' => array(
        'Fields' => array(
            'IP',
            'Starts (UTC)',
            'Ends (UTC)',
            'MAC',
            'Host',
            'Status',
    		),
		),
    'named' => array(
        'Name' => _TITLE2('DNS Server'),
        'Path' => 'named',
        'Fields' => array(
            'Date',
            'Time',
            'Process',
            'Log',
    		),
        'Stats' => array(
            'Total' => array(
                'Title' => _STATS('All queries'),
                'Cmd' => '/bin/cat <LF>',
                'Needle' => '( query: )',
                'NVPs' => array(),
                'BriefStats' => array(
                    'Domain' => _STATS('Domains'),
                    'IP' => _STATS('IPs querying'),
                    'Type' => _STATS('Query types'),
            		),
                'Counters' => array(),
        		),
            'Queries' => array(
                'Title' => _STATS('All queries'),
                'Needle' => '( query: )',
                'Color' => '#004a4a',
                'NVPs' => array(
                    'Domain' => _STATS('Domains'),
                    'IP' => _STATS('IPs querying'),
                    'Type' => _STATS('Query types'),
            		),
        		),
    		),
        'SubMenus' => array(
            'stats.php' => array(
                'general' => _MENU('General'),
                'daily' => _MENU('Daily'),
                'hourly' => _MENU('Hourly'),
                'live' => _MENU('Live'),
        		),
            'logs.php' => array(
                'archives' => _MENU('Archives'),
                'live' => _MENU('Live'),
        		),
    		),
		),
    'squid' => array(
        'Name' => _TITLE2('HTTP Proxy'),
        'Path' => 'squid',
        'Fields' => array(
            'DateTime',
            'Target',
            'Link',
            'Size',
            'Mtd',
            'Code',
            'Direct',
            'Cache',
            'Type',
    		),
        'HighlightLogs' => array(
            'REs' => array(
                'red' => array('ERROR'),
                'yellow' => array('MISS'),
                'green' => array('HIT'),
        		),
    		),
        'Stats' => array(
            'Total' => array(
                'Title' => _STATS('All requests'),
                'Cmd' => '/bin/cat <LF>',
                'Needle' => '',
                'Color' => '#004a4a',
                'NVPs' => array(
					'Link' => _STATS('Links'),
                    'Mtd' => _STATS('Methods'),
					'Target' => _STATS('Target'),
                    'Code' => _STATS('HTTP Codes'),
            		),
                'BriefStats' => array(
                    'Link' => _STATS('Links'),
                    'Mtd' => _STATS('Methods'),
                    'Code' => _STATS('HTTP Codes'),
                    'Cache' => _STATS('Cache'),
                    'Type' => _STATS('Type'),
            		),
                'Counters' => array(
                    'Sizes' => array(
                        'Field' => 'Size',
                        'Title' => _STATS('Downloaded (KB)'),
                        'Color' => '#FF8000',
                        'Divisor' => 1000,
                        'NVPs' => array(
                            'Link' => _STATS('Size by Link (KB)'),
                            'Target' => _STATS('Size by Target (KB)'),
                    		),
                		),
            		),
        		),
    		),
        'SubMenus' => array(
            'stats.php' => array(
                'general' => _MENU('General'),
                'daily' => _MENU('Daily'),
                'hourly' => _MENU('Hourly'),
                'live' => _MENU('Live'),
        		),
            'logs.php' => array(
                'archives' => _MENU('Archives'),
                'live' => _MENU('Live'),
        		),
    		),
		),
    'httpd' => array(
        'Name' => _TITLE2('Web Server'),
        'Path' => 'httpd',
        'Fields' => array(
            'Date',
            'Time',
            'Level',
            'Log',
    		),
        'HighlightLogs' => array(
            'REs' => array(
                'red' => array('\berror\b'),
                'yellow' => array('\bwarning\b', '\bnotice\b'),
                'green' => array('\bsuccess'),
        		),
    		),
        'SubMenus' => array(
            'logs.php' => array(
                'archives' => _MENU('Archives'),
                'live' => _MENU('Live'),
        		),
    		),
		),
    'httpdlogs' => array(
        'Name' => _TITLE2('Web Server Access'),
        'Fields' => array(
            'DateTime',
            'IP',
            'Mtd',
            'Link',
            'Code',
            'Size',
    		),
        'Stats' => array(
            'Total' => array(
                'Title' => _STATS('All requests'),
                'Cmd' => '/bin/cat <LF>',
                'Needle' => '',
                'Color' => '#004a4a',
                'NVPs' => array(
					'IP' => _STATS('Clients'),
					'Link' => _STATS('Links'),
                    'Mtd' => _STATS('Methods'),
                    'Code' => _STATS('HTTP Codes'),
            		),
                'BriefStats' => array(
                    'IP' => _STATS('Clients'),
                    'Mtd' => _STATS('Methods'),
                    'Code' => _STATS('HTTP Codes'),
                    'Link' => _STATS('Links'),
            		),
                'Counters' => array(
                    'Sizes' => array(
                        'Field' => 'Size',
                        'Title' => _STATS('Downloaded (KB)'),
                        'Color' => '#FF8000',
                        'Divisor' => 1000,
                        'NVPs' => array(
                            'Link' => _STATS('Size by Link (KB)'),
                            'IP' => _STATS('Size by IP (KB)'),
                    		),
                		),
            		),
        		),
    		),
        'SubMenus' => array(
            'stats.php' => array(
                'general' => _MENU('General'),
                'daily' => _MENU('Daily'),
                'hourly' => _MENU('Hourly'),
                'live' => _MENU('Live'),
                'charts' => _MENU('Charts'),
        		),
            'accesslogs.php' => array(
                'archives' => _MENU('Archives'),
                'live' => _MENU('Live'),
        		),
    		),
		),
    'cwwui_syslog' => array(
        'Name' => _TITLE2('WUI'),
        'Fields' => array(
            'Date',
            'Time',
            'LogLevel',
            'User',
            'IP',
            'File',
            'Function',
            'Line',
            'Reason',
            'Log',
    		),
        'HighlightLogs' => array(
            'Col' => 'LogLevel',
            'REs' => array(
                'red' => array('LOG_EMERG', 'LOG_ALERT', 'LOG_CRIT', 'LOG_ERR'),
                'yellow' => array('LOG_WARNING', 'LOG_NOTICE'),
                'green' => array('LOG_INFO'),
        		),
    		),
        'SubMenus' => array(
            'cwwuilogs.php' => array(
                'archives' => _MENU('Archives'),
                'live' => _MENU('Live'),
        		),
    		),
		),
    'cwc_syslog' => array(
        'Name' => _TITLE2('CWC'),
        'Fields' => array(
            'Date',
            'Time',
            'LogLevel',
            'File',
            'Function',
            'Line',
            'Reason',
            'Log',
    		),
        'HighlightLogs' => array(
            'Col' => 'LogLevel',
            'REs' => array(
                'red' => array('LOG_EMERG', 'LOG_ALERT', 'LOG_CRIT', 'LOG_ERR'),
                'yellow' => array('LOG_WARNING', 'LOG_NOTICE'),
                'green' => array('LOG_INFO'),
        		),
    		),
        'SubMenus' => array(
            'cwclogs.php' => array(
                'archives' => _MENU('Archives'),
                'live' => _MENU('Live'),
        		),
    		),
		),
    'snort' => array(
        'Name' => _TITLE2('Intrusion Detection'),
        'Path' => 'snort',
        'Fields' => array(
            'Date',
            'Time',
            'Process',
            'Log',
    		),
        'SubMenus' => array(
            'logs.php' => array(
                'archives' => _MENU('Archives'),
                'live' => _MENU('Live'),
        		),
            'conf.php' => array(
                'basic' => _MENU('Basic'),
                'advanced' => _MENU('Advanced'),
                'rules' => _MENU('Rules'),
        		),
    		),
		),
    'snortalerts' => array(
        'Name' => _TITLE2('Intrusion Alerts'),
        'Path' => 'snort',
        'Fields' => array(
            'Date',
            'Time',
            'Process',
            'Log',
            'Prio',
            'Proto',
            'SrcIP',
            'SPort',
            'DstIP',
            'DPort',
    		),
        'HighlightLogs' => array(
            'Col' => 'Prio',
    		),
        'Stats' => array(
            'Total' => array(
                'Title' => _STATS('All alerts'),
                'Cmd' => '/bin/cat <LF>',
                'Needle' => '( -> )',
                'Color' => '#004a4a',
                'NVPs' => array(
                    'SrcIP' => _STATS('Source IPs'),
                    'DstIP' => _STATS('Target IPs'),
                    'SPort' => _STATS('Source Ports'),
                    'DPort' => _STATS('Target Ports'),
            		),
                'BriefStats' => array(
                    'SrcIP' => _STATS('Source IPs'),
                    'DstIP' => _STATS('Target IPs'),
                    'DPort' => _STATS('Target Ports'),
                    'Prio' => _STATS('Priorities'),
            		),
                'Counters' => array(),
        		),
            'Priorities' => array(
                'Title' => _STATS('Priorities'),
                'Needle' => '( -> )',
                'Color' => 'Red',
                'NVPs' => array(
                    'Prio' => _STATS('Priority'),
            		),
        		),
            'Names' => array(
                'Title' => _STATS('Attack Types'),
                'Needle' => '( -> )',
                'Color' => 'Blue',
                'NVPs' => array(
                    'Log' => _STATS('Type'),
            		),
        		),
    		),
        'SubMenus' => array(
            'stats.php' => array(
                'general' => _MENU('General'),
                'daily' => _MENU('Daily'),
                'hourly' => _MENU('Hourly'),
                'live' => _MENU('Live'),
        		),
            'alerts.php' => array(
                'archives' => _MENU('Archives'),
                'live' => _MENU('Live'),
        		),
    		),
		),
    'snortips' => array(
        'Name' => _TITLE2('Intrusion Prevention'),
        'Path' => 'snortips',
        'Fields' => array(
            'Date',
            'Time',
            'Process',
            'Log',
    		),
        'HighlightLogs' => array(
            'REs' => array(
                'red' => array('Blocking', 'already blocked', 'Exiting'),
                'yellow' => array('Starting', 'Loaded'),
                'green' => array('Unblocking'),
        		),
    		),
        'Stats' => array(
            'Total' => array(
                'Title' => _STATS('All un/blocks and extensions'),
                'Cmd' => '/bin/cat <LF>',
                'Needle' => '(Blocking|Unblocking|extending| blocking)',
                'Color' => '#004a4a',
                'NVPs' => array(
                    'Blocked' => _STATS('Blocked Hosts'),
                    'Unblocking' => _STATS('Unblocked Hosts'),
                    'Extended' => _STATS('Extended Hosts'),
            		),
                'BriefStats' => array(
                    'Softinit' => _STATS('Soft inits (unblock all)'),
                    'Blocked' => _STATS('Blocked Hosts'),
                    'Extended' => _STATS('Extended Hosts'),
                    'Unblocking' => _STATS('Unblocked Hosts'),
                    'BlockedTime' => _STATS('Blocked Times (sec)'),
            		),
                'Counters' => array(
                    'BlockedTime' => array(
                        'Field' => 'BlockedTime',
                        'Title' => _STATS('Blocked Times (min)'),
                        'Color' => '#FF8000',
                        'Divisor' => 60,
                        'NVPs' => array(
                            'Blocked' => _STATS('Blocked Hosts'),
                    		),
                		),
                    'ExtendedTime' => array(
                        'Field' => 'ExtendedTime',
                        'Title' => _STATS('Extended Times (min)'),
                        'Color' => '#FF8000',
                        'Divisor' => 60,
                        'NVPs' => array(
                            'Extended' => _STATS('Extended Hosts'),
                    		),
                		),
            		),
        		),
            'Blocked' => array(
                'Title' => _STATS('Blocked Hosts'),
                'Needle' => '(Blocking| blocking)',
                'Color' => 'Red',
                'NVPs' => array(
                    'Blocked' => _STATS('Blocked Hosts'),
                    'BlockedTime' => _STATS('Blocked Time'),
            		),
        		),
            'Unblocking' => array(
                'Title' => _STATS('Unblocked Hosts'),
                'Needle' => '(Unblocking)',
                'Color' => 'Green',
                'NVPs' => array(
                    'Unblocking' => _STATS('Unblocked Hosts'),
            		),
        		),
            'Softinit' => array(
                'Title' => _STATS('Soft inits'),
                'Needle' => '(Soft init)',
                'Color' => 'Blue',
                'NVPs' => array(
                    'Softinit' => _STATS('Soft inits (unblock all)'),
            		),
        		),
    		),
        'SubMenus' => array(
            'stats.php' => array(
                'general' => _MENU('General'),
                'daily' => _MENU('Daily'),
                'hourly' => _MENU('Hourly'),
                'live' => _MENU('Live'),
        		),
            'logs.php' => array(
                'archives' => _MENU('Archives'),
                'live' => _MENU('Live'),
        		),
            'conf.php' => array(
                'basic' => _MENU('Basic'),
                'managed' => _MENU('Managed'),
                'lists' => _MENU('Lists'),
        		),
    		),
		),
    'dante' => array(
        'Name' => _TITLE2('SOCKS Proxy'),
        'Path' => 'dante',
        'Fields' => array(
            'Date',
            'Time',
            'Process',
            'Log',
    		),
        'HighlightLogs' => array(
            'REs' => array(
                'red' => array('Connection timed out', 'Connection refused', 'Connection reset by peer'),
                'yellow' => array('remote close'),
                'green' => array('connect', 'accept'),
        		),
    		),
        'SubMenus' => array(
            'logs.php' => array(
                'archives' => _MENU('Archives'),
                'live' => _MENU('Live'),
        		),
    		),
		),
    'openssh' => array(
        'Name' => _TITLE2('OpenSSH'),
        'Path' => 'openssh',
        'Fields' => array(
            'Date',
            'Time',
            'Process',
            'Log',
    		),
        'HighlightLogs' => array(
            'REs' => array(
                'red' => array('Failed'),
                'yellow' => array('WARNING'),
                'green' => array('Accepted'),
        		),
    		),
        'Stats' => array(
            'Total' => array(
                'Title' => _STATS('All attempts'),
                'Cmd' => '/bin/cat <LF>',
                'Needle' => '(Accepted|Failed)',
                'Color' => '#004a4a',
                'NVPs' => array(
                    'IP' => _STATS('Client IPs'),
                    'User' => _STATS('Users'),
                    'Type' => _STATS('SSH version'),
            		),
                'BriefStats' => array(
                    'Type' => _STATS('SSH version'),
                    'Reason' => _STATS('Failure reason'),
                    'IP' => _STATS('Client IPs'),
                    'User' => _STATS('Users'),
            		),
                'Counters' => array(),
        		),
            'Failures' => array(
                'Title' => _STATS('Failed attempts'),
                'Needle' => '(Failed .* for )',
                'Color' => 'Red',
                'NVPs' => array(
                    'IP' => _STATS('Client IPs'),
                    'User' => _STATS('Failed users'),
                    'Type' => _STATS('SSH version'),
                    'Reason' => _STATS('Failure reason'),
            		),
        		),
            'Successes' => array(
                'Title' => _STATS('Successful logins'),
                'Needle' => '(Accepted .* for )',
                'Color' => 'Green',
                'NVPs' => array(
                    'IP' => _STATS('Client IPs'),
                    'User' => _STATS('Logged in user'),
                    'Type' => _STATS('SSH version'),
            		),
        		),
    		),
        'SubMenus' => array(
            'stats.php' => array(
                'general' => _MENU('General'),
                'daily' => _MENU('Daily'),
                'hourly' => _MENU('Hourly'),
                'live' => _MENU('Live'),
        		),
            'logs.php' => array(
                'archives' => _MENU('Archives'),
                'live' => _MENU('Live'),
        		),
    		),
		),
    'openvpn' => array(
        'Name' => _TITLE2('OpenVPN'),
        'Path' => 'openvpn',
        'Fields' => array(
            'Date',
            'Time',
            'Process',
            'Log',
    		),
        'HighlightLogs' => array(
            'REs' => array(
                'red' => array('Failed'),
                'yellow' => array('WARNING'),
                'green' => array('Accepted'),
        		),
    		),
        'SubMenus' => array(
            'logs.php' => array(
                'archives' => _MENU('Archives'),
                'live' => _MENU('Live'),
        		),
    		),
		),
    'clamd' => array(
        'Name' => _TITLE2('Virus Filter'),
        'Path' => 'clamav',
        'Fields' => array(
            'Date',
            'Time',
            'Log',
    		),
        'HighlightLogs' => array(
            'REs' => array(
                'red' => array('FOUND', 'ERROR'),
                'yellow' => array('Started', 'Database modification detected'),
                'green' => array('Database status OK', 'Database correctly reloaded'),
        		),
    		),
        'SubMenus' => array(
            'logs.php' => array(
                'archives' => _MENU('Archives'),
                'live' => _MENU('Live'),
        		),
            'conf.php' => array(
                'clamd' => _MENU('Clamd'),
                'freshclam' => _MENU('DB'),
        		),
    		),
		),
    'freshclam' => array(
        'Name' => _TITLE2('Virus DB Update'),
        'Path' => 'clamav',
        'Fields' => array(
            'Date',
            'Time',
            'Log',
    		),
        'HighlightLogs' => array(
            'REs' => array(
                'red' => array('failed', 'ERROR'),
                'yellow' => array('update process started at', 'Waiting to lock database directory'),
                'green' => array('up to date', 'updated', '\bsuccess'),
        		),
    		),
        'SubMenus' => array(
            'freshclamlogs.php' => array(
                'archives' => _MENU('Archives'),
                'live' => _MENU('Live'),
        		),
            'conf.php' => array(
                'clamd' => _MENU('Clamd'),
                'freshclam' => _MENU('DB'),
        		),
    		),
		),
    'ftp-proxy' => array(
        'Name' => _TITLE2('FTP Proxy'),
        'Path' => 'ftp-proxy',
        'Fields' => array(
            'Date',
            'Time',
            'Process',
            'Log',
    		),
        'Stats' => array(
            'Total' => array(
                'Title' => _STATS('All sessions'),
                'Cmd' => '/bin/cat <LF>',
                'Needle' => '(FTP session )',
                'Color' => '#004a4a',
                'NVPs' => array(
                    'Client' => _STATS('Client'),
                    'Server' => _STATS('Server'),
            		),
                'BriefStats' => array(
                    'Client' => _STATS('Client'),
                    'Server' => _STATS('Server'),
            		),
        		),
    		),
        'SubMenus' => array(
            'stats.php' => array(
                'general' => _MENU('General'),
                'daily' => _MENU('Daily'),
                'hourly' => _MENU('Hourly'),
                'live' => _MENU('Live'),
        		),
            'logs.php' => array(
                'archives' => _MENU('Archives'),
                'live' => _MENU('Live'),
        		),
    		),
		),
    'monitoring' => array(
        'Name' => _TITLE2('Monitoring'),
        'Path' => 'monitoring',
        'Fields' => array(
            'Date',
            'Time',
            'Process',
            'Log',
    		),
        'SubMenus' => array(
            'logs.php' => array(
                'archives' => _MENU('Archives'),
                'live' => _MENU('Live'),
        		),
    		),
		),
    'symon' => array(
        'Name' => _TITLE2('Symon'),
		),
    'symux' => array(
        'Name' => _TITLE2('Symux'),
		),
    'pmacct' => array(
        'Name' => _TITLE2('Pmacct'),
		),
    'docs' => array(
        'Name' => _TITLE2('System Administration Guides'),
        // Expanded by sh to get all SAGs under the docs directory
        'LogFile' => '/var/www/htdocs/comixwall/docs/ComixWall*SAG*.pdf',
		),
);

$ModelsToLogConfig= array(
	'system',
	'pf',
	'e2guardian',
	'e2guardianlogs',
	'squid',
	'snort',
	'snortalerts',
	'snortips',
	'clamd',
	'freshclam',
	'dhcpd',
	'named',
	'openvpn',
	'openssh',
	'ftp-proxy',
	'dante',
	'httpd',
	'httpdlogs',
	'cwwui_syslog',
	'cwc_syslog',
	'monitoring',
	);
?>
