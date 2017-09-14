<?php
/* $ComixWall: include.inc.php,v 1.7 2009/11/20 12:12:28 soner Exp $ */

/*
 * Copyright (c) 2004 Allard Consulting.  All rights reserved.
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
 *    product includes software developed by Allard Consulting
 *    and its contributors.
 * 4. Neither the name of Allard Consulting nor the names of
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

chdir(dirname(__FILE__));
require_once('include.php');

/// @attention $inst_dir is set within vars.php
// $inst_dir = dirname(__FILE__);
/// @todo Not used by ComixWall webif.
$database = $inst_dir. "/conf/config.db";

/**
* Releads the current page. This is done to clean up the url after some
* variables has been passed and it's undesireble for the user to resubmit
* the changes through a page reload.
*
* @param string $url If any url parameters needs adding when reloading.
* @return void
*/
function reload($url = false)
{
	if (isset($_GET['rulenumber']) && !$url) {
	  $url = "?rulenumber=". $_GET['rulenumber'];
	}
	header("location: ".  $_SERVER['PHP_SELF']. $url);
}

/**
* Displayes the page header
*
* @param[in]	$title string The page title.
* @param[in]	$prefix string The relative directory level from the web root.
* @param[in]	$reload_rate int The number of seconds to wait before reloading the page.
*								With a NULL value, the page won't automatically reload.
*/
function page_header ($title, $prefix = "..", $reload_rate = false)
{
	global $inst_dir, $active, $pfhosts, $style, $database,
		/// @attention Since page_header() is a function, things get a bit hairy here,
		/// Need to include many vars defined in vars.php. Especially $PF_MODULES is bad.
		/// But at least we know these are all pf module needs. Fine for now.
		$View, $VIEW_PATH, $LOCALES, $PF_PATH, $CW_MODULES, $PF_MODULES, $IMG_PATH, $ModelsToStat;

	$Reload= $reload_rate;

	require_once($VIEW_PATH.'header.php');
	?>
	<table id="topmenu">
		<tr>
			<td>
				<ul id="tabs">
				<?php
				if (file_exists("$inst_dir/web/$active_tab/submenu.inc.php")) {
					include_once("$inst_dir/web/$active_tab/submenu.inc.php");
				}

				if ($_SESSION['flash']['notice']) {
					print "<div class=\"notice\">". $_SESSION['flash']['notice']. "</div>\n";
					unset($_SESSION['flash']['notice']);
				}

				if ($_SESSION['flash']['error']) {
					print "<div class=\"error\">". $_SESSION['flash']['error']. "</div>\n";
					unset($_SESSION['flash']['error']);
				}
				?>
				</ul>
			</td>
		</tr>
	</table>
	<?php
}

$_SESSION['pfhost']['connect']= 'localhost';

if (!isset($_SESSION['pfw'])) {
	$_SESSION['pfw'] = new pfw();
	$View->Controller($Output, 'GetPfwPfFileName', $_SESSION['pfhost']['connect']);
	$filename= $Output[0];
	  
	$_SESSION['pfw']->parseRulebase (file_get_contents($filename));
	unlink ($filename);
	unset($_SESSION['filename']);
}


if (!isset($_SESSION['edit'])) {
	$_SESSION['edit'] = array();
}
?>
