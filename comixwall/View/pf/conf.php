<?php
/* $ComixWall: conf.php,v 1.20 2009/11/26 20:52:44 soner Exp $ */

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
 * Basic PF configuration, pf enable/disable and After Hours settings.
 */

require_once('include.php');

/** Prints After Hour modification form.
 *
 * Sample After Hour lines in cron file:
 *
 * 30	19	*	*	1,2,3,4,5,6	/sbin/pfctl -a AfterHours -f /etc/pf.conf.afterhours
 * 0	9	*	*	1,2,3,4,5,6	/sbin/pfctl -a AfterHours -Fr
 * *	*	*	*	7			/sbin/pfctl -a AfterHours -f /etc/pf.conf.afterhours
 */
function PrintAfterHoursForm()
{
	global $View, $IMG_PATH, $Row;

	/// @warning Sunday is also 0, but should not be used in After Hours lines.
	$weekdays= array(
		//0 => _('Sun'),
		1 => _TITLE2('Mon'),
		2 => _TITLE2('Tue'),
		3 => _TITLE2('Wed'),
		4 => _TITLE2('Thu'),
		5 => _TITLE2('Fri'),
		6 => _TITLE2('Sat'),
		7 => _TITLE2('Sun'),
		);

	if ($View->Controller($output, 'GetAfterHours')) {
		list($businessdaysdisabled, $holidaysdisabled, $flushdisabled,
			$startmin, $starthour, $endmin, $endhour,
			$businessdays, $holidays, $flushdays)= unserialize($output[0]);
	}
	
	if ($View->Controller($output, 'GetAfterHoursPfRules')) {
		if (count($output) > 0) {
			$status= _TITLE('enabled');
			$button= 'Disable';
			$imgfile= 'run.png';
		}
		else {
			$status= _TITLE('disabled');
			$button= 'Enable';
			$imgfile= 'stop.png';
		}
	}
	
	$class= $Row++ % 2 == 0 ? 'evenline' : 'oddline';
	?>
	<tr class="<?php echo $class ?>">
		<td class="title">
			<img src="<?php echo $IMG_PATH.$imgfile ?>" name="AfterHours" alt="AfterHours" border="0" align="absmiddle">
			<?php echo _TITLE2('After hours').' '.$status.':' ?>
		</td>
		<td>
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<input type="submit" name="<?php echo $button ?>" value="<?php echo _($button) ?>"/>
			</form>
		</td>
		<td class="none">
			<?php PrintHelpBox(_HELPBOX('This button allows you to <b>manually</b> enable or disable after hours rules. Note that the rules are activated or deactivated immediately, irrespective of the after hours definitions below. For example, you can use this button to shutdown after hours rules in case of an emergency.')) ?>
		</td>
	</tr>
	<?php
	$class= $Row++ % 2 == 0 ? 'evenline' : 'oddline';
	?>
	<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
		<tr class="<?php echo $class ?>">
			<td class="titlegrouptop">
				<?php
				echo _TITLE('Business Days').':';
				?>
			</td>
			<td>
				<table>
					<tr>
						<td class="pfafterhours">
							<?php
							foreach ($weekdays as $num => $day) {
								$checked= in_array($num, $businessdays) ? 'checked' : '';
								$checked= $businessdaysdisabled ? $checked.' disabled' : $checked;
								?>
								<label><input name="BusinessDays[]" type="checkbox" <?php echo $checked ?> value="<?php echo $num ?>"><?php echo _($day) ?></label>
								<?php
							}
							?>
						</td>
						<td class="pfafterhoursbutton">
							<?php
							$button= $businessdaysdisabled ? 'Enable' : 'Disable';
							?>
							<input type="submit" name="<?php echo $button.'BusinessDays' ?>" value="<?php echo _($button) ?>"/>
						</td>
					</tr>
				</table>
			</td>
			<td class="none" rowspan="3">
				<?php PrintHelpBox(_HELPBOX('Definition of after hours can be set here. If you modify any of these definitions, you should click the Apply button.

Note that when you enable Business Days or Holidays, the related rules will become active according to these definitions. If you don\'t want to use the after hours feature of ComixWall, make sure these definitions are disabled.

Business hours configuration is meaningful only for business days.')) ?>
			</td>
		</tr>
		<tr class="<?php echo $class ?>">
			<td class="titlegroupmiddle">
				<?php
				echo _TITLE('Holidays').':';
				?>
			</td>
			<td>
				<table>
					<tr>
						<td class="pfafterhours">
							<?php
							foreach ($weekdays as $num => $day) {
								$checked= in_array($num, $holidays) ? 'checked' : '';
								$checked= $holidaysdisabled ? $checked.' disabled' : $checked;
								?>
								<label><input name="Holidays[]" type="checkbox" <?php echo $checked ?> value="<?php echo $num ?>"><?php echo _($day) ?></label>
								<?php
							}
							?>
						</td>
						<td class="pfafterhoursbutton">
							<?php
							$button= $holidaysdisabled ? 'Enable' : 'Disable';
							?>
							<input type="submit" name="<?php echo $button.'Holidays' ?>" value="<?php echo _($button) ?>"/>
						</td>
					</tr>
				</table>
			</td>
			<td class="none">
			</td>
		</tr>
		<tr class="<?php echo $class ?>">
			<td class="titlegroupbottom">
				<?php
				echo _TITLE('Business Hours').':';
				?>
			</td>
			<td>
				<table>
					<tr>
						<td class="pfafterhours">
							<?php
							$disabled= $businessdaysdisabled ? 'disabled' : '';
							echo _TITLE('From').':';
							?>
							<input type="text" name="StartHour" <?php echo $disabled ?> style="width: 20px;" maxlength="2" value="<?php echo $starthour ?>" />
							:
							<input type="text" name="StartMin" <?php echo $disabled ?> style="width: 20px;" maxlength="2" value="<?php echo $startmin ?>" />
							-
							<?php
							$disabled= $flushdisabled ? 'disabled' : '';
							echo _TITLE('To').':';
							?>
							<input type="text" name="EndHour" <?php echo $disabled ?> style="width: 20px;" maxlength="2" value="<?php echo $endhour ?>" />
							:
							<input type="text" name="EndMin" <?php echo $disabled ?> style="width: 20px;" maxlength="2" value="<?php echo $endmin ?>" />
						</td>
						<td class="pfafterhoursbutton">
							<input type="submit" name="Apply" value="<?php echo _CONTROL('Apply') ?>"/>
						</td>
					</tr>
				</table>
			</td>
			<td class="none">
			</td>
		</tr>
	</form>
	<?php
}

/** Displays privileged IPs form.
 *
 * This list is the !-prefixed IPs in pf.restrictedips.
 */
function PrintPrivIPsForm()
{
	global $View, $Row;
	
	$class= $Row++ % 2 == 0 ? 'evenline' : 'oddline';
	?>
	<tr class="<?php echo $class ?>">
		<td class="title">
			<?php echo _TITLE('Privileged IPs').':' ?>
		</td>
		<td>
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<input style="display:none;" type="submit" name="Add" value="<?php echo _CONTROL('Add') ?>"/>
				<select name="PrivilegedIPs[]" multiple style="width: 200px; height: 100px;">
					<?php
					if ($View->Controller($ips, 'GetAllowedIps')) {
						foreach ($ips as $ip) {
							?>
							<option value="<?php echo $ip ?>"><?php echo $ip ?></option>
							<?php
						}
					}
					?>
				</select>
				<input type="submit" name="Delete" value="<?php echo _CONTROL('Delete') ?>"/><br />
				<input type="text" name="PrivilegedIPToAdd" style="width: 200px;" maxlength="18"/>
				<input type="submit" name="Add" value="<?php echo _CONTROL('Add') ?>"/>
			</form>
		</td>
		<td class="none">
			<?php 
			PrintHelpBox(_HELPBOX('Privileged IPs have access to more services than other IPs, such as instant messaging and file sharing. Normally, the ports of such services should remain blocked for users who do not need them.'));
			?>
		</td>
	</tr>
	<?php
}

/** Displays restricted IPs form.
 *
 * This list is the non-!-prefixed IPs in pf.restrictedips.
 */
function PrintRestrictedIPsForm()
{
	global $View, $Row;
	
	$class= $Row++ % 2 == 0 ? 'evenline' : 'oddline';
	?>
	<tr class="<?php echo $class ?>">
		<td class="title">
			<?php echo _TITLE('Restricted IPs').':' ?>
		</td>
		<td>
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<input style="display:none;" type="submit" name="Add" value="<?php echo _CONTROL('Add') ?>"/>
				<select name="RestrictedIPs[]" multiple style="width: 200px; height: 100px;">
					<?php
					if ($View->Controller($ips, 'GetRestrictedIps')) {
						foreach ($ips as $ip) {
							?>
							<option value="<?php echo $ip ?>"><?php echo $ip ?></option>
							<?php
						}
					}
					?>
				</select>
				<input type="submit" name="Delete" value="<?php echo _CONTROL('Delete') ?>"/><br />
				<input type="text" name="RestrictedIPToAdd" style="width: 200px;" maxlength="18"/>
				<input type="submit" name="Add" value="<?php echo _CONTROL('Add') ?>"/>
			</form>
		</td>
		<td class="none">
			<?php
			PrintHelpBox(_HELPBOX('This is the opposite of Privileged IPs. You can enter IP ranges as well, e.g. 192.168.1.0/24.'));
			?>
		</td>
	</tr>
	<?php
}

if ($_POST) {
	if (array_key_exists('Add', $_POST) || array_key_exists('Delete', $_POST)) {
		if ($_POST['Add']) {
			if ($_POST['PrivilegedIPToAdd']) {
				$View->Controller($Output, 'AddAllowedIp', $_POST['PrivilegedIPToAdd']);
			}
			else if ($_POST['RestrictedIPToAdd']) {
				$View->Controller($Output, 'AddRestrictedIp', $_POST['RestrictedIPToAdd']);
			}
		}
		else if ($_POST['Delete']) {
			if ($_POST['PrivilegedIPs']) {
				foreach ($_POST['PrivilegedIPs'] as $Ip) {
					$View->Controller($Output, 'DelAllowedIp', $Ip);
				}
			}
			if ($_POST['RestrictedIPs']) {
				foreach ($_POST['RestrictedIPs'] as $Ip) {
					$View->Controller($Output, 'DelRestrictedIp', $Ip);
				}
			}
		}
		/// Rules should be reapplied after a change to RestrictedIPs table.
		$View->Controller($Output, 'ApplyPfRules');
		cwwui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'PF rules reloaded: RestrictedIPs table changed');
	}
	else if ($_POST['Enable']) {
		$View->Controller($Output, 'EnableAfterHours');
		cwwui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'AfterHours enabled');
	}
	else if ($_POST['Disable']) {
		$View->Controller($Output, 'DisableAfterHours');
		cwwui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'AfterHours disabled');
	}
	else if ($_POST['EnableBusinessDays']) {
		$View->Controller($Output, 'EnableAfterHoursBusinessDays');
	}
	else if ($_POST['DisableBusinessDays']) {
		$View->Controller($Output, 'DisableAfterHoursBusinessDays');
	}
	else if ($_POST['EnableHolidays']) {
		$View->Controller($Output, 'EnableAfterHoursHolidays');
	}
	else if ($_POST['DisableHolidays']) {
		$View->Controller($Output, 'DisableAfterHoursHolidays');
	}
	else if ($_POST['Apply']) {
		if ($_POST['BusinessDays']) {
			$BusinessDaysList= implode(',', $_POST['BusinessDays']);
		}
		else {
			/// Disable business hours line if no business hours selected.
			$View->Controller($Output, 'DisableAfterHoursBusinessDays');
		}

		if ($_POST['Holidays']) {
			$HolidaysList= implode(',', $_POST['Holidays']);
		}
		else {
			/// Disable holidays line if no holidays selected.
			$View->Controller($Output, 'DisableAfterHoursHolidays');
		}
		$View->Controller($Output, 'ApplyAfterHours', $_POST['StartHour'], $_POST['StartMin'], $_POST['EndHour'], $_POST['EndMin'], $BusinessDaysList, $HolidaysList);
		cwwui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'AfterHours changed');
	}
}

require_once($VIEW_PATH.'header.php');
?>
<table id="nvp">
	<?php
	$Row= 1;
	PrintAfterHoursForm();
	PrintPrivIPsForm();
	PrintRestrictedIPsForm();
	?>
</table>
<?php
PrintHelpWindow(_HELPWINDOW('This page provides basic Packet Filter configuration. Packet filter rules can be modified under the Rules tab.

OpenBSD/pf is a powerful and flexible stateful packet filter. Among many advanced features of pf are:<ul><li>Stateful inspection</li><li>Network Address Translation (NAT)</li><li>Packet normalization</li><li>Spoof protection</li><li>Packet queueing and bandwidth management</li><li>Traffic shaping</li><li>Policy routing</li><li>Load balancing, and much more</li></ul>You can add an IP range to the restricted IPs list, and enter individual IP addresses or subnets to privileged IPs list, to define exceptions over the restricted IP range. Or visa-versa.'));
require_once($VIEW_PATH.'footer.php');
?>
