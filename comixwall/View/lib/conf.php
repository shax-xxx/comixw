<?php
/* $ComixWall: conf.php,v 1.34 2009/11/21 22:41:11 soner Exp $ */

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
 * Main configuration file included by all configuration pages.
 */

/** Displays an NVP configuration form, usually a textbox and buttons.
 *
 * This function is used in all configuration pages to modify module parameters.
 * Params can be enabled/disabled, and values can be changed.
 *
 * @param[in]	$values	array All of the NVPs in $Config struct at once
 */
function PrintNVPForm($values)
{
	global $View, $Row;

	foreach ($values as $name => $valueconf) {
		// Model config may be different from View, View displays only this many config
		if (isset($View->Config[$name])) {
			$class= $Row++ % 2 == 0 ? 'evenline' : 'oddline';
			?>
			<tr class="<?php echo $class ?>">
				<td class="title">
					<?php
					if (isset($View->Config[$name]['title'])) {
						$title= $View->Config[$name]['title'];
					}
					else {
						$title= $name;
					}
					echo $title.':';
					?>
				</td>
				<td>
					<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
						<?php
						/// @todo Is there a way to get browser's current font width to use here?
						define('CHAR_WIDTH', '5.75');

						if ($valueconf['Enabled'] === TRUE) {
							if (preg_match("|^$name=(.*)$|", $valueconf['Value'], $match)) {
								// There are values like this: /usr/local/bin/send_sms 123456789 "VIRUS ALERT: %v"
								$value= htmlentities($match[1], ENT_QUOTES);
								?>
								<input type="text" name="ValueToChange" style="width: <?php echo strlen($value)*CHAR_WIDTH > 50 ? strlen($value)*CHAR_WIDTH : 50 ?>px;" maxlength="100" value="<?php echo $value ?>"/>
								<input type="submit" name="Apply" value="<?php echo _CONTROL('Apply') ?>"/>
								<?php
							}
							?>
							<input class="disablebutton" type="submit" name="Disable" value="<?php echo _CONTROL('Disable') ?>"/>
							<?php
						}
						else if ($valueconf['Enabled'] === FALSE) {
							if (preg_match("|^$name=(.*)$|", $valueconf['Value'], $match)) {
								$value= htmlentities($match[1], ENT_QUOTES);
								?>
								<input type="text" disabled name="ValueToChange" style="width: <?php echo strlen($value)*CHAR_WIDTH > 50 ? strlen($value)*CHAR_WIDTH : 50 ?>px;" maxlength="100" value="<?php echo $value ?>"/>
								<?php
							}
							?>
							<input class="enablebutton" type="submit" name="Enable" value="<?php echo _CONTROL('Enable') ?>"/>
							<?php
						}
						?>
						<input type="hidden" name="KeyToChange" value="<?php echo $name ?>" />
					</form>
				</td>
				<td class="none">
					<?php
					if (isset($View->Config[$name]['info'])) {
						PrintHelpBox($View->Config[$name]['info']);
					}
					?>
				</td>
			</tr>
			<?php
		}
	}
}

function PrintReloadConfigForm()
{
	global $ReloadConfig, $Class;
	?>
	<tr class="<?php echo $Class ?>">
		<td class="title">
			<?php echo _TITLE2('Apply configuration').':' ?>
		</td>
		<td>
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<input type="submit" name="Reload" value="<?php echo _CONTROL('Reload') ?>"/>
			</form>
		</td>
		<td class="none">
			<?php
			PrintHelpBox(_HELPBOX2('You can apply configuration changes without restarting currently running process.'));
			?>
		</td>
	</tr>
	<?php
}

// Reset to 0 for non-Dansgardian modules, otherwise Controller complains about arg type of Group
$Group= $_SESSION[$View->Model]['Group'] ? $_SESSION[$View->Model]['Group'] : 0;

if ($_POST) {
	if ($_POST['Apply']) {
		/// Need to remove \'s for RE match; POST method escapes 's and "s
		$Value= preg_replace("/\\\\'/", "'", $_POST['ValueToChange']);
		$Value= preg_replace('/\\\\"/', '"', $Value);

		if ($View->Controller($Output, 'SetConfValue', $_POST['KeyToChange'], $Value, $ViewConfigName, $Group)) {
			cwwui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'Configuration changed: '.$_POST['ConfFile'].': '.$_POST['KeyToChange'].' = '.$_POST['ValueToChange']);
		}
	}
	else if ($_POST['Disable']) {
		/// @warning PHP (?) escapes backslashes, remove first
		if ($View->Controller($Output, 'DisableConf', RemoveBackSlashes($_POST['KeyToChange']), $ViewConfigName, $Group)) {
			cwwui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'Configuration disabled: '.$_POST['ConfFile'].': '.$_POST['KeyToChange']);
		}
	}
	else if ($_POST['Enable']) {
		/// @warning PHP (?) escapes backslashes, remove first
		if ($View->Controller($Output, 'EnableConf', RemoveBackSlashes($_POST['KeyToChange']), $ViewConfigName, $Group)) {
			cwwui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'Configuration enabled: '.$_POST['ConfFile'].': '.$_POST['KeyToChange']);
		}
	}
	else if ($_POST['Reload']) {
		if ($View->Controller($Output, 'Reload')) {
			cwwui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'Configuration reloaded');
		}
	}
}

$View->SetSessionFilterGroup();

require_once($VIEW_PATH.'header.php');

if (isset($PRINT_GROUP_FORM) && $PRINT_GROUP_FORM) {
	$View->PrintFilterGroupForm();
	$Group= $_SESSION[$View->Model]['Group'];
}
?>
<table id="nvp">
	<?php
	$Row= 1;
	if (isset($ReloadConfig) && $ReloadConfig) {
		$Class= $Row++ % 2 == 0 ? 'evenline' : 'oddline';
		PrintReloadConfigForm();
	}

	if (isset($CustomFunc)) {
		$Class= $Row++ % 2 == 0 ? 'evenline' : 'oddline';
		$CustomFunc($CustomFuncParam);
	}

	if ($View->Controller($output, 'GetConfigValues', $ViewConfigName, $Group)) {
		PrintNVPForm(unserialize($output[0]));
	}
	?>
</table>
<?php
PrintHelpWindow($View->ConfHelpMsg);
require_once($VIEW_PATH.'footer.php');
?>
