<?php
/* $ComixWall: leftmenu.php,v 1.18 2009/11/21 21:55:59 soner Exp $ */

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
 * Prints left menu (main menu), datetime, and sensor values.
 */

require_once('lib/vars.php');

$DirHandle= opendir($VIEW_PATH);
while (FALSE !== ($DirName= readdir($DirHandle))) {
	if (is_dir("$VIEW_PATH/$DirName")) {
		$ModuleFiles[]= $DirName;
	}
}
closedir($DirHandle);

$ServiceStatus= '';
if ($View->Controller($Output, 'GetServiceStatus')) {
	$ServiceStatus= implode(',', $Output);
}
?>
<td id="mainmenuframe" rowspan=2>
	<table class="fixed">
		<tr>
			<td id="mainmenu">
				<table>
					<tr id="leftmenutext">
						<td class="center">
							<?php echo date('d.m.Y H:i') ?>
						</td>
					</tr>
				</table>

				<ul>
				<?php
				foreach ($CW_MODULES as $Module => $SubmoduleList) {
					if (in_array($Module, $ModuleFiles)) {
						foreach ($SubmoduleList as $ModuleName => $ModuleConf) {
							if (in_array($_SESSION['USER'], $ModuleConf['Perms'])) {
								$Class= '';
								if (strpos($_SERVER['PHP_SELF'], "/$Module/") !== FALSE) {
									$Class= 'class="active" ';
								}
								
								if (in_array($Module, $ModelsToStat)) {
									if (preg_match("/$Module=R/", $ServiceStatus)) {
										$Image= 'run.png';
										$Name= 'R';
									}
									else {
										$Image= 'stop.png';
										$Name= 'S';
									}
								}
								else {
									$Image= 'transparent.png';
									$Name= ' ';
								}
								?>
								<li <?php echo $Class ?>>
									<a href="<?php echo "/$Module/index.php" ?>">
										<img src="<?php echo $IMG_PATH.$Image ?>" name="<?php echo $Name ?>" alt="<?php echo $Name ?>" align="absmiddle"><?php echo _($ModuleConf['Name']) ?>
									</a>
								</li>
								<?php
							}
						}
					}
				}
				?>
				</ul>

				<?php
				if ($View->Controller($SensorReadings, 'GetSysCtl', 'hw.sensors')) {
					$Sensors= array();
					foreach ($SensorReadings as $Sensor) {
						// hw.sensors.lm1.temp1=38.50 degC (zone temperature)
						if (preg_match('/^.*=\s*([\d.]+)\s*((degC|RPM).*)$/', $Sensor, $Match)) {
							$Sensors[]= array(
								'Value' => $Match[1],
								'Unit' 	=> $Match[2],
								);
						}
					}
					// Print only if sensor values are available
					if (count($Sensors) > 0) {
						?>
						<div id="leftmenutext">
							<table align="right">
								<tr>
									<td colspan="2">
										<?php echo _MENU('Sensors').':' ?>
									</td>
								</tr>
								<?php
								foreach ($Sensors as $Sensor) {
									?>
									<tr>
										<td class="value">
											<?php echo $Sensor['Value'] ?>
										</td>
										<td class="unit">
											<?php echo wordwrap($Sensor['Unit'], 12, '<br />', TRUE) ?>
										</td>
									</tr>
									<?php
								}
								?>
							</table>
						</div>
						<?php
					}
				}
				?>
			</td>
		</tr>
	</table>
</td>
<?php
CheckPageActivation($PageActivated);
?>
