<?php
/* $ComixWall: hostgraphs.php,v 1.9 2009/11/21 21:55:58 soner Exp $ */

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

$PnrgImgPath= '../pmacct/pnrg/spool/';

if ($_POST['IP']) {
	$Ip= $_POST['IP'];
}

require_once($VIEW_PATH.'header.php');
?>
<table>
	<tr>
		<td>
			<form method="post" id="ipform" name="ipform" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
				<strong><?php echo _TITLE2('Host IP').': ' ?></strong>
				<select name="IP" onchange="document.ipform.submit()">
				<?php
				$View->Controller($Output, 'GetHostGraphsList', $Ip);
				foreach ($Output as $Host) {
					if (!isset($Ip)) {
						$Ip= $Host;
					}

					$Selected= $Host === $Ip ? ' selected' : '';
					?>
					<option value="<?php echo $Host ?>"<?php echo $Selected ?>><?php echo $Host ?></option>
					<?php
				}
				?>
				</select>
			</form>
		</td>
		<td style="width: 50%;">
			<?php PrintHelpBox(_HELPBOX2('Select an IP to view its network usage graphs.')) ?>
		</td>
	</tr>
</table>
<?php
if ($Ip) {
	/// No need for yearly graph.
	$Exts= array('.1hr.gif', '.8hr.gif', '.1day.gif', '.1wk.gif', '.1mon.gif');
	foreach ($Exts as $Ext) {
		?>
		<p>
		<img src="<?php echo $PnrgImgPath.$Ip.$Ext ?>" name="IP Graph" alt="IP Graph" border="0">
		</p>
		<?php
	}
}

PrintHelpWindow($View->GraphHelpMsg);
require_once($VIEW_PATH.'footer.php');
?>
