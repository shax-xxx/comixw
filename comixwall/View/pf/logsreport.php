<?php
/* $ComixWall: logsreport.php,v 1.4 2009/11/24 22:37:53 soner Exp $ */

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

require_once("include.inc.php");

function parse_log ($log)
{
  $log_a = array();

  foreach (preg_split("/\n/", $log, '-1', PREG_SPLIT_NO_EMPTY) as $rule_line) {
  	$items_a = preg_split("/\s+/", $rule_line, '-1', PREG_SPLIT_NO_EMPTY);
    $data = array();
    $data['month'] = $items_a[0];
    $data['day'] = $items_a[1];
//     $data['time'] = $items_a[2];
    $fulltime= explode('.', $items_a[2]);
    $data['time'] = $fulltime[0];
    preg_match('/[\d]+/', $items_a[4], $match);
    $data['rule'] = $match[0];
    $data['type'] = $items_a[5];
    $data['direction'] = $items_a[6];
    $data['interface'] = substr($items_a[8], 0, -1);

    if (preg_match('/[A-Z.]/', substr($items_a[12], 0, 1))) {
      $data['proto'] = "tcp";
      $data['flags'] = $items_a[12];
    } else if ($items_a[12] == "udp" || preg_match('/[0-9]/', substr($items_a[12], 0, 1))) {
      $data['proto'] = "udp";
    } else {
      $data['proto'] = substr($items_a[12], 0, -1);
    }
    if ($data['proto'] == "igmp-") {
      $data['proto'] = 'igmp';
    }

    if ($data['proto'] == "tcp" || $data['proto'] == "udp" || $data['proto'] == "v") {
      $data['source'] = substr($items_a[9], 0, strrpos($items_a[9], "."));
      $data['sourceport'] = substr($items_a[9], strrpos($items_a[9], ".") + 1);
      $data['destination'] = substr($items_a[11], 0, strrpos($items_a[11], "."));
      $data['destinationport'] = substr($items_a[11], strrpos($items_a[11], ".") + 1, -1);
    } else {
      $data['source'] = $items_a[9];
      $data['destination'] = substr($items_a[11], 0, -1);
    }

    if ($data['proto'] == "icmp") {
      $data['flags'] = $items_a[13]. " ". $items_a[14];
    }

    $log_a[] = $data;
  }
  return array_reverse($log_a);
}

function log_stats ($log_a)
{
  $stats = array();
  foreach ($log_a as $log_entry) {
    if ($log_entry['proto'] == "tcp" || $log_entry['proto'] == "udp") {
      $stats['sourceport'][$log_entry['sourceport']] += 1;
      $stats['destinationport'][$log_entry['destinationport']] += 1;
    }
    $stats['destination'][$log_entry['destination']] += 1;
    $stats['source'][$log_entry['source']] += 1;
    $stats['proto'][$log_entry['proto']] += 1;
    $stats['type'][$log_entry['type']] += 1;
  }
  if (count($stats['sourceport'])) {
    arsort($stats['sourceport']);
  }
  if (count($stats['destinationport'])) {
    arsort($stats['destinationport']);
  }
  if (count($stats['destination'])) {
    arsort($stats['destination']);
  }
  if (count($stats['source'])) {
    arsort($stats['source']);
  }
  if (count($stats['proto'])) {
    arsort($stats['proto']);
  }
  if (count($stats['type'])) {
    arsort($stats['type']);
  }
  return $stats;
}

function get_host_by_address ($ip, $resolve = false)
{
  global $dns_cache;
  
  if ($resolve) {

    if (!isset($dns_cache["$ip"])) {
      $dns_cache["$ip"] = gethostbyaddr($ip);
    }
    return $dns_cache[$ip];
  } else {
    return $ip;
  }
}

function get_service_by_port ($port, $proto = "tcp", $resolve = false)
{
  global $service_cache;
  
  if ($resolve) {
    if (!isset($service_cache["$proto"]["$port"])) {
      $service = getservbyport($port, $proto);
      if ($service) {
        $service_cache["$proto"]["$port"] = $service;
      } else {
        $service_cache["$proto"]["$port"] = $port;
      }
    }
    return $service_cache["$proto"]["$port"];
  } else {
    return $port;
  }
}

/*
 * Begin Parameter parsing 
 */

if (trim($_GET['count'])) {
  $count = $_GET['count'];
} else {
  $count = '50';
}

$regexp = stripslashes($_GET['regexp']);

$pfhost = $_SESSION['pfhost']['connect'];
/// @todo What is $invert_match here? Not defined anywhere
// $logs = parse_log (`sudo $inst_dir/bin/commandwrapper.sh $pfhost -log -$count $regexp $invert_match`);
$View->Controller($Output, 'GetPfwCmdWrapperLog', $pfhost, $count, $regexp);
$logs= parse_log(implode("\n", $Output));
$stats = log_stats($logs);

if ($_SESSION['dns_cache']) { 
  $dns_cache = $_SESSION['dns_cache'];
} else {
  $dns_cache = array();
}

if ($_SESSION['service_cache']) {
  $service_cache = $_SESSION['service_cache'];
} else {
  $service_cache = array();
}

/*
 * Begin Page Output
 */

page_header("filter", "..", $_GET['reload']);

?>
<style>

@import "web/comixwall.css";

caption
{
  font-weight: bold;
  font-size: 90%;
  text-align: left;
  padding-top: 0em;
  padding-bottom: 0.5em;
  margin: 0;
}

#statscolumn
{
  float: right; 
  width: 12%; 
  margin-right: 1em;
  margin-top: 2em;
}

#statscolumn table
{
  margin-bottom: 1.5em;
}

</style>


