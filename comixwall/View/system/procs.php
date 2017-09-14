<?php
/* $ComixWall: procs.php,v 1.24 2009/11/21 21:55:58 soner Exp $ */

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
 * Lists all processes running on the system.
 */

require_once('include.php');

$GoingDown= FALSE;
if ($_POST) {
	if ($_POST['Restart']) {
		PrintHelpWindow(_NOTICE('System is restarting...'), 'auto', 'WARN');
		$View->Controller($Output, 'Restart');
	}
	else if ($_POST['Stop']) {
		PrintHelpWindow(_NOTICE('System is going down...'), 'auto', 'WARN');
		$View->Controller($Output, 'Shutdown');
	}
	$GoingDown= TRUE;
}

$Reload= TRUE;
require_once($VIEW_PATH.'header.php');

if (!$GoingDown) {
	/// Only admin can start/stop the system
	if (in_array($_SESSION['USER'], $ADMIN)) {
		$RestartConfirm= _NOTICE('Are you sure you want to restart the <NAME>?');
		$RestartConfirm= preg_replace('/<NAME>/', _($Modules[$View->Model]['Name']), $RestartConfirm);
		$StopConfirm= _NOTICE('Are you sure you want to stop the <NAME>?');
		$StopConfirm= preg_replace('/<NAME>/', _($Modules[$View->Model]['Name']), $StopConfirm);
		?>
		<table>
			<tr>
				<td>
					<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
						<strong><?php echo _TITLE('System').' ' ?></strong>
						<input type="submit" name="Restart" value="<?php echo _CONTROL('Restart') ?>" onclick="return confirm('<?php echo $RestartConfirm ?>')"/>
						<input type="submit" name="Stop" value="<?php echo _CONTROL('Stop') ?>" onclick="return confirm('<?php echo $StopConfirm ?>')"/>
					</form>
				</td>
				<td style="width: 50%;">
					<?php
					PrintHelpBox(_HELPBOX('You can restart or stop the system using these buttons.'), 400);
					?>
				</td>
			</tr>
		</table>
		<?php
	}
	$View->Controller($Output, 'GetProcList');
	$View->PrintProcessTable(unserialize($Output[0]), TRUE);
}

require_once($VIEW_PATH.'footer.php');
?>
