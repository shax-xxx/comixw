<?php
/* $ComixWall: topmenu.php,v 1.24 2009/11/24 19:26:22 soner Exp $ */

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
 * Prints top menu.
 */

require_once('lib/vars.php');

if (!isset($_SESSION[$View->Model]['ReloadRate'])) {
	$_SESSION[$View->Model]['ReloadRate']= $DefaultReloadRate;
}

if ($Reload) {
	AuthHTMLHeader($_SESSION[$View->Model]['ReloadRate']);
}
else {
	AuthHTMLHeader();
}
?>
<tr id="top">
	<td class="logo">
		<a href="http://comixwall.org" name="ComixWall"><img src="<?php echo $IMG_PATH."comix.png" ?>" name="ComixWall" alt="ComixWall" border="0"></a>
	</td>
	<td>
		<table class="topmenu">
			<tr>
				<td class="title">
					<b><?php echo _MENU('COMIXWALL ADMINISTRATION INTERFACE') ?></b>
				</td>
				<td class="lang">
					<form method="post" id="languageform" name="languageform" action="<?php echo preg_replace("/&/", "&amp;", $_SERVER['REQUEST_URI'], -1) ?>">
						<?php echo _MENU('Language').': ' ?>
						<select name="Locale" onchange="document.languageform.submit()">
						<?php
						foreach ($LOCALES as $Locale => $Conf) {
							$Selected= ($_SESSION['Locale'] == $Locale) ? 'selected' : '';
							if ($_SESSION['Locale'] !== 'en_EN') {
								$LocaleDisplayName= _($Conf['Name']).' ('.$Conf['Name'].')';
							}
							else {
								$LocaleDisplayName= _($Conf['Name']);
							}
							?>
							<option value="<?php echo $Locale ?>" <?php echo $Selected ?>><?php echo $LocaleDisplayName ?></option>
							<?php
						}
						?>
						</select>
					</form>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<div id="menu">
						<ul id="tabs">
						<?php
						$ModuleDirectory= dirname($_SERVER['SCRIPT_FILENAME']).'/';
						$ServerPath= dirname($_SERVER['PHP_SELF']).'/';

						if (preg_match('|/pf/|', $_SERVER['PHP_SELF'])) {
							$ServerPath= '';
							if (basename(dirname($_SERVER['PHP_SELF'])) !== 'pf') {
								$ServerPath= '../../';
							}
							$ModuleDirectory= $PF_PATH;
						}

						$ModuleFiles[]= array();
						$DirHandle= opendir($ModuleDirectory);
						while (false !== ($FileName= readdir($DirHandle))) {
							if (!is_dir($ModuleDirectory.$FileName)) {
								$ModuleFiles[]= $FileName;
							}
						}
						closedir($DirHandle);
						
						$PageActivated= FALSE;
						foreach ($CW_MODULES[basename($ModuleDirectory)] as $ModuleName => $ModuleConf) {
							foreach (${$ModuleName} as $FileName => $SubMenuConf) {
								if (in_array($FileName, $ModuleFiles)) {
									if (in_array($_SESSION['USER'], $SubMenuConf['Perms'])) {
										$Class= '';
										if (basename($_SERVER['PHP_SELF']) == $FileName) {
											$Class= 'class="active"';
											$_SESSION[basename($ModuleDirectory)]['topmenu']= $FileName;
											$PageActivated= TRUE;
										}
										?>
										<li <?php echo $Class ?>>
											<a href="<?php echo $ServerPath.$FileName ?>"><?php echo _($SubMenuConf['Name']) ?></a>
										</li>
										<?php
									}
								}
							}
						}

						// Rest of the menu items are for pfw:
						if ($ModuleDirectory == $PF_PATH) {
							$inst_dir = dirname(__FILE__).'/pf';
							
							$ServerPath= ($ServerPath == '') ? 'web/' : '../';
							$DirHandle= opendir("$inst_dir/web");
							while (false !== ($DirName= readdir($DirHandle))) {
								if (!preg_match('/^\./', $DirName) && is_dir("$inst_dir/web/$DirName")
									&& $DirName <> "stylesheet" && ($DirName <> "CVS")) {
							
									foreach ($CW_MODULES[basename($ModuleDirectory)] as $ModuleName => $ModuleConf) {
										foreach (${$ModuleName} as $filename => $SubMenuConf) {
											if (in_array($_SESSION['USER'], $SubMenuConf['Perms'])) {
												if ($DirName == $filename) {
													$Class= '';
													if (preg_match("/\/$DirName\//", $_SERVER['PHP_SELF'])) {
														$Class= 'class="active"';
														$active_tab = $DirName;
														$_SESSION[basename($ModuleDirectory)]['topmenu']= 'web/'.$DirName.'/index.php';
														$PageActivated= TRUE;
													}
													?>
													<li <?php echo $Class ?>>
														<a href="<?php echo $ServerPath.$DirName.'/index.php' ?>"><?php echo _($SubMenuConf['Name']) ?></a>
													</li>
													<?php
												}
											}
										}
									}
								}
							}
							closedir($DirHandle);
						}
						?>
						</ul>
					</div>

					<div id="menuunderline">
					</div>
				</td>
			</tr>
		</table>
	</td>
</tr>
