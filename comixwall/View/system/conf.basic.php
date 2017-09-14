<?php
/* $ComixWall: conf.basic.php,v 1.22 2009/11/22 22:20:14 soner Exp $ */

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
 * Basic system-wide configuration.
 */

/** Displays a form to change system datetime.
 */
function PrintDateTimeForm($remotetime)
{
	global $TimeServer;

	if ($_SESSION['TimeServer']) {
		$TimeServer= $_SESSION['TimeServer'];
	}

	$day= date('d');
	$month= date('m');
	$year= date('y');
	$hour= date('H');
	$min= date('i');
	
	$confirm= _NOTICE('Are you sure you want to set the date?');
	?>
	<tr class="oddline">
		<td class="title">
			<?php echo _TITLE('Time server').':' ?>
		</td>
		<td>
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<input type="text" name="TimeServer" style="width: 200px;" maxlength="50" value="<?php echo $TimeServer ?>" />
				<input type="submit" name="Display" value="<?php echo _CONTROL('Display') ?>"/>
				<input type="submit" name="Apply" value="<?php echo _CONTROL('Apply') ?>" onclick="return confirm('<?php echo $confirm ?>')"/>
				<?php
				if (isset($remotetime)) {
					echo '<br />'.$remotetime;
				}
				?>
			</form>
		</td>
		<td class="none">
			<?php
			PrintHelpBox(_HELPBOX('Usually the network time daemon adjusts the system clock according to Internet time servers gradually. But if the clock is too out of sync with the time servers, you are advised to set the system clock to Internet time manually. You can use your own time server if you like.'));
			?>
		</td>
	</tr>
	<tr class="evenline">
		<td class="title">
			<?php echo _TITLE('Date').':' ?>
		</td>
		<td>
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<input type="text" name="Day" style="width: 20px;" maxlength="2" value="<?php echo $day ?>" />.
				<input type="text" name="Month" style="width: 20px;" maxlength="2" value="<?php echo $month ?>" />.
				<input type="text" name="Year" style="width: 20px;" maxlength="2" value="<?php echo $year ?>" /> -
				<input type="text" name="Hour" style="width: 20px;" maxlength="2" value="<?php echo $hour ?>" />:
				<input type="text" name="Minute" style="width: 20px;" maxlength="2" value="<?php echo $min ?>" />
				<input type="submit" name="Apply" value="<?php echo _CONTROL('Apply') ?>" onclick="return confirm('<?php echo $confirm ?>')"/>
			</form>
		</td>
		<td class="none">
			<?php
			PrintHelpBox(_HELPBOX('You can set the system clock here. Format is day.month.year-hour:minute.

<b>You must be very careful when setting the system clock, significant hops in system time may cause system malfunction.</b>'));
			?>
		</td>
	</tr>
	<?php
}

if ($_POST) {
	if ($_POST['Year'] && $_POST['Month'] && $_POST['Day'] && $_POST['Hour'] && $_POST['Minute']) {
		$NewDateTime= $_POST['Year'].$_POST['Month'].$_POST['Day'].$_POST['Hour'].$_POST['Minute'];
		if (!$View->Controller($Output, 'SetDateTime', $NewDateTime)) {
			cwwui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "Time set failed: $NewDateTime");
		}
	}
	else if ($_POST['TimeServer']) {
		$_SESSION['TimeServer']= $_POST['TimeServer'];
		$TimeServer= $_SESSION['TimeServer'];

		if ($_POST['Apply']) {
			$View->Controller($Output, 'SetRemoteTime', $TimeServer);
		}
		else if ($_POST['Display']) {
			$View->Controller($Output, 'DisplayRemoteTime', $TimeServer);
		}
		$View->Controller($Output, 'GetRemoteTime');
		$RemoteTime= implode("\n", $Output);
	}
	else if ($_POST['MyName']) {
		$View->Controller($Output, 'SetMyName', trim($_POST['MyName']));
	}
	else if ($_POST['RootEmail']) {
		if ($View->Controller($Output, 'SetRootEmail', $_POST['RootEmail'])) {
			$View->Controller($Output, 'UpdateMailAliases');
		}
	}
}

require_once($VIEW_PATH.'header.php');
?>
<table id="nvp">
	<tr class="oddline">
		<td class="title">
			<?php echo _TITLE('Hostname').':' ?>
		</td>
		<td>
			<?php
			if ($View->Controller($Myname, 'GetMyName')) {
				?>
				<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
					<input type="text" name="MyName" style="width: 200px;" maxlength="50" value="<?php echo $Myname[0] ?>" />
					<input type="submit" name="Apply" value="<?php echo _CONTROL('Apply') ?>"/>
				</form>
				<?php
			}
			?>
		</td>
		<td class="none">
			<?php
			PrintHelpBox(_HELPBOX('This is the system name. If you change the hostname, use automatic configuration button to apply it system-wide, and reboot the system.'));
			?>
		</td>
	</tr>
	<tr class="evenline">
		<td class="title">
			<?php echo _TITLE('Admin e-mail').':' ?>
		</td>
		<td>
			<?php
			if ($View->Controller($RootEmail, 'GetRootEmail')) {
				?>
				<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
					<input type="text" name="RootEmail" style="width: 200px;" maxlength="50" value="<?php echo $RootEmail[0] ?>" />
					<input type="submit" name="Apply" value="<?php echo _CONTROL('Apply') ?>"/>
				</form>
				<?php
			}
			?>
		</td>
		<td class="none">
			<?php
			PrintHelpBox(_HELPBOX('ComixWall sends warning and information messages to its system administrator. Here you can type in your own e-mail address.'));
			?>
		</td>
	</tr>
	<?php
	PrintDateTimeForm($RemoteTime);
	?>
</table>
<?php
PrintHelpWindow(_HELPWINDOW('New system hostname does not fully take effect until after reboot.

Messages to the system administrator are directly transfered to the mail server hosting the e-mail address you have provided above. The remote mail server may consider e-mails sent from this system as spam, and move them into your spam or junk folder.'));
require_once($VIEW_PATH.'footer.php');
?>
