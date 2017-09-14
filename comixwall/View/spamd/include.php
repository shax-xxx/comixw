<?php
/* $ComixWall: include.php,v 1.15 2009/11/16 12:05:38 soner Exp $ */

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

class Spamd extends View
{
	public $Model= 'spamd';
	public $Layout= 'spamd';
	
	function Spamd()
	{
		$this->LogsHelpMsg= _HELPWINDOW('Spamd logs connections from spam sources and greylisted clients. Also recorded is length of time the client remained connected to spamd.');
	}
}

$View= new Spamd();

/** Parses spamd database output line.
 *
 * @param[in]	$logline	DB output line to parse.
 * @param[out]	$cols		Parser output, parsed fields.
 *
 * @todo Should return something?
 * @todo Move this to Model
 */
function ParseSpamdDBLine($logline, &$cols)
{
	global $Re_Ip;

	$re_srcip= "($Re_Ip)";
	$re_num= '(\d+)';

	//WHITE|89.37.208.82|||1173738113|1173739941|1176851242|3|1
	$re= "/^WHITE\|$re_srcip\|\|\|$re_num\|$re_num\|$re_num\|$re_num\|$re_num$/";
	if (preg_match($re, $logline, $match)) {
		$cols['IP']= $match[1];
		$cols['First']= date('d.m.Y H:i', $match[2]);
		$cols['Listed']= date('d.m.Y H:i', $match[3]);
		$cols['Expire']= date('d.m.Y H:i', $match[4]);
		$cols['#Blocked']= $match[5];
		$cols['#Passed']= $match[6];
	}

	$re_domain= '([^|]+)';
	$re_email= '([^|]+)';

	//GREY|83.13.153.59|mail.optimeyes.com|<rifling@optimeyes.com>|<info@comixpbx.com>|1176311682|1176326082|1176326082|1|0
	$re= "/^GREY\|$re_srcip\|$re_domain\|$re_email\|$re_email\|$re_num\|$re_num\|$re_num\|$re_num\|$re_num$/";
	if (preg_match($re, $logline, $match)) {
		$cols['IP']= $match[1];
		$cols['From']= wordwrap(str_replace(array('<', '>'), '', $match[3]), 30, '<br />', TRUE);
		$cols['To']= wordwrap(str_replace(array('<', '>'), '', $match[4]), 30, '<br />', TRUE);
		$cols['First']= date('d.m.Y H:i', $match[5]);
		$cols['Listed']= date('d.m.Y H:i', $match[6]);
		$cols['Expire']= date('d.m.Y H:i', $match[7]);
		$cols['#Blocked']= $match[8];
		$cols['#Passed']= $match[9];
	}
	$cols['Log']= '';
}

/** Displays spamd DB output lines.
 *
 * @param[in]	$logline	DB output to parse.
 * @param[in]	$linenum	Line number to print as the first column.
 */
function PrintSpamdDBLine($logline, $linenum)
{
	ParseSpamdDBLine($logline, $cols);

	echo '<tr>';
	PrintLogCols($linenum, $cols);
	echo '</tr>';
}
?>
