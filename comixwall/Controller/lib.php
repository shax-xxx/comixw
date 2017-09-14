<?php
/* $ComixWall: lib.php,v 1.17 2009/12/05 10:55:46 soner Exp $ */

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
 * Defs and library functions for Controller.
 */

/** Wrapper for controller error logging via syslog.
 *
 * A global $LOG_LEVEL is set in setup.php.
 *
 * @param[in]	$prio	Log priority checked against $LOG_LEVEL
 * @param[in]	$file	Source file the function is in
 * @param[in]	$func	Function where the log is taken
 * @param[in]	$line	Line number within the function
 * @param[in]	$msg	Log message
 */
function cwc_syslog($prio, $file, $func, $line, $msg)
{
	global $LOG_LEVEL, $LOG_PRIOS;

	try {
		openlog("cwc", LOG_PID, LOG_LOCAL0);
		
		if ($prio <= $LOG_LEVEL) {
			$func= $func == '' ? 'NA' : $func;
			$log= "$LOG_PRIOS[$prio] $file: $func ($line): $msg\n";
			if (!syslog($prio, $log)) {
				if (!fwrite(STDERR, $log)) {
					echo $log;
				}
			}
		}
		closelog();
	}
	catch (Exception $e) {
		echo 'Caught exception: ',  $e->getMessage(), "\n";
		echo "cwc_syslog() failed: $prio, $file, $func, $line, $msg\n";
		// No need to closelog(), it is optional
	}
}

/// Redirect file for Controller().
$TmpFile= '/var/tmp/comixwall/cwc.out';

/// Matches model names to files. View provides the name only.
$ModelFiles= array(
	'system'			=> 'system.php',
	'pf'				=> 'pf.php',
	'dhcpd'				=> 'dhcpd.php',
	'named'				=> 'named.php',
	'dansguardian'		=> 'dansguardian.php',
	'dansguardianlogs'	=> 'dansguardianlogs.php',
	'blacklists'		=> 'blacklists.php',
	'squid'				=> 'squid.php',
	'snort'				=> 'snort.php',
	'snortalerts'		=> 'snortalerts.php',
	'snortips'			=> 'snortips.php',
	'openvpn'			=> 'openvpn.php',
	'apache'			=> 'apache.php',
	'apachelogs'		=> 'apachelogs.php',
	'cwwui_syslog'		=> 'cwwui.php',
	'cwc_syslog'		=> 'cwc.php',
	'spamassassin'		=> 'spamassassin.php',
	'spamd'				=> 'spamd.php',
	'spamdgreydb'		=> 'spamd.php',
	'spamdwhitedb'		=> 'spamd.php',
	'clamav'			=> 'clamav.php',
	'clamd'				=> 'clamd.php',
	'freshclam'			=> 'freshclam.php',
	'p3scan'			=> 'p3scan.php',
	'openssh'			=> 'openssh.php',
	'smtp-gated'		=> 'smtp-gated.php',
	'ftp-proxy'			=> 'ftp-proxy.php',
	'dante'				=> 'dante.php',
	'symon'				=> 'symon.php',
	'symux'				=> 'symux.php',
	'pmacct'			=> 'pmacct.php',
	'monitoring'		=> 'monitoring.php',
	'docs'				=> 'docs.php',
	);

/// Matches model names to Classes. Used to create the object.
$Models= array(
	'system'			=> 'System',
	'pf'				=> 'Pf',
	'dhcpd'				=> 'Dhcpd',
	'named'				=> 'Named',
	'dansguardian'		=> 'Dansguardian',
	'dansguardianlogs'	=> 'Dansguardianlogs',
	'blacklists'		=> 'Blacklists',
	'squid'				=> 'Squid',
	'snort'				=> 'Snort',
	'snortalerts'		=> 'Snortalerts',
	'snortips'			=> 'Snortips',
	'openvpn'			=> 'Openvpn',
	'apache'			=> 'Apache',
	'apachelogs'		=> 'Apachelogs',
	'cwwui_syslog'		=> 'Cwwui',
	'cwc_syslog'		=> 'Cwc',
	'spamassassin'		=> 'Spamassassin',
	'spamd'				=> 'Spamd',
	'spamdgreydb'		=> 'Spamd',
	'spamdwhitedb'		=> 'Spamd',
	'clamav'			=> 'Clamav',
	'clamd'				=> 'Clamd',
	'freshclam'			=> 'Freshclam',
	'p3scan'			=> 'P3scan',
	'openssh'			=> 'Openssh',
	'smtp-gated'		=> 'Smtpgated',
	'ftp-proxy'			=> 'Ftpproxy',
	'dante'				=> 'Dante',
	'symon'				=> 'Symon',
	'symux'				=> 'Symux',
	'pmacct'			=> 'Pmacct',
	'monitoring'		=> 'Monitoring',
	'docs'				=> 'Docs',
	);

