<?php
/* $ComixWall: stats.live.php,v 1.27 2009/11/24 19:27:50 soner Exp $ */

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
 * All live statistics pages include this file.
 * Statistics configuration is in $Modules.
 */

require_once('../lib/vars.php');

$Reload= TRUE;
SetRefreshInterval();

$View->Controller($Output, 'GetDefaultLogFile');
$LogFile= $Output[0];

$DateArray['Month']= date('m');
$DateArray['Day']= date('d');
$DateArray['Hour']= date('H');

$GraphType= 'Horizontal';

if ($_POST) {
	$GraphType= $_POST['GraphType'];
	$_SESSION[$View->Model]['GraphType']= $GraphType;
}
else if ($_SESSION[$View->Model]['GraphType']) {
	$GraphType= $_SESSION[$View->Model]['GraphType'];
}

$Hour= $DateArray['Hour'];
$Date= $View->FormatDate($DateArray);

$StatsConf= $Modules[$View->Model]['Stats'];

$View->Controller($Output, 'GetStats', $LogFile, serialize($DateArray), 'COLLECT');
$Stats= unserialize($Output[0]);
$DateStats= $Stats['Date'];

require_once($VIEW_PATH.'header.php');
?>
<table>
	<tr>
		<td>
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<?php echo _TITLE('Refresh interval').':' ?>
				<input type="text" name="RefreshInterval" style="width: 20px;" maxlength="2" value="<?php echo $_SESSION[$View->Model]['ReloadRate'] ?>" />
				<?php echo _TITLE('secs') ?>
				<select name="GraphType">
					<option <?php echo ($GraphType == 'Vertical') ? 'selected' : '' ?> value="<?php echo 'Vertical' ?>"><?php echo _CONTROL('Vertical') ?></option>
					<option <?php echo ($GraphType == 'Horizontal') ? 'selected' : '' ?> value="<?php echo 'Horizontal' ?>"><?php echo _CONTROL('Horizontal') ?></option>
				</select>
				<input type="submit" name="Apply" value="<?php echo _CONTROL('Apply') ?>"/>
			</form>
		</td>
		<td>
			<strong><?php echo _TITLE('Date').': '.$Date.', '.$Hour.':'.date('i') ?></strong>
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

require_once($VIEW_PATH.'footer.php');
?>
