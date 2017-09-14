<?php
/* $ComixWall: graphs.php,v 1.13 2009/11/10 18:47:50 soner Exp $ */

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

$Submenu= SetSubmenu('cpus');

switch ($Submenu) {
	case 'cpus':
		$View->Layout= 'cpus';
		$View->GraphHelpMsg= _HELPWINDOW('If your system has a multi-core CPU or more than one CPU, you can run the SMP kernel. If this page does not display multiple graphs, one for each CPU, after you switch to the SMP kernel, go to system configuration pages, apply automatic configuration, and reinitialize graph files.');
		break;

	case 'sensors':
		$View->Layout= 'sensors';
		$View->GraphHelpMsg= _HELPWINDOW('This page displays the graphs of all temperature and fan sensors in your system. You may have multiple sensors depending on your hardware. Virtual machines may not provide any sensors at all.');
		break;

	case 'memory':
		$View->Layout= 'memory';
		$View->GraphHelpMsg= _HELPWINDOW('This page displays the graph for shared memory and swap area usage. If the swap area usage is too high, you may consider adding more RAM. For higher system performance, you want to have no swap usage at all.');
		break;

	case 'disks':
		$View->Layout= 'disks';
		$View->GraphHelpMsg= _HELPWINDOW('This page displays the I/O graphs of all the disks in your system.');
		break;

	case 'partitions':
		$View->Layout= 'partitions';
		$View->GraphHelpMsg= _HELPWINDOW('This page displays the usage graphs of all the partitions on your disks.');
		break;
}

require_once('../lib/graphs.php');
?>