/// @attention PHP is not compiled, otherwise would use bindec()
/// @warning Do not use bitwise shift operator either, would mean 100+ shifts for constant values!
/// Shell command argument types
define('NONE',		1);
define('FILEPATH', 	2);
define('IPADR', 	4);
define('IPRANGE',	8);
define('NAME',	 	16);
define('NUM',	 	32);
define('EXT',	 	64);
define('MIME',	 	128);
define('HOST', 		256);
define('IPPORT',	512);
define('DGIPRANGE', 1024);
define('IPADRLIST', 2048);
define('STR',		4096);
define('URL', 		8192);
define('EMAIL', 	16384);
define('DATETIME', 	32768);
define('EMPTYSTR',	65536);
define('ASTERISK',	131072);
define('SERIALARRAY',262144);
define('SHA1STR', 	524288);
define('CONFNAME', 	1048576);
define('REGEXP', 	2097152);
define('AFTERHOURS',4194304);

/** Functions and info strings used in shell arg control.
 *
 * @param[out]	func	Function to check type
 * @param[out]	desc	Info string to use when check failed
 */
$ArgTypes= array(
	FILEPATH	=>	array(
		'func'	=> 'IsFilePath',
		'desc'	=> _('Filepath wrong'),
		),
	IPADR		=>	array(
		'func'	=> 'IsIPAddress',
		'desc'	=> _('IP address wrong'),
		),
	IPRANGE		=>	array(
		'func'	=> 'IsIPRange',
		'desc'	=> _('IP range wrong'),
		),
	NAME		=>	array(
		'func'	=> 'IsName',
		'desc'	=> _('Name wrong'),
		),
	NUM			=>	array(
		'func'	=> 'IsNumber',
		'desc'	=> _('Number wrong'),
		),
	EXT			=>	array(
		'func'	=> 'IsExt',
		'desc'	=> _('Extension wrong'),
		),
	MIME		=>	array(
		'func'	=> 'IsMime',
		'desc'	=> _('Mime type wrong'),
		),
	HOST		=>	array(
		'func'	=> 'IsHost',
		'desc'	=> _('Host wrong'),
		),
	IPPORT		=>	array(
		'func'	=> 'IsIPPort',
		'desc'	=> _('IP address or port wrong'),
		),
	DGIPRANGE	=>	array(
		'func'	=> 'IsDGIPRange',
		'desc'	=> _('Web Filter IP range wrong'),
		),
	IPADRLIST	=>	array(
		'func'	=> 'IsIPList',
		'desc'	=> _('IP or IP list wrong'),
		),
	STR			=>	array(
		'func'	=> 'IsStr',
		'desc'	=> _('String wrong'),
		),
	URL			=>	array(
		'func'	=> 'IsUrl',
		'desc'	=> _('Url wrong'),
		),
	EMAIL		=>	array(
		'func'	=> 'IsEmailAddress',
		'desc'	=> _('E-mail address wrong'),
		),
	DATETIME	=>	array(
		'func'	=> 'IsDateTime',
		'desc'	=> _('Datetime wrong'),
		),
	EMPTYSTR	=>	array(
		'func'	=> 'IsEmpty',
		'desc'	=> _('Not empty string'),
		),
	ASTERISK	=>	array(
		'func'	=> 'IsAsterisk',
		'desc'	=> _('Not asterisk'),
		),
	SERIALARRAY	=>	array(
		'func'	=> 'IsSerializedArray',
		'desc'	=> _('Not serialized array'),
		),
	SHA1STR	=>	array(
		'func'	=> 'IsSha1Str',
		'desc'	=> _('Not sha1 encrypted string'),
		),
	CONFNAME	=>	array(
		'func'	=> 'IsStr',
		'desc'	=> _('Not config name'),
		),
	REGEXP	=>	array(
		'func'	=> 'IsStr',
		'desc'	=> _('Regular expression wrong'),
		),
	AFTERHOURS	=>	array(
		'func'	=> 'IsAfterHours',
		'desc'	=> _('Not comma separated digits'),
		),
);

