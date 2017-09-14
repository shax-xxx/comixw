<?php
/* $ComixWall: lists.php,v 1.14 2009/11/21 21:55:58 soner Exp $ */

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

$Submenu= SetSubmenu('sites');

$SubmenuConf= array(
	'sites'			=> array(
		'PrintGroupForm'=> TRUE,
		'HelpMsg'		=> _HELPWINDOW("These lists allow or block all of a site. You can omit the leading www. or http://"),
		'IncludeFile'	=> 'lists.sites.php',
		),
	
	'urls'			=>	array(
		'PrintGroupForm'=> TRUE,
		'HelpMsg'		=> _HELPWINDOW("If you don't want to control access to a site completely, you can use URL lists to define finer access rights."),
		'IncludeFile'	=> 'lists.sites.php',
		),
	
	'exts'			=>	array(
		'PrintGroupForm'=> TRUE,
		'HelpMsg'		=> _HELPWINDOW("Group users can download files with extensions not in the Blacklist only. If you are having problems downloading files with some extensions, you may need to disable that file type from Blacklist. If the problem persists, you may need to disable the appropriate mime type on the Mimes configuration page too."),
		'IncludeFile'	=> 'include.lists.php',
		),
	
	'mimes'			=>	array(
		'PrintGroupForm'=> TRUE,
		'HelpMsg'		=> _HELPWINDOW("Group users can download files with mime types not in the Blacklist only. If you are having problems downloading files, you may need to disable the file's mime type from the BlackList. If the problem persists, you may need to disable the appropriate file extension on the Extensions configuration page too."),
		'IncludeFile'	=> 'include.lists.php',
		),
	
	'dm_exts'		=>	array(
		'HelpMsg'		=> _HELPWINDOW("Download Manager is responsible for downloading the files requested. The file extensions it manages are in Enabled list. Other files extensions are handled by the user's browser. During download, Fancy Download Manager reports the progress via a progress bar and status information."),
		'IncludeFile'	=> 'include.lists.php',
		),
	
	'dm_mimes'		=>	array(
		'HelpMsg'		=> _HELPWINDOW("Download Manager is responsible for downloading the content requested. The mime types it manages are in Enabled list. Other mime types are handled by the user's browser. During download, Fancy Download Manager reports the progress via a progress bar and status information."),
		'IncludeFile'	=> 'include.lists.php',
		),
	
	'virus_sites'	=>	array(
		'HelpMsg'		=> _HELPWINDOW("Sites in the Whitelist are not scanned for viruses. You can omit the leading www. or http://"),
		'IncludeFile'	=> 'lists.sites.php',
		),
	
	'virus_urls'	=>	array(
		'HelpMsg'		=> _HELPWINDOW("URLs in the Whitelist are not scanned for viruses. You can omit the leading www. or http://"),
		'IncludeFile'	=> 'lists.sites.php',
		),
	
	'virus_exts'	=>	array(
		'HelpMsg'		=> _HELPWINDOW("Files with extensions in the Whitelist are not scanned for viruses."),
		'IncludeFile'	=> 'include.lists.php',
		),
	
	'virus_mimes'	=>	array(
		'HelpMsg'		=> _HELPWINDOW("Files with mime types in the Whitelist are not scanned for viruses."),
		'IncludeFile'	=> 'include.lists.php',
		),
	);

$PrintGroupForm= isset($SubmenuConf[$Submenu]['PrintGroupForm']) && $SubmenuConf[$Submenu]['PrintGroupForm'] ? TRUE : FALSE;
$View->ConfHelpMsg= $SubmenuConf[$Submenu]['HelpMsg'];
require_once($SubmenuConf[$Submenu]['IncludeFile']);
?>
