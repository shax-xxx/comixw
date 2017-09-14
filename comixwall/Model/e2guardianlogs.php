<?php
/* $ComixWall: e2guardianlogs.php,v 1.14 2009/11/16 12:05:37 soner Exp $ */

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
 * e2guardian access logs.
 */

require_once($MODEL_PATH.'e2guardian.php');

class E2guardianlogs extends E2guardian
{
	public $Name= 'e2guardianlogs';
	
	public $LogFile= '/var/log/e2guardian/access.log';

	function ParseLogLine($logline, &$cols)
	{
		global $Re_Ip;

		$re_datetime= '(\d+\.\d+\.\d+) (\d+:\d+:\d+)';
		$re_pip= "($Re_Ip|-)";
		$re_srcip= "($Re_Ip)";
		$re_link= '(http:\/\/[^ \/]*|https:\/\/[^ \/]*)(\S*)';
		$re_result= '(.*|)';
		$re_mtd= '(GET|POST|HEAD|PROPFIND|PROPPATCH|CONNECT)';
		$re_size= '(\d+)';
		$re_ttl= '(-{0,1}\d+)';
		$re_rest= '(.*)';
		
		$re_nonempty= '(\S+|)';
		$re_num= '(-{0,1}\d+|)';
		$re_restorempty= '(.*|)';
		
		// 2007.12.29 20:46:18 - 192.168.1.33 http://URL.com *DENIED* Banned site: URL.com GET 0 0 Cleaning Domains 1 403 -   -
		// 2007.12.29 20:10:15 - 192.168.1.34 http://URL.com  GET 1632 0  1 404 text/html   -
		// 2007.12.29 20:09:57 - 192.168.1.34 http://URL.com *SCANNED*  GET 5137 -20  1 200 text/html   -
		$re= "/^$re_datetime\s+$re_pip\s+$re_srcip\s+$re_link\s+$re_result\s+$re_mtd\s+$re_size\s+$re_ttl\s+$re_restorempty\s*$re_num\s+$re_num\s+$re_rest$/";
		if (preg_match($re, $logline, $match)) {
			$cols['Date']= $match[1];
			$cols['Time']= $match[2];
			$cols['IPsrc']= $match[3];
			$cols['IP']= $match[4];
			$cols['Link']= $match[5].$match[6];
			$cols['Scan']= $match[7];
			$cols['Mtd']= $match[8];
			$cols['Size']= $match[9];
			$cols['TTL']= $match[10];
			$log= $match[11].' '.$match[12].' '.$match[13].' '.$match[14];
			/// @todo What are the other category names?
			if (preg_match('/(\S+)\s+(Domains|URLs|Sites|Phrases)/', $log, $cats)) {
				$cols['Cat']= $cats[1];
			}
			$cols['Log']= $log;
			return TRUE;
		}
		else {
			$cols['IP']= _('Unknown');
			$cols['Link']= _('Unknown');

			$re= "/^$re_datetime$re_result\s+$re_mtd\s+$re_nonempty\s+$re_nonempty\s+$re_nonempty\s+$re_link\s+$re_rest$/";
			if (preg_match($re, $logline, $match)) {
				$cols['Date']= $match[1];
				$cols['Time']= $match[2];
				$cols['Mtd']= $match[4];
				$cols['Scan']= $match[6].' '.$match[3].' '.$match[5].' '.$match[7];
				$cols['Link']= $match[8].$match[9];
				$cols['Log']= $match[10];
				return TRUE;
			}
			else if ($this->ParseSyslogLine($logline, $cols)) {
				$cols['IP']= _('NA');
				$cols['DateTime']= $cols['Date'].' '.$cols['Time'];
				return TRUE;
			}
		}
		return FALSE;
	}
	
	function PostProcessCols(&$cols)
	{
		preg_match('|http://([^/]*)|', $cols['Link'], $match);
		$cols['Link']= $match[1];

		if (preg_match('/(\d+)\.(\d+)\.(\d+)/', $cols['Date'], $match)) {
			$cols['Date']= $match[1].'.'.sprintf('%02d', $match[2]).'.'.sprintf('%02d', $match[3]);
		}

		$time= explode(':', $cols['Time'], 3);
		$cols['Time']= sprintf('%02d', $time[0]).':'.sprintf('%02d', $time[1]).':'.sprintf('%02d', $time[2]);
	}
	
	function GetDateRegexp($date)
	{
		// Match all years
		$re= '.*\.';
		if ($date['Month'] == '') {
			$re.= '.*';
		}
		else {
			$re.= $date['Month'].'\.';
			if ($date['Day'] == '') {
				$re.= '.*';
			}
			else {
				$re.= $date['Day'];
			}
		}
		return $re;
	}
}
?>