$MonthDays= array(
	1	=> 31,
	2	=> 29, ///< Forget about leap year calculations for now.
	3	=> 31,
	4	=> 30,
	5	=> 31,
	6	=> 30,
	7	=> 31,
	8	=> 31,
	9	=> 30,
	10	=> 31,
	11	=> 30,
	12	=> 31,
	);

function IsFilePath($filepath)
{
	return preg_match('|^/var/log/\w[\w./-]*$|', $filepath)
		// For CVS Tag displayed in the footer
		|| preg_match('|^/var/www/htdocs/comixwall/View/\w[\w./-]*$|', $filepath)
		// Messaging logs
		|| preg_match('|^/var/www/logs/\w[\w./-]*$|', $filepath)
		|| preg_match('|^/var/squid/logs/\w[\w./-]*$|', $filepath)
		// Statistics and uncompressed logs
		|| preg_match('|^/var/tmp/comixwall/\w[\w./-]*$|', $filepath)
		/// @todo /tmp path is for pfw, remove later
		|| preg_match('|^/tmp/\w[\w./-]*$|', $filepath);
}

function IsNumber($num)
{
	return preg_match('/^\d{1,20}$/', $num);
}

function IsName($name)
{
	return preg_match('/^\w[\w_.-]{0,50}$/', $name);
}

function IsExt($ext)
{
	return preg_match('/^\.[a-z0-9A-Z][a-z0-9A-Z_.]{0,10}$/', $ext);
}

function IsMime($mime)
{
	return preg_match('|^[a-zA-Z][a-z0-9A-Z_-]{0,20}/[a-z0-9A-Z_.-]{0,20}$|', $mime);
}

function IsUrl($name)
{
	return preg_match('|^[\w_.-/?=]{1,100}$|', $name);
}

function IsEmailAddress($addr)
{
	return preg_match('/^root(@localhost|)$/', $addr)
		|| preg_match('/^[a-z]+[a-z0-9]*(\.|-|_)?[a-z0-9]+@([a-z]+[a-z0-9]*(\.|-)?[a-z]+[a-z0-9]*[a-z0-9]+){1,4}\.[a-z]{2,4}$/', $addr);
}

function IsIPAddress($ip)
{
	global $preIP;

	return preg_match("/^$preIP$/", $ip);
}

function IsIPRange($iprange)
{
	global $preIP, $preIPRange;

	return preg_match("/^$preIP\/$preIPRange$/", $iprange);
}

function IsDGIPRange($iprange)
{
	global $preIP;

	return preg_match("/^$preIP\/$preIP$/", $iprange)
		|| preg_match("/^$preIP-$preIP$/", $iprange);
}

function IsIPPort($ipport)
{
	global $preIP;

	$ipport= explode(':', $ipport, 2);
	$ip= $ipport[0];
	$port= $ipport[1];

	return preg_match("/^$preIP$/", $ip) && ((0 < $port) && ($port < 65536));
}

function IsIPList($iplist)
{
	$ips= explode(';', $iplist, 10);
	foreach ($ips as $ip) {
		if ($ip !== '' && !IsIPAddress(trim($ip))) {
			return FALSE;
		}
	}
	return TRUE;
}

function IsStr($str)
{
	/// @todo This is still too general?
	return preg_match("/^[^\n]{0,200}$/", $str);
}

function IsSerializedArray($str)
{
	// Serialized arrays passed to Model are small enough to warrant this unserialize() and array check
	// Otherwise, this is not true for return values of Model, especially logs and statistics
	return is_array(unserialize($str));
}

function IsSha1Str($str)
{
	return preg_match('/^[a-f\d]+$/', $str);
}

function IsAfterHours($str)
{
	return preg_match('/^[1-7,]*$/', $str);
}

