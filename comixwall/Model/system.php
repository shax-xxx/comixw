<?php
/* $ComixWall: system.php,v 1.53 2009/11/26 12:03:38 soner Exp $ */

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
 * System-wide.
 */

require_once($MODEL_PATH.'model.php');

class System extends Model
{
	public $Name= 'system';
	public $User= '\S+';
	
	private $confDir= '/etc/';
	private $rcLocalServices= array();
	private $rcConfLocalServices= array();

	public $LogFile= '/var/log/messages';

	function System()
	{
		parent::Model();
	
		$this->Proc= '.';
		
		/** rc.local module search strings and descriptions
		 *
		 * rc.local file should have lines like the following:
		 *
		 * <pre>
		 * if [ -x /usr/local/libexec/symon ]; then
		 * 	echo -n ' symon';
		 * 	/usr/local/libexec/symon
		 * fi
		 * </pre>
		 *
		 * Indeces of this array are used to comment or uncomment
		 * the lines like the 3rd one above.
		 */
		$this->rcLocalServices= array(
			'/usr/local/sbin/e2guardian',
			'/usr/local/sbin/squid',
			'/usr/local/bin/snort',
			'/usr/local/sbin/snortips',
			'/usr/local/sbin/clamd',
			'/usr/local/bin/freshclam',
			'/usr/local/sbin/sockd',
			'/usr/local/libexec/symux',
			'/usr/local/libexec/symon',
			'/usr/local/sbin/pmacctd',
			);

		/// rc.conf.local module search strings and descriptions
		$this->rcConfLocalServices= array(
			'pf',
			'dhcpd_flags',
			'named_flags',
			'ftpproxy_flags',
			'httpd_flags',
			'ntpd_flags',
			'apmd_flags',
			);
				
		$this->Commands= array_merge(
			$this->Commands,
			array(
				'GetMyName'		=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Read system hostname'),
					),

				'GetRootEmail'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get system admin e-mail address'),
					),

				'GetIfConfig'		=>	array(
					'argv'	=>	array(NAME),
					'desc'	=>	_('Get if config'),
					),

				'GetStaticGateway'		=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Read system gateway'),
					),

