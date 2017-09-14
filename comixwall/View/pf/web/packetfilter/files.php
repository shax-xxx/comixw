<?php
/* $ComixWall: files.php,v 1.4 2009/11/16 12:05:38 soner Exp $ */

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

include_once("../../include.inc.php");


if ($_FILES) {
  unset($_SESSION['pfw']);
  unset($_SESSION['filename']);
  $_SESSION['pfw'] = new pfw();
  $_SESSION['pfw']->parseRulebase (file_get_contents($_FILES['userfile']['tmp_name']));
  unlink ($_FILES['userfile']['tmp_name']);

  $flash['notice'] = "File uploaded successfully";
}

if ($_POST['filename']) {
  $filename = preg_replace ('/\s/', '_', $_POST['filename']);
  preg_match ('/[a-zA-Z0-9\.\-_]+/', $filename, $filename_a);
  $filename = $filename_a[0];
  $handle = fopen("$inst_dir/conf/packetfilter/". $filename, "w");
  if (fwrite($handle, $_SESSION['pfw']->generate()) === FALSE) {
    $flash['error'] = "Cannot write to file ($filename)";
  } else {
    $flash['notice'] = "File $filename save successfully.";
    $_SESSION['filename'] = $_POST['filename'];
  }
  fclose ($handle);
}

if ($_GET['load']) {
  unset($_SESSION['pfw']);
  $_SESSION['pfw'] = new pfw();
  $_SESSION['pfw']->parseRulebase (file_get_contents("$inst_dir/conf/packetfilter/". $_GET['load']));
  $_SESSION['filename'] = $_GET['load'];

  $flash['notice'] = "File loaded successfully";
}

if ($_GET['reload']) {
  unset($_SESSION['pfw']);
  unset($_SESSION['filename']);
  $_SESSION['pfw'] = new pfw();
/// @todo Fix this
//   $filename = exec ($_SESSION['pfhost']['sudo']. " $inst_dir/bin/packetfilter.sh ". $_SESSION['pfhost']['connect']. " -r");
  $View->Controller($Output, 'GetPfwPfFileName', $_SESSION['pfhost']['connect']);
  $filename= $Output[0];
  $_SESSION['pfw']->parseRulebase (file_get_contents($filename));
  unlink ($filename);
  unset($_SESSION['filename']);
}

if ($_GET['download_current']) {
  if(isset($_SERVER['HTTP_USER_AGENT']) && preg_match("/MSIE/", $_SERVER['HTTP_USER_AGENT'])) {
    // IE Bug in download name workaround
    ini_set( 'zlib.output_compression','Off' ); 
  } 
  header( 'Content-Type: application/octet-stream' );
#     header( 'Content-Size: $fileSize' );
  header( "Content-Disposition: attachment; filename=\"pf.conf\"");
  print $_SESSION['pfw']->generate();
  exit;
}

if ($_GET['download']) {
  if(isset($_SERVER['HTTP_USER_AGENT']) && preg_match("/MSIE/", $_SERVER['HTTP_USER_AGENT'])) {
    // IE Bug in download name workaround
    ini_set( 'zlib.output_compression','Off' ); 
  } 
  header( 'Content-Type: application/octet-stream' );
#     header( 'Content-Size: $fileSize' );
  header( "Content-Disposition: attachment; filename=\"". $_GET['download']. "\"");
  readfile("$inst_dir/conf/packetfilter/". $_GET['download']);
  exit;
}

if ($_GET['delete']) {
  if (preg_match('/^\./', $_GET['delete'])) {
    $flash['error'] = "No dots in the beginning of filenames please.";
  }
  if (!file_exists("$inst_dir/conf/packetfilter/". $_GET['delete'])) {
    $flash['error'] = "There is no such file: ". $_GET['delete'];
  }

  if (!$flash['error']) {
    unlink ("$inst_dir/conf/packetfilter/". $_GET['delete']);
    $flash['notice'] = "File deleted successfully";
  }
}

$active = "load";
page_header("Load &amp; Save");
?>

<p>&nbsp;</p>

<?php
/*
 * The right column with file listings, if there are any files.
 */
if (file_exists("$inst_dir/conf/packetfilter/")) {
	$dh  = opendir("$inst_dir/conf/packetfilter/");
	while (false !== ($filename = readdir($dh))) {
	  if (!preg_match('/^\./', $filename)) {
		$conf_files[] = $filename;
	  }
	}
}

if (count($conf_files)) {
?>
  <div style="float: right; margin: 0em 3em;">
  <style>
  #filelist td
  {
    padding: 0.2em 1em;
    text-align: left;
  }
  </style>
  <table id="filelist">
    <tr>
      <th>Filename</th>
      <th>Last Modified</th>
      <th>Load</th>
      <th>Download</th>
      <th>Delete</th>
    </tr>
  <?php

  foreach ($conf_files as $file) {
    print "<tr><td>$file</td>";
    print "<td>". date ("F d Y H:i:s", filemtime("$inst_dir/conf/packetfilter/$file")). "</td>";
    print "<td><a href=\"?load=$file\">Load</a></td>";
    print "<td><a href=\"?download=$file\">Download</a></td>";
    print "<td><a href=\"?delete=$file\">Delete</a></td>";
    print "</tr>\n";
  }

  ?>
  </table>
  </div>
<?php } ?>

<h2>Currently Loaded rulebase</h2>
<br />
<a href="?reload=true">Reload the currently installed rulebase</a>
<p>&nbsp;</p>

<h2>Upload rulebase</h2>
<br />

<form enctype="multipart/form-data" method="post">
    <input type="hidden" name="max_file_size" value="30000" />
    Send this file: <input name="userfile" type="file" />
    <input type="submit" value="Send File" />
</form>

<p>&nbsp;</p>
<h2>Save rulebase</h2>
<p>If you save a file with an existing filename, it will replace the previous file.</p>

<?php
if (is_writable("$inst_dir/conf/packetfilter")) {
?><form method="post">
  <label for="filename">filename</label>
	<input type="text" name="filename" id="filename" value="<?php print $_SESSION['filename'];?>" size="32" />
	<input type="submit" value="Save File" />
</form><?php
} else {
  print "<p>if <samp>$inst_dir/conf/packetfilter</samp> was writeable, you could have saved this file to the server.</p>";
}

?>

<h2>Download rulebase</h2>
<p><a href="?download_current=true">Download the current working rulebase</a></p>

<?php
require_once($VIEW_PATH.'footer.php');
?>
