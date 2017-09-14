<?php
/* $ComixWall: stats.php,v 1.11 2009/11/15 15:24:51 soner Exp $ */

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

/// Stats page warning message.
$StatsWarningMsg= _NOTICE('Analysis of statistical data may take a long time to process. Please be patient. Also note that if you refresh this page frequently, CPU load may increase considerably.');

/// Main help box used on all statistics pages.
$StatsHelpMsg= _HELPWINDOW('This page displays statistical data collected over the log files of this module.

You can change the date of statistics using drop-down boxes. An empty value means match-all. For example, if you choose 3 for month and empty value for day fields, the charts and lists display statistics for all the days in March. Choosing empty value for month means empty value for day field as well.

For single dates, Horizontal chart direction is assumed. For date ranges, default graph style is Daily, and direction is Vertical. Graph style can be changed to Hourly for date ranges, where cumulative hourly statistics are shown. In Daily style, horizontal direction is not possible.');

$Submenu= SetSubmenu('general');
require_once("../lib/stats.$Submenu.php");
?>