				'GetHosts'		=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('List hosts'),
					),

				'GetNameServer'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Read system nameserver'),
					),

				'GetConfig'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get configuration'),
					),

				'SetMyName'		=>	array(
					'argv'	=>	array(NAME),
					'desc'	=>	_('Set system hostname'),
					),

				'SetRootEmail'	=>	array(
					'argv'	=>	array(EMAIL),
					'desc'	=>	_('Set e-mail address'),
					),

				'SystemMakeStaticGateway'		=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Make system gateway static'),
					),

				'SystemMakeDynamicGateway'		=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Make system gateway dynamic'),
					),

				'GetDynamicGateway'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get system gateway'),
					),

				'SetMyGate'		=>	array(
					'argv'	=>	array(IPADR),
					'desc'	=>	_('Set system gateway'),
					),

				'SetIf'		=>	array(
					/// @todo Is there any pattern or size for options, 6th param?
					'argv'	=>	array(NAME, NAME, IPADR|NAME|EMPTYSTR, IPADR|NAME|EMPTYSTR, IPADR|NAME|EMPTYSTR, STR|EMPTYSTR),
					'desc'	=>	_('Configure an interface'),
					),

				'DeleteIf'	=>	array(
					'argv'	=>	array(NAME),
					'desc'	=>	_('Unconfigure an interface'),
					),

				'SetNameServer'	=>	array(
					'argv'	=>	array(IPADR),
					'desc'	=>	_('Set system nameserver'),
					),

				'AddHost'		=>	array(
					'argv'	=>	array(HOST),
					'desc'	=>	_('Add host'),
					),

				'DelHost'		=>	array(
					'argv'	=>	array(HOST),
					'desc'	=>	_('Delete host'),
					),

				'GetServiceStartStatus'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get service start status'),
					),

				'DisableService'=>	array(
					'argv'	=>	array(URL),
					'desc'	=>	_('Turn off service'),
					),

				'EnableService'=>	array(
					'argv'	=>	array(URL),
					'desc'	=>	_('Turn on service'),
					),

				'SetDateTime'		=>	array(
					'argv'	=>	array(DATETIME),
					'desc'	=>	_('Set system clock'),
					),

				'UpdateMailAliases'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Update mail aliases'),
					),

				'DisplayRemoteTime'	=>	array(
					'argv'	=>	array(URL|IP),
					'desc'	=>	_('Display remote time'),
					),
				
				'SetRemoteTime'	=>	array(
					'argv'	=>	array(URL|IP),
					'desc'	=>	_('Set remote time'),
					),

				'GetRemoteTime'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get remote time'),
					),

				'AutoConfig'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Automatic configuration'),
					),

				'InitGraphs'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Init graphs'),
					),

				'DeleteStats'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Erase statistics files'),
					),
				
				'Shutdown'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('System shutdown'),
					),

				'Restart'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('System restart'),
					),

				'GetPartitionsPfw'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get partition list'),
					),

				'GetSystemInfo'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get system information'),
					),

				'GetLogsConfig'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get logs configuration'),
					),

				'SetLogsConfig'=>	array(
					'argv'	=>	array(NAME, FILEPATH, NUM, NUM|ASTERISK, NUM|ASTERISK),
					'desc'	=>	_('Set logs configuration'),
					),

				'RotateLogFile'=>	array(
					'argv'	=>	array(FILEPATH),
					'desc'	=>	_('Rotate log file'),
					),

				'RotateAllLogFiles'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Rotate all log files'),
					),

				'SetManCgiHome'=>	array(
					'argv'	=>	array(IPADR),
					'desc'	=>	_('Set man.cgi home'),
					),
				
				'SetPassword'	=>	array(
					'argv'	=>	array(NAME, SHA1STR),
					'desc'	=>	_('Set user password'),
					),

				'SetLocale'=>	array(
					'argv'	=>	array(NAME),
					'desc'	=>	_('Set locale'),
					),

				'SetLogLevel'=>	array(
					'argv'	=>	array(NAME),
					'desc'	=>	_('Set log level'),
					),

				'SetHelpBoxes'=>	array(
					'argv'	=>	array(NAME),
					'desc'	=>	_('Set help boxes'),
					),

				'SetReloadRate'=>	array(
					'argv'	=>	array(NUM),
					'desc'	=>	_('Set reload rate'),
					),

				'SetSessionTimeout'=>	array(
					'argv'	=>	array(NUM),
					'desc'	=>	_('Set session timeout'),
					),

				'NetStart'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Restart network'),
					),
				)
			);
	}

	/** Adds host line to hosts file.
	 *
	 * @param[in]	$host	Host definition line.
	 */
	function AddHost($host)
	{
		$this->DelHost($host);
		return $this->AppendToFile($this->confDir.'hosts', $host);
	}

	/** Deletes host from hosts file.
	 *
	 * @param[in]	$host	Host definition line.
	 */
	function DelHost($host)
	{
		return $this->ReplaceRegexp($this->confDir.'hosts', "/^(\h*$host(\s|))/m", '');
	}

	/** Reads hosts file contents.
	 *
	 * @return Hosts file uncommented lines.
	 */
	function GetHosts()
	{
		global $Re_Ip;

		return $this->SearchFileAll($this->confDir.'hosts', "/^\h*(($Re_Ip|[:\d]+)\b.*)\h*$/m");
	}

	/** Reads gateway address.
	 *
	 * @return IP of the gateway.
	 */
	function GetStaticGateway()
	{
		return $this->GetFile($this->confDir.'mygate');
	}

	/** Reads hostname.
	 *
	 * @return System name, output of hostname too.
	 */
	function GetMyName()
	{
		return $this->GetFile($this->confDir.'myname');
	}

	/** Reads nameserver setting.
	 *
	 * @return ystem-wide nameserver.
	 */
	function GetNameServer()
	{
		return $this->SearchFile($this->confDir.'resolv.conf', "/^\h*nameserver\h*([^#]*)\h*$/m");
	}

	/** Reads root e-mail address.
	 *
	 * @return oot address.
	 */
	function GetRootEmail()
	{
		return $this->SearchFile($this->confDir.'mail/aliases', "/^\h*root:\h*([^#]*)\h*$/m");
	}

	function GetIfConfig($if)
	{
		$file= $this->confDir."hostname.".$if;
		if (file_exists($file)) {
			if (($contents= $this->GetFile($file)) !== FALSE) {
				$re= '^\s*(inet|dhcp)\s*(\S*)\s*(\S*)\s*(\S*)\s*(\S*)\s*$';
				if (preg_match("/$re/m", $contents, $match)) {
					return serialize(array_slice($match, 1));
				}
			}
		}
		return FALSE;
	}

	/** Gets the default gateway from routing table.
	 */
	function GetDynamicGateway()
	{
		global $Re_Ip;

		$cmd= "/sbin/route -n get default | /usr/bin/grep gateway 2>&1";
		exec($cmd, $output, $retval);
		if ($retval === 0) {
			if (count($output) > 0) {
				#    gateway: 10.0.0.2
				$re= "\s*gateway:\s*($Re_Ip)\s*";
				if (preg_match("/$re/m", $output[0], $match)) {
					return $match[1];
				}
			}
		}
		else {
			$errout= implode("\n", $output);
			ViewError($errout);
			cwc_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Get dynamic gateway failed: $errout");
		}
		return FALSE;
	}

	function GetConfig()
	{
		global $MODEL_PATH, $ModelFiles, $Models;
		
		$config= array();
		
		if (($myname= $this->GetMyName()) !== FALSE) {
			$config['Myname']= trim($myname);
		}
	
		if (($mygate= $this->GetStaticGateway()) !== FALSE) {
			$config['Mygate']= trim($mygate);
			$config['StaticGateway']= TRUE;
		}
		else if (($mygate= $this->GetDynamicGateway()) !== FALSE) {
			$config['Mygate']= trim($mygate);
			$config['StaticGateway']= FALSE;
		}

		if (($intif= $this->GetIntIf()) !== FALSE) {
			$config['IntIf']= trim($intif, '"');
		}
		
		if (($extif= $this->GetExtIf()) !== FALSE) {
			$config['ExtIf']= trim($extif, '"');
		}
		
		if (($ifs= $this->GetPhyIfs()) !== FALSE) {
			$ifs= explode("\n", $ifs);
			foreach ($ifs as $if) {
				$config['Ifs'][$if]= array();
				if (($output= $this->GetIfConfig($if)) !== FALSE) {
					$config['Ifs'][$if]= unserialize($output);
				}
			}
		}
		
		return serialize($config);
	}

	function SystemMakeStaticGateway()
	{
		if (($gateway= $this->GetDynamicGateway()) !== FALSE) {
			return $this->SetMyGate($gateway);
		}
		return FALSE;
	}

	function SystemMakeDynamicGateway()
	{
		return $this->DeleteFile($this->confDir.'mygate');
	}

	/** Sets system gateway.
	 *
	 * @param[in]	$mygate	Gateway IP.
	 */
	function SetMyGate($mygate)
	{
		// If filename does not exist, the file is created. Otherwise, the existing file is overwritten
		return file_put_contents($this->confDir.'mygate', $mygate.PHP_EOL);
	}

	/** Sets system hostname.
	 *
	 * @param[in]	$myname	Hostname.
	 */
	function SetMyName($myname)
	{
		return file_put_contents($this->confDir.'myname', $myname.PHP_EOL);
	}

	/** Sets system interface configuration.
	 *
	 * @param[in]	$if		Interface name.
	 * @param[in]	$type	inet or dhcp.
	 * @param[in]	$ip		IP.
	 * @param[in]	$mask	Netmask.
	 * @param[in]	$bc		Broadcast address.
	 * @param[in]	$opt	Options.
	 */
	function SetIf($if, $type, $ip, $mask, $bc, $opt)
	{
		global $Re_Ip;
		
		// Trim for whitespace caused by empty strings
		$ifconf= trim("$type $ip $mask $bc $opt");
		// ComixWall supports only these configuration
		if (preg_match("/^inet\s*$Re_Ip\s*$Re_Ip\s*($Re_Ip|).*$/", $ifconf)
			|| preg_match('/^dhcp\s*NONE\s*NONE\s*NONE.*$/', $ifconf)
			|| preg_match('/^dhcp$/', $ifconf)) {
			/// @warning Need a new line at the end of a line in hostname.if (otherwise /etc/netstart fails), file_put_contents() removes the last new line
			return file_put_contents($this->confDir.'hostname.'.$if, $ifconf.PHP_EOL);
		}
		else {
			ViewError(_('Unsupported interface configuration').": $ifconf");
		}
		return FALSE;
	}

	/** Unconfigures interface by deleting its hostname file.
	 *
	 * @param[in]	$if		Interface name.
	 */
	function DeleteIf($if)
	{
		exec("/sbin/ifconfig $if down");
		exec("/sbin/ifconfig $if delete");
		return $this->DeleteFile($this->confDir.'hostname.'.$if);
	}

	/** Changes nameserver.
	 *
	 * @param[in]	$nameserver	System nameserver IP.
	 */
	function SetNameServer($nameserver)
	{
		global $Re_Ip;
		
		return $this->ReplaceRegexp($this->confDir.'resolv.conf', "/^(\h*nameserver\h*)($Re_Ip)(\b.*)$/m", '${1}'.$nameserver.'${3}');
	}

	/** Change root e-mail address.
	 *
	 * @param[in]	$emailaddr	E-mail address.
	 */
	function SetRootEmail($emailaddr)
	{
		return $this->ReplaceRegexp($this->confDir.'mail/aliases', "/^(\h*root:\h*)([^#\s]*)(.*)$/m", '${1}'.$emailaddr.'${3}');
	}

	/** Set int_net.
	 */
	function SetManCgiHome($ip)
	{
		$re= '|^(\s*\$www\{\'home\'\}\h*=\h*\')(.*)(\'\h*;\h*)$|m';
		return $this->ReplaceRegexp('/var/www/cgi-bin/man.cgi', $re, '${1}'."https://$ip".'${3}');
	}

	/** Sets system clock.
	 *
	 * @param[in]	$datetime	string Datetime.
	 */
	function SetDateTime($datetime)
	{
		exec("/bin/date $datetime 2>&1", $output, $retval);
		/// Date returns 2 on locally successful.
		if ($retval === 2) {
			return TRUE;
		}
		$errout= implode("\n", $output);
		ViewError($errout);
		cwc_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Set date failed: $errout");
		return FALSE;
	}

	/** Update mail aliases.
	 */
	function UpdateMailAliases()
	{
		return $this->RunShellCommand('/usr/bin/newaliases');
	}

	/** Runs installer with automatic configuration option.
	 */
	function AutoConfig()
	{
		global $ROOT;
		
		exec("$ROOT/Installer/install.php -a 2>&1", $output, $retval);
		if ($retval === 0) {
			return TRUE;
		}
		$errout= implode("\n", $output);
		ViewError($errout);
		cwc_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Auto configuration failed: $errout");
		return FALSE;
	}

	/** Delete all graph files, and recreate if necessary.
	 */
	function InitGraphs()
	{
		global $VIEW_PATH;

		$success= TRUE;
		// symon
		exec("/bin/rm -f ${VIEW_PATH}symon/cache/* 2>&1", $output, $retval);
		// Failing to clear the cache dir is not fatal
		exec("/bin/rm -f ${VIEW_PATH}symon/rrds/localhost/*.rrd 2>&1", $output, $retval);
		if ($retval === 0) {
			exec('/bin/sh /usr/local/share/symon/c_smrrds.sh all 2>&1', $output, $retval);
			if ($retval !== 0) {
				$success= FALSE;
			}
		}
		else {
			$success= FALSE;
		}
		
		// pnrg
		exec("/bin/rm -f ${VIEW_PATH}pmacct/protograph/comixwall.rrd 2>&1", $output, $retval);
		if ($retval === 0) {
			exec("/bin/sh ${VIEW_PATH}pmacct/protograph/createrrd.sh 2>&1", $output, $retval);
			if ($retval !== 0) {
				$success= FALSE;
			}
		}
		else {
			$success= FALSE;
		}
		
		// protograph
		exec("/bin/rm -f ${VIEW_PATH}pmacct/pnrg/spool/*.{gif,cgi,rrd,desc} 2>&1", $output, $retval);
		if ($retval !== 0) {
			$success= FALSE;
		}
				
		if (!$success) {
			$errout= implode("\n", $output);
			ViewError($errout);
			cwc_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed initializing graphs: $errout");
		}
		return $success;
	}

	/** Delete files under /var/tmp/comixwall.
	 */
	function DeleteStats()
	{
		exec('/bin/rm -rf /var/tmp/comixwall/* 2>&1', $output, $retval);
		if ($retval === 0) {
			return TRUE;
		}
		$errout= implode("\n", $output);
		ViewError($errout);
		cwc_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed erasing statistics files: $errout");
		return FALSE;
	}
	
	/** Shut down.
	 */
	function Shutdown()
	{
		global $TmpFile;
		
		$this->RunShellCommand("/sbin/shutdown -h -p now > $TmpFile 2>&1 &");
	}

	function Restart()
	{
		global $TmpFile;
		
		$this->RunShellCommand("/sbin/shutdown -r now > $TmpFile 2>&1 &");
	}

	/** Gets pfw partition list.
	 */
	function GetPartitionsPfw()
	{
		global $VIEW_PATH;
		
		return $this->RunShellCommand("${VIEW_PATH}pf/bin/commandwrapper.sh localhost -df");
	}

	/** Gets system information.
	 */
	function GetSystemInfo()
	{
		global $VIEW_PATH;
		
		return $this->RunShellCommand("${VIEW_PATH}pf/bin/commandwrapper.sh localhost -info");
	}

	/** Displays datetime from time server.
	 */
	function DisplayRemoteTime($timeserver)
	{
		global $TmpFile;
		
		$this->Pkill('rdate');
		return $this->RunShellCommand("/usr/sbin/rdate -pn $timeserver > $TmpFile 2>&1 &");
	}

	/** Sets datetime from time server.
	 */
	function SetRemoteTime($timeserver)
	{
		global $TmpFile;

		$this->Pkill('rdate');
		return $this->RunShellCommand("/usr/sbin/rdate -n $timeserver > $TmpFile 2>&1 &");
	}

	function GetRemoteTime()
	{
		global $TmpFile;
		
		$count= 0;
		while ($count++ < self::PROC_STAT_TIMEOUT) {
			if (!$this->IsRunning('rdate')) {
				break;
			}
			// shell sleep command seems not affected by changes to clock
			exec('/bin/sleep .1');
		}
		
		if ($count < self::PROC_STAT_TIMEOUT) {
			if (($output= $this->GetFile($TmpFile)) !== FALSE) {
				$retval= $output;
			}
		}
		else {
			$retval= _('The process is taking too long, thus will run in the background.');
		}
		return $retval;
	}
	
	/** Gets service start stati.
	 */
	function GetServiceStartStatus()
	{
		$output= array();
		foreach ($this->rcConfLocalServices as $service) {
			$stat= $this->GetServiceStatRcConfLocal($this->rcConfLocal, $service);
			if ($stat === '') {
				$output[$service]= TRUE;
			}
			else if ($stat === '#') {
				$output[$service]= FALSE;
			}
		}
		foreach ($this->rcLocalServices as $service) {
			$stat= $this->GetServiceStatRcLocal($this->confDir.'rc.local', $service);
			if ($stat === '') {
				$output[$service]= TRUE;
			}
			else if ($stat === '#') {
				$output[$service]= FALSE;
			}
		}
		return serialize($output);
	}
	
	/** Gets service startup status in rc.conf.local.
	 *
	 * @param[in]	$file		Config file.
	 * @param[in]	$service	Service name in rc.conf.local.
	 * @return Empty if on, # if off.
	 */
	function GetServiceStatRcConfLocal($file, $service)
	{
		return $this->SearchFile($file, "/^\h*(#|)\h*$service\h*=.*$/m");
	}

	/** Gets service startup status in rc.local.
	 *
	 * @param[in]	$file		Config file.
	 * @param[in]	$service	Service name in rc.local.
	 * @return Empty if on, # if off.
	 */
	function GetServiceStatRcLocal($file, $service)
	{
		$service= Escape($service, '/');
		return $this->SearchFile($file, "/^\h*(#|)\h*$service\b.*$/m");
	}
	
	/** Turn off (disable) service startup.
	 *
	 * @param[in]	$service	Service name.
	 */
	function DisableService($service)
	{
		if (in_array($service, $this->rcConfLocalServices)) {
			return $this->DisableServiceRcConfLocal($service);
		}
		else if (in_array($service, $this->rcLocalServices)) {
			return $this->DisableServiceRcLocal($service);
		}
		else {
			return FALSE;
		}
	}

	/** Turn on (enable) service startup.
	 *
	 * @param[in]	$service	Service name.
	 */
	function EnableService($service)
	{
		if (in_array($service, $this->rcConfLocalServices)) {
			return $this->EnableServiceRcConfLocal($service);
		}
		else if (in_array($service, $this->rcLocalServices)) {
			return $this->EnableServiceRcLocal($service);
		}
		else {
			return FALSE;
		}
	}

	/** Turn off (disable) service startup in rc.conf.local.
	 *
	 * @param[in]	$service	Service name in rc.conf.local.
	 */
	function DisableServiceRcConfLocal($service)
	{
		return $this->ReplaceRegexp($this->rcConfLocal, "/^(\h*$service\h*=.*)$/m", '#${1}');
	}

	/** Turn off (disable) service startup in rc.local.
	 *
	 * @param[in]	$service	Service name in rc.local.
	 */
	function DisableServiceRcLocal($service)
	{
		$service= Escape($service, '/');
		return $this->ReplaceRegexp($this->confDir.'rc.local', "/^(\h*$service\b.*)$/m", '#${1}');
	}

	/** Turn on (enable) service startup in rc.conf.local.
	 *
	 * @param[in]	$service	Service name in rc.conf.local.
	 */
	function EnableServiceRcConfLocal($service)
	{
		return $this->ReplaceRegexp($this->rcConfLocal, "/^\h*#(\h*$service\h*=.*)$/m", '${1}');
	}

	/** Turn on (enable) service startup in rc.local.
	 *
	 * @param[in]	$service	Service name in rc.local.
	 */
	function EnableServiceRcLocal($service)
	{
		$service= Escape($service, '/');
		return $this->ReplaceRegexp($this->confDir.'rc.local', "/^\h*#(\h*$service\b.*)$/m", '${1}');
	}

	/** Get logs configuration.
	 */
	function GetLogsConfig()
	{
		global $MODEL_PATH, $ModelFiles, $Models, $ModelsToLogConfig;

		$output= array();
		foreach ($ModelsToLogConfig as $m) {
			if (array_key_exists($m, $ModelFiles)) {
				require_once($MODEL_PATH.$ModelFiles[$m]);

				if (class_exists($Models[$m])) {
					$model= new $Models[$m]();
					if (($config= $model->GetNewsyslogConfig($m)) !== FALSE) {
						$output= array_merge($output, $config);
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
		return serialize($output);
	}

	/** Set logs configuration.
	 */
	function SetLogsConfig($model, $file, $count, $size, $when)
	{
		global $MODEL_PATH, $ModelFiles, $Models, $ModelsToLogConfig;

		if (array_key_exists($model, $ModelFiles)) {
			require_once($MODEL_PATH.$ModelFiles[$model]);

			if (class_exists($Models[$model])) {
				$model= new $Models[$model]();
				return $model->SetNewsyslogConfig($file, $count, $size, $when);
			}
			else {
				cwc_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "Not in Models: $model");
			}
		}
		else {
			cwc_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "Not in ModelFiles: $model");
		}
		return FALSE;
	}

	/** Calls forked rotation function.
	 */
	function RotateLogFile($file)
	{
		return $this->DaemonizeFunc('DaemonizedRotateLogFile', $file);
	}

	/** Rotate log file via newsyslog.
	 *
	 * Daemonized, because newsyslog may kill httpd, hence parent.
	 */
	function DaemonizedRotateLogFile($file)
	{
		global $TmpFile;

		if (($contents= $this->GetFile($this->newSyslogConf)) !== FALSE) {
			$re_filepath= Escape($file, '/');
			$re_owner= '([\w:]+|)';
			$re_mode= '(\d+)';
			$re_count= '(\d+)';
			$re_size= '(\d+|\*)';
			$re_when= '(\d+|\*)';

			$re= "/^(\s*$re_filepath\s+$re_owner\s*$re_mode\s+$re_count\s+$re_size\s+$re_when\s+.*)$/m";
			if (preg_match($re, $contents, $match)) {
				$line= $match[1];
				$cmd= "/bin/echo '$line' | /usr/bin/newsyslog -vF -f -  > $TmpFile 2>&1 &";
				exec($cmd, $output, $retval);
				if ($retval === 0) {
					return TRUE;
				}
				$errout= implode("\n", $output);
				ViewError($errout);
				cwc_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Log rotation failed: $errout");
			}
		}
		return FALSE;
	}

	/** Calls forked full rotation function.
	 */
	function RotateAllLogFiles()
	{
		return $this->DaemonizeFunc('DaemonizedRotateAllLogFiles');
	}

	/** Rotate all log files via newsyslog.
	 *
	 * Daemonized, because newsyslog kills httpd, hence parent,
	 * stopping rotation in the middle, e.g. before compressing files.
	 */
	function DaemonizedRotateAllLogFiles()
	{
		global $TmpFile;

		$cmd= "/usr/bin/newsyslog -vF -f $this->newSyslogConf > $TmpFile 2>&1 &";
		exec($cmd, $output, $retval);
		if ($retval === 0) {
			return TRUE;
		}
		$errout= implode("\n", $output);
		ViewError($errout);
		cwc_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Log rotation failed: $errout");
		return FALSE;
	}
	
	/** Daemonizes to run the given function.
	 */
	function DaemonizeFunc($func, $param= '')
	{
		$pid= pcntl_fork();
		if ($pid == -1) {
			cwc_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Cannot fork');
		}
		else if ($pid) {
			 // Parent should exit
			return TRUE;
		}
		else {
			// Make the child process a session leader
	        $sid= posix_setsid();
		    if ($sid < 0) {
				cwc_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Cannot make the child a session leader');
				// Exit if cannot daemonize completely
				return FALSE;
			}
			// The child is daemonized now, hence survives even if its process group is killed.
			// This is necessary when rotating httpd logs.

			$argv= array();
			if ($param !== '') {
				$argv= array($param);
			}
			return call_user_func_array(array($this, $func), $argv);
		}
	}

	/** Sets user's password in htpasswd file.
	 */
	function SetPassword($user, $passwd)
	{
		/// Passwords in htpasswd file are SHA1 encrypted.
		exec("/usr/bin/htpasswd -b -s $this->passwdFile $user $passwd 2>&1", $output, $retval);
		if ($retval === 0) {
			return TRUE;
		}
		$errout= implode("\n", $output);
		ViewError($errout);
		cwc_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Set password failed: $errout");
		return FALSE;
	}

	function SetLocale($locale)
	{
		global $ROOT;

		$this->NVPS= '=';
		// Append semi-colon to new value, this setting is a PHP line
		return $this->SetNVP($ROOT.'/lib/setup.php', '\$DefaultLocale', "'$locale';");
	}

	function SetLogLevel($level)
	{
		global $ROOT;
		
		$this->NVPS= '=';
		// Append semi-colon to new value, this setting is a PHP line
		return $this->SetNVP($ROOT.'/lib/setup.php', '\$LOG_LEVEL', $level.';');
	}

	function SetHelpBoxes($bool)
	{
		global $VIEW_PATH;
		
		$this->NVPS= '=';
		// Append semi-colon to new value, this setting is a PHP line
		return $this->SetNVP($VIEW_PATH.'lib/setup.php', '\$ShowHelpBox', $bool.';');
	}
	
	function SetReloadRate($rate)
	{
		global $VIEW_PATH;
		
		$this->NVPS= '=';
		// Append semi-colon to new value, this setting is a PHP line
		return $this->SetNVP($VIEW_PATH.'lib/setup.php', '\$DefaultReloadRate', $rate.';');
	}
	
	function SetSessionTimeout($timeout)
	{
		global $VIEW_PATH;

		if ($timeout < 10) {
			$timeout= 10;
		}
		
		$this->NVPS= '=';
		// Append semi-colon to new value, this setting is a PHP line
		return $this->SetNVP($VIEW_PATH.'lib/setup.php', '\$SessionTimeout', $timeout.';');
	}
	
	function NetStart()
	{
		// Should refresh pf rules too
		$cmd= "/bin/sh /etc/netstart 2>&1 && /sbin/pfctl -f $this->PfRulesFile 2>&1";
		exec($cmd, $output, $retval);
		if ($retval === 0) {
			return TRUE;
		}
		$errout= implode("\n", $output);
		ViewError($errout);
		cwc_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Netstart failed: $errout");
		return FALSE;
	}
	
	/** Returns all processes from ps output.
	 *
	 * Used by Processes page, hence lists all processes.
	 *
	 * @param[in]	$psout	ps output.
	 * @return array Parsed ps output
	 */
	function SelectProcesses($psout)
	{
		//   PID STARTED  %CPU      TIME %MEM   RSS   VSZ STAT  PRI  NI TTY      USER     GROUP    COMMAND
		//     1  5:10PM   0.0   0:00.03  0.0   388   412 Is     10   0 ??       root     wheel    /sbin/init
		$re= '/^\s*(\d+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\d+)\s+(\d+)\s+(\S+)\s+(\d+)\s+(\d+)\s+\S+\s+(\S+)\s+(\S+)\s+(.+)$/';
		
		$processes= array();
		foreach ($psout as $line) {
			if (preg_match($re, $line, $match)) {
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
		return $processes;
	}
}
?>
