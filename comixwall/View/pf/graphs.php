<?php
/* $ComixWall: graphs.php,v 1.5 2009/11/10 18:47:49 soner Exp $ */

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

require_once('include.php');

$Submenu= SetSubmenu('ifs');

switch ($Submenu) {
	case 'ifs':
		$View->Layout= 'ifs';
		$View->GraphHelpMsg= _HELPWINDOW('Loopback is a logical interface.');
		break;

	case 'transfer':
		$View->Layout= 'transfer';
		$View->GraphHelpMsg= _HELPWINDOW('This graph shows the data transfer rate of packet filter. The transfer is between interfaces.');
		break;

	case 'states':
		$View->Layout= 'states';
		$View->GraphHelpMsg= _HELPWINDOW('State operations are perhaps the most meaningful measure of packet filter load.');
		break;

	case 'mbufs':
		$View->Layout= 'mbufs';
		$View->GraphHelpMsg= _HELPWINDOW('Mbufs indicate kernel memory management for networking.');
		break;

	case 'hosts':
		$View->GraphHelpMsg= _HELPWINDOW('These graphs show network usage per host. From top to bottom, host usage graphs are for the last hour, last 8-hour, daily, weekly, and monthly usages.');
		require_once("hostgraphs.php");
		exit;

	case 'protocol':
		$View->Layout= 'protograph';
		$View->GraphHelpMsg= _HELPWINDOW('This graph shows overall network usage based on certain protocols. Note that each protocol may cover more than one port.');
		break;
}

require_once('../lib/graphs.php');
?>
