<?php
/* $ComixWall: conf.php,v 1.15 2009/11/23 16:59:58 soner Exp $ */

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

require_once('include.php');

/** Displays P3scan language file selector.
 */
function PrintSetLanguageForm()
{
	global $View, $Class;
	
	$View->Controller($languages, 'GetLocales');
	if ($View->Controller($output, 'GetCurrentLocale')) {
		$currentlang= $output[0];
	}
	?>
	<tr class="<?php echo $Class ?>">
		<td class="title">
			<?php echo _TITLE2('Report language').':' ?>
		</td>
		<td>
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<select name="Language">
					<?php
					foreach ($languages as $lang) {
						$selected= $currentlang === $lang ? 'selected' : '';
						?>
						<option <?php echo $selected ?> value="<?php echo $lang ?>"><?php echo $lang ?></option>
						<?php
					}
					?>
				</select>
				<input type="submit" name="ApplyLanguage" value="<?php echo _CONTROL('Apply') ?>"/>
			</form>
		</td>
		<td class="none">
			<?php PrintHelpBox(_HELPBOX2('Here you can set the report language in emails sent to client, instead of infected e-mails.')) ?>
		</td>
	</tr>
	<?php
}

if ($_POST['Language']) {
	$View->Controller($Output, 'ChangeLocal', $_POST['Language']);
}

$CustomFunc= 'PrintSetLanguageForm';

require_once('../lib/conf.php');
?>
