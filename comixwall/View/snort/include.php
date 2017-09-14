<?php
/* $ComixWall: include.php,v 1.14 2009/11/21 21:55:59 soner Exp $ */

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

require_once('../lib/vars.php');
require_once('../lib/view.php');

class Snort extends View
{
	public $Model= 'snort';
	public $Layout= 'snort';

	function Snort()
	{
		$this->LogsHelpMsg= _HELPWINDOW('These logs contain messages from all IDS process. Alerts are duplicated here as well.');
	}
	
	function FormatLogCols(&$cols)
	{
	}

	/** Wrapper for InterfaceSelectForm().
	 */
	function PrintInterfaceSelectForm()
	{
		global $ADMIN;
		/// Only admin can start/stop the processes
		if (in_array($_SESSION['USER'], $ADMIN)) {
			?>
			<table id="ifselect">
				<tr>
					<td class="title">
						<?php echo _TITLE2('Interfaces').':' ?>
					</td>
					<td>
						<?php
						$this->InterfaceSelectForm();
						?>
					</td>
					<td class="help">
						<?php PrintHelpBox(_HELPBOX2('Here you should select the interface(s) the IDS listens to. Instance with the selected interface is restarted if it is already running.')) ?>
					</td>
				</tr>
			</table>
			<?php
		}
	}

	/** General form for selecting a physical interface in the system.
	 */
	function InterfaceSelectForm()
	{
		global $Modules;

		$startconfirm= _NOTICE('Are you sure you want to start the <NAME>?');
		$startconfirm= preg_replace('/<NAME>/', _($Modules[$this->Model]['Name']), $startconfirm);

		$stopconfirm= _NOTICE('Are you sure you want to stop the <NAME>?');
		$stopconfirm= preg_replace('/<NAME>/', _($Modules[$this->Model]['Name']), $stopconfirm);
		?>
		<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
			<select name="Interfaces[]" multiple style="width: 100px; height: 50px;">
			<?php
			if ($this->Controller($output, 'GetPhyIfs')) {
				foreach ($output as $if) {
					?>
					<option value="<?php echo $if ?>"><?php echo $if ?></option>
					<?php
				}
			}
			?>
			</select>
			<br />
			<input type="submit" name="Start" value="<?php echo _CONTROL('Start') ?>" onclick="return confirm('<?php echo $startconfirm ?>')"/>
			<input type="submit" name="Stop" value="<?php echo _CONTROL('Stop') ?>" onclick="return confirm('<?php echo $stopconfirm ?>')"/>
		</form>
		<?php
	}
}

$View= new Snort();

/** Basic configuration.
 */
