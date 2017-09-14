<?php
/* $ComixWall: conf.php,v 1.18 2009/11/16 12:05:36 soner Exp $ */

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
 * Group configuration.
 */

require_once('include.php');

$Submenu= SetSubmenu('groups');

$Msg= _HELPWINDOW('These settings apply to currently active group only.');

switch ($Submenu) {
	case 'groups':
		require_once('conf.groups.php');
		exit;
	case 'basic':
		$View->ConfHelpMsg= _HELPWINDOW('The options on this page determine basic features of the group. Group name shows on the Denied page and logs.')."\n\n".$Msg;
		break;
	case 'scan':
		$View->ConfHelpMsg= _HELPWINDOW('Thresholds and settings here are related with the content scanning feature of the web filter.')."\n\n".$Msg;
		break;
	case 'blanket':
		$View->ConfHelpMsg= _HELPWINDOW('Blanket block configuration is used to block all traffic for that rule.')."\n\n".$Msg;
		break;
	case 'bypass':
		$View->ConfHelpMsg= _HELPWINDOW('The options here are for the Denied page which the user receives instead of the page requested.')."\n\n".$Msg;
		break;
	case 'email':
		$View->ConfHelpMsg= _HELPWINDOW('The web filter can report incidents via e-mails. You can setup which incidents to be reported based on violation types and thresholds. Do not forget the single quotes around e-mail addresses and strings.')."\n\n".$Msg;
		break;
}

$ViewConfigName= $Submenu.'Config';
$View->Config= ${$ViewConfigName};
/// conf.php included can print DG group change form. Displayed for DG only.
/// Default is FALSE.
$PRINT_GROUP_FORM= TRUE;
require_once('../lib/conf.php');
?>
