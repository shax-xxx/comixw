<?php
/* $ComixWall: libauth.php,v 1.31 2009/11/27 12:21:30 soner Exp $ */

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
 * Authentication and related library functions.
 */

require_once('setup.php');

if (!isset($_SESSION)) {
	session_name('comixwall');
	session_start();
}

if ($_POST['Locale']) {
	$_SESSION['Locale'] = $_POST['Locale'];
	// To refresh the page after language change
	header('Location: '.$_SERVER['REQUEST_URI']);
	exit;
}

if (!isset($_SESSION['Locale'])) {
	$_SESSION['Locale']= $DefaultLocale;
}
putenv('LC_ALL='.$_SESSION['Locale']);
putenv('LANG='.$_SESSION['Locale']);

$Domain= 'comixwall';
bindtextdomain($Domain, $VIEW_PATH.'locale');
bind_textdomain_codeset($Domain, $LOCALES[$_SESSION['Locale']]['Codeset']);
textdomain($Domain);

/** Wrapper for syslog().
 *
 * Web interface related syslog messages.
 * A global $LOG_LEVEL is set in setup.php.
 *
 * @param[in]	$prio	Log priority checked against $LOG_LEVEL
 * @param[in]	$file	Source file the function is in
 * @param[in]	$func	Function where the log is taken
 * @param[in]	$line	Line number within the function
 * @param[in]	$msg	Log message
 */
function cwwui_syslog($prio, $file, $func, $line, $msg)
{
	global $LOG_LEVEL, $LOG_PRIOS;

	try {
		openlog("cwwui", LOG_PID, LOG_LOCAL0);
		
		if ($prio <= $LOG_LEVEL) {
			$useratip= $_SESSION['USER'].'@'.$_SERVER['REMOTE_ADDR'];
			$func= $func == '' ? 'NA' : $func;
			$log= "$LOG_PRIOS[$prio] $useratip $file: $func ($line): $msg\n";
			if (!syslog($prio, $log)) {
				if (!fwrite(STDERR, $log)) {
					echo $log;
				}
			}
		}
		closelog();
	}
	catch (Exception $e) {
		echo 'Caught exception: ',  $e->getMessage(), "\n";
		echo "cwwui_syslog() failed: $prio, $file, $func, $line, $msg\n";
		// No need to closelog(), it is optional
	}
}

/** Logs user out by setting session USER var to loggedout.
 *
 * Redirects to the main index page, which asks for re-authentication.
 *
 * @param[in]	$reason	string Reason for log message
 */
function LogUserOut($reason= 'User logged out')
{
	cwwui_syslog(LOG_INFO, __FILE__, __FUNCTION__, __LINE__, $reason);
	$_SESSION['USER']= 'loggedout';
	/// @warning Relogin page should not time out
	$_SESSION['Timeout']= -1;
	session_write_close();

	header('Location: /index.php');
	exit;
}

/** Authenticates session user with the password supplied.
 *
 * Passwords are sha1 encrypted before passed to Controller,
 * so the password string is never passed around plain text.
 * This means double encryption in the password file,
 * because Model encrypts again while storing into the file.
 *
 * @param[in]	$passwd	string Password submitted by user
 */
function Authentication($passwd)
{
	global $ALL_USERS, $SessionTimeout, $View;

	cwwui_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, 'Login attempt');

	if (!in_array($_SESSION['USER'], $ALL_USERS)) {
		cwwui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'Not a valid user');
		// Throttle authentication failures
		exec('/bin/sleep 5');
		LogUserOut('Authentication failed');
	}
	if (!$View->Controller($Output, 'CheckAuthentication', $_SESSION['USER'], sha1($passwd))) {
		cwwui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'Password mismatch');
		// Throttle authentication failures
		exec('/bin/sleep 5');
		LogUserOut('Authentication failed');
	}
	cwwui_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, 'Authentication succeeded');

	// Update session timeout now, otherwise in the worst case scenario, vars.php may log user out on very close session timeout
	$_SESSION['Timeout']= time() + $SessionTimeout;
	
	header("Location: /system/index.php");
	exit;
}

/** HTML Header without authentication.
 *
 * Called by AuthHTMLHeader() after logout check, and also by Login page.
 * Separate from AuthHTMLHeader() because Login page should not check logout naturally.
 *
 * @param[in]	$reloadrate	Page reload rate, defaults to 0 (no reload)
 * @param[in]	$color		Page background, Login page uses gray
 */
function HTMLHeader($reloadrate= 0, $color= 'white')
{
	global $LOCALES;
	// Unindent these html lines, against the project style guidelines, otherwise they are indented in page source too
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title><?php echo _MENU('ComixWall Administration Interface') ?></title>
		<meta http-equiv="content-type" content="text/html; charset=<?php echo $LOCALES[$_SESSION['Locale']]['Codeset'] ?>" />
		<meta name="description" content="ComixWall Internet Security Gateway" />
		<meta name="author" content="Soner Tari"/>
		<meta name="keywords" content="ComixWall, Internet, Security, Gateway, :)" />
		<link rel="stylesheet" href="../comixwall.css" type="text/css" media="screen" />
		<?php
		if ($reloadrate !== 0) {
			?>
			<meta http-equiv="refresh" content="<?php echo $reloadrate ?>" />
			<?php
		}
		?>
	</head>
	<body style="background: <?php echo $color ?>;">
		<table>
		<?php
}

function HTMLFooter()
{
		?>
		</table>
	</body>
</html>
<?php
}

/** Sets session topmenu variable.
 *
 * View object does not exists yet when this function is called
 * in index.php files, hence the $view parameter.
 *
 * @param[in]	$view		string Module name
 * @param[in]	$default	string Default topmenu selected
 * @return string Selected topmenu
 */
function SetTopMenu($view, $default= 'info.php')
{
	if ($_SESSION[$view]['topmenu']) {
		$topmenu= $_SESSION[$view]['topmenu'];
	}
	else {
		$topmenu= $default;
	}

	$_SESSION[$view]['topmenu']= $topmenu;
	return $topmenu;
}

/** Sets session submenu variable.
 *
 * @param[in]	$default	string Default submenu selected
 * @return string Selected submenu
 */
function SetSubmenu($default)
{
	global $View, $Modules;

	$page= basename($_SERVER['PHP_SELF']);

	if ($_GET['submenu']) {
		if (array_key_exists($_GET['submenu'], $Modules[$View->Model]['SubMenus'][$page])) {
			$Submenu= $_GET['submenu'];
			$_SESSION[$View->Model][$page]['submenu']= $Submenu;
		}
		else {
			cwwui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "No such submenu for $View->Model>$page: ".$_GET['submenu']);
			echo _TITLE2('Resource not available').": $page?submenu=".$_GET['submenu'];
			exit(1);
		}
	}

	if ($_SESSION[$View->Model][$page]['submenu']) {
		$Submenu= $_SESSION[$View->Model][$page]['submenu'];
	}
	else {
		$Submenu= $default;
	}

	$_SESSION[$View->Model][$page]['submenu']= $Submenu;
	return $Submenu;
}
?>
