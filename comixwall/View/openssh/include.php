<?php
/* $ComixWall: include.php,v 1.11 2009/11/10 18:47:49 soner Exp $ */

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

class Openssh extends View
{
	public $Model= 'openssh';
	public $Layout= 'sshd';
	
	function Openssh()
	{
		$this->LogsHelpMsg= _HELPWINDOW('OpenSSH logs detailed information on all connections, successful logins and failures. Also provided are warnings based on reverse DNS lookups.');
		$this->ConfHelpMsg= _HELPWINDOW('Thanks to OpenSSH, remote root login to the system is permitted by default. TCP Keep Alive and Use DNS options may help you resolve some issues.');
	
		$this->Config = array(
			'Port' => array(
				'title' => _TITLE2('Port'),
				'info' => _HELPBOX2('The strategy used for options in the default sshd_config shipped with OpenSSH is to specify options with their default value where possible, but leave them commented.  Uncommented options change a default value.'),
				),
			'Protocol' => array(
				'title' => _TITLE2('Protocol'),
				),
			'AddressFamily' => array(
				'title' => _TITLE2('Address Family'),
				),
			'ListenAddress' => array(
				'title' => _TITLE2('Listen Address'),
				),
			'ServerKeyBits' => array(
				'title' => _TITLE2('Server Key Bits'),
				),
			'SyslogFacility' => array(
				'title' => _TITLE2('Syslog Facility'),
				'info' => _HELPBOX2('Obsoletes QuietMode and FascistLogging'),
				),
			'LogLevel' => array(
				'title' => _TITLE2('Log Level'),
				),
			'LoginGraceTime' => array(
				'title' => _TITLE2('Login Grace Time'),
				),
			'PermitRootLogin' => array(
				'title' => _TITLE2('Permit Root Login'),
				),
			'MaxAuthTries' => array(
				'title' => _TITLE2('Max Auth Tries'),
				),
			'PermitEmptyPasswords' => array(
				'title' => _TITLE2('Permit Empty Passwords'),
				),
			'PrintMotd' => array(
				'title' => _TITLE2('Print Motd'),
				),
			'PrintLastLog' => array(
				'title' => _TITLE2('Print Last Log'),
				),
			'TCPKeepAlive' => array(
				'title' => _TITLE2('TCP Keep Alive'),
				),
			'UseDNS' => array(
				'title' => _TITLE2('Use DNS'),
				),
			'MaxStartups' => array(
				'title' => _TITLE2('MaxStartups'),
				),
			'Banner' => array(
				'title' => _TITLE2('Banner'),
				'info' => _HELPBOX2('No default banner path'),
				),
			'Subsystem\s+sftp' => array(
				'title' => _TITLE2('Subsystem sftp'),
				'info' => _HELPBOX2('Override default of no subsystems'),
				),
		);
	}
}

$View= new Openssh();
?>
