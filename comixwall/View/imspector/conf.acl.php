<?php
/* $ComixWall: conf.acl.php,v 1.5 2009/11/25 10:22:47 soner Exp $ */

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

/** Displays Access Control List.
 */
function PrintAclForm()
{
	global $View, $Class;
	?>
	<tr class="<?php echo $Class ?>">
		<td class="title">
			<?php echo _TITLE2('ACL').':' ?>
		</td>
		<td>
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<input style="display:none;" type="submit" name="Add" value="<?php echo _CONTROL('Add') ?>"/>
				<select name="SelectedAcls[]" multiple style="width: 300px; height: 200px;">
					<?php
					if ($View->Controller($output, 'GetAcl')) {
						foreach ($output as $acl) {
							if ($_POST['SelectedAcls'] && in_array($acl, $_POST['SelectedAcls'])) {
								$selected= ' selected';
							}
							else {
								$selected= '';
							}
							?>
							<option value="<?php echo $acl ?>" title="<?php echo $acl ?>"<?php echo $selected ?>><?php echo $acl ?></option>
							<?php
						}
					}
					?>
				</select>
				<br />
				<input type="submit" name="Delete" value="<?php echo _CONTROL('Delete') ?>"/>
				<input type="submit" name="MoveUp" value="<?php echo _CONTROL('Move Up') ?>"/>
				<input type="submit" name="MoveDown" value="<?php echo _CONTROL('Move Down') ?>"/><br />
				<br />
				<select name="Action" style="width: 100px;">
					<option value="allow">allow</option>
					<option value="deny">deny</option>
				</select>
				<input type="text" name="List" style="width: 200px;" maxlength="200"/>
				<input type="submit" name="Add" value="<?php echo _CONTROL('Add') ?>"/><br />
			</form>
		</td>
		<td class="none">
			<?php
			PrintHelpBox(_HELPBOX2('List format is:
allow|deny localid|all [groupchat|remoteid1 ... remoteidN]

Order of the access rules is important. For example, if deny all is the first rule, all access is effectively blocked, so make sure the deny all rule, if any, is at the bottom.'));
			?>
		</td>
	</tr>
	<?php
}

if ($_POST) {
	if ($_POST['Delete']) {
		foreach ($_POST['SelectedAcls'] as $Acl) {
			$View->Controller($Output, 'DelAcl', $Acl);
		}
	}
	else if ($_POST['MoveUp']) {
		foreach ($_POST['SelectedAcls'] as $Acl) {
			$View->Controller($Output, 'MoveAclUp', $Acl);
		}
	}
	else if ($_POST['MoveDown']) {
		$SelectedAcls= $_POST['SelectedAcls'];
		for ($i= count($SelectedAcls) - 1; $i >= 0; $i--) {
			$View->Controller($Output, 'MoveAclDown', $SelectedAcls[$i]);
		}
	}
	else if ($_POST['Add'] && $_POST['List']) {
		$View->Controller($Output, 'AddAcl', $_POST['Action'].' '.$_POST['List']);
	}
}

$CustomFunc= 'PrintAclForm';
require_once('../lib/conf.php');
?>
