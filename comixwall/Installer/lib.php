<?php
/* $ComixWall: lib.php,v 1.21 2009/11/26 20:49:22 soner Exp $ */

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
 * Installer library.
 */

/** To satisfy Controller().
 */
function PrintHelpWindow($msg, $width= 'auto', $type= 'INFO')
{
	// For Controller()
	$msg= preg_replace('|<br\s*/>|', ' ', $msg);
	echo "$type: $msg\n";
}

/** Apply configuration.
 */
function ApplyConfig()
{
	global $Config, $Re_Ip, $View;

	try {
		$myname= $Config['Myname'];
		$mygate= $Config['Mygate'];
		
		$lanif= $Config['IntIf'];
		$wanif= $Config['ExtIf'];

		$lanip= $Config['Ifs'][$lanif][1];
		$lanmask= $Config['Ifs'][$lanif][2];

		ComputeIfDefs($lanip, $lanmask, $lannet, $lanbc, $lancidr);

		$View->Model= 'pf';
		if (!$View->Controller($output, 'SetIfs', $lanif, $wanif)) {
			cwwui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed setting pf interfaces: $lanif, $wanif");
		}
		if (!$View->Controller($output, 'SetIntnet', $lancidr)) {
			cwwui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed setting pf internal net: $lancidr");
		}
		
		if (!$View->Controller($output, 'SetAfterhoursIf', $lanif)) {
			cwwui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed setting pf afterhours interface: $lanif");
		}
		if (!$View->Controller($output, 'AddAllowedIp', $lancidr)) {
			cwwui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed setting pf restricted ip: $lancidr");
		}

		$View->Model= 'named';
		if (!$View->Controller($output, 'SetForwarders', $mygate)) {
			cwwui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed setting forwarders: $mygate");
		}
		
		$View->Model= 'system';
		if (!$View->Controller($output, 'SetManCgiHome', $lanip)) {
			cwwui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed setting man.cgi home: $lanip");
		}
		
		$View->Model= 'dhcpd';
		ComputeDhcpdIpRange($lanip, $lannet, $lanbc, $min, $max);
		if (!$View->Controller($output, 'SetDhcpdConf', $lanip, $lanmask, $lannet, $lanbc, $min, $max)) {
			cwwui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed setting dhcpd configuration: $lanip, $lanmask, $lannet, $lanbc, $min, $max");
		}
		
		if (!$View->Controller($output, 'AddIf', $lanif)) {
			cwwui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed setting dhcpd interface: $lanif");
		}
		
		$View->Model= 'snort';
		if (!$View->Controller($output, 'SetStartupIfs', $lanif, $wanif)) {
			cwwui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed setting snort interfaces: $lanif, $wanif");
		}
		
		$View->Model= 'spamd';
		if (!$View->Controller($output, 'SetStartupIf', $wanif)) {
			cwwui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed setting spamlogd interfaces: $wanif");
		}
		
		$View->Model= 'dante';
		if (!$View->Controller($output, 'SetIfs', $lanif, $wanif)) {
			cwwui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed setting dante interfaces: $lanif, $wanif");
		}
		
		if (!$View->Controller($output, 'SetIntnet', $lancidr)) {
			cwwui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed setting dante internal net: $lancidr");
		}
		
		$View->Model= 'smtp-gated';
		if (!$View->Controller($output, 'SetConfValue', 'proxy_name', $myname, '', '')) {
			cwwui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed setting smtp-gated proxy_name: $myname");
		}
		
		$View->Model= 'apache';
		if (!$View->Controller($output, 'SetWebalizerHostname', $lanip)) {
			cwwui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed setting webalizer hostname: $lanip");
		}
		
		$View->Model= 'dansguardian';
		if (!$View->Controller($output, 'SetTemplateIps', $lanip)) {
			cwwui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed setting dansguardian template ips: $lanip");
		}
		
		$View->Model= 'snortips';
		if (!$View->Controller($output, 'AddAllowedIp', $lanip)) {
			cwwui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed setting snortips whitelist: $lanip");
		}
		
		if (preg_match("/^$Re_Ip$/", $wanip)) {
			if (!$View->Controller($output, 'AddAllowedIp', $wanip)) {
				cwwui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed setting snortips whitelist: $wanip");
			}
		}
		
		if (preg_match("/^$Re_Ip$/", $mygate)) {
			if (!$View->Controller($output, 'AddAllowedIp', $mygate)) {
				cwwui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed setting snortips whitelist: $mygate");
			}
		}
		
		$View->Model= 'pmacct';
		if (!$View->Controller($output, 'SetIf', $lanif)) {
			cwwui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed setting pmacct if: $lanif");
		}
		
		if (!$View->Controller($output, 'SetNet', $lancidr)) {
			cwwui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed setting pmacct network: $lancidr");
		}

		$View->Model= 'symon';
		if (!$View->Controller($output, 'SetCpus')) {
			cwwui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Failed setting symon cpus');
		}

		if (!$View->Controller($output, 'SetIfs', $lanif, $wanif)) {
			cwwui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Failed setting symon ifs: $lanif, $wanif");
		}

		if (!$View->Controller($output, 'SetSensors')) {
			cwwui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Failed setting symon sensors');
		}

		if (!$View->Controller($output, 'SetPartitions')) {
			cwwui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Failed setting symon partitions');
		}

		if (!$View->Controller($output, 'SetConf', $lanif, $wanif)) {
			cwwui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Failed setting symon conf');
		}

		return TRUE;
	}
	catch (Exception $e) {
		echo 'Caught exception: ', $e->getMessage(), "\n";
		return FALSE;
	}
}

