<?php
/* $ComixWall: login.php,v 1.21 2009/11/21 21:55:59 soner Exp $ */

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
 * Login page.
 */

/// Force https
if (!isset($_SERVER['HTTPS'])) {
	header('Location: https://'.$_SERVER['SERVER_ADDR'].'/index.php');
	exit;
}

/// @warning Do not include vars.php here, it checks for logged in user, otherwise would loop back here
// Init minimal setup for login page
$ROOT= dirname(dirname(__FILE__));
require_once($ROOT.'/lib/setup.php');
require_once($ROOT.'/lib/defs.php');
require_once($ROOT.'/lib/lib.php');
$IMG_PATH= '/images/';

// Sessions are started in libauth.php
// Need a session everywhere below, after successful Authentication() too
require_once('lib/libauth.php');
// This include is necessary, because $View->Controller() tries to print a helpbox
// Such help boxes are never shown on login page (there is no UserRegion),
// but not including this lib causes a fatal PHP error: white screen
require_once('lib/libwui.php');

require_once('lib/view.php');
$View= new View();
$View->Model= 'system';

if ($_POST['Login']) {
	$_SESSION['USER']= $_POST['UserName'];
	Authentication($_POST['Password']);
}
else {
	if ($_SESSION['Timeout']) {
		// If user was already logged out, do not check timeout, LogUserOut() sets timeout to -1
		// Otherwise results in a loop
		if ($_SESSION['Timeout'] > 0) {
			if ($_SESSION['Timeout'] <= time()) {
				LogUserOut('Session timed out');
			}
		}
	}
}

HTMLHeader(0, 'gray');
?>
<tr>
	<td>
		<table style="height: 400px;">
			<tr>
				<td>
					<div align="center">
					<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
						<table id="window">
							<tr>
								<td class="titlebar">
									<?php echo _MENU('COMIXWALL ADMINISTRATION INTERFACE') ?>
								</td>
							</tr>
							<tr>
								<td class="authbox">
									<a href="http://comixwall.org" name="ComixWall"><img src="<?php echo $IMG_PATH."comix.png" ?>" name="ComixWall" alt="ComixWall" border="0"></a>
									<table id="authbox">
											<tr>
												<td class="label">
													<?php echo _TITLE('User name').':' ?>
												</td>
												<td class="textbox">
													<input class="textbox" type="text" name="UserName" maxlength="20"/>
												</td>
											</tr>
											<tr>
												<td class="label">
													<?php echo _TITLE('Password').':' ?>
												</td>
												<td class="textbox">
													<input class="textbox" type="password" name="Password" maxlength="20"/>
												</td>
											</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td class="button">
									<input class="button" type="submit" name="Login" value="<?php echo _CONTROL('Log in') ?>"/>
								</td>
							</tr>
						</table>
					</form>
					</div>
				</td>
			</tr>
		</table>
	</td>
</tr>
<?php
HTMLFooter();
?>
