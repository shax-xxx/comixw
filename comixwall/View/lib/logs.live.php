<?php
/* $ComixWall: logs.live.php,v 1.19 2009/11/22 09:04:51 soner Exp $ */

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
 * All live log pages include this file.
 *
 * Includes a different top menu if so configured. Currently only for pf.
 *
 * Module configuration are in $Modules. Module pages which include
 * this file should first set its module index as $View.
 *
 * Restarts the session for live page reload rate.
 */
require_once('../lib/vars.php');

$Reload= TRUE;
SetRefreshInterval();

$View->Controller($Output, 'GetDefaultLogFile');
$LogFile= $Output[0];

UpdateLogsPageSessionVars($LinesPerPage, $SearchRegExp);

$View->Controller($Output, 'GetFileLineCount', $LogFile, $SearchRegExp);
$LogSize= $Output[0];

require_once($VIEW_PATH.'header.php');
		
PrintLiveLogHeaderForm();
?>
<table id="logline">
	<?php
	PrintTableHeaders($View->Model);

	$View->Controller($Logs, 'GetLiveLogs', $LogFile, $LinesPerPage, $SearchRegExp);
	$Logs= unserialize($Logs[0]);

	$LineCount= 1;
	if ($LogSize > $LinesPerPage ) {
		$LineCount= $LogSize - $LinesPerPage + 1;
	}

	foreach ($Logs as $Logline) {
		$View->PrintLogLine($Logline, $LineCount++);
	}
	?>
</table>
<?php
PrintHelpWindow($View->LogsHelpMsg);
require_once($VIEW_PATH.'footer.php');
?>
