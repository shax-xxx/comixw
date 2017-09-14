<?php
/* $ComixWall: imlogs.php,v 1.28 2009/11/25 14:27:36 soner Exp $ */

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

class Imlogs extends View
{
	public $Model= 'imlogs';

	function PrintLogLine($cols, $linenum)
	{
		$this->PrintLogLineClass($cols['User']);

		PrintLogCols($linenum, $cols);
		echo '</tr>';
	}

	function FormatLogCols(&$cols)
	{
		$cols['Log']= wordwrap($cols['Log'], 100, '<br />', TRUE);
	}
}

$View= new Imlogs();

if ($_POST['Proto']) {
	$_SESSION[$View->Model]['Proto']= $_POST['Proto'];
	unset($_SESSION[$View->Model]['LocalUser'],
		$_SESSION[$View->Model]['RemoteUser'],
		$_SESSION[$View->Model]['Session']);
}
if ($_POST['LocalUser']) {
	$_SESSION[$View->Model]['LocalUser']= $_POST['LocalUser'];
	unset($_SESSION[$View->Model]['RemoteUser'],
		$_SESSION[$View->Model]['Session']);
}
if ($_POST['RemoteUser']) {
	$_SESSION[$View->Model]['RemoteUser']= $_POST['RemoteUser'];
	unset($_SESSION[$View->Model]['Session']);
}
if ($_POST['Session']) {
	$_SESSION[$View->Model]['Session']= $_POST['Session'];
}

if ($_SESSION[$View->Model]['Proto']) {
	$Proto= $_SESSION[$View->Model]['Proto'];
}
if ($_SESSION[$View->Model]['LocalUser']) {
	$LocalUser= $_SESSION[$View->Model]['LocalUser'];
}
if ($_SESSION[$View->Model]['RemoteUser']) {
	$RemoteUser= $_SESSION[$View->Model]['RemoteUser'];
}
if ($_SESSION[$View->Model]['Session']) {
	$Session= $_SESSION[$View->Model]['Session'];
}

$SelectHeight= '100px';
require_once($VIEW_PATH.'header.php');
?>
<table>
	<tr>
		<td class="imselectbox">
		<?php
		$View->Controller($Output, 'GetProtocols');
		?>
		<?php echo _TITLE2('Protocol').':' ?>
		<form id="ProtoForm" name="ProtoForm" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
			<select name="Proto" onchange="document.ProtoForm.submit()" multiple style="width: 175px; height: <?php echo $SelectHeight ?>;">
				<?php
				if (!empty($Output)) {
					foreach ($Output as $Protocol) {
						$Selected= $Protocol === $Proto ? ' selected' : '';
						?>
						<option value="<?php echo $Protocol ?>"<?php echo $Selected ?>><?php echo $Protocol ?></option>
						<?php
					}
				}
				?>
			</select>
		</form>
		</td>
		<td class="imselectbox">
		<?php
		if (isset($Proto)) {
			$View->Controller($Output, 'GetLocalUsers', $Proto);
			?>
			<label class="imlocaluser"><?php echo _TITLE2('Local User').':' ?></label>
			<form id="LocalUserForm" name="LocalUserForm" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<select name="LocalUser" onchange="document.LocalUserForm.submit()" multiple style="width: 175px; height: <?php echo $SelectHeight ?>;">
					<?php
					foreach ($Output as $User) {
						$Selected= $User === $LocalUser ? ' selected' : '';
						?>
						<option value="<?php echo $User ?>"<?php echo $Selected ?>><?php echo $User ?></option>
						<?php
					}
					?>
				</select>
				<input type="hidden" name="Proto" value="<?php echo $Proto ?>" />
			</form>
			<?php
		}
		?>
		</td>
		<td class="imselectbox">
		<?php
		if (isset($LocalUser)) {
			$View->Controller($Output, 'GetRemoteUsers', $Proto, $LocalUser);
			?>
			<?php echo _TITLE2('Remote User').':' ?>
			<form id="RemoteUserForm" name="RemoteUserForm" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<select name="RemoteUser" onchange="document.RemoteUserForm.submit()" multiple style="width: 175px; height: <?php echo $SelectHeight ?>;">
					<?php
					foreach ($Output as $User) {
						$Selected= $User === $RemoteUser ? ' selected' : '';
						?>
						<option value="<?php echo $User ?>"<?php echo $Selected ?>><?php echo $User ?></option>
						<?php
					}
					?>
				</select>
				<input type="hidden" name="Proto" value="<?php echo $Proto ?>" />
				<input type="hidden" name="LocalUser" value="<?php echo $LocalUser ?>" />
			</form>
			<?php
		}
		?>
		</td>
		<td class="imselectbox">
		<?php
		if (isset($RemoteUser)) {
			$View->Controller($Output, 'GetSessions', $Proto, $LocalUser, $RemoteUser);
			?>
			<?php echo _TITLE2('Session').':' ?>
			<form id="SessionForm" name="SessionForm" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<select name="Session" onchange="document.SessionForm.submit()" multiple style="width: 175px; height: <?php echo $SelectHeight ?>;">
					<?php
					foreach ($Output as $SessionDate) {
						$Selected= $SessionDate === $Session ? ' selected' : '';
						?>
						<option value="<?php echo $SessionDate ?>"<?php echo $Selected ?>><?php echo $SessionDate ?></option>
						<?php
					}
					?>
				</select>
				<input type="hidden" name="Proto" value="<?php echo $Proto ?>" />
				<input type="hidden" name="LocalUser" value="<?php echo $LocalUser ?>" />
				<input type="hidden" name="RemoteUser" value="<?php echo $RemoteUser ?>" />
			</form>
			<?php
		}
		?>
		</td>
	</tr>
