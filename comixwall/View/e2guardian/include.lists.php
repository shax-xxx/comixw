<?php
/* $ComixWall: include.lists.php,v 1.25 2009/11/25 10:21:47 soner Exp $ */

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
 * Exception and mime pages.
 */

/** Displays a list of extensions and mime types defined and forms with components to change.
 *
 * @param[in]	$list	List name, selected submenu.
 */
function PrintFilterExtMimeForm($list)
{
	global $View;

	$titles= array(
		'exts'			=> _TITLE2('Ext'),
		'mimes'			=> _TITLE2('Mime'),
		'dm_exts'		=> _TITLE2('Ext'),
		'dm_mimes'		=> _TITLE2('Mime'),
		'virus_exts'	=> _TITLE2('Ext'),
		'virus_mimes'	=> _TITLE2('Mime'),
		);

	$title= $titles[$list];

	$lists= array(
		'exts'			=> array(
			'exception'	=> array(
				'title' => _TITLE2('Whitelist'),
				'color' => '#eee',
				),
			'banned'	=> array(
				'title' => _TITLE2('Blacklist'),
				'color' => 'gray',
				),
			),
		'mimes'			=> array(
			'exception'	=> array(
				'title' => _TITLE2('Whitelist'),
				'color' => '#eee',
				),
			'banned'	=> array(
				'title' => _TITLE2('Blacklist'),
				'color' => 'gray',
				),
			),
		'dm_exts'		=>	array(
			'exception'	=> array(
				'title' => _TITLE2('Managed extensions'),
				'color' => '#eee',
				),
			),
		'dm_mimes'		=>	array(
			'exception'	=> array(
				'title' => _TITLE2('Managed mime types'),
				'color' => '#eee',
				),
			),
		'virus_exts'	=>	array(
			'exception'	=> array(
				'title' => _TITLE2('Whitelist'),
				'color' => '#eee',
				),
			),
		'virus_mimes'	=>	array(
			'exception'	=> array(
				'title' => _TITLE2('Whitelist'),
				'color' => '#eee',
				),
			),
		);
	
	if (array_key_exists($list, $lists)) {
		foreach ($lists[$list] as $type => $conf) {
			if ($View->Controller($output, 'GetExtMimeList', $_SESSION[$View->Model]['Group'], $list, $type)) {
				echo $conf['title'].':';
				?>
				<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
					<input style="display:none;" type="submit" name="Add" value="<?php echo _CONTROL('Add') ?>"/>
					<table style="width: 600px;">
						<tr>
							<td style="background: <?php echo $conf['color'] ?>;">
								<?php
								$output= unserialize($output[0]);
								ksort($output);
								?>
								<table style="width: auto;">
									<tr>
										<td style="width: 0;">
											<?php
											echo _TITLE2('Enabled');
											?>
											<br />
											<select name="Items[]" multiple style="width: 250px; height: 250px;">
												<?php
												foreach ($output as $entry => $desc) {
													if ($desc['Enabled']) {
														$comment= trim(ltrim($desc['Comment'], '#'));
														$display= $comment !== '' ? "$entry ($comment)" : $entry;
														?>
														<option value="<?php echo $entry ?>" title="<?php echo $comment ?>"><?php echo $display ?></option>
														<?php
													}
												}
												?>
											</select>
										</td>
										<td class="center" style="width: 0;">
											<input type="submit" name=">>" value=">>"/>
											<input type="submit" name="<<" value="<<"/>
											<br />
											<br />
											<input type="submit" name="Delete" value="<?php echo _CONTROL('Delete') ?>" onclick="return confirm('<?php echo _NOTICE('Are you sure you want to delete the selected items?') ?>')"/>
										</td>
										<td style="width: 0;">
											<?php
											echo _TITLE2('Disabled');
											?>
											<br />
											<select name="Items[]" multiple style="width: 250px; height: 250px;">
												<?php
												foreach ($output as $entry => $desc) {
													if (!$desc['Enabled']) {
														$comment= trim(ltrim($desc['Comment'], '#'));
														$display= $comment !== '' ? "$entry ($comment)" : $entry;
														?>
														<option value="<?php echo $entry ?>" title="<?php echo $comment ?>"><?php echo $display ?></option>
														<?php
													}
												}
												?>
											</select>
										</td>
									</tr>
									<tr>
										<td id="extadd" colspan="3">
											<table id="extadd">
												<tr>
													<td class="title">
														<?php echo _($title).':' ?>
													</td>
													<td>
														<input type="text" name="ItemToAdd" style="width: 200px;" maxlength="50"/><br />
													</td>
												</tr>
												<tr>
													<td class="title">
														<?php echo _TITLE2('Desc').':' ?>
													</td>
													<td>
														<input type="text" name="CommentToAdd" style="width: 300px;" maxlength="100"/>
														<input type="submit" name="Add" value="<?php echo _CONTROL('Add') ?>" />
													</td>
												</tr>
											</table>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
					<input type="hidden" name="ListType" value=<?php echo $type ?> />
				</form>
				<?php
			}
		}
	}
	?>
	<?php
}

if ($_POST) {
	if ($_POST['>>']) {
		foreach ($_POST['Items'] as $Ext) {
			$View->Controller($Output, 'DisableExtMime', $_SESSION[$View->Model]['Group'], $Submenu, $_POST['ListType'], $Ext);
		}
	}
	else if ($_POST['<<']) {
		foreach ($_POST['Items'] as $Ext) {
			$View->Controller($Output, 'EnableExtMime', $_SESSION[$View->Model]['Group'], $Submenu, $_POST['ListType'], $Ext);
		}
	}
	else if ($_POST['Delete']) {
		foreach ($_POST['Items'] as $Ext) {
			$View->Controller($Output, 'DelExtMime', $_SESSION[$View->Model]['Group'], $Submenu, $_POST['ListType'], $Ext);
		}
	}
	else if ($_POST['Add'] && $_POST['ItemToAdd']) {
		$View->Controller($Output, 'AddExtMime', $_SESSION[$View->Model]['Group'], $Submenu, $_POST['ListType'], $_POST['ItemToAdd'], $_POST['CommentToAdd']);
	}
}

$View->SetSessionFilterGroup();

require_once($VIEW_PATH.'header.php');

if ($PrintGroupForm) {
	$View->PrintFilterGroupForm();
}
PrintFilterExtMimeForm($Submenu);
PrintHelpWindow($View->ConfHelpMsg."\n\n".$ListHelpMsg);

require_once($VIEW_PATH.'footer.php');
?>
