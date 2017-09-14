<?php
/* $ComixWall: database.php,v 1.14 2009/11/21 21:56:00 soner Exp $ */

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

include('include.php');

require_once($VIEW_PATH.'header.php');
?>
<table id="logline" class="centered">
	<?php

	echo '<strong>'._TITLE2('Spamd Grey DB').' : '.'</strong><br />';
	PrintTableHeaders('spamdgreydb');

	$View->Controller($SpamdDB, 'GetGreylist');
	$View->Model= 'spamdgreydb';
	$LineCount= 1;
	foreach ($SpamdDB as $Line) {
		PrintSpamdDBLine($Line, $LineCount++, $Cols);
	}
	?>
</table>
<table id="logline" class="centered">
	<?php
	echo '<br /><strong>'._TITLE2('Spamd White DB').' : '.'</strong><br />';
	PrintTableHeaders('spamdwhitedb');

	$View->Controller($SpamdDB, 'GetWhitelist');
	$View->Model= 'spamdwhitedb';
	$LineCount= 1;
	foreach ($SpamdDB as $Line) {
		PrintSpamdDBLine($Line, $LineCount++, $Cols);
	}
	?>
</table>
<?php
PrintHelpWindow(_HELPWINDOW('This page lists Spamd database entries. Definitions of the columns are as follows:
<ul class="nomargin"><li class="nomargin">Source IP: IP address the connection originated from</li><li class="nomargin">From: envelope-from address for GREY (empty for WHITE entries)</li><li class="nomargin">To: envelope-to address for GREY (empty for WHITE entries)</li><li class="nomargin">First: time the entry was first seen</li><li class="nomargin">Listed: time the entry passed from being GREY to being WHITE</li><li class="nomargin">Expire: time the entry will expire and be removed from the database</li><li class="nomargin">#Block: number of times a corresponding connection received a temporary failure from spamd(8)</li><li class="nomargin">#Passed: number of times a corresponding connection has been seen to pass to the real MTA by spamlogd(8)</li></ul>
E-mails originating from Whitelist entries are allowed until expiration date.

Greylist entries are currently active entries according to grey-listing mode of spamd.'));
require_once($VIEW_PATH.'footer.php');
?>