</table>
<?php
if ($Session) {
	$View->Controller($Output, 'GetImLogFile', $Proto, $LocalUser, $RemoteUser, $Session);
	$LogFile= Escape($Output[0], ';{}');
	$_SESSION[$View->Model]['LogFile']= $LogFile;

	ProcessStartLine($StartLine);
	UpdateLogsPageSessionVars($LinesPerPage, $SearchRegExp);

	/// @todo GetLogs here, compute LogSize using Logs, this is double work otherwise
	$View->Controller($Output, 'GetFileLineCount', $LogFile, $SearchRegExp);
	$LogSize= $Output[0];

	ProcessNavigationButtons($LinesPerPage, $LogSize, $StartLine, $HeadStart);

	$CustomHiddenInputs= <<<EOF
<input type="hidden" name="Proto" value="$Proto" />
<input type="hidden" name="LocalUser" value="$LocalUser" />
<input type="hidden" name="RemoteUser" value="$RemoteUser" />
<input type="hidden" name="Session" value="$Session" />
EOF;
	PrintLogHeaderForm($StartLine, $LogSize, $LinesPerPage, $SearchRegExp, $CustomHiddenInputs);
	?>
	<table id="imlogs">
		<?php
		PrintTableHeaders($View->Model);
		?>
		<?php
		$View->Controller($Output, 'GetLogs', $LogFile, $HeadStart, $LinesPerPage, $SearchRegExp);
		$Logs= unserialize($Output[0]);

		$LineCount= $StartLine + 1;
		foreach ($Logs as $Log) {
			$View->PrintLogLine($Log, $LineCount++);
		}
		?>
	</table>
	<?php
}

PrintHelpWindow(_HELPWINDOW('IM logs are categorized by protocols. After you select the protocol, you need to select the local user. Local user sessions are further categorized by remote users connected. Finally, sessions with remote users are categorized by date. IM proxy can log group chats as well.

You can find these logs under /var/log/imspector/.'));
require_once($VIEW_PATH.'footer.php');
?>
