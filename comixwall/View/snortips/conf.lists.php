<?php
/* $ComixWall: conf.lists.php,v 1.16 2009/11/25 10:24:41 soner Exp $ */

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
 * Snort IPS white and black lists.
 */

if ($_POST['Delete'] && $_POST['IPs']) {
	$View->Controller($Output, 'DelIPFromList', $_POST['List'], serialize($_POST['IPs']));
}
else if ($_POST['Add'] && $_POST['IPToAdd']) {
	$View->Controller($Output, 'AddIPToList', $_POST['List'], $_POST['IPToAdd']);
}

require_once($VIEW_PATH.'header.php');
?>
<table id="nvp">
	<?php
	$Row= 1;
	$View->PrintListedIPsForm('whitelist', _TITLE2('Whitelisted'), _HELPBOX2('Whitelisted IPs are never blocked, even if IDS produces alerts for them. Make sure you have internal and external IP addresses of the system whitelisted here. Otherwise, false positives may block access to the system from the network.'));
	$View->PrintListedIPsForm('blacklist', _TITLE2('Blacklisted'), _HELPBOX2('Blacklisted IPs are always blocked.'));
	?>
</table>
<?php
PrintHelpWindow(_HELPWINDOW('Intrusion alerts produced by the IDS are guesses, hence there may be false positives or wrong alarms. Since the IPS depends on alerts produced by the IDS, you may want to make sure some IP addresses are never blocked accidentally, such as the internal and external IP addresses of the system, or the IP address of the computer you use to access this web administration interface.

You can enter individual IPs or network addresses. IP and network addresses can overlap. For example, you can blacklist 10.0.0.0/24, but whitelist 10.0.0.1.'));
require_once($VIEW_PATH.'footer.php');
?>
