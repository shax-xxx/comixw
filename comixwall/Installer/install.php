#!/usr/local/bin/php
<?php
/* $ComixWall: install.php,v 1.10 2009/11/22 17:49:03 soner Exp $ */

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
 * Configuration script used both during installation and by the web interface.
 */

/// This is a command line tool, should never be requested on the web interface.
if (isset($_SERVER['SERVER_ADDR'])) {
	header('Location: /index.php');
	exit(1);
}

// chdir is for libraries
chdir(dirname(__FILE__));

$ROOT= dirname(dirname(__FILE__));
$VIEW_PATH= $ROOT.'/View/';

require_once($ROOT.'/lib/setup.php');
/// Log all during installation.
$LOG_LEVEL= LOG_DEBUG;
require_once($ROOT.'/lib/defs.php');

require_once($VIEW_PATH.'lib/libauth.php');

$Auto= FALSE;
$FirstBoot= FALSE;
if ($_SERVER['argv'][1]) {
	if ($_SERVER['argv'][1] == '-a') {
		$Auto= TRUE;
	}
	else if ($_SERVER['argv'][1] == '-f') {
		$Auto= TRUE;
		$FirstBoot= TRUE;
	}
}

require_once('lib.php');

require_once($VIEW_PATH.'lib/view.php');
$View= new View();
$View->Model= 'system';

if ($View->Controller($Output, 'GetConfig')) {
	$Config= unserialize($Output[0]);

	if (InitIfs()) {
		if (!$Auto) {
			GetIfSelection();
			SetWuiPasswd();
		}
		
		if (ApplyConfig()) {
			$msg= 'Successfully configured the system';
			echo $msg.".\n";
			cwwui_syslog(LOG_INFO, __FILE__, __FUNCTION__, __LINE__, $msg);
			
			if ($FirstBoot) {
				FirstBootTasks();
			}
			exit(0);
		}
		else {
			cwwui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Failed applying configuration');
		}
	}
}
else {
	cwwui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Cannot get configuration');
}

cwwui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Configuration failed');
exit(1);
?>