/** Configuration which cannot be completed during installation.
 */
function FirstBootTasks()
{
	// Run symon script to create rrd files again for cpu and sensor probes
	exec('/bin/sh /usr/local/share/symon/c_smrrds.sh all');

	// Create initial web server statistics page
	exec('/usr/local/bin/webalizer');

	// Disable rc.local line which leads to this function call
	$file= '/etc/rc.local';
	if (copy($file, $file.'.bak')) {
		$re= '|^(\h*/var/www/htdocs/comixwall/Installer/install\.php\h+-f\h*)|ms';
		$contents= preg_replace($re, '#${1}', file_get_contents($file), 1, $count);
		if ($contents !== NULL && $count === 1) {
			file_put_contents($file, $contents);
		}
	}
}

/** Computes network, broadcast, and CIDR net addresses, given ip and netmask.
 *
 * Quoting from nice explanations here:
 * http://downloads.openwrt.org/people/mbm/network
 *
 * if we take the ip and netmask and do an AND: (hint, AND the columns)
 *
 *       11000000 10101000 00000001 00001011 = 192.168.1.11 (some ip address)
 *       11111111 11111111 11111111 11110000 = 255.255.255.240 (netmask)
 *  AND: 11000000 10101000 00000001 00000000 = 192.168.1.0 (network address)
 *
 *  This gives our network address, the lowest address in the subnet
 *  Now, flip the netmask: (hint, NOT the columns)
 *
 *       11111111 11111111 11111111 11110000 = 255.255.255.240 (netmask)
 *  NOT: 00000000 00000000 00000000 00001111 = 0.0.0.15 (NOT 255.255.255.240)
 *
 *  then OR this with the network address: (hint, OR the columns)
 *
 *       11000000 10101000 00000001 00000000 = 192.168.1.0 (network address)
 *       00000000 00000000 00000000 00001111 = 0.0.0.15 (NOT 255.255.255.240)
 *  OR:  11000000 10101000 00000001 00001111 = 192.168.1.15 (broadcast address)
 *
 * @param[in]	$ip		string IPv4 address.
 * @param[in]	$mask	string Netmask.
 * @param[out]	$net	string Network address.
 * @param[out]	$bc		string Broadcast address.
 * @param[out]	$cidr	string CIDR.
 */
