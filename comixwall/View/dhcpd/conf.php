<?php
/* $ComixWall: conf.php,v 1.19 2009/11/25 10:22:21 soner Exp $ */

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
 * Dynamic configuration.
 */

require_once('include.php');

/** Displays a form to change the interface(s) DHCP server distributes IPs from.
 */
function PrintDHCPInterfaceForm()
{
	global $View, $Row;
	
	$class= $Row++ % 2 == 0 ? 'evenline' : 'oddline';
	?>
	<tr class="<?php echo $class ?>">
		<td class="title">
			<?php echo _TITLE2('Interfaces').':' ?>
		</td>
		<td>
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<input style="display:none;" type="submit" name="Add" value="<?php echo _CONTROL('Add') ?>"/>
				<select name="InterfacesToDelete[]" multiple style="width: 100px; height: 50px;">
				<?php
				if ($View->Controller($ifs, 'GetIfs')) {
					foreach ($ifs as $if) {
						?>
						<option value="<?php echo $if ?>"><?php echo $if ?></option>
						<?php
					}
				}
				?>
				</select>
				<input type="submit" name="Delete" value="<?php echo _CONTROL('Delete') ?>"/>
				<br />
				<select name="Interfaces[]" multiple style="width: 100px; height: 50px;">
				<?php
				if ($View->Controller($ifs, 'GetPhyIfs')) {
					foreach ($ifs as $if) {
						?>
						<option value="<?php echo $if ?>"><?php echo $if ?></option>
						<?php
					}
				}
				?>
				</select>
				<input type="submit" name="Add" value="<?php echo _CONTROL('Add') ?>"/>
			</form>
		</td>
		<td class="none">
			<?php PrintHelpBox(_HELPBOX2('This is the list of interfaces DHCP server listens and distributes IP leases. Here you should add the interface(s) to start the DHCP server for.')) ?>
		</td>
	</tr>
	<?php
}

/** Displays a form to change DHCP server dynamic options.
 *
 * All the options, except range, use this function.
 */
function PrintDHCPOptionForm($option)
{
	global $View, $Row;
	
	$helpmsgs= array(
		'domain-name-servers'	=>	_HELPBOX2('This is the DNS server internal clients use. The default is the internal IP address of the system.'),
		'routers'				=>	_HELPBOX2('This is the gateway internal clients use to reach the external network. The default is the internal IP address of the system.'),
		);

	$titles= array(
		'domain-name-servers'	=>	_TITLE2('Name server'),
		'routers'				=>	_TITLE2('Gateway'),
		'subnet-mask'			=>	_TITLE2('Subnet mask'),
		'broadcast-address'		=>	_TITLE2('Broadcast address'),
		);

	$class= $Row++ % 2 == 0 ? 'evenline' : 'oddline';
	?>
	<tr class="<?php echo $class ?>">
		<td class="title">
			<?php echo _($titles[$option]).':';?>
		</td>
		<td>
			<?php
			if ($View->Controller($value, 'GetOption', $option)) {
				?>
				<input type="text" name="<?php echo $option ?>" value="<?php echo $value[0] ?>" style="width: 100px;" maxlength="15"/>
				<?php
			}
			?>
		</td>
		<td class="none">
			<?php
			if (isset($helpmsgs[$option])) {
				PrintHelpBox(_($helpmsgs[$option]));
			}
			?>
		</td>
	</tr>
	<?php
}

/** Displays a form to change DHCP server IP range.
 */
function PrintDHCPRangeOptionForm()
{
	global $View, $Row;
	
	$class= $Row++ % 2 == 0 ? 'evenline' : 'oddline';
	?>
	<tr class="<?php echo $class ?>">
		<td class="title">
			<?php echo _TITLE2('IP range').':' ?>
		</td>
		<td>
			<?php
			if ($View->Controller($value, 'GetRange')) {
				?>
				<input type="text" name="lower_range" value="<?php echo $value[0] ?>" style="width: 100px;" maxlength="15"/>
				-
				<input type="text" name="upper_range" value="<?php echo $value[1] ?>" style="width: 100px;" maxlength="15"/>
				<input type="submit" name="Apply" value="<?php echo _CONTROL('Apply') ?>"/>
				<?php
			}
			?>
		</td>
		<td class="none">
			<?php PrintHelpBox(_HELPBOX2('You should take into account that there may be computers with static IP addresses or using BOOTP protocol, and choose a range accordingly.')) ?>
		</td>
	</tr>
	<?php
}

if ($_POST['Delete']) {
	foreach ($_POST['InterfacesToDelete'] as $If) {
		$View->Controller($Output, 'DelIf', $If);
	}
}
else if ($_POST['Add']) {
	foreach ($_POST['Interfaces'] as $If) {
		$View->Controller($Output, 'AddIf', $If);
	}
}
else if ($_POST['Apply']) {
	$View->Controller($Output, 'SetOptions', $_POST['domain-name-servers'], $_POST['routers'], $_POST['subnet-mask'],
		$_POST['broadcast-address'], $_POST['lower_range'], $_POST['upper_range']);
}

require_once($VIEW_PATH.'header.php');
?>
<table id="nvp">
	<?php
	$Row= 1;
	PrintDHCPInterfaceForm();
	?>
	<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
		<?php
		PrintDHCPOptionForm('domain-name-servers');
		PrintDHCPOptionForm('routers');
		PrintDHCPOptionForm('subnet-mask');
		PrintDHCPOptionForm('broadcast-address');
		PrintDHCPRangeOptionForm();
		?>
	</form>
</table>
<?php
PrintHelpWindow(_HELPWINDOW('DHCP dynamic configuration is used by clients in the internal network to obtain an IP address and network settings.'));
require_once($VIEW_PATH.'footer.php');
?>
