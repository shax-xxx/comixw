<?php
/* $ComixWall: conf.php,v 1.20 2009/11/21 21:55:59 soner Exp $ */

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

/** Wrapper for ConfSelectForm().
 *
 * @param[in]	$module		string Module name passed to ConfSelectForm()
 * @param[in]	$helpmsg	string Extra help msg to append.
 */
function PrintConfSelectForm($module, $helpmsg= '')
{
	global $View, $Class;
	?>
	<tr class="<?php echo $Class ?>">
		<td class="title">
			<?php echo _TITLE2('Configuration').':' ?>
		</td>
		<td>
			<?php
			$View->ConfSelectForm($module);
			?>
		</td>
		<td class="none">
			<?php PrintHelpBox(_HELPBOX2('Here you can select the configuration file to view or modify. Also, you can delete the selected configuration file or copy it under another name. Only file names with .conf extention are displayed.').' '.$helpmsg) ?>
		</td>
	</tr>
	<?php
}

if ($_SESSION[$View->Model][basename($_SERVER['PHP_SELF'])]['ConfFile']) {
	$ConfigFile= $_SESSION[$View->Model][basename($_SERVER['PHP_SELF'])]['ConfFile'];
}

if ($_POST) {
	if ($_POST['Select']) {
		$ConfigFile= $_POST['ConfFile'];
	}
	else if ($_POST['Delete']) {
		if ($View->Controller($ConfFiles, 'DeleteConf', $_POST['ConfFile'])) {
			if ($View->Controller($ConfFiles, 'GetConfs')) {
				if (count($ConfFiles) > 0) {
					$ConfigFile= basename($ConfFiles[0]);
				}
			}
		}
	}
	else if ($_POST['Copy']) {
		// File names should always have .conf ext, otherwise they are not displayed on the webif
		$NewFileName= $_POST['CopyTo'];
		if (!preg_match('/^.*\.conf$/', $_POST['CopyTo'], $Fields)) {
			$NewFileName= $_POST['CopyTo'].'.conf';
		}

		$View->Controller($Output, 'CopyConf', $ConfigFile, $NewFileName);
	}
}

$View->SetConfig($ConfigFile);

$CustomFunc= 'PrintConfSelectForm';
$CustomFuncParam= $View->Model;

require_once('../lib/conf.php');
?>