<div id="statscolumn">
  
<table>
  <caption>Type</caption>
  <tr>
    <th>type</th>
    <th>count</th>
  </tr>
<?php
for ($i = 0; $i < 10; $i++) {
  if ($stats) {
    $cp = each($stats['type']);
    if (!is_array($cp)) {
      break;
    }
    print "<tr><td>". $cp[key]. "</td><td>". $cp[value]. " </td></tr>";
  }
}
?>
</table>

<table>
  <caption>Dest ports</caption>
  <tr>
    <th>port</th>
    <th>count</th>
  </tr>
<?php
for ($i = 0; $i < 10; $i++) {
  if ($stats) {
    $cp = each($stats['destinationport']);
    if (!is_array($cp)) {
      break;
    }
    print "<tr><td>". get_service_by_port($cp[key], "tcp", $_GET['resolv_services']). "</td><td>". $cp[value]. " </td></tr>";
  }
}
?>
</table>

<table>
  <caption>Src ports</caption>
  <tr>
    <th>port</th>
    <th>count</th>
  </tr>
<?php
for ($i = 0; $i < 10; $i++) {
  if ($stats) {
    $cp = each($stats['sourceport']);
    if (!is_array($cp)) {
      break;
    }
    print "<tr><td>". $cp[key]. "</td><td>". $cp[value]. " </td></tr>";
  }
}
?>
</table>

<table>
  <caption>Destinations</caption>
  <tr>
    <th>address</th>
    <th>count</th>
  </tr>
<?php
for ($i = 0; $i < 10; $i++) {
  if ($stats) {
    $cp = each($stats['destination']);
    if (!is_array($cp)) {
      break;
    }
    print "<tr><td>". get_host_by_address($cp[key], $_GET['resolv_hosts']). "</td><td>". $cp[value]. " </td></tr>";
  }
}
?>
</table>

<table>
  <caption>Sources</caption>
  <tr>
    <th>address</th>
    <th>count</th>
  </tr>
<?php
for ($i = 0; $i < 10; $i++) {
  if ($stats) {
    $cp = each($stats['source']);
    if (!is_array($cp)) {
      break;
    }
    print "<tr><td>". get_host_by_address($cp[key], $_GET['resolv_hosts']). "</td><td>". $cp[value]. " </td></tr>";
  }
}
?>
</table>

<table>
  <caption>Protocols</caption>
  <tr>
    <th>proto</th>
    <th>count</th>
  </tr>
<?php
for ($i = 0; $i < 10; $i++) {
  if ($stats) {
    $cp = each($stats['proto']);
    if (!is_array($cp)) {
      break;
    }
    print "<tr><td>". $cp[key]. "</td><td>". $cp[value]. " </td></tr>";
  }
}
?>
</table>

</div>

<div id="main" style="width: 85%;">

<form>
  <label for="count">Display the last</label>
  <input type="text" id="count" name="count" value="<?php print $count;?>" style="text-align: right;" size="6" /> log lines. 
  <label><input type="checkbox" name="resolv_hosts" <?php print  ($_GET['resolv_hosts'] ? "checked=\"checked\"" : "") ;?> /> Resolve Hosts.</label>
  <label><input type="checkbox" name="resolv_services" <?php print  ($_GET['resolv_services'] ? "checked=\"checked\"" : "") ;?> /> Resolve Services.</label>

  <label for="reload">Reload page every</label>
  <input type="text" id="reload" name="reload" value="<?php print  $_GET['reload'];?>" size="4" style="text-align: right" />s

  <input type="submit" id="submit" value="go" />
  <p>
  <label for="regexp">Tcpdump match</label>
  <input type="text" id="regexp" name="regexp" value="<?php print stripslashes($_GET['regexp']);?>" size="100" style="width: 500px;"/>
  </p>
</form>

<table>
  <tr>
    <th>Line</th>
    <th>Date</th>
    <th>Time</th>
    <th>Rule</th>
    <th>Type</th>
    <th>Dir</th>
    <th>If</th>
    <th>Proto</th>
    <th>Source</th>
    <th>Port</th>
    <th>Dest</th>
    <th>Port</th>
    <th>Flags</th>
  </tr>
<?php
foreach($logs as $log) {
	$Rule= $log['type'];
	if (stripos($Rule, 'block') !== FALSE) {
		echo '<tr class="red">';
	}
	else if (stripos($Rule, 'pass') !== FALSE) {
		echo '<tr class="green">';
	}
	else if (stripos($Rule, 'match') !== FALSE) {
		echo '<tr class="yellow">';
	}
	else {
		echo '<tr>';
	}
	?>
    <td><?php print ++$line_no ?></td>
    <td><?php print $log ['month']. " ". $log['day'];?></td>
    <td><?php print $log['time'] ?></td>
    <td><?php print $log['rule'] ?></td>
    <td><?php print $log['type'] ?></td>
    <td><?php print $log['direction'] ?></td>
    <td><?php print $log['interface'] ?></td>
    <td><?php print $log['proto'] ?></td>
    <td><?php print get_host_by_address($log['source'], $_GET['resolv_hosts']) ?></td>
    <td><?php print $log['sourceport'] ?></td>
    <td><?php print get_host_by_address($log['destination'], $_GET['resolv_hosts']) ?></td>
    <td><?php print get_service_by_port($log['destinationport'], $log['proto'], $_GET['resolv_services']) ?></td>
    <td><?php print $log['flags'] ?></td>
  </tr>
<?php
}
?>
</table>
</div>
<?php
require_once($VIEW_PATH.'footer.php');
?>
<?php $_SESSION['dns_cache'] = $dns_cache; $_SESSION['service_cache'] = $service_cache ?>
