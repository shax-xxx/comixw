<?php
/* $ComixWall: lists.sites.php,v 1.22 2009/11/25 10:22:03 soner Exp $ */

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
 * Site and url lists.
 */

/** Displays exception, grey, and banned list boxes and components for modification.
 *
 * @param[in]	$list	List name, selected submenu.
 */
function PrintFilterConfForms($list)
{
	global $View;

	$lists= array(
		'sites'			=> array(
			'exception'	=> array(
				'title' => _TITLE2('Whitelist'),
				'color' => 'white',
				),
			'gray'		=> array(
				'title' => _TITLE2('Greylist'),
				'color' => '#eee',
				),
			'banned'	=> array(
				'title' => _TITLE2('Blacklist'),
				'color' => 'gray',
				),
			),
		'urls'			=>	array(
			'exception'	=> array(
				'title' => _TITLE2('Whitelist'),
				'color' => 'white',
				),
			'gray'		=> array(
				'title' => _TITLE2('Greylist'),
				'color' => '#eee',
				),
			'banned'	=> array(
				'title' => _TITLE2('Blacklist'),
				'color' => 'gray',
				),
			),
		'virus_sites'	=>	array(
			'exception'	=> array(
				'title' => _TITLE2('Whitelist'),
				'color' => 'white',
				),
			),
		'virus_urls'	=>	array(
			'exception'	=> array(
				'title' => _TITLE2('Whitelist'),
				'color' => 'white',
				),
			),
		);
	?>
	<table style="width: auto;">
	<?php
	foreach ($lists[$list] as $type => $conf) {
		if ($View->Controller($items, 'GetList', $_SESSION[$View->Model]['Group'], $list, $type)) {
			?>
			<tr>
				<td style="background: <?php echo $conf['color'] ?>;">
					<?php echo $conf['title'].':' ?>
					<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
						<input style="display:none;" type="submit" name="Add" value="<?php echo _CONTROL('Add') ?>"/>
						<select name="SitesToDelete[]" multiple style="width: 400px; height: 100px;">
						<?php
						for ($i = 0; $i < count($items); $i++){
							?>
							<option value="<?php echo $items[$i] ?>"><?php echo $items[$i] ?></option>
							<?php
						}
						?>
						</select>
						<input type="submit" name="Delete" value="<?php echo _CONTROL('Delete') ?>"/><br />
						<input type="text" name="SiteToAdd" style="width: 400px;" maxlength="200"/>
						<input type="submit" name="Add" value="<?php echo _CONTROL('Add') ?>"/><br />
						<input type="hidden" name="ListType" value=<?php echo $type ?> />
					</form>
				</td>
			</tr>
			<?php
		}
	}
	?>
	</table>
	<?php
}

if ($_POST['Delete']) {
		foreach ($_POST['SitesToDelete'] as $Site) {
			$View->Controller($Output, 'DelSiteUrl', $_SESSION[$View->Model]['Group'], $Submenu, $_POST['ListType'], $Site);
		}
}
else if ($_POST['Add'] && $_POST['SiteToAdd']) {
	$View->Controller($Output, 'AddSiteUrl', $_SESSION[$View->Model]['Group'], $Submenu, $_POST['ListType'], $_POST['SiteToAdd']);
}

$View->SetSessionFilterGroup();

require_once($VIEW_PATH.'header.php');
		
if ($PrintGroupForm) {
	$View->PrintFilterGroupForm();
}
PrintFilterConfForms($Submenu);

PrintHelpWindow($View->ConfHelpMsg."\n\n"._HELPWINDOW('Group users are allowed unrestricted access to Whitelisted sites or urls. Whitelisted entries are not checked for viruses either. Therefore, Whitelist should be used with caution. In contrast to Whitelist, Greylisted entries are checked for viruses. If possible, Greylist should be preferred over Whitelist. Access to blacklisted entries is denied.'));
require_once($VIEW_PATH.'footer.php');
?>
