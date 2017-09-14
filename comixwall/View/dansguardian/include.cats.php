<?php
/* $ComixWall: include.cats.php,v 1.25 2009/11/26 20:51:49 soner Exp $ */

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
 * Category pages.
 */

/** Displays lists of categories and forms with components to change.
 *
 * All category lists are handled by this function.
 */
function PrintFilterCatForms($list)
{
	global $View;

	$listconf= array(
		'exception'	=> array(
			'title'	=> _TITLE2('Whitelist'),
			'color'	=> 'white',
			),
		'gray'	=> array(
			'title'	=> _TITLE2('Greylist'),
			'color'	=> '#eee',
			),
		'banned'	=> array(
			'title'	=> _TITLE2('Blacklist'),
			'color'	=> 'gray',
			),
		'weighted'	=> array(
			'title'	=> _TITLE2('Weighted phrase list'),
			'color'	=> '#f4f3c2',
			),
		);

	$group= $_SESSION[$View->Model]['Group'];

	$catlists= array(
		'sites'		=> array('exception', 'gray', 'banned'),
		'urls'		=> array('exception', 'gray', 'banned'),
		'phrases'	=> array('exception', 'banned', 'weighted'),
		);
	
	foreach ($catlists[$list] as $type) {
		if ($View->Controller($cats, 'GetEnabledCats', $group, $list, $type)) {
			echo $listconf[$type]['title'].':';
			?>
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<table style="width: auto; background: <?php echo $listconf[$type]['color'] ?>;">
					<tr>
						<td>
							<?php echo _TITLE2('Enabled') ?>
							<br />
							<select name="Cats[]" multiple style="width: 200px; height: 100px;">
								<?php
								for ($i = 0; $i < count($cats); $i++) {
									if (preg_match('/([^\/]*)\/([^_]*)_(.*)/', $cats[$i], $match)) {
										$title= $match[1].' ('.$match[3].')';
									}
									else {
										$title= dirname($cats[$i]);
									}
									?>
									<option value="<?php echo $cats[$i] ?>"><?php echo $title ?></option>
									<?php
								}
								?>
							</select>
							<input type="submit" name="Disable" value="<?php echo _CONTROL('Disable') ?>"/>
						</td>
						<td>
							<?php
							if ($View->Controller($cats, 'GetDisabledCats', $group, $list, $type)) {
								echo _TITLE2('Disabled');
								?>
								<br />
								<select name="Cats[]" multiple style="width: 200px; height: 100px;">
									<?php
									for ($i = 0; $i < count($cats); $i++) {
										if (preg_match('/([^\/]*)\/([^_]*)_(.*)/', $cats[$i], $match)) {
											$title= $match[1].' ('.$match[3].')';
										}
										else {
											$title= dirname($cats[$i]);
										}
										?>
										<option value="<?php echo $cats[$i] ?>"><?php echo $title ?></option>
										<?php
									}
									?>
								</select>
								<input type="submit" name="Enable" value="<?php echo _CONTROL('Enable') ?>"/>
								<?php
							}
							?>
						</td>
					</tr>
				</table>
				<input type="hidden" name="ListType" value=<?php echo $type ?> />
			</form>
			<?php
		}
	}
}

if ($_POST['Cats']) {
	foreach ($_POST['Cats'] as $CatSubcat) {
		$CatArray= explode('/', $CatSubcat, 2);
		$Cat= $CatArray[0];
		$Subcat= $CatArray[1];
		if ($_POST['Disable']) {
			$View->Controller($Output, 'TurnOffCats', $_SESSION[$View->Model]['Group'], $Submenu, $_POST['ListType'], $Cat, $Subcat);
		}
		else if ($_POST['Enable']) {
			$View->Controller($Output, 'TurnOnCats', $_SESSION[$View->Model]['Group'], $Submenu, $_POST['ListType'], $Cat, $Subcat);
		}
	}
}

$View->SetSessionFilterGroup();

require_once($VIEW_PATH.'header.php');
		
$View->PrintFilterGroupForm();
PrintFilterCatForms($Submenu);

PrintHelpWindow($View->ConfHelpMsg."\n\n".$ListHelpMsg.' '.$WeightedListHelpMsg);
require_once($VIEW_PATH.'footer.php');
?>
