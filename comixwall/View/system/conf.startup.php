<?php
/* $ComixWall: conf.startup.php,v 1.19 2009/11/21 21:55:58 soner Exp $ */

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
 * Service startup configuration.
 */

if ($_POST) {
	foreach ($_POST['Services'] as $Service) {
		if ($_POST['>>']) {
			$View->Controller($Status, 'DisableService', $Service);
		}
		else if ($_POST['<<']) {
			$View->Controller($Status, 'EnableService', $Service);
		}
	}
}

require_once($VIEW_PATH.'header.php');

$ServiceDescs= array(
	'/usr/local/sbin/e2guardian'	=> _TITLE2('Web Filter'),
	'/usr/local/sbin/squid'			=> _TITLE2('HTTP Proxy'),
	'/usr/local/bin/snort'			=> _TITLE2('Intrusion Detection'),
	'/usr/local/sbin/snortips'		=> _TITLE2('Intrusion Prevention'),
	'/usr/local/sbin/clamd'			=> _TITLE2('Virus Filter'),
	'/usr/local/bin/freshclam'		=> _TITLE2('Virus DB Update'),
	'/usr/local/sbin/sockd'			=> _TITLE2('SOCKS Proxy'),
	'/usr/local/libexec/symux'		=> _TITLE2('Symux System Monitoring'),
	'/usr/local/libexec/symon'		=> _TITLE2('Symon System Monitoring'),
	'/usr/local/sbin/pmacctd'		=> _TITLE2('Pmacct Network Monitoring'),
	'pf'							=> _TITLE2('Packet Filter'),
	'dhcpd_flags'					=> _TITLE2('DHCP Server'),
	'named_flags'					=> _TITLE2('DNS Server'),
	'ftpproxy_flags'				=> _TITLE2('FTP Proxy'),
	'nginx_flags'					=> _TITLE2('Web Server (WUI)'),
	'ntpd_flags'					=> _TITLE2('Network Time'),
	'apmd_flags'					=> _TITLE2('Advanced Power Management'),
	);


if ($View->Controller($Output, 'GetServiceStartStatus')) {
	$StartStatus= unserialize($Output[0]);
	?>
	<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
		<table style="width: auto;">
			<tr>
				<td style="width: 0;">
					<?php
					echo _TITLE('Enabled Services').':';
					?>
					<br />
					<select name="Services[]" multiple style="width: 250px; height: 350px;">
						<?php
						foreach ($StartStatus as $Service => $Status) {
							if ($Status) {
								?>
								<option value="<?php echo $Service ?>" title="<?php echo $Service ?>"><?php echo _($ServiceDescs[$Service]) ?></option>
								<?php
							}
						}
						?>
					</select>
				</td>
				<td style="width: 0;">
					<input type="submit" name=">>" value=">>"/>
					<input type="submit" name="<<" value="<<"/>
				</td>
				<td style="width: 0;">
					<?php
					echo _TITLE('Disabled Services').':';
					?>
					<br />
					<select name="Services[]" multiple style="width: 250px; height: 350px;">
						<?php
						foreach ($StartStatus as $Service => $Status) {
							if (!$Status) {
								?>
								<option value="<?php echo $Service ?>" title="<?php echo $Service ?>"><?php echo _($ServiceDescs[$Service]) ?></option>
								<?php
							}
						}
						?>
					</select>
				</td>
			</tr>
		</table>
	</form>
	<?php
}

PrintHelpWindow(_HELPWINDOW('ComixWall runs many services or daemons in default installation. On this page you can configure these services to start at boot time.

Note that if you modify startup configuration for some services, you may need to change packet filter rules or other related settings too.'));
require_once($VIEW_PATH.'footer.php');
?>
