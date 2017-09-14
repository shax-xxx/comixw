<?php
/* $ComixWall: vars.php,v 1.59 2009/11/21 21:55:58 soner Exp $ */

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
 * Mostly menu related structures for View.
 * 
 * @attention TAB size throughout this source code is 4 spaces.
 * @bug	There is partial PHP support in Doxygen, thus there are many issues.
 */

$ROOT= dirname(dirname(dirname(__FILE__)));

/// Root directory of the project tree obtained from Apache.
$VIEW_PATH= $_SERVER['DOCUMENT_ROOT'].'/';

$MODEL_PATH= $ROOT.'/Model/';

require_once($ROOT.'/lib/defs.php');
require_once($ROOT.'/lib/lib.php');
require_once($ROOT.'/lib/setup.php');

require_once('setup.php');

require_once($MODEL_PATH.'model.php');

/// Path to image files used in help boxes and credits page.
$IMG_PATH= '/images/';

/// PF module absolute path.
$PF_PATH= $VIEW_PATH.'pf/';

// For pfw
$inst_dir= $PF_PATH;
$dh= opendir("$inst_dir/include");
while (FALSE !== ($filename= readdir($dh))) {
	if (!preg_match('/^\./', $filename) && !preg_match('/^CVS/', $filename) && !preg_match('/~$/', $filename)) {
		require_once("$inst_dir/include/$filename");
	}
}
closedir($dh);

/// Default behaviour is no reload for pages.
$Reload= FALSE;

