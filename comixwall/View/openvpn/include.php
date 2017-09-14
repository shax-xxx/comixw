<?php
/* $ComixWall: include.php,v 1.18 2009/11/21 21:55:59 soner Exp $ */

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

class Openvpn extends View
{
	public $Model= 'openvpn';
	public $Layout= 'openvpn';
	
	function Openvpn()
	{
		$this->LogsHelpMsg= _HELPWINDOW('Messages from all OpenVPN processes are recorded in the same log file.');
		$this->ConfHelpMsg= _HELPWINDOW('Sample client and server configuration are provided to get you started. You may want to replace the default SSL certificates with yours.');
	}

	/** Sets session config file, ModelConfig and ModelConfigName params.
	 *
	 * @param[in]	$file	string Config file
	 */
	function SetConfig($file)
	{
		global $ViewConfigName, $ClientConfig, $ServerConfig;

		if (isset($file)) {
			$_SESSION[$this->Model][basename($_SERVER['PHP_SELF'])]['ConfFile']= $file;
			
			if ($this->Controller($output, 'IsClientConf', $file)) {
				$this->Config= $ClientConfig;
			}
			else {
				$this->Config= $ServerConfig;
			}
			$ViewConfigName= $file;
		}
	}

	/** General form for selecting, deleting, and copying an OpenVPN conf file.
	 *
	 * @param[in]	$module		string Module name, the caller
	 */
	function ConfSelectForm($module)
	{
		global $ConfigFile;

		$deleteconfirm= _NOTICE('Are you sure you want to delete the configuration?');

		if ($this->Controller($conffiles, 'GetConfs')) {
			?>
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<select name="ConfFile">
				<?php
				$conffileexists= FALSE;
				foreach ($conffiles as $file) {
					$selected= '';
					if ($ConfigFile === basename($file)) {
						$selected= ' selected';
						$conffileexists= TRUE;
					}
					?>
					<option value="<?php echo basename($file) ?>"<?php echo $selected ?>><?php echo basename($file) ?></option>
					<?php
				}

				if (!$conffileexists && (count($conffiles) > 0)) {
					$ConfigFile= basename($conffiles[0]);
					$this->SetConfig($ConfigFile);
				}
				?>
				</select>
				<input type="submit" name="Select" value="<?php echo _CONTROL('Select') ?>"/>
				<br />
				<input type="submit" name="Delete" value="<?php echo _CONTROL('Delete') ?>" onclick="return confirm('<?php echo $deleteconfirm ?>')"/>
				<br />
				<input type="text" name="CopyTo" style="width: 150px;" maxlength="100" value="<?php echo basename($ConfigFile) ?>"/>
				<input type="submit" name="Copy" value="<?php echo _CONTROL('Copy') ?>"/>
			</form>
			<?php
		}
	}

