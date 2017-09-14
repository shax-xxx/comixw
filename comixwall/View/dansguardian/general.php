<?php
/* $ComixWall: general.php,v 1.19 2009/11/16 12:05:36 soner Exp $ */

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
 * General configuration.
 */

require_once('include.php');

$Submenu= SetSubmenu('basic');

$Msg= _HELPWINDOW('The settings on this page are valid filter-wide.');

switch ($Submenu) {
	case 'basic':
		$View->ConfHelpMsg= _HELPWINDOW('IP and port configuration here are fundamental to the correct functioning of the web filter.')."\n\n".$Msg;
		break;
	case 'filter':
		$View->ConfHelpMsg= _HELPWINDOW('Phrase modes and cache settings determine how content scan works.')."\n\n".$Msg;
		break;
	case 'scan':
		$View->ConfHelpMsg= _HELPWINDOW('The web filter on ComixWall uses weighted phrase lists to scan the content of web pages, hence the name Content Scanning Web Filter. This feature of the web filter is different from and in addition to site or url lists, blacklists, and related categories.')."\n\n".$Msg;
		break;
	case 'logs':
		$View->ConfHelpMsg= _HELPWINDOW('Statistics collection entirely depends on what and how logs are recorded in the log files. So do not change these settings unless necessary.')."\n\n".$Msg;
		break;
	case 'downloads':
		$View->ConfHelpMsg= _HELPWINDOW('Fancy download manager is designed to provide download status to the user via a progress bar and text information. Therefore, it expects a web browser as user agent by default. Otherwise, downloads are handled by the default download manager.')."\n\n".$Msg;
		break;
	case 'advanced':
		$View->ConfHelpMsg= _HELPWINDOW('These advanced options can help you tune the performance of the web filter. For example, you can increase the maximum number of child processes if your internal network is large. However, the maximum number of processes a daemon user can start is restricted in login.conf file of the operating system. So make sure you adjust both settings accordingly.')."\n\n".$Msg;
		break;
}

$ViewConfigName= 'General'.$Submenu.'Config';
$View->Config= ${$ViewConfigName};
require_once('../lib/conf.php');
?>
