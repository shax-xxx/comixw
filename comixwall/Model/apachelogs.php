<?php
/* $ComixWall: apachelogs.php,v 1.11 2009/11/23 08:47:43 soner Exp $ */

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
 * Apache access logs.
 */

require_once($MODEL_PATH.'apache.php');

class Apachelogs extends Apache
{
	public $Name= 'apachelogs';
	
	public $LogFile= '/var/www/logs/access_log';

	function ParseLogLine($logline, &$cols)
	{
		global $Re_Ip;
	
		//10.0.0.11 - - [26/Sep/2009:03:03:45 +0300] "GET /symon/graph.php?61ea9e7cb820b19cc116fc5eb37490de HTTP/1.1" 200 31949
		//10.0.0.11 - - [26/Sep/2009:03:04:23 +0300] "GET /images/run.png HTTP/1.1" 304 -
		//10.0.0.11 - - [25/Sep/2009:20:05:54 +0300] "POST /snortips/conf.php HTTP/1.1" 200 11063
		//10.0.0.11 - - [08/Oct/2009:12:56:03 +0300] "GET / HTTP/1.1" 302 5
		$datetime= '\[(\d+\/\w+\/\d+):(\d+:\d+:\d+)\s*[\w+]*\]';
		$ip= "($Re_Ip)";
		$mtd= '(GET|POST|\S+)';
		$link= '(\S*)';
		$code= '(\d+)';
		$size= '(\d+|-)';

		$re= "/^$ip\s+.*\s+$datetime\s+\"$mtd\s+$link\s+HTTP\/\d+\.\d+\"\s+$code\s+$size$/";
		if (preg_match($re, $logline, $match)) {
			$cols['IP']= $match[1];
			$cols['Date']= $match[2];
			$cols['Time']= $match[3];
			$cols['DateTime']= $cols['Date'].' '.$cols['Time'];;
			$cols['Mtd']= $match[4];
			$cols['Link']= $match[5];
			$cols['Code']= $match[6];
			$cols['Size']= $match[7];
			if ($cols['Size'] == '-') {
				$cols['Size']= 0;
			}
			return TRUE;
		}
		else if ($this->ParseSyslogLine($logline, $cols)) {
			$cols['DateTime']= $cols['Date'].' '.$cols['Time'];
			$cols['IP']= _('NA');
			return TRUE;
		}
		return FALSE;
	}

	function PostProcessCols(&$cols)
	{
		// Exclude encoded image names, but include submenus
		preg_match('/^([^?]+(\?submenu=.*|)).*$/', $cols['Link'], $match);
		$cols['Link']= $match[1];
	}

	function GetDateRegexp($date)
	{
		global $MonthNames;
		
		// Match all years
		$re= '.*';
		if ($date['Month'] == '') {
			$re= '.*\/'.$re;
		}
		else {
			$re= $MonthNames[$date['Month']].'\/'.$re;
			if ($date['Day'] == '') {
				$re= '.*\/'.$re;
			}
			else {
				$re= $date['Day'].'\/'.$re;
			}
		}
		return $re;
	}
}
?>