	/** General form for selecting an OpenVPN conf file to run.
	 */
	function ConfStartStopForm()
	{
		global $Modules;

		$startconfirm= _NOTICE('Are you sure you want to start the <NAME>?');
		$startconfirm= preg_replace('/<NAME>/', _($Modules[$this->Model]['Name']), $startconfirm);

		$stopconfirm= _NOTICE('Are you sure you want to stop the <NAME>?');
		$stopconfirm= preg_replace('/<NAME>/', _($Modules[$this->Model]['Name']), $stopconfirm);

		if ($this->Controller($conffiles, 'GetConfs')) {
			?>
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<select name="ConfFiles[]" multiple style="width: 150px; height: 100px;">
					<?php
					foreach ($conffiles as $file) {
						?>
						<option value="<?php echo basename($file) ?>"><?php echo basename($file) ?></option>
						<?php
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
}

$View= new Openvpn();

/** Sever configuration.
 */
$ServerConfig = array(
    'local' => array(
        'title' => _TITLE2('Local IP address'),
        'info' => _HELPBOX2('Which local IP address should OpenVPN listen on? (optional).'),
		),
    'ifconfig' => array(
        'title' => _TITLE2('Interface Configuration'),
        'info' => _HELPBOX2('Local and remote VPN endpoint IP addresses.'),
		),
    'route' => array(
        'title' => _TITLE2('Route'),
        'info' => _HELPBOX2('Rule to be added to the routing table.'),
		),
    'port' => array(
        'title' => _TITLE2('Port'),
        'info' => _HELPBOX2('Which TCP/UDP port should OpenVPN listen on? If you want to run multiple OpenVPN instances on the same machine, use a different port number for each one.  You will need to open up this port on your firewall.'),
		),
    'proto' => array(
        'title' => _TITLE2('Protocol'),
        'info' => _HELPBOX2('TCP or UDP server?'),
		),
    'dev' => array(
        'title' => _TITLE2('Device'),
        'info' => _HELPBOX2('"dev tun" will create a routed IP tunnel, "dev tap" will create an ethernet tunnel. Use "dev tap0" if you are ethernet bridging and have precreated a tap0 virtual interface and bridged it with your ethernet interface. If you want to control access policies over the VPN, you must create firewall rules for the the TUN/TAP interface.'),
		),
    'ca' => array(
        'title' => _TITLE2('Root Certificate'),
        'info' => _HELPBOX2('SSL/TLS root certificate (ca), certificate (cert), and private key (key).  Each client and the server must have their own cert and key file. The server and all clients will use the same ca file.
Any X509 key management system can be used. OpenVPN can also use a PKCS #12 formatted key file (see "pkcs12" directive in man page).'),
		),
    'cert' => array(
        'title' => _TITLE2('Certificate'),
		),
    'key' => array(
        'title' => _TITLE2('Private Key'),
		),
    'dh' => array(
        'title' => _TITLE2('Diffie-Hellman params'),
        'info' => _HELPBOX2('Generate your own with:
   openssl dhparam -out dh1024.pem 1024
Substitute 2048 for 1024 if you are using 2048 bit keys.'),
		),
    'cipher' => array(
        'title' => _TITLE2('Cipher'),
        'info' => _HELPBOX2('Select a cryptographic cipher. This config item must be copied to the client config file as well.'),
		),
    'server' => array(
        'title' => _TITLE2('Server Mode'),
        'info' => _HELPBOX2('Configure server mode and supply a VPN subnet for OpenVPN to draw client addresses from. Comment this line out if you are ethernet bridging. See the man page for more info.'),
		),
    'tls-server' => array(
        'title' => _TITLE2('Enable SSL/TLS'),
        'info' => _HELPBOX2('Configure server to use SSL/TLS.'),
		),
    'tls-auth' => array(
        'title' => _TITLE2('TLS Auth'),
        'info' => _HELPBOX2('For extra security beyond that provided by SSL/TLS, create an "HMAC firewall" to help block DoS attacks and UDP port flooding.

Generate with:
  openvpn --genkey --secret ta.key

The server and each client must have a copy of this key. The second parameter should be 0 on the server and 1 on the clients. This file is secret.'),
		),
    'keepalive' => array(
        'title' => _TITLE2('Keep Alive'),
        'info' => _HELPBOX2('The keepalive directive causes ping-like messages to be sent back and forth over the link so that each side knows when the other side has gone down. If for example "10 120", ping every 10 seconds, assume that remote peer is down if no ping received during a 120 second time period.'),
		),
    'comp-lzo' => array(
        'title' => _TITLE2('Compression'),
        'info' => _HELPBOX2('Enable compression on the VPN link. If you enable it here, you must also enable it in the client config file.'),
		),
    'persist-key' => array(
        'title' => _TITLE2('Persist Key'),
        'info' => _HELPBOX2('The persist options will try to avoid accessing certain resources on restart that may no longer be accessible because of the privilege downgrade.'),
		),
    'persist-tun' => array(
        'title' => _TITLE2('Persist Tun'),
		),
    'max-clients' => array(
        'title' => _TITLE2('Max Clients'),
        'info' => _HELPBOX2('The maximum number of concurrently connected clients we want to allow.'),
		),
    'verb' => array(
        'title' => _TITLE2('Verbosity'),
        'info' => _HELPBOX2('Set the appropriate level of log file verbosity.
0 is silent, except for fatal errors
4 is reasonable for general usage
5 and 6 can help to debug connection problems
9 is extremely verbose'),
		),
    'ping' => array(
        'title' => _TITLE2('Ping'),
        'info' => _HELPBOX2('Send a UDP ping to remote once every given seconds to keep stateful firewall connection alive.'),
		),
);

/** Client configuration.
 */
$ClientConfig = array(
    'remote' => array(
        'title' => _TITLE2('Remote Server'),
        'info' => _HELPBOX2('Use the same setting as you are using on the server. On most systems, the VPN will not function unless you partially or fully disable the firewall for the TUN/TAP interface.'),
		),
    'ifconfig' => array(
        'title' => _TITLE2('Interface Configuration'),
        'info' => _HELPBOX2('Local and remote VPN endpoint IP addresses.'),
		),
    'route' => array(
        'title' => _TITLE2('Route'),
        'info' => _HELPBOX2('Rule to be added to the routing table.'),
		),
    'proto' => array(
        'title' => _TITLE2('Protocol'),
        'info' => _HELPBOX2('Are we connecting to a TCP or UDP server?  Use the same setting as on the server.'),
		),
    'nobind' => array(
        'title' => _TITLE2('No bind'),
        'info' => _HELPBOX2('Most clients don\'t need to bind to a specific local port number.'),
		),
    'dev' => array(
        'title' => _TITLE2('Device'),
        'info' => _HELPBOX2('Use the same setting as you are using on the server. On most systems, the VPN will not function unless you partially or fully disable the firewall for the TUN/TAP interface.'),
		),
    'ca' => array(
        'title' => _TITLE2('Root Certificate'),
        'info' => _HELPBOX2('SSL/TLS parms. See the server config file for more description.  It\'s best to use a separate .crt/.key file pair for each client.  A single ca file can be used for all clients.'),
		),
    'cert' => array(
        'title' => _TITLE2('Certificate'),
		),
    'key' => array(
        'title' => _TITLE2('Private Key'),
		),
    'cipher' => array(
        'title' => _TITLE2('Cipher'),
        'info' => _HELPBOX2('Select a cryptographic cipher. If the cipher option is used on the server then you must also specify it here.'),
		),
    'tls-client' => array(
        'title' => _TITLE2('Enable SSL/TLS'),
        'info' => _HELPBOX2('Configure client to use SSL/TLS.'),
		),
    'tls-auth' => array(
        'title' => _TITLE2('TLS Auth'),
        'info' => _HELPBOX2('If a tls-auth key is used on the server then every client must also have the key.'),
		),
    'comp-lzo' => array(
        'title' => _TITLE2('Compression'),
        'info' => _HELPBOX2('Enable compression on the VPN link. Don\'t enable this unless it is also enabled in the server config file.'),
		),
    'persist-key' => array(
        'title' => _TITLE2('Persist Key'),
        'info' => _HELPBOX2('Try to preserve some state across restarts.'),
		),
    'persist-tun' => array(
        'title' => _TITLE2('Persist Tun'),
		),
    'verb' => array(
        'title' => _TITLE2('Verbosity'),
        'info' => _HELPBOX2('Set the appropriate level of log file verbosity.
0 is silent, except for fatal errors
4 is reasonable for general usage
5 and 6 can help to debug connection problems
9 is extremely verbose'),
		),
    'ping' => array(
        'title' => _TITLE2('Ping'),
        'info' => _HELPBOX2('Send a UDP ping to remote once every given seconds to keep stateful firewall connection alive.'),
		),
);
?>
