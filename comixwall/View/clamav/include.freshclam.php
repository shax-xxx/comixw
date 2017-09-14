<?php
/* $ComixWall: include.freshclam.php,v 1.22 2009/12/01 15:00:00 soner Exp $ */

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

class Freshclam extends View
{
	public $Model= 'freshclam';

	function Freshclam()
	{
		$this->LogsHelpMsg= _HELPWINDOW('Freshclam logs details of each virus update session. Version warnings may not be critical as mentioned in the logs as well.');
		$this->ConfHelpMsg= _HELPWINDOW('You may want to customize the frequency of database checks. Also make sure you have chosen a database mirror close to you.');
	
		$this->Config = array(
			'Checks' => array(
				'title' => _TITLE2('Database Checks'),
				'info' => _HELPBOX2('Number of database checks per day.
		Default: 12 (every two hours)'),
				),
			'MaxAttempts' => array(
				'title' => _TITLE2('Max Attempts'),
				'info' => _HELPBOX2('How many attempts to make before giving up.
		Default: 3 (per mirror)'),
				),
			'SafeBrowsing' => array(
				'title' => _TITLE2('Safe Browsing'),
				'info' => _HELPBOX2('This option enables support for Google Safe Browsing. When activated for the first time, freshclam will download a new database file (safebrowsing.cvd) which will be automatically loaded by clamd and clamscan during the next reload, provided that the heuristic phishing detection is turned on. This database includes information about websites that may be phishing sites or possible sources of malware. When using this option, it\'s mandatory to run freshclam at least every 30 minutes.
		Default: disabled'),
				),
			'LogVerbose' => array(
				'title' => _TITLE2('Log Verbose'),
				'info' => _HELPBOX2('Enable verbose logging.
		Default: disabled'),
				),
			'DNSDatabaseInfo' => array(
				'title' => _TITLE2('DNS Database Info'),
				'info' => _HELPBOX2('Use DNS to verify virus database version. Freshclam uses DNS TXT records to verify database and software versions. With this directive you can change the database verification domain.
		Default: enabled, pointing to current.cvd.clamav.net'),
				),
			'HTTPProxyServer' => array(
				'title' => _TITLE2('HTTP Proxy Server'),
				'info' => _HELPBOX2('Proxy settings
		Default: disabled'),
				),
			'HTTPProxyPort' => array(
				'title' => _TITLE2('HTTP Proxy Port'),
				),
			'HTTPProxyUsername' => array(
				'title' => _TITLE2('HTTP Proxy Username'),
				),
			'HTTPProxyPassword' => array(
				'title' => _TITLE2('HTTP Proxy Password'),
				),
			'LocalIPAddress' => array(
				'title' => _TITLE2('Local IP Address'),
				'info' => _HELPBOX2('Use aaa.bbb.ccc.ddd as client address for downloading databases. Useful for multi-homed systems.
		Default: Use OS\'es default outgoing IP address.'),
				),
			'Debug' => array(
				'title' => _TITLE2('Debug'),
				'info' => _HELPBOX2('Enable debug messages in libclamav.
		Default: disabled'),
				),
		);
	}
	
	function FormatLogCols(&$cols)
	{
		$cols['Log']= wordwrap($cols['Log'], 100, '<br />', TRUE);
	}
}

$View= new Freshclam();

/** Prints database mirrors.
*/
function PrintDatabaseMirrorsForm()
{
	global $View, $Class;
	?>
	<tr class="<?php echo $Class ?>">
		<td class="title">
			<?php echo _TITLE2('Database Mirrors').':' ?>
		</td>
		<td>
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<input style="display:none;" type="submit" name="Add" value="<?php echo _CONTROL('Add') ?>"/>
				<select name="Mirrors[]" multiple style="width: 200px; height: 50px;">
					<?php
					if ($View->Controller($output, 'GetMirrors')) {
						foreach ($output as $mirror) {
							?>
							<option value="<?php echo $mirror ?>"><?php echo $mirror ?></option>
							<?php
						}
					}
					?>
				</select>
				<input type="submit" name="Delete" value="<?php echo _CONTROL('Delete') ?>"/><br />
				<input type="text" name="MirrorToAdd" style="width: 200px;" maxlength="21"/>
				<input type="submit" name="Add" value="<?php echo _CONTROL('Add') ?>"/>
			</form>
		</td>
		<td class="none">
			<?php
			PrintHelpBox(_HELPBOX2("Here you can enter db.XY.clamav.net for a mirror close to you. Replace XY with your country code. See http://www.iana.org/cctld/cctld-whois.htm for the full list.

Not shown in this list is database.clamav.net, which is a round-robin record and points to ClamAV's most reliable mirrors. It is used as a fall back in case db.XY.clamav.net is not working."));
			?>
		</td>
	</tr>
	<?php
}
?>
