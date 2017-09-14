<?php
/* $ComixWall: conf.basic.php,v 1.9 2009/11/26 20:53:07 soner Exp $ */

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

/** Displays alert keywords.
 */
function PrintKeywordsForm()
{
	global $View, $Class;
	?>
	<tr class="<?php echo $Class ?>">
		<td class="title">
			<?php echo _TITLE2('Keywords').':' ?>
		</td>
		<td>
			<?php
			$View->Controller($keywords, 'GetKeywords');
			?>
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<input style="display:none;" type="submit" name="Add" value="<?php echo _CONTROL('Add') ?>"/>
				<select name="Keywords[]" multiple style="width: 200px; height: 100px;">
					<?php
					foreach ($keywords as $key) {
						?>
						<option value="<?php echo $key ?>"><?php echo $key ?></option>
						<?php
					}
					?>
				</select>
				<input type="submit" name="Delete" value="<?php echo _CONTROL('Delete') ?>"/><br />
				<input type="text" name="KeywordToAdd" style="width: 200px;" maxlength="50"/>
				<input type="submit" name="Add" value="<?php echo _CONTROL('Add') ?>"/>
			</form>
		</td>
		<td class="none">
			<?php 
			PrintHelpBox(_HELPBOX2('These are keywords to match in alert messages. If alerts contain such words, the IP in that alert will be blocked.'));
			?>
		</td>
	</tr>
	<?php
}

if ($_POST['Delete']) {
	foreach ($_POST['Keywords'] as $Keyword) {
		$View->Controller($Output, 'DelKeyword', $Keyword);
	}
}
else if ($_POST['Add'] && $_POST['KeywordToAdd']) {
	$View->Controller($Output, 'AddKeyword', $_POST['KeywordToAdd']);
}

$CustomFunc= 'PrintKeywordsForm';
require_once('../lib/conf.php');
?>
