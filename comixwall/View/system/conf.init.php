<?php
/* $ComixWall: conf.init.php,v 1.14 2009/11/21 21:55:58 soner Exp $ */

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
 * System initialization.
 */

if ($_POST['Apply']) {
	if ($View->Controller($Output, 'AutoConfig')) {
		PrintHelpWindow(_NOTICE('Automatic configuration completed. You might want to check the details now.'), 'auto', 'INFO');
	}
	else {
		PrintHelpWindow(_NOTICE('Automatic configuration failed.'), 'auto', 'ERROR');
	}
}
else if ($_POST['Reinitialize']) {
	$View->Controller($Output, 'InitGraphs');
}
else if ($_POST['Delete']) {
	$View->Controller($Output, 'DeleteStats');
}

require_once($VIEW_PATH.'header.php');
?>
<table id="nvp">
	<?php
	if ($View->Controller($Interfaces, 'GetPhyIfs')) {
		?>
		<tr class="oddline">
			<td class="title">
				<?php echo _TITLE('Automatic config').':' ?>
			</td>
			<td>
				<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
					<input type="submit" name="Apply" value="<?php echo _CONTROL('Apply') ?>"/>
				</form>
			</td>
			<td class="none">
				<?php
				PrintHelpBox(_HELPBOX('Changes to system configuration should be applied system-wide.

<b>If you modify basic or network settings, you are advised to use this button.</b>

Internal and external physical interface names are obtained from packet filter configuration, i.e. int_if and ext_if macros.'));
				?>
			</td>
		</tr>
		<?php
	}
	?>
	<tr class="evenline">
		<td class="title">
			<?php echo _TITLE('Graph files').':' ?>
		</td>
		<td>
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<input type="submit" name="Reinitialize" value="<?php echo _CONTROL('Reinitialize') ?>" onclick="return confirm('<?php echo _NOTICE('Are you sure you want to reinitialize graph files?') ?>')"/>
			</form>
		</td>
		<td class="none">
			<?php
			PrintHelpBox(_HELPBOX('Here you can reinitialize, hence erase all accumulated data in graph source files.

This may be necessary if these files are corrupted and you cannot see any graphs displayed, such as when the system clock is set to a very distant time.'));
			?>
		</td>
	</tr>
	<tr class="oddline">
		<td class="title">
			<?php echo _TITLE('Statistics and uncompressed log files').':' ?>
		</td>
		<td>
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<input type="submit" name="Delete" value="<?php echo _CONTROL('Delete') ?>" onclick="return confirm('<?php echo _NOTICE('Are you sure you want to erase statistics files?') ?>')"/>
			</form>
		</td>
		<td class="none">
			<?php
			PrintHelpBox(_HELPBOX('This button allows you to delete all the statistics and uncompressed log files created by this user interface. Note that deleting these files does not mean that accumulated statistics are lost forever. <b>Original log files under /var/log folder are not affected by this action</b> either. These files will be recreated, and statistics will be recollected, the next time you access Statistics or Logs pages.'));
			?>
		</td>
	</tr>
</table>
<?php
PrintHelpWindow(_HELPWINDOW('Buttons on this page should help you apply the new configuration system-wide when you change certain system settings or hardware.

The web user interface stores statistics under /var/tmp/comixwall folder. The statistics are updated incrementally when new messages are appended to log files. When user selects a compressed log file for viewing or statistics, its uncompressed copy is saved under the same folder also. This strategy greatly improves the performance of Statistics and Logs pages, and enables other features of the web user interface. Since these statistics and uncompressed log files may take up a lot of disk space, you are advised to keep your /var partition as large as possible.'));
require_once($VIEW_PATH.'footer.php');
?>
