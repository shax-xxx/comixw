#!/usr/local/bin/php
<?php
/* $ComixWall: cwc.php,v 1.17 2009/11/29 22:49:14 soner Exp $ */

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
 * Proxying to run all the shell commands.
 * This way we only have one entry in sudoers file.
 * @todo Continually check for security issues.
 */

/// @todo Is there a better way?
$ROOT= dirname(dirname(__FILE__));
$VIEW_PATH= $ROOT.'/View/';
$MODEL_PATH= $ROOT.'/Model/';

require_once($ROOT.'/lib/defs.php');
require_once($ROOT.'/lib/setup.php');

// chdir is for PCRE, libraries
chdir(dirname(__FILE__));

/// This is a command line tool, should never be requested on the web interface.
if (isset($_SERVER['SERVER_ADDR'])) {
	cwc_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Requested on the wui, exiting...');
	header('Location: /index.php');
	exit(1);
}

require_once($ROOT.'/lib/lib.php');
require_once('lib.php');

// Controller runs using the session locale of View
$Locale= $argv[1];

unset($ViewError);
$retval= 1;

$View= $argv[2];

if (array_key_exists($View, $ModelFiles)) {
	require_once($MODEL_PATH.$ModelFiles[$View]);
}
else {
	cwc_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "View not in ModelFiles: $View");
}

if (class_exists($Models[$View])) {
	$Model= new $Models[$View]();
}
else {
	require_once($MODEL_PATH.'model.php');
	$Model= new Model();
	cwc_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "View not in Models: $View");
}

/// @attention Do not set locale until after model file is included and model is created,
/// otherwise strings recorded into logs are also translated, such as the strings on Commands array of models.
/// Strings cannot be untranslated.
if (!array_key_exists($Locale, $LOCALES)) {
	$Locale= $DefaultLocale;
}

putenv('LC_ALL='.$Locale);
putenv('LANG='.$Locale);

$Domain= 'comixwall';
bindtextdomain($Domain, $VIEW_PATH.'locale');
bind_textdomain_codeset($Domain, $LOCALES[$Locale]['Codeset']);
textdomain($Domain);

$Command= $argv[3];

if (method_exists($Model, $Command)) {

	$ArgV= array_slice($argv, 4);

	if (array_key_exists($Command, $Model->Commands)) {
		$run= FALSE;

		ComputeArgCounts($Model->Commands, $ArgV, $Command, $ActualArgC, $ExpectedArgC, $AcceptableArgC, $ArgCheckC);

		// Extra args are OK for now, will drop later
		if ($ActualArgC >= $AcceptableArgC) {
			if ($ArgCheckC === 0) {
				$run= TRUE;
			}
			else {
				// Check only the relevant args
				$run= ValidateArgs($Model->Commands, $Command, $ArgV, $ArgCheckC);
			}
		}
		else {
			$ErrorStr= "[$AcceptableArgC]: $ActualArgC";
			ViewError(_('Not enough args')." $ErrorStr");
			cwc_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Not enough args $ErrorStr");
		}

		if ($run) {
			if ($ActualArgC > $ExpectedArgC) {
				$ErrorStr= "[$ExpectedArgC]: $ActualArgC: ".implode(', ', array_slice($ArgV, $ExpectedArgC));

				// Drop extra arguments before passing to the function
				$ArgV= array_slice($ArgV, 0, $ExpectedArgC);

				ViewError(_('Too many args, truncating')." $ErrorStr");
				cwc_syslog(LOG_WARNING, __FILE__, __FUNCTION__, __LINE__, "Too many args, truncating $ErrorStr");
			}

			if (($Output= call_user_func_array(array($Model, $Command), $ArgV)) !== FALSE) {
				if ($Output !== TRUE) {
					// If func retval is not boolean, it is data, return it
					echo $Output;
				}
				$retval= 0;
			}
		}
		else {
			ViewError(_('Not running command').": $Command");
			cwc_syslog(LOG_WARNING, __FILE__, __FUNCTION__, __LINE__, "Not running command: $Command");
		}
	}
	else {
		ViewError(_('Unsupported command').": $Command");
		cwc_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Unsupported command: $Command");
	}
}
else {
	$ErrorStr= "$Models[$View]->$Command()";
	ViewError(_('Method does not exist').": $ErrorStr");
	cwc_syslog(LOG_WARNING, __FILE__, __FUNCTION__, __LINE__, "Method does not exist: $ErrorStr");
}

if ($retval === 1 && isset($ViewError)) {
	echo $ViewError;
}
exit($retval);
?>
