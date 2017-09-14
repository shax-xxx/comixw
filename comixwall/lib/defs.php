<?php
/* $ComixWall: defs.php,v 1.25 2009/12/07 14:11:50 soner Exp $ */

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
 * Common variables, arrays, and constants.
 */

/// Project version.
define('VERSION', '4.6');

/// Syslog priority strings.
$LOG_PRIOS= array(
	'LOG_EMERG',	// system is unusable
	'LOG_ALERT',	// action must be taken immediately
	'LOG_CRIT',		// critical conditions
	'LOG_ERR',		// error conditions
	'LOG_WARNING',	// warning conditions
	'LOG_NOTICE',	// normal, but significant, condition
	'LOG_INFO',		// informational message
	'LOG_DEBUG',	// debug-level message
	);

/// Superuser
$ADMIN= array('admin');
/// Unprivileged user who can modify any configuration
$USER= array('user');
/// All the valid Apache users
$ALL_USERS= array_merge($ADMIN, $USER);

/** Locale definitions used by both View and Controller.
 *
 * It is recommended that all translations use UTF-8 codeset.
 *
 * @param Name		string Title string
 * @param Codeset	string Locale codeset
 */
$LOCALES = array(
    'en_EN' => array(
        'Name' => _('English'),
        'Codeset' => 'UTF-8'
		),
    'tr_TR' => array(
        'Name' => _('Turkish'),
        'Codeset' => 'UTF-8'
		),
    'sp_SP' => array(
        'Name' => _('Spanish'),
        'Codeset' => 'UTF-8'
		),
    'ru_RU' => array(
        'Name' => _('Russian'),
        'Codeset' => 'UTF-8'
		),
    'zh_CN' => array(
        'Name' => _('Chinese simplified'),
        'Codeset' => 'UTF-8'
		),
    'nl_NL' => array(
        'Name' => _('Dutch'),
        'Codeset' => 'UTF-8'
		),
    'fr_FR' => array(
        'Name' => _('French'),
        'Codeset' => 'UTF-8'
		),
);

/// Used for months translation from number to string.
$MonthNames= array(
	'01' => 'Jan',
	'02' => 'Feb',
	'03' => 'Mar',
	'04' => 'Apr',
	'05' => 'May',
	'06' => 'Jun',
	'07' => 'Jul',
	'08' => 'Aug',
	'09' => 'Sep',
	'10' => 'Oct',
	'11' => 'Nov',
	'12' => 'Dec',
	);

/// Used for months translation from string to number.
$MonthNumbers= array(
	'Jan' => '01',
	'Feb' => '02',
	'Mar' => '03',
	'Apr' => '04',
	'May' => '05',
	'Jun' => '06',
	'Jul' => '07',
	'Aug' => '08',
	'Sep' => '09',
	'Oct' => '10',
	'Nov' => '11',
	'Dec' => '12',
	);

/// Type definitions for config settings as PREs
/// @todo Fix leading 0's problem(s)
define('UINT_0_2', '[0-2]');
define('STR_on_off', 'on|off');
define('STR_On_Off', 'On|Off');
define('STR_SING_QUOTED', '\'[^\']*\'');
define('STR_yes_no', 'yes|no');
define('UINT', '[0-9]+');
define('INT_M1_0_UP', '-1|[0-9]+');
define('UINT_0_1', '0|1');
define('INT_M1_0_3', '-1|[0-3]');
define('UINT_0_3', '[0-3]');
define('UINT_1_4', '[0-4]');
define('IP', '\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}');
define('IPorNET', '(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})|(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\/\d{1,2})');
define('PORT', '[0-9]+');
define('FLOAT', '[0-9]+|([0-9]+\.[0-9]+)');
define('CHAR', '.');

/// Common regexps.
/// @todo Find a proper regexp for IPv4 addresses, this is too general.
$Re_Ip= '\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}';
$Re_Net= "$Re_Ip\/\d{1,2}";
$Re_IpPort= '\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}:\d{1,5}';
/// @todo $num and $range need full testing. Define $port.
$preIPOctet= '(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])';
$preIPRange= '(\d|[1-2]\d|3[0-2])';
$preIP= "$preIPOctet\\.$preIPOctet\\.$preIPOctet\\.$preIPOctet";
$preNet= "$preIP\/$preIPRange";

/// General tcpdump command used everywhere.
/// @todo All system binaries called should be defined like this.
$TCPDUMP= '/usr/sbin/tcpdump -nettt -r';

/// Models to get statuses
$ModelsToStat= array(
	'pf',
	'dansguardian',
	'squid',
	'snort',
	'snortips',
	'spamassassin',
	'clamav',
	'p3scan',
	'smtp-gated',
	'dhcpd',
	'named',
	'openvpn',
	'openssh',
	'ftp-proxy',
	'dante',
	'spamd',
	'apache',
	'monitoring',
	);
?>
