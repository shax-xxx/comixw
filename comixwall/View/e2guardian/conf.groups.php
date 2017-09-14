<?php
/* $ComixWall: conf.groups.php,v 1.28 2009/11/25 10:21:20 soner Exp $ */

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
 * Main group configuration.
 */

/** Displays DG group IPs in a list with Delete and Add forms.
 *
 * @todo How to get browser's font sizes?
 */
function PrintFilterGroupIPsForms()
{
	global $View;
	
	if ($View->Controller($output, 'GetGroupCount')) {
		$count= $output[0];
		?>
		<tr>
			<td class="none" colspan="2">
				<?php
				echo _TITLE2('Number of filter groups').':'.$count;
				?>
			</td>
		</tr>
		<?php
		for ($group= 1; $group <= $count; $group++) {
			if ($group % 2 == 1) {
				?>
				<tr id="groupbox">
				<?php
			}
			?>
			<td class="group">
				<?php
				echo _TITLE2('Group').' '.$group.':';
				?>
				<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
					<input style="display:none;" type="submit" name="Add" value="<?php echo _CONTROL('Add') ?>"/>
					<select name="IPs[]" multiple style="width: 250px; height: 100px;">
					<?php
					if ($View->Controller($ips, 'GetGroupIpList', $group)) {
						for ($i = 0; $i < count($ips); $i++) {
							?>
							<option value="<?php echo $ips[$i] ?>"><?php echo $ips[$i] ?></option>
							<?php
						}
					}
					?>
					</select>
					<input type="submit" name="Delete" value="<?php echo _CONTROL('Delete') ?>"/><br />
					<input type="text" name="IPToAdd" style="width: 250px;" maxlength="31"/>
					<input type="submit" name="Add" value="<?php echo _CONTROL('Add') ?>"/>
					<input type="hidden" name="GroupToChange" value=<?php echo $group ?> />
				</form>
			</td>
			<?php
			if ($group % 2 == 0) {
				?>
				</tr>
				<?php
			}
			?>
			<?php
		}
		if ($count % 2 == 1) {
			?>
			</tr>
			<?php
		}
	}
}

/** Displays DG auth IPs in a list with Delete and Add forms.
 */
function PrintFilterAuthIPForms()
{
	global $View, $Row;
	
	$conf= array(
		'exceptionlist'	=>	array(
			'title'		=> _TITLE2('Whitelist'),
			'color'		=> 'white',
			),
		'bannedlist' 	=>	array(
			'title'		=> _TITLE2('Blacklist'),
			'color'		=> 'gray',
			),
		);
	?>
	<tr id="groupbox">
	<?php
	foreach ($conf as $name => $listconf) {
		?>
		<td style="background-color: <?php echo $listconf['color'] ?>;">
			<?php echo $listconf['title'].':' ?>
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<input style="display:none;" type="submit" name="AddAuthIp" value="<?php echo _CONTROL('Add') ?>"/>
				<select name="AuthIPs[]" multiple style="width: 250px; height: 100px;">
				<?php
				if ($View->Controller($ips, 'GetAuthIpList', $name)) {
					for ($i = 0; $i < count($ips); $i++){
					?>
					<option value="<?php echo $ips[$i] ?>"><?php echo $ips[$i] ?></option>
					<?php
					}
				}
				?>
				</select>
				<input type="submit" name="DeleteAuthIp" value="<?php echo _CONTROL('Delete') ?>"/><br />
				<input type="text" name="AuthIPToAdd" style="width: 250px;" maxlength="31"/>
				<input type="submit" name="AddAuthIp" value="<?php echo _CONTROL('Add') ?>"/>
				<input type="hidden" name="List" value=<?php echo $name ?> />
			</form>
		</td>
		<?php
	}
	?>
	</tr>
	<?php
}

if ($_POST['Create']) {
	/// @attention Session group should be set before this point, always
	$View->Controller($Output, 'CreateNewGroup', $_SESSION[$View->Model]['Group']);
}
else if ($_POST['Delete']) {
	foreach ($_POST['IPs'] as $Ip) {
		$View->Controller($Output, 'DelIpFilterGrp', $Ip);
	}
}
else if ($_POST['Add'] && $_POST['IPToAdd']) {
	$View->Controller($Output, 'SetIpFilterGrp', $_POST['GroupToChange'], $_POST['IPToAdd']);
}
else if ($_POST['DeleteAuthIp']) {
	foreach ($_POST['AuthIPs'] as $Ip) {
		$View->Controller($Output, 'DelIp', $_POST['List'], $Ip);
	}
}
else if ($_POST['AddAuthIp'] && $_POST['AuthIPToAdd']) {
	$View->Controller($Output, 'AddIp', $_POST['List'], $_POST['AuthIPToAdd']);
}

$View->SetSessionFilterGroup();

require_once($VIEW_PATH.'header.php');

$View->PrintFilterGroupForm();
?>
<table id="nvp">
	<tr class="oddline">
		<td class="title">
			<?php echo _TITLE2('Create filter group').':' ?>
		</td>
		<td>
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<input type="submit" name="Create" value="<?php echo _CONTROL('Create') ?>" onclick="return confirm('<?php echo _NOTICE('Are you sure you want to create a new group?') ?>')"/>
			</form>
		</td>
		<td class="none">
			<?php
			PrintHelpBox(_HELPBOX2('This button creates a new web filter group identical to the active group.'));
			?>
		</td>
	</tr>
</table>
<table style="width: auto;">
	<?php
	PrintFilterGroupIPsForms();
	PrintFilterAuthIPForms();
	?>
</table>
<?php
PrintHelpWindow(_HELPWINDOW("Groups are used to grant or revoke certain privileges to IPs, such as which web sites or file types to allow or deny. ComixWall comes with 4 predefined groups by default. However, the web filter is capable of having any number of IP groups.

Predefined groups and their basic features are as follows:
Filter Group 1:
<ul class='nomargin'><li class='nomargin'>All IPs not defined in other groups are considered in this group by default,</li><li class='nomargin'>Group members are allowed to browse the web according to group configuration,</li><li class='nomargin'>If not specifically disabled, all files are scanned for viruses.</li></ul>
Filter Group 2:
<ul class='nomargin'><li class='nomargin'>Members of this group have unrestricted access to the web,</li><li class='nomargin'>File downloads are not scanned for viruses.</li></ul>
Filter Group 3:
<ul class='nomargin'><li class='nomargin'>Members of this group are completely denied of any web access.</li></ul>
Filter Group 4:
<ul class='nomargin'><li class='nomargin'>Members of this group have more privileges than Group 1 members, because they can download files without any restrictions,</li><li class='nomargin'>Rest of the configuration for this group is similar to Group 1.</li></ul>
You can add IP or network addresses to groups. If there is another group with the same entry, it is deleted from that group first, i.e. an IP address can be a member of only one group.
 
The format of ip ranges is Start IP address-End IP address, such as 192.168.1.1-192.168.1.253. And that of subnets is IP address/Subnet mask, such as 192.168.1.0/255.255.255.0.

White and black lists give you other methods of allowing or denying certain IP addresses access to the web without needing to add them to any group.

This web interface does not provide an option to delete filter groups. But you can always delete groups on the command line."));
require_once($VIEW_PATH.'footer.php');
?>
