<?php
/* $ComixWall: conf.rules.php,v 1.25 2009/11/21 21:55:59 soner Exp $ */

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

/** Provides enabled/disabled snort rules in list boxes, and buttons to modify.
 */
function PrintRulesForms()
{
	global $View;
	?>
	<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
		<td style="width: 0;">
			<?php
			echo _TITLE2('Enabled Rules').':';
			$View->Controller($output, 'GetRules');
			?>
			<br />
			<select name="RulesToChange[]" multiple style="width: 200px; height: 400px;">
				<?php
				foreach ($output as $rule){
					$rulename= $rule;
					if (preg_match('/^(.*)\.rules$/', $rule, $match)) {
						$rulename= $match[1];
					}

					if ($_POST['RulesToChange'] && in_array($rule, $_POST['RulesToChange'])) {
						$selected= ' selected';
					}
					else {
						$selected= '';
					}
					?>
					<option value="<?php echo $rule ?>"<?php echo $selected ?>><?php echo $rulename ?></option>
					<?php
				}
				?>
			</select>
			<input type="submit" name="MoveUp" value="<?php echo _CONTROL('Move Up') ?>"/>
			<input type="submit" name="MoveDown" value="<?php echo _CONTROL('Move Down') ?>"/>
		</td>
		<td class="center" style="width: 0;">
			<input type="submit" name=">>" value=">>"/><br />
			<input type="submit" name="<<" value="<<"/>
		</td>
		<td style="width: 0;">
			<?php
			echo _TITLE2('Disabled Rules').':';
			$View->Controller($output, 'GetDisabledRules');
			sort($output);
			?>
			<br />
			<select name="RulesToChange[]" multiple style="width: 200px; height: 400px;">
				<?php
				foreach ($output as $rule){
					$rulename= $rule;
					if (preg_match('/^(.*)\.rules$/', $rule, $match)) {
						$rulename= $match[1];
					}
					?>
					<option value="<?php echo $rule ?>"><?php echo $rulename ?></option>
					<?php
				}
				?>
			</select>
		</td>
	</form>
	<?php
}

if ($_POST['>>']) {
	foreach ($_POST['RulesToChange'] as $Rule) {
		$View->Controller($Output, 'DisableRule', $Rule);
	}
}
else if ($_POST['<<']) {
	foreach ($_POST['RulesToChange'] as $Rule) {
		$View->Controller($Output, 'EnableRule', $Rule);
	}
}
else if ($_POST['MoveUp']) {
	foreach ($_POST['RulesToChange'] as $Rule) {
		$View->Controller($Output, 'MoveRuleUp', $Rule);
	}
}
else if ($_POST['MoveDown']) {
	$SelectedRules= $_POST['RulesToChange'];
	for ($i= count($SelectedRules) - 1; $i >= 0; $i--) {
		$View->Controller($Output, 'MoveRuleDown', $SelectedRules[$i]);
	}
}

require_once($VIEW_PATH.'header.php');
?>
<table>
	<tr>
		<?php
		PrintRulesForms();
		?>
		<td>
			<?php
			PrintHelpBox(_HELPBOX2('You can customize your rule sets here. Include only the relevant rule sets.

Some of rule sets are disabled by default. These rules are either site policy specific or require tuning in order to not generate false positive alerts in most environments.'), 200);
			?>
		</td>
	</tr>
</table>
<?php
PrintHelpWindow(_HELPWINDOW("The IDS uses rules categorized in different packages. Note that you cannot obtain a 'better' IDS by enabling all the rules. In fact, irrelevant rules may trigger false alarms.

Updated Snort rules for your version of the software are released periodically. Make sure you have appropriate rules installed on your system."));
require_once($VIEW_PATH.'footer.php');
?>