function ComputeIfDefs($ip, $mask, &$net, &$bc, &$cidr)
{
	global $Re_Ip;
	
	if (preg_match("/^$Re_Ip$/", $ip) && preg_match("/^$Re_Ip$/", $mask)) {
		$net= long2ip(ip2long($ip) & ip2long($mask));
		$bc= long2ip(ip2long($net) | ~ip2long($mask));
		$cidr= $net.'/'.(32 - round(log(sprintf("%u", ip2long('255.255.255.255')) - sprintf("%u", ip2long($mask)), 2)));
	}
}

/** Computes a DHCP IP range.
 *
 * This function provides a guess only.
 *
 * @param[in]	$ip		string System internal ip.
 * @param[in]	$net	string System local network.
 * @param[in]	$bc		string System broadcast address to guess max range.
 * @param[out]	$min	string DHCP IP range min.
 * @param[out]	$max	string DHCP IP range max.
 */
function ComputeDhcpdIpRange($ip, $net, $bc, &$min, &$max)
{
	if (preg_match('/(\d{1,3}\.\d{1,3}\.\d{1,3})\.(\d{1,3})/', $net, $match)) {
		$minnet= $match[1];
		$minoct= $match[2];
		$min= $minnet.'.'.($minoct + 1);

		// Avoid clash with system internal IP
		if ($ip === $min) {
			$min= $minnet.'.'.($minoct + 2);
		}
	
		if (preg_match('/(\d{1,3}\.\d{1,3}\.\d{1,3})\.(\d{1,3})/', $bc, $match)) {
			$maxnet= $match[1];
			$maxoct= $match[2];
			$max= $maxnet.'.'.($maxoct - 1);

			// Avoid clash with system internal IP
			if ($ip === $max) {
				$max= $maxnet.'.'.($maxoct - 2);
			}
			return TRUE;
		}
	}
}

/** Initializes interfaces.
 */
function InitIfs()
{
	global $Config;

	if (!isset($Config['Ifs'])) {
		$Config['Ifs']= array();
	}
	$Ifs= array_keys($Config['Ifs']);
	
	if (count($Ifs) > 0) {
		// Necessary during first install with lan0/wan0 in pf.conf
		if (!in_array($Config['IntIf'], $Ifs)) {
			$Config['IntIf']= $Ifs[0];
		}
		
		if (count($Ifs) > 1) {
			if (!in_array($Config['ExtIf'], $Ifs)) {
				$Config['ExtIf']= $Ifs[1];
			}
		}
		else {
			$Config['ExtIf']= $Config['IntIf'];
			cwwui_syslog(LOG_WARNING, __FILE__, __FUNCTION__, __LINE__, 'WARNING: Found only one interface, assigned internal to external if');
		}
		return TRUE;
	}
	cwwui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'ERROR: Expected at least one interface, found: '.count($Ifs));
	return FALSE;
}

/** Asks user for internal or external interface selection.
 */
function GetIfSelection()
{
	global $Config;

	$ifs= array_keys($Config['Ifs']);
	$iflist= implode(', ', $ifs);
	$ifcount= count($ifs);
	
	while (TRUE) {
		// Reset to system values
		$lanif= $Config['IntIf'];
		$wanif= $Config['ExtIf'];
		
		if (!isset($lanif) || (($ifcount > 1) && ($lanif === $wanif))) {
			$lanif= $ifs[0] === $wanif ? $ifs[1] : $ifs[0];
		}
		PrintIfConfig($lanif, $wanif);
		
		$selection= ReadIfSelection("Internal interface ($iflist or enter) [$lanif] ", $ifs);
		if ($selection !== '') {
			$lanif= $selection;
		}

		// Fix wan if necessary
		if (!isset($wanif) || (($ifcount > 1) && ($lanif === $wanif))) {
			$wanif= $ifs[0] === $lanif ? $ifs[1] : $ifs[0];
		}
		PrintIfConfig($lanif, $wanif);
		
		$selection= ReadIfSelection("External interface ($iflist or enter) [$wanif] ", $ifs);
		if ($selection !== '') {
			$wanif= $selection;
		}

		$warn= PrintIfConfig($lanif, $wanif);
		
		$selection= readline('Type done to accept or press enter to try again: ');
		if ($selection === 'done') {
			break;
		}
		echo "\n";
	}
	
	if ($warn) {
		echo "\nProceeding with warnings...\n";
	}
	
	$Config['IntIf']= $lanif;
	$Config['ExtIf']= $wanif;
}

