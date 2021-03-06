<?php
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

$rulenumber = $_GET['rulenumber'];
$rule = $_SESSION['pfw']->altq->rules[$rulenumber];

if ($_SESSION['edit']['type'] != 'altq') {
	unset ($_SESSION['edit']['save']);
	$_SESSION['edit']['type'] = 'altq';
}

if ($_SESSION['edit']['rulenumber'] != $rulenumber) {
	unset ($_SESSION['edit']['save']);
	$_SESSION['edit']['rulenumber'];
}

if (!isset($_SESSION['edit']['save']) && isset($rule['identifier'])) {
	unset ($_SESSION['edit']['save']);
	$_SESSION['edit']['save'] = $_SESSION['pfw']->altq->rules[$rulenumber];
}

if (isset($_GET['dropqueue'])) {
	$_SESSION['pfw']->altq->delEntity ("queue", $_GET['rulenumber'], $_GET['dropqueue']);
	for ($i = '0'; $i < count($_SESSION['pfw']->queue->rules); $i++) {
		if ($_SESSION['pfw']->queue->rules[$i]['name'] ==  $_GET['dropqueue']) {
			$_SESSION['pfw']->queue->del ($i);
		}
	}
	reload();
}

if (isset($_POST['addqueue']) && (!preg_match("/ueue name/", $_POST['addqueue']))) {
	$_SESSION['pfw']->altq->addEntity("queue", $rulenumber, preg_replace("/\"/", "", $_POST['addqueue']));
	$_newQueue = $_SESSION['pfw']->queue->addRule();
	$_SESSION['pfw']->queue->rules[$_newQueue]['name'] = preg_replace("/\"/", "", $_POST['addqueue']);
	$_SESSION['edit']['addedQueues'][] = $_newQueue;

	reload();
}

if (count($_POST)) {
	$_SESSION['pfw']->altq->rules[$rulenumber]['interface'] =	$_POST['interface'];
	$_SESSION['pfw']->altq->rules[$rulenumber]['bandwidth'] =	$_POST['bandwidth'];
	$_SESSION['pfw']->altq->rules[$rulenumber]['scheduler'] =	$_POST['scheduler'];
	$_SESSION['pfw']->altq->rules[$rulenumber]['qlimit'] =		  $_POST['qlimit'];
	$_SESSION['pfw']->altq->rules[$rulenumber]['tbrsize'] =		$_POST['tbrsize'];
	$_SESSION['pfw']->altq->rules[$rulenumber]['comment'] =		$_POST['comment'];
	$rule = $_SESSION['pfw']->altq->rules[$rulenumber];
}

/*
* go back
*/
if (isset($_POST['cancelme']) && ($_POST['cancelme'] == 'cancel')) {
	$_SESSION['pfw']->altq->rules[$rulenumber] = $_SESSION['edit']['save'];
	if (!isset($_SESSION['pfw']->altq->rules[$rulenumber]['interface'])) {
		$_SESSION['pfw']->altq->del ($rulenumber);
		foreach ($_SESSION['edit']['addedQueues'] as $_newQueue) {
			$_SESSION['pfw']->queue->del ($_newQueue);
		}
	}
	unset ($_SESSION['edit']);
	header ("Location: queue.php");
}

if (isset($_POST['save']) && $_POST['save'] == "save and return") {
	unset ($_SESSION['edit']);
	header ("Location: queue.php");
}

/*
* Begin Output
*/
$active = "queue";
page_header("Edit Queue");

?>
<div id="main">
<h2>Edit Altq</h2>
	<fieldset>
		<form id="theform" action="<?php print $_SERVER['PHP_SELF']. "?rulenumber=$rulenumber";?>" method="post">
			<table width="100%" cellspacing="0" cellpadding="10">
			<tr align="center">
			<td class="top">
				<label for="interface">Interface</label>
				<input type="text" id="interface" name="interface" size="10" value="<?php print $rule['interface'];?>" />
			</td>

			<td class="top">
				<label for="bandwidth">Bandwidth</label>
				<input type="text" id="bandwidth" name="bandwidth" size="10" value="<?php print $rule['bandwidth'];?>" />
			</td>

			<td class="top">
				<label for="scheduler">Scheduler</label>
				<select id="scheduler" name="scheduler">
					<option value="cbq" label="cbq" <?php print ($rule['scheduler'] == "cbq" ? "selected=\"selected\"" : "");?>>cbq</option>
					<option value="priq" label="priq" <?php print ($rule['scheduler'] == "priq" ? "selected=\"selected\"" : "");?>>priq</option>
					<option value="hfsc" label="hfsc" <?php print ($rule['scheduler'] == "hfsc" ? "selected=\"selected\"" : "");?>>hfsc</option>
				</select>
			</td>

			<td class="top">
				<label for="qlimit">Qlimit</label>
				<input type="text" id="qlimit" name="qlimit" size="10" value="<?php print $rule['qlimit'];?>" />
			</td>

			<td class="top">
				<label for="tbrsize">Tbrsize</label>
				<input type="text" id="tbrsize" name="tbrsize" size="10" value="<?php print $rule['tbrsize'];?>" />
			</td>

			<td class="toplast">
				<?php
				if (isset($rule['queue'])) {
					if (is_array($rule['queue'])) {
						foreach ($rule['queue'] as $queue) {
							print "$queue ";
							print "<a href=\"". $_SERVER['PHP_SELF']. "?rulenumber=$rulenumber&amp;dropqueue=$queue\">del</a>";
							print "<br />";
						}
					} else {
						print $rule['queue']. " ";
						print "<a href=\"". $_SERVER['PHP_SELF']. "?rulenumber=$rulenumber&amp;dropqueue='". $rule['queue']. "'\">del</a>";
					}
					print "<div class=\"add\">";
				}
				print "<label for=\"addqueue\">add queue</label>";
				print "<input type=\"text\" id=\"addqueue\" name=\"addqueue\" size=\"10\" value=\"Queue name\" 
							onfocus=\"if (addqueue.value=='Queue name') addqueue.value = '' \" 
							onblur=\"if (addqueue.value == '') addqueue.value = 'Queue name'\" />";
				if (isset($rule['queue'])) {
					print "</div>";
				}
				?>
			</td>

			</tr>

			</table>
			<table width="100%" cellspacing="0" cellpadding="10">
				<tr>
				<td class="last" style="white-space: nowrap">
					<label for="comment">Comment</label>
					<input type="text" id="comment" name="comment" value="<?php print stripslashes($rule['comment']);?>" size="80" style="width: 90%" />
				</td>
				</tr>
			</table>
			<div class="buttons">
				<input type="submit" id="save"      name="save"     value="save" />
				<input type="submit" id="return"    name="save"     value="save and return" />
				<input type="submit" id="cancelme"  name="cancelme" value="cancel" />
			</div>
		</form>
	</fieldset>
</div>
<?php
require('manual/altq.php');
require_once($VIEW_PATH.'footer.php');
?>
