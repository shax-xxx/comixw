<?php
/* $ComixWall: conf.logs.php,v 1.23 2009/11/25 23:44:27 soner Exp $ */

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
 * Newsyslog configuration.
 */

if ($_POST) {
	$LogFile= RemoveBackSlashes($_POST['LogFile']);
	if ($_POST['Apply']) {
		$View->Controller($Output, 'SetLogsConfig', $_POST['Model'], $LogFile, $_POST['Count'], $_POST['Size'], $_POST['When']);
	}
	else if ($_POST['Rotate']) {
		$View->Controller($Output, 'RotateLogFile', $LogFile);
		// nginx may be killed during rotation
		header('Location: '.$_SERVER['REQUEST_URI']);
		exit;
	}
	else if ($_POST['RotateAll']) {
		$View->Controller($Output, 'RotateAllLogFiles');
		// nginx is killed during rotation
		header('Location: '.$_SERVER['REQUEST_URI']);
		exit;
	}
}

require_once($VIEW_PATH.'header.php');

if ($View->Controller($Output, 'GetLogsConfig')) {
	$LogsConfig= unserialize($Output[0]);

	$confirm= _NOTICE('Are you sure you want to rotate the logs?');
	?>
	<table id="nvp">
		<tr id="logline">
			<th><?php echo _TITLE('Logs') ?></th>
			<th class="lheader"><?php echo _TITLE('File') ?></th>
			<th class="lheader"><?php echo _TITLE('Count') ?></th>
			<th class="lheader"><?php echo _TITLE('Size (KB)') ?></th>
			<th class="lheader"><?php echo _TITLE('When (h)') ?></th>
		</tr>
		<?php
		$Row= 1;
		foreach ($LogsConfig as $LogFile => $Conf) {
			$Class= $Row++ % 2 == 0 ? 'evenline' : 'oddline';
			?>
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<tr class="<?php echo $Class ?>">
					<td class="title">
						<?php echo _($Modules[$Conf['Model']]['Name']).':' ?>
					</td>
					<td>
						<?php echo $LogFile ?>
					</td>
					<td class="logprop">
						<input type="text" name="Count" style="width: 50px;" maxlength="10" value="<?php echo $Conf['Count'] ?>"/>
					</td>
					<td class="logprop">
						<input type="text" name="Size" style="width: 50px;" maxlength="10" value="<?php echo $Conf['Size'] ?>"/>
					</td>
					<td class="logprop">
						<input type="text" name="When" style="width: 50px;" maxlength="10" value="<?php echo $Conf['When'] ?>"/>
					</td>
					<td class="logprop">
						<input type="submit" name="Apply" value="<?php echo _CONTROL('Apply') ?>"/>
						<input type="submit" name="Rotate" value="<?php echo _CONTROL('Rotate') ?>" onclick="return confirm('<?php echo $confirm ?>')"/>
					</td>
				</tr>
				<input type="hidden" name="Model" value="<?php echo $Conf['Model'] ?>"/>
				<input type="hidden" name="LogFile" value="<?php echo $LogFile ?>"/>
			</form>
			<?php
		}
		
		$Class= $Row++ % 2 == 0 ? 'evenline' : 'oddline';
		?>
		<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
			<tr class="<?php echo $Class ?>">
				<td class="title">
					<?php echo _TITLE('Rotate all log files').':' ?>
				</td>
				<td>
					<input type="submit" name="RotateAll" value="<?php echo _CONTROL('Rotate All') ?>" onclick="return confirm('<?php echo $confirm ?>')"/>
				</td>
				<td class="none" colspan="4">
					<?php
					PrintHelpBox(_HELPBOX('Here you can rotate all log files manually. Note that this action forcefully rotates all log files irrespective of the configuration displayed on this page. Normally this should not be necessary.'));
					?>
				</td>
			</tr>
		</form>
	</table>
	<?php
}

PrintHelpWindow(_HELPWINDOW('Log files are rotated according to two main criteria: size and time, whichever is reached first. When a log file is rotated, it is renamed and compressed, and a new one is opened for writing.
<ul><li>Count is the maximum number of compressed log files to keep.</li><li>Size is the maximum file size. The log file will be rotated when its file size reaches this value. The unit is KB.</li><li>When defines the frequency interval of rotation. This is in hours. For example, 168 means once each week. This web user interface supports only interval format, not time format for this setting.</li></ul>Asterisk (*) means don\'t care.

Note that log files contain very important information about system and network activity. Statistics are generated over these log files.'));
require_once($VIEW_PATH.'footer.php');
?>