/** Prints current internal/external interface selections.
 *
 * @param[in]	$lanif	string Internal if.
 * @param[in]	$wanif	string External if.
 * @return boolean if user should be warned.
 */
function PrintIfConfig($lanif, $wanif)
{
	global $Config;

	$warn= FALSE;
	
	echo "\nInterface assignment:\n";
	$lanconfig= trim(implode(' ', $Config['Ifs'][$lanif]));
	$lanconfig= $lanconfig === '' ? 'not configured':$lanconfig;
	echo "  internal= $lanif ($lanconfig)\n";
	$wanconfig= trim(implode(' ', $Config['Ifs'][$wanif]));
	$wanconfig= $wanconfig === '' ? 'not configured':$wanconfig;
	echo "  external= $wanif ($wanconfig)\n\n";
	
	if (($lanconfig == 'not configured') || ($wanconfig == 'not configured')) {
		echo "WARNING: There are unconfigured interfaces\n";
		$warn= TRUE;
	}
	
	if ($lanif === $wanif) {
		echo "WARNING: Internal and external interfaces are the same\n";
		$warn= TRUE;
	}

	if (isset($Config['Ifs'][$lanif][0])) {
		if ($Config['Ifs'][$lanif][0] === 'dhcp') {
			echo "WARNING: Internal interface is configured as dhcp\n";
			$warn= TRUE;
		}
	}
	return $warn;
}

/** Prompts for and reads internal/external interface selection.
 *
 * @param[in]	$prompt	string Message to display.
 * @param[in]	$ifs array Interface names.
 * @return User input.
 */
function ReadIfSelection($prompt, $ifs)
{
	while (TRUE) {
		$selection= readline($prompt);
		if (($selection === '') || in_array($selection, $ifs)) {
			return $selection;
		}
		echo "\nInvalid choice\n";
	}
}

/** Reads a line of input from stdin.
 *
 * @param[in]	$prompt	string Message to display.
 * @return User input, no newlines.
 */
function readline($prompt= '')
{
    echo $prompt;
    return rtrim(fgets(STDIN), "\n");
}

/** Sets both admin and user passwords on the WUI.
 *
 * Password should have at least 8 alphanumeric chars.
 */
function SetWuiPasswd()
{
	global $View;
	
	// In case
	$View->Model= 'system';
	
	echo "\nPassword for web administration interface:\n";
	
	while (TRUE) {
		echo "Password? (will not echo) ";
		$passwd= AskPass();
		
		echo "\nPassword? (again) ";
		if ($passwd === AskPass()) {
			if (preg_match('/^\w{8,}$/', $passwd)) {
				echo "\n";
				// Update admin password
				if ($View->Controller($output, 'SetPassword', 'admin', sha1($passwd))) {
					cwwui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'User password changed: admin');
					// Update user password
					if ($View->Controller($output, 'SetPassword', 'user', sha1($passwd))) {
						cwwui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'User password changed: user');
					}
					else {
						cwwui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Password change failed: user');
					}
				}
				else {
					cwwui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Password change failed: admin');
				}
				echo "Successfully set admin and user passwords.\n\n";
				break;
			}
			else {
				echo "\nERROR: Choose a password with at least 8 alphanumeric characters.\n";
			}
		}
		else {
			echo "\nERROR: Passwords do not match.\n";
		}
	}
}

/** Reads typed chars without echo.
 *
 * @return exec() return value is the last line of shell cmd output, i.e. user input
 */
function AskPass()
{
	return exec('set -o noglob; stty -echo; read resp; stty echo; set +o noglob; echo $resp');
}
?>