/// Left menu items with captions and user permissions.
$CW_MODULES = array(
    'system' => array(
        'SYS_MODULES' => array(
            'Name' => _MENU('SYSTEM'),
            'Perms' => $ALL_USERS,
    		),
		),
    'pf' => array(
        'PF_MODULES' => array(
            'Name' => _MENU('PACKET FILTER'),
            'Perms' => $ALL_USERS,
    		),
		),
    'e2guardian' => array(
        'E2_MODULES' => array(
            'Name' => _MENU('WEB FILTER'),
            'Perms' => $ALL_USERS,
    		),
		),
    'squid' => array(
        'SQUID_MODULES' => array(
            'Name' => _MENU('HTTP PROXY'),
            'Perms' => $ALL_USERS,
    		),
		),
    'snort' => array(
        'IDS_MODULES' => array(
            'Name' => _MENU('IDS'),
            'Perms' => $ALL_USERS,
    		),
		),
    'snortips' => array(
        'IPS_MODULES' => array(
            'Name' => _MENU('IPS'),
            'Perms' => $ALL_USERS,
    		),
		),
    'clamav' => array(
        'VIRUS_MODULES' => array(
            'Name' => _MENU('VIRUS FILTER'),
            'Perms' => $ALL_USERS,
    		),
		),
    'dhcpd' => array(
        'DHCP_MODULES' => array(
            'Name' => _MENU('DHCP'),
            'Perms' => $ALL_USERS,
    		),
		),
    'named' => array(
        'DNS_MODULES' => array(
            'Name' => _MENU('DNS'),
            'Perms' => $ALL_USERS,
    		),
		),
    'openvpn' => array(
        'OPENVPN_MODULES' => array(
            'Name' => _MENU('OPENVPN'),
            'Perms' => $ALL_USERS,
    		),
		),
    'openssh' => array(
        'SSH_MODULES' => array(
            'Name' => _MENU('OPENSSH'),
            'Perms' => $ALL_USERS,
    		),
		),
    'ftp-proxy' => array(
        'FTP_MODULES' => array(
            'Name' => _MENU('FTP PROXY'),
            'Perms' => $ALL_USERS,
    		),
		),
    'dante' => array(
        'SOCKS_MODULES' => array(
            'Name' => _MENU('SOCKS PROXY'),
            'Perms' => $ALL_USERS,
    		),
		),
    'apache' => array(
        'APACHE_MODULES' => array(
            'Name' => _MENU('WEB SERVER'),
            'Perms' => $ALL_USERS,
    		),
		),
    'monitoring' => array(
        'MONITORING_MODULES' => array(
            'Name' => _MENU('MONITORING'),
            'Perms' => $ALL_USERS,
    		),
		),
    'info' => array(
        'INFO_MODULES' => array(
            'Name' => _MENU('INFORMATION'),
            'Perms' => $ALL_USERS,
    		),
		),
);
/// System general top menu.
$SYS_MODULES = array(
    'info.php' => array(
        'Name' => _MENU('Info'),
        'Perms' => $ALL_USERS,
		),
    'graphs.php' => array(
        'Name' => _MENU('Graphs'),
        'Perms' => $ALL_USERS,
		),
    'logs.php' => array(
        'Name' => _MENU('Logs'),
        'Perms' => $ALL_USERS,
		),
    'conf.php' => array(
        'Name' => _MENU('Config'),
        'Perms' => $ADMIN,
		),
    'procs.php' => array(
        'Name' => _MENU('Processes'),
        'Perms' => $ALL_USERS,
		),
);
/// Information top menu.
$INFO_MODULES = array(
    'help.php' => array(
        'Name' => _MENU('Help'),
        'Perms' => $ALL_USERS,
		),
    'docs.php' => array(
        'Name' => _MENU('Source Docs'),
        'Perms' => $ALL_USERS,
		),
    'credits.php' => array(
        'Name' => _MENU('Credits'),
        'Perms' => $ALL_USERS,
		),
);
/// Packet Filter top menu.
$PF_MODULES = array(
    'info.php' => array(
        'Name' => _MENU('Info'),
        'Perms' => $ALL_USERS,
		),
    'stats.php' => array(
        'Name' => _MENU('Statistics'),
        'Perms' => $ALL_USERS,
		),
    'graphs.php' => array(
        'Name' => _MENU('Graphs'),
        'Perms' => $ALL_USERS,
		),
    'logs.php' => array(
        'Name' => _MENU('Logs'),
        'Perms' => $ALL_USERS,
		),
    'logsreport.php' => array(
        'Name' => _MENU('Logs Report'),
        'Perms' => $ALL_USERS,
		),
    'states.php' => array(
        'Name' => _MENU('States'),
        'Perms' => $ALL_USERS,
		),
    'queues.php' => array(
        'Name' => _MENU('Queues'),
        'Perms' => $ALL_USERS,
		),
    'conf.php' => array(
        'Name' => _MENU('Config'),
        'Perms' => $ADMIN,
		),
    'packetfilter' => array(
        'Name' => _MENU('Rules'),
        'Perms' => $ADMIN,
		),
    'status' => array(
        'Name' => _MENU('Status'),
        'Perms' => $ALL_USERS,
		),
);
/// E2Guardian Web Filter top menu.
$E2_MODULES = array(
    'info.php' => array(
        'Name' => _MENU('Info'),
        'Perms' => $ALL_USERS,
		),
    'stats.php' => array(
        'Name' => _MENU('Statistics'),
        'Perms' => $ALL_USERS,
		),
    'graphs.php' => array(
        'Name' => _MENU('Graphs'),
        'Perms' => $ALL_USERS,
		),
    'logs.php' => array(
        'Name' => _MENU('Logs'),
        'Perms' => $ALL_USERS,
		),
    'accesslogs.php' => array(
        'Name' => _MENU('Access Logs'),
        'Perms' => $ALL_USERS,
		),
    'general.php' => array(
        'Name' => _MENU('General'),
        'Perms' => $ADMIN,
		),
    'conf.php' => array(
        'Name' => _MENU('Groups'),
        'Perms' => $ADMIN,
		),
    'lists.php' => array(
        'Name' => _MENU('Lists'),
        'Perms' => $ADMIN,
		),
    'cats.php' => array(
        'Name' => _MENU('Categories'),
        'Perms' => $ADMIN,
		),
);
/// ClamAV top menu.
$VIRUS_MODULES = array(
    'info.php' => array(
        'Name' => _MENU('Info'),
        'Perms' => $ALL_USERS,
		),
    'graphs.php' => array(
        'Name' => _MENU('Graphs'),
        'Perms' => $ALL_USERS,
		),
    'logs.php' => array(
        'Name' => _MENU('Logs'),
        'Perms' => $ALL_USERS,
		),
    'freshclamlogs.php' => array(
        'Name' => _MENU('DB Logs'),
        'Perms' => $ALL_USERS,
		),
    'conf.php' => array(
        'Name' => _MENU('Config'),
        'Perms' => $ADMIN,
		),
);
/// Snort intrusion detection top menu.
$IDS_MODULES = array(
    'info.php' => array(
        'Name' => _MENU('Info'),
        'Perms' => $ALL_USERS,
		),
    'stats.php' => array(
        'Name' => _MENU('Statistics'),
        'Perms' => $ALL_USERS,
		),
    'graphs.php' => array(
        'Name' => _MENU('Graphs'),
        'Perms' => $ALL_USERS,
		),
    'logs.php' => array(
        'Name' => _MENU('Logs'),
        'Perms' => $ALL_USERS,
		),
    'alerts.php' => array(
        'Name' => _MENU('Alerts'),
        'Perms' => $ALL_USERS,
		),
    'conf.php' => array(
        'Name' => _MENU('Config'),
        'Perms' => $ADMIN,
		),
);
/// Snort intrusion prevention top menu.
$IPS_MODULES = array(
    'info.php' => array(
        'Name' => _MENU('Info'),
        'Perms' => $ALL_USERS,
		),
    'stats.php' => array(
        'Name' => _MENU('Statistics'),
        'Perms' => $ALL_USERS,
		),
    'graphs.php' => array(
        'Name' => _MENU('Graphs'),
        'Perms' => $ALL_USERS,
		),
    'logs.php' => array(
        'Name' => _MENU('Logs'),
        'Perms' => $ALL_USERS,
		),
    'conf.php' => array(
        'Name' => _MENU('Config'),
        'Perms' => $ADMIN,
		),
);
/// Squid http proxy top menu.
$SQUID_MODULES = array(
    'info.php' => array(
        'Name' => _MENU('Info'),
        'Perms' => $ALL_USERS,
		),
    'stats.php' => array(
        'Name' => _MENU('Statistics'),
        'Perms' => $ALL_USERS,
		),
    'graphs.php' => array(
        'Name' => _MENU('Graphs'),
        'Perms' => $ALL_USERS,
		),
    'logs.php' => array(
        'Name' => _MENU('Logs'),
        'Perms' => $ALL_USERS,
		),
    'conf.php' => array(
        'Name' => _MENU('Config'),
        'Perms' => $ADMIN,
		),
);
/// Apache web server top menu.
$APACHE_MODULES = array(
    'info.php' => array(
        'Name' => _MENU('Info'),
        'Perms' => $ALL_USERS,
		),
    'stats.php' => array(
        'Name' => _MENU('Statistics'),
        'Perms' => $ALL_USERS,
		),
    'graphs.php' => array(
        'Name' => _MENU('Graphs'),
        'Perms' => $ALL_USERS,
		),
    'logs.php' => array(
        'Name' => _MENU('Logs'),
        'Perms' => $ALL_USERS,
		),
    'accesslogs.php' => array(
        'Name' => _MENU('Access Logs'),
        'Perms' => $ALL_USERS,
		),
    'cwwuilogs.php' => array(
        'Name' => _MENU('WUI Logs'),
        'Perms' => $ADMIN,
		),
    'cwclogs.php' => array(
        'Name' => _MENU('CWC Logs'),
        'Perms' => $ADMIN,
		),
    'conf.php' => array(
        'Name' => _MENU('Config'),
        'Perms' => $ADMIN,
		),
);
/// Dante SOCKS proxy top menu.
$SOCKS_MODULES = array(
    'info.php' => array(
        'Name' => _MENU('Info'),
        'Perms' => $ALL_USERS,
		),
    'graphs.php' => array(
        'Name' => _MENU('Graphs'),
        'Perms' => $ALL_USERS,
		),
    'logs.php' => array(
        'Name' => _MENU('Logs'),
        'Perms' => $ALL_USERS,
		),
);
/// FTP proxy top menu.
$FTP_MODULES = array(
    'info.php' => array(
        'Name' => _MENU('Info'),
        'Perms' => $ALL_USERS,
		),
    'stats.php' => array(
        'Name' => _MENU('Statistics'),
        'Perms' => $ALL_USERS,
		),
    'graphs.php' => array(
        'Name' => _MENU('Graphs'),
        'Perms' => $ALL_USERS,
		),
    'logs.php' => array(
        'Name' => _MENU('Logs'),
        'Perms' => $ALL_USERS,
		),
);
/// DNS server top menu.
$DNS_MODULES = array(
    'info.php' => array(
        'Name' => _MENU('Info'),
        'Perms' => $ALL_USERS,
		),
    'stats.php' => array(
        'Name' => _MENU('Statistics'),
        'Perms' => $ALL_USERS,
		),
    'graphs.php' => array(
        'Name' => _MENU('Graphs'),
        'Perms' => $ALL_USERS,
		),
    'logs.php' => array(
        'Name' => _MENU('Logs'),
        'Perms' => $ALL_USERS,
		),
    'conf.php' => array(
        'Name' => _MENU('Config'),
        'Perms' => $ADMIN,
		),
);
/// DHCP server top menu.
$DHCP_MODULES = array(
    'info.php' => array(
        'Name' => _MENU('Info'),
        'Perms' => $ALL_USERS,
		),
    'graphs.php' => array(
        'Name' => _MENU('Graphs'),
        'Perms' => $ALL_USERS,
		),
    'logs.php' => array(
        'Name' => _MENU('Logs'),
        'Perms' => $ALL_USERS,
		),
    'conf.php' => array(
        'Name' => _MENU('Config'),
        'Perms' => $ADMIN,
		),
);
/// OpenSSH server top menu.
$SSH_MODULES = array(
    'info.php' => array(
        'Name' => _MENU('Info'),
        'Perms' => $ALL_USERS,
		),
    'stats.php' => array(
        'Name' => _MENU('Statistics'),
        'Perms' => $ALL_USERS,
		),
    'graphs.php' => array(
        'Name' => _MENU('Graphs'),
        'Perms' => $ALL_USERS,
		),
    'logs.php' => array(
        'Name' => _MENU('Logs'),
        'Perms' => $ALL_USERS,
		),
    'conf.php' => array(
        'Name' => _MENU('Config'),
        'Perms' => $ADMIN,
		),
);
/// OpenVPN module top menu.
$OPENVPN_MODULES = array(
    'info.php' => array(
        'Name' => _MENU('Info'),
        'Perms' => $ALL_USERS,
		),
    'graphs.php' => array(
        'Name' => _MENU('Graphs'),
        'Perms' => $ALL_USERS,
		),
    'logs.php' => array(
        'Name' => _MENU('Logs'),
        'Perms' => $ALL_USERS,
		),
    'conf.php' => array(
        'Name' => _MENU('Config'),
        'Perms' => $ADMIN,
		),
);
/// Monitoring module top menu.
$MONITORING_MODULES = array(
    'info.php' => array(
        'Name' => _MENU('Info'),
        'Perms' => $ALL_USERS,
		),
    'logs.php' => array(
        'Name' => _MENU('Logs'),
        'Perms' => $ALL_USERS,
		),
    'conf.php' => array(
        'Name' => _MENU('Config'),
        'Perms' => $ADMIN,
		),
);

/** Types of help boxes.
 *
 * @param name	Title string
 * @param icon	Image to display on top-left corner
 */
$HelpBoxTypes = array(
    'INFO' => array(
        'name' => _TITLE('INFORMATION'),
        'icon' => 'info.png'
		),
    'ERROR' => array(
        'name' => _TITLE('ERROR'),
        'icon' => 'error.png'
		),
    'WARN' => array(
        'name' => _TITLE('WARNING'),
        'icon' => 'warning.png'
		),
);

/// Used as arg to PrintProcessTable() to print the number of processes at the top.
define('PRINT_COUNT', TRUE);

require_once('libauth.php');

if ($_SESSION['Timeout']) {
	if ($_SESSION['Timeout'] <= time()) {
		LogUserOut('Session expired');
	}
}
else {
	$_SESSION['Timeout']= time() + $SessionTimeout;
}

if (!isset($_SESSION['USER']) || $_SESSION['USER'] == 'loggedout') {
	header('Location: https://'.$_SERVER['SERVER_ADDR'].'/index.php');
	exit;
}

require_once('libwui.php');
?>
