<?php
/* $ComixWall: conf.php,v 1.13 2009/11/22 22:18:51 soner Exp $ */

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

if ($_POST['Forwarders']) {
	$View->Controller($Output, 'SetForwarders', $_POST['Forwarders']);
}

require_once($VIEW_PATH.'header.php');

if ($View->Controller($Output, 'GetForwarders')) {
	$Forwarders= $Output[0];
	?>
	<table id="nvp">
		<tr class="oddline">
			<td class="title">
				<?php echo _TITLE2('Forwarders').':' ?>
			</td>
			<td>
				<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
					<input type="text" name="Forwarders" style="width: 100px;" maxlength="160" value="<?php echo $Forwarders ?>"/>
					<input type="submit" name="Apply" value="<?php echo _CONTROL('Apply') ?>"/>
				</form>
			</td>
			<td class="none">
				<?php PrintHelpBox(_HELPBOX2('Forwarder is the IP address of a DNS server that the system itself queries. In simple setups, this is usually the IP address of a DSL modem which functions as gateway to the Internet.')) ?>
			</td>
		</tr>
	</table>
	<?php
}

PrintHelpWindow(_HELPWINDOW('By default, a simple DNS configuration is provided; only one forwarders is configured, which you can modify on this page. There are no zone records defined, and caching is disabled. However, you can obtain a full-featured DNS server by modifying configuration files.'));
require_once($VIEW_PATH.'footer.php');
?>
