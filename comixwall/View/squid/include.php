<?php
/* $ComixWall: include.php,v 1.16 2009/11/15 21:26:14 soner Exp $ */

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

class Squid extends View
{
	public $Model= 'squid';
	public $Layout= 'squid';

	function Squid()
	{
		$this->LogsHelpMsg= _HELPWINDOW('Source IP of all the requests on this page is the loopback interface, i.e. 127.0.0.1, hence not listed here. If the HTTP proxy is configured as non-caching proxy, you should see TCP_MISS on the Cache column.');
		$this->ConfHelpMsg= _HELPWINDOW('By default, the Web Filter connects to the HTTP proxy over the loopback interface and at port 3128. If you do not want to use the Web Filter, you can change this Proxy IP:Port setting to the internal IP address of the system and port 8080. When you stop the Web Filter, all requests from the internal network should be directed to the HTTP proxy.');
	
		$this->Config = array(
			'no_cache deny localhost' => array(
				'title' => _TITLE2('No cache'),
				'info' => _HELPBOX2('Enable ComixWall as a non-caching proxy for the local network.'),
				),
			'debug_options' => array(
				'title' => _TITLE2('Debug options'),
				'info' => _HELPBOX2('Logging options are set as section,level where each source file is assigned a unique section.  Lower levels result in less output,  Full debugging (level 9) can result in a very large log file, so be careful.  The magic word "ALL" sets debugging levels for all sections.  We recommend normally running with "ALL,1".'),
				),
			'client_netmask' => array(
				'title' => _TITLE2('Client netmask'),
				'info' => _HELPBOX2('A netmask for client addresses in logfiles and cachemgr output.
		Change this to protect the privacy of your cache clients.
		A netmask of 255.255.255.0 will log all IP\'s in that range with the last digit set to \'0\'.'),
				),
			'http_access allow localhost' => array(
				'title' => _TITLE2('Allow localhost'),
				),
			'http_access deny all' => array(
				'title' => _TITLE2('Deny others'),
				'info' => _HELPBOX2('And finally deny all other access to this proxy'),
				),
			'cache_mgr' => array(
				'title' => _TITLE2('Cache mgr'),
				'info' => _HELPBOX2('Email-address of local cache manager who will receive mail if the cache dies. The default is "webmaster".'),
				),
		);
	}
	
	function PrintLogLine($cols, $linenum)
	{
		$this->PrintLogLineClass($cols['Cache']);

		PrintLogCols($linenum, $cols);
		echo '</tr>';
	}
	
	function FormatLogCols(&$cols)
	{
		$link= $cols['Link'];
		if (preg_match('|^(http://[^/]*)|', $cols['Link'], $match)) {
			$linkbase= $match[1];
		}
		$cols['Link']= '<a href="'.$link.'" title="'.$link.'">'.wordwrap($linkbase, 40, '<br />', TRUE).'</a>';
	}
}

$View= new Squid();
?>
