<?php
/* $ComixWall: include.accesslogs.php,v 1.3 2009/11/10 18:47:50 soner Exp $ */

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

require_once('../lib/vars.php');
require_once('../lib/view.php');

class Httpdlogs extends View
{
	public $Model= 'httpdlogs';

	function Httpdlogs()
	{
		$this->LogsHelpMsg= _HELPWINDOW('These are access logs of the httpd web server. Logs contain logged-in users, client IP addresses, and pages accessed.');
	}
	
	function FormatDate($date)
	{
		global $MonthNames;
		
		return $date['Day'].'/'.$MonthNames[$date['Month']].'/'.date('Y');
	}

	function FormatDateArray($datestr, &$date)
	{
		global $MonthNumbers;

		if (preg_match('/^(\d+)\/(\w+)\/(\d+)$/', $datestr, $match)) {
			$date['Day']= $match[1];
			$date['Month']= $MonthNumbers[$match[2]];
			return TRUE;
		}
		else if (preg_match('/(\w+)\s+(\d+)/', $datestr, $match)) {
			if (array_key_exists($match[1], $MonthNumbers)) {
				$date['Month']= sprintf('%02d', $MonthNumbers[$match[1]]);
				$date['Day']= sprintf('%02d', $match[2]);
				return TRUE;
			}
		}
		return FALSE;
	}
	
	function FormatLogCols(&$cols)
	{
		$cols['Link']= wordwrap($cols['Link'], 50, '<br />', TRUE);
	}
}

$View= new Httpdlogs();
?>
