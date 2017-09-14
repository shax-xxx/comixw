<?php
/* $ComixWall: stats.hourly.php,v 1.20 2009/11/21 21:55:58 soner Exp $ */

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
 * All hourly statistics pages include this file.
 * Statistics configuration is in $Modules.
 */

require_once('../lib/vars.php');

$View->UploadLogFile();

$LogFile= GetLogFile();

$ApplyDefaults= TRUE;

// Will apply defaults if log file changed
if ($LogFile === $_SESSION[$View->Model][$Submenu]['PrevLogFile']) {
	if ($_POST) {
		if ($_POST['Apply']) {
			$DateArray['Month']= $_POST['Month'];
			$DateArray['Day']= $_POST['Day'];
			$DateArray['Hour']= $_POST['Hour'];
			$GraphType= $_POST['GraphType'];
			
			$ApplyDefaults= FALSE;
		}
	}
	// Use isset here, Month and Day may be empty string
	else if (isset($_SESSION[$View->Model][$Submenu]['Month'],
		$_SESSION[$View->Model][$Submenu]['Day'],
		$_SESSION[$View->Model][$Submenu]['Hour'],
		$_SESSION[$View->Model][$Submenu]['GraphType'])) {
		
		$DateArray['Month']= $_SESSION[$View->Model][$Submenu]['Month'];
		$DateArray['Day']= $_SESSION[$View->Model][$Submenu]['Day'];
		$DateArray['Hour']= $_SESSION[$View->Model][$Submenu]['Hour'];
		$GraphType= $_SESSION[$View->Model][$Submenu]['GraphType'];
		
		$ApplyDefaults= FALSE;
	}
}
// Set the previous log file now, due to above if condition
$_SESSION[$View->Model][$Submenu]['PrevLogFile']= $LogFile;

if ($ApplyDefaults) {
	$View->Controller($Output, 'GetDefaultLogFile');
	$file= $Output[0];
	if (basename($LogFile) == basename($file)) {
		$DateArray['Month']= date('m');
		$DateArray['Day']= date('d');
		$DateArray['Hour']= date('H');
	}
	else {
		$View->Controller($Output, 'GetLogStartDate', $LogFile);
		$LogsStartDate= $Output[0];

		if (preg_match('/^(.*)\s(\d+):\d+:\d+$/', $LogsStartDate, $match)) {
			$Date= $match[1];
			$Hour= $match[2];
		}
		else {
			$Hour= 12;
		}
		$View->FormatDateArray($Date, $DateArray);
		$DateArray['Hour']= $Hour;
	}
	$GraphType= 'Horizontal';
}

$_SESSION[$View->Model][$Submenu]['Month']= $DateArray['Month'];
$_SESSION[$View->Model][$Submenu]['Day']= $DateArray['Day'];
$_SESSION[$View->Model][$Submenu]['Hour']= $DateArray['Hour'];
$_SESSION[$View->Model][$Submenu]['GraphType']= $GraphType;

$Hour= $DateArray['Hour'];
$Date= $View->FormatDate($DateArray);

$StatsConf= $Modules[$View->Model]['Stats'];

$View->Controller($Output, 'GetStats', $LogFile, serialize($DateArray), 'COLLECT');
$Stats= unserialize($Output[0]);
$DateStats= $Stats['Date'];

require_once($VIEW_PATH.'header.php');

PrintLogFileChooser($LogFile);
?>
<table id="nvp">
	<tr class="evenline">
		<td colspan="2">
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<?php echo _TITLE('Month').':' ?>
				<select name="Month" style="width: 50px;">
					<?php
					for ($m= 1; $m <= 12; $m++) {
						$m= sprintf('%02d', $m);
						$selected= ($DateArray['Month'] == $m) ? 'selected' : '';
						?>
						<option <?php echo $selected ?> value="<?php echo $m ?>"><?php echo $m ?></option>
						<?php
					}
					?>
				</select>
				<?php echo _TITLE('Day').':' ?>
				<select name="Day" style="width: 50px;">
					<?php
					for ($d= 1; $d <= 31; $d++) {
						$d= sprintf('%02d', $d);
						$selected= ($DateArray['Day'] == $d) ? 'selected' : '';
						?>
						<option <?php echo $selected ?> value="<?php echo $d ?>"><?php echo $d ?></option>
						<?php
					}
					?>
				</select>
				<?php echo _TITLE('Hour').':' ?>
				<select name="Hour">
					<?php
					for ($h= 0; $h < 24; $h++) {
						$h= sprintf('%02d', $h);
						$selected= ($Hour == $h) ? 'selected' : '';
						?>
						<option <?php echo $selected ?> value="<?php echo $h ?>"><?php echo $h ?></option>
						<?php
					}
					?>
				</select>
				<select name="GraphType">
					<option <?php echo ($GraphType == 'Vertical') ? 'selected' : '' ?> value="<?php echo 'Vertical' ?>"><?php echo _CONTROL('Vertical') ?></option>
					<option <?php echo ($GraphType == 'Horizontal') ? 'selected' : '' ?> value="<?php echo 'Horizontal' ?>"><?php echo _CONTROL('Horizontal') ?></option>
				</select>
				<input type="submit" name="Apply" value="<?php echo _CONTROL('Apply') ?>"/>
				<input type="submit" name="Defaults" value="<?php echo _CONTROL('Defaults') ?>"/>
			</form>
		</td>
	</tr>
</table>
<?php
foreach ($StatsConf as $Name => $Conf) {
	if (isset($Conf['Color'])) {
		PrintMinutesGraphNVPSet($DateStats[$Date]['Hours'][$Hour], $Name, $Conf, $GraphType);
	}
}

if (isset($StatsConf['Total']['Counters'])) {
	foreach ($StatsConf['Total']['Counters'] as $Name => $Conf) {
		PrintMinutesGraphNVPSet($DateStats[$Date]['Hours'][$Hour], $Name, $Conf, $GraphType);
	}
}

PrintHelpWindow(_($StatsWarningMsg), 'auto', 'WARN');
PrintHelpWindow(_($StatsHelpMsg));
require_once($VIEW_PATH.'footer.php');
?>