$basicConfig = array(
    'var HOME_NET' => array(
        'title' => _TITLE2('HOME_NET'),
        'info' => _HELPBOX2('You must change the following variables to reflect your local network. The variable is currently setup for an RFC 1918 address space.'),
		),
    'var EXTERNAL_NET' => array(
        'title' => _TITLE2('EXTERNAL_NET'),
        'info' => _HELPBOX2('Set up the external network addresses as well.  A good start may be "any"'),
		),
    'var DNS_SERVERS' => array(
        'title' => _TITLE2('DNS_SERVERS'),
        'info' => _HELPBOX2('Configure your server lists.  This allows snort to only look for attacks to systems that have a service up.  Why look for HTTP attacks if you are not running a web server?  This allows quick filtering based on IP addresses. These configurations MUST follow the same configuration scheme as defined above for $HOME_NET.
List of DNS servers on your network'),
		),
    'var SMTP_SERVERS' => array(
        'title' => _TITLE2('SMTP_SERVERS'),
        'info' => _HELPBOX2('List of SMTP servers on your network'),
		),
    'var HTTP_SERVERS' => array(
        'title' => _TITLE2('HTTP_SERVERS'),
        'info' => _HELPBOX2('List of web servers on your network'),
		),
    'var SQL_SERVERS' => array(
        'title' => _TITLE2('SQL_SERVERS'),
        'info' => _HELPBOX2('List of sql servers on your network'),
		),
    'var TELNET_SERVERS' => array(
        'title' => _TITLE2('TELNET_SERVERS'),
        'info' => _HELPBOX2('List of telnet servers on your network'),
		),
    'var SNMP_SERVERS' => array(
        'title' => _TITLE2('SNMP_SERVERS'),
        'info' => _HELPBOX2('List of snmp servers on your network'),
		),
    'portvar SSH_PORTS' => array(
        'title' => _TITLE2('SSH_PORTS'),
        'info' => _HELPBOX2('Ports you run secure shell on.'),
		),
    'portvar HTTP_PORTS' => array(
        'title' => _TITLE2('HTTP_PORTS'),
        'info' => _HELPBOX2('Ports you run web servers on.

Please note:  [80,8080] does not work.
If you wish to define multiple HTTP ports, port lists must either be continuous [eg 80:8080], or a single port [eg 80].
We will add support for a real list of ports in the future.'),
		),
    'portvar SHELLCODE_PORTS' => array(
        'title' => _TITLE2('SHELLCODE_PORTS'),
        'info' => _HELPBOX2('Ports you want to look for SHELLCODE on.'),
		),
);

/** Advanced configuration.
 */
$advancedConfig = array(
    'config disable_decode_alerts' => array(
        'title' => _TITLE2('Disable decode alerts'),
        'info' => _HELPBOX2('Snort\'s decoder will alert on lots of things such as header truncation or options of unusual length or infrequently used tcp options.
Stop generic decode events:'),
		),
    'config disable_tcpopt_experimental_alerts' => array(
        'title' => _TITLE2('Disable tcpopt experimental alerts'),
        'info' => _HELPBOX2('Stop Alerts on experimental TCP options'),
		),
    'config disable_tcpopt_obsolete_alerts' => array(
        'title' => _TITLE2('Disable tcpopt obsolete alerts'),
        'info' => _HELPBOX2('Stop Alerts on obsolete TCP options'),
		),
    'config disable_tcpopt_ttcp_alerts' => array(
        'title' => _TITLE2('Disable tcpopt ttcp alerts'),
        'info' => _HELPBOX2('Stop Alerts on T/TCP alerts

In snort 2.0.1 and above, this only alerts when a TCP option is detected that shows T/TCP being actively used on the network.  If this is normal behavior for your network, disable the next option.'),
		),
    'config disable_tcpopt_alerts' => array(
        'title' => _TITLE2('Disable tcpopt alerts'),
        'info' => _HELPBOX2('Stop Alerts on all other TCPOption type events'),
		),
    'config disable_ipopt_alerts' => array(
        'title' => _TITLE2('Disable ipopt alerts'),
        'info' => _HELPBOX2('Stop Alerts on invalid ip options'),
		),
    'preprocessor frag3_global: max_frags' => array(
        'title' => _TITLE2('IP defragmentation support'),
        'info' => _HELPBOX2('This preprocessor performs IP defragmentation.  This plugin will also detect people launching fragmentation attacks (usually DoS) against hosts. Maximum number of frag trackers that may be active at once.'),
		),
    'preprocessor bo' => array(
        'title' => _TITLE2('Back Orifice detector'),
        'info' => _HELPBOX2('Detects Back Orifice traffic on the network.'),
		),
    'preprocessor telnet_decode' => array(
        'title' => _TITLE2('Telnet negotiation string normalizer'),
        'info' => _HELPBOX2('This preprocessor "normalizes" telnet negotiation strings from telnet and ftp traffic.  It works in much the same way as the http_decode preprocessor, searching for traffic that breaks up the normal data stream of a protocol and replacing it with a normalized representation of that traffic so that the "content" pattern matching keyword can work without requiring modifications.'),
		),
    'include classification.config' => array(
        'title' => _TITLE2('Include classification & priority settings'),
		),
    'include reference.config' => array(
        'title' => _TITLE2('Include reference systems'),
		),
);
?>
