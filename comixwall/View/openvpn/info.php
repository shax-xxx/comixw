<?php
/* $ComixWall: info.php,v 1.25 2009/11/21 22:41:11 soner Exp $ */

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

require_once('include.php');

/** Wrapper for ConfStartStopForm().
 */
function PrintConfStartStopForm()
{
	global $View, $ADMIN;
	
	/// Only admin can start/stop the processes
	if (in_array($_SESSION['USER'], $ADMIN)) {
		?>
		<table id="ifselect">
			<tr>
				<td class="title">
					<?php echo _TITLE2('Configuration').':' ?>
				</td>
				<td>
					<?php
					$View->ConfStartStopForm();
					?>
				</td>
				<td class="help">
					<?php PrintHelpBox(_HELPBOX2('Here you should select configuration file(s) to start or stop the process with. Instance with the selected configuration is restarted if it is already running. Only the files with .conf extention are displayed.')) ?>
				</td>
			</tr>
		</table>
		<?php
	}
}

if ($_POST['Start']) {
	if ($_POST['ConfFiles']) {
		foreach ($_POST['ConfFiles'] as $file) {
			$View->Controller($Output, 'Restart', $file);
		}
	}
	else {
		PrintHelpWindow(_NOTICE('FAILED').': '._NOTICE('You should select at least one conf file to start the process'), 'auto', 'ERROR');
	}
}
else if ($_POST['Stop']) {
	if ($_POST['ConfFiles']) {
		foreach ($_POST['ConfFiles'] as $file) {
			$View->Controller($Output, 'StopProcess', $file);
		}
	}
	else {
		$View->Stop();
	}
}

$Reload= TRUE;
require_once($VIEW_PATH.'header.php');
		
$View->PrintStatusForm(FALSE, FALSE);
PrintConfStartStopForm();

PrintHelpWindow(_HELPWINDOW('OpenVPN is a virtual private networking solution based on OpenSSL. You should create different configuration for servers and clients, and start OpenVPN accordingly.'));
require_once($VIEW_PATH.'footer.php');
?>