function IsHost($host)
{
	global $preIP;

	return preg_match("/^($preIP|::1)\s[a-zA-Z][a-z0-9A-Z_.#\s]{0,100}$/", $host);
}

function IsEmpty($str)
{
	return empty($str);
}

function IsAsterisk($str)
{
	return $str === '*';
}

/** Checks type of datetime string as supplied to date command.
 *
 * @param[in]	$datetime	Arg
 * @return boolean Type check result
 */
function IsDateTime($datetime)
{
	global $MonthDays;
	
	/// There should be 10 digits.
	if (preg_match('/^\d{10,}$/', $datetime)) {
		$datetime= str_split($datetime, 2);
		/// Year can be 00-99, so no need to check.
		//$Year= $datetime[0] + 0;
		$month= $datetime[1] + 0;
		$day= $datetime[2] + 0;
		$hour= $datetime[3] + 0;
		$min= $datetime[4] + 0;
		if (($month <= 12)
			&& ($day <= $MonthDays[$month])
			&& ($hour <= 23)
			&& ($min <= 59)) {
			return TRUE;
		}
	}
	return FALSE;
}

/** Compute and fill arg count variables.
 *
 * @param[in]	$commands	Available commands for the current model
 * @param[in]	$argv		Argument vector
 * @param[in]	$cmd		Method name, key to $commands
 * @param[out]	$actual		Given arg count
 * @param[out]	$expected	Expected arg count
 * @param[out]	$acceptable	Acceptable arg count
 * @param[out]	$check		Arg count used while validating
 */
function ComputeArgCounts($commands, $argv, $cmd, &$actual, &$expected, &$acceptable, &$check)
{
	$actual= count($argv);
	$expected= count($commands[$cmd]['argv']);

	$acceptable= $expected;
	for ($argpos= 0; $argpos < $expected; $argpos++) {
		$argtype= $commands[$cmd]['argv'][$argpos];
		if ($argtype & NONE) {
			$acceptable--;
		}
	}
	
	/// @attention There may be extra or missing args, hence min() here
	$check= min($actual, $expected);
}

/** Checks types of the arguments passed.
 *
 * The arguments are checked against the types listed in $commands.
 *
 * @param[in]	$commands	Available commands for the current model
 * @param[in]	$command	Method name, key to $commands
 * @param[in]	$argv		Argument vector
 * @param[out]	$check		Arg count used while validating
 * @return boolean Validation result
 *
 * @todo There are 2 types of argument checks in this project, which one to choose?
 */
function ValidateArgs($commands, $command, $argv, $check)
{
	global $ArgTypes;

	$helpmsg= $commands[$command]['desc'];
	$logmsg= $commands[$command]['desc'];
	
	$valid= FALSE;
	// Check each argument in order
	for ($argpos= 0; $argpos < $check; $argpos++) {
		$arg= $argv[$argpos];
		$argtype= $commands[$command]['argv'][$argpos];

		// Multiple types may match for an arg, hence the foreach loop
		foreach ($ArgTypes as $type => $conf) {
			// Acceptable types are bitwise ORed, hence the AND here
			if ($argtype & $type) {
				$validatefunc= $conf['func'];
				if ($validatefunc($arg)) {
					$valid= TRUE;

					if ($type & FILEPATH) {
						// Further check if file really exists
						exec("[ -e $arg ]", $output, $retval);
						if ($retval !== 0) {
							$valid= FALSE;

							$errormsg= "$command: $arg";
							ViewError(_('No such file').": $errormsg");
							cwc_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "No such file: $errormsg");
						}
					}

					if ($valid) {
						// One type succeded, hence do not check for other possible types for this arg
						break;
					}
				}
				else {
					$valid= FALSE;
					
					$helpmsg.= "\n"._($conf['desc']).': '.$arg;
					$logmsg.= "\n".$conf['desc'].': '.$arg;
					// Will keep checking if further types are possible for this arg
				}
			}
		}

		if (!$valid) {
			// One arg failed to check, do not run the func
			break;
		}
	}
	
	if (!$valid) {
		ViewError(_('Arg type check failed').": $helpmsg");
		cwc_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "Arg type check failed: $logmsg");
	}
	return $valid;
}
?>
