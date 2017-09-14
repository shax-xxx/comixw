<?php
/* $ComixWall: include.accesslogs.php,v 1.7 2009/11/16 12:05:36 soner Exp $ */

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

class E2guardianlogs extends View
{
	public $Model= 'e2guardianlogs';

	function e2guardianlogs()
	{
		$this->LogsHelpMsg= _HELPWINDOW('Among web filter log messages are page denials, virus scan results, denial bypasses or exceptions. However, some details can be found in HTTP proxy logs only, such as the sizes of file downloads if the download manager is engaged.');
	}
	
	/** Builds DG specific string from $date.
	 *
	 * The datetimes in log lines are different for each module.
	 * Does the opposite of FormatDateArray()
	 *
	 * @param[in]	$date	array Datetime struct
	 */
	function FormatDate($date)
	{
		return date('Y').'.'.$date['Month'].'.'.$date['Day'];
	}

	/** Builds DG specific $date from string.
	 */
	function FormatDateArray($datestr, &$date)
	{
		global $MonthNumbers;

		if (preg_match('/^(\d+)\.(\d+)\.(\d+)$/', $datestr, $match)) {
			$date['Month']= $match[2];
			$date['Day']= $match[3];
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
		$link= $cols['Link'];
		if (preg_match('|^(http://[^/]*)|', $cols['Link'], $match)) {
			$linkbase= $match[1];
		}
		$cols['Link']= '<a href="'.$link.'" title="'.$link.'">'.wordwrap($linkbase, 40, '<br />', TRUE).'</a>';
		$cols['Scan']= wordwrap($cols['Scan'], 40, '<br />', TRUE);
	}
}

$View= new e2guardianlogs();
?>
