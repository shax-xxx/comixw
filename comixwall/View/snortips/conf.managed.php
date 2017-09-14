<?php
/* $ComixWall: conf.managed.php,v 1.20 2009/11/24 19:28:45 soner Exp $ */

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
 * Snort IPS managed lists.
 */

$Reload= TRUE;
SetRefreshInterval();

if ($_POST) {
	if ($_POST['UnblockAll']) {
		$View->Controller($Output, 'UnblockAll');
	}
	else if ($_POST['Unblock']) {
		$View->Controller($Output, 'UnblockIPs', serialize($_POST['ItemsToDelete']));
	}
	else if ($_POST['Block']) {
		$View->Controller($Output, 'BlockIP', $_POST['ItemToAdd'], $_POST['TimeToAdd']);
	}
}

require_once($VIEW_PATH.'header.php');
?>
<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
	<table>
		<tr>
			<td>
				<?php echo _TITLE('Refresh interval').': ' ?><input type="text" name="RefreshInterval" style="width: 20px;" maxlength="2" value="<?php echo $_SESSION[$View->Model]['ReloadRate'] ?>" />
				<?php echo ' '._TITLE('secs') ?>
				<input type="submit" name="Apply" value="<?php echo _CONTROL('Apply') ?>"/>
			</td>
		</tr>
	</table>
</form>
<table style="width: 500px;">
	<tr>
	<?php
	$View->PrintBlockedIPsForm();
	?>
	</tr>
</table>
<?php
PrintHelpWindow(_HELPWINDOW('This page displays the hosts managed by the IPS, i.e. blocked or unblocked IPs or networks. You can temporarily block hosts for a period of time. If expiration time is not provided, the default duration is used. You can enter individual IPs or network addresses. IP and network addresses can overlap.

This page reloads automatically to update the managed list. If the IPS is not running, the page displays blank.'));
require_once($VIEW_PATH.'footer.php');
?>
