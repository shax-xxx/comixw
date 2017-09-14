<?php
/* $ComixWall: stats.general.php,v 1.40 2009/11/21 21:55:58 soner Exp $ */

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
 * All general statistics pages include this file.
 * Statistics configuration is in $Modules.
 */

require_once('../lib/vars.php');

$View->UploadLogFile();

$LogFile= GetLogFile();

$DateArray= array('Month' => '', 'Day' => '');

$GraphStyle= 'Daily';
$GraphType= 'Horizontal';

if ($_POST) {
	$GraphStyle= $_POST['GraphStyle'];

	if ($_POST['GraphType']) {
		$GraphType= $_POST['GraphType'];
	}
	else {
		if ($_SESSION[$View->Model][$Submenu]['GraphType']) {
			$GraphType= $_SESSION[$View->Model][$Submenu]['GraphType'];
		}
	}
}
else if ($_SESSION[$View->Model][$Submenu]['GraphStyle']) {
	/// @attention Daily style does not set GraphType, do not check it in if condition
	$GraphStyle= $_SESSION[$View->Model][$Submenu]['GraphStyle'];
	$GraphType= $_SESSION[$View->Model][$Submenu]['GraphType'];
}

if ($GraphStyle == 'Daily') {
	$GraphType= 'Vertical';
}

$_SESSION[$View->Model][$Submenu]['GraphStyle']= $GraphStyle;
if ($GraphStyle == 'Hourly') {
	$_SESSION[$View->Model][$Submenu]['GraphType']= $GraphType;
}

$StatsConf= $Modules[$View->Model]['Stats'];

$View->Controller($Output, 'GetAllStats', $LogFile, $GraphStyle == 'Hourly' ? 'COLLECT' : '');
$AllStats= unserialize($Output[0]);
$Stats= unserialize($AllStats['stats']);
$BriefStats= unserialize($AllStats['briefstats']);
$DateStats= $Stats['Date'];

require_once($VIEW_PATH.'header.php');

PrintLogFileChooser($LogFile);
?>
<table>
	<tr>
		<td class="top">
			<?php
			$View->PrintStats($LogFile);

			if (isset($StatsConf['Total']['BriefStats'])) {
				foreach ($StatsConf['Total']['BriefStats'] as $Field => $Name) {
					PrintNVPs($BriefStats[$Field], _($Name), 50);
				}
			}
			?>
		</td>
		<td class="top">
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<select name="GraphStyle">
					<option <?php echo ($GraphStyle == 'Hourly') ? 'selected' : '' ?> value="<?php echo 'Hourly' ?>"><?php echo _CONTROL('Hourly') ?></option>
					<option <?php echo ($GraphStyle == 'Daily') ? 'selected' : '' ?> value="<?php echo 'Daily' ?>"><?php echo _CONTROL('Daily') ?></option>
				</select>
				<?php
				if ($GraphStyle == 'Hourly') {
				?>
				<select name="GraphType">
					<option <?php echo ($GraphType == 'Vertical') ? 'selected' : '' ?> value="<?php echo 'Vertical' ?>"><?php echo _CONTROL('Vertical') ?></option>
					<option <?php echo ($GraphType == 'Horizontal') ? 'selected' : '' ?> value="<?php echo 'Horizontal' ?>"><?php echo _CONTROL('Horizontal') ?></option>
				</select>
				<?php
				}
				?>
				<input type="submit" name="Apply" value="<?php echo _CONTROL('Apply') ?>"/>
			</form>
			<?php
			foreach ($StatsConf as $Name => $Conf) {
				if (isset($Conf['Color'])) {
					PrintGraphNVPSet($DateStats, $DateArray, $Name, $Conf, $GraphType, $GraphStyle);
				}
			}

			if (isset($StatsConf['Total']['Counters'])) {
				foreach ($StatsConf['Total']['Counters'] as $Name => $Conf) {
					PrintGraphNVPSet($DateStats, $DateArray, $Name, $Conf, $GraphType, $GraphStyle);
				}
			}
			?>
		</td>
	</tr>
</table>
<?php
PrintHelpWindow(_($StatsWarningMsg), 'auto', 'WARN');
PrintHelpWindow(_($StatsHelpMsg));
require_once($VIEW_PATH.'footer.php');
?>
