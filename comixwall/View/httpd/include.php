<?php
/* $ComixWall: include.php,v 1.12 2009/11/20 14:27:44 soner Exp $ */

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

class Httpd extends View
{
	public $Model= 'httpd';
	public $Layout= 'httpd';

	function Httpd()
	{
		$this->LogsHelpMsg= _HELPWINDOW('These logs may be important for diagnosing web server related problems.');

		$this->ConfHelpMsg= _HELPWINDOW('Since this web administration interface depends on the httpd web server, you should be careful while modifying these options. By default, the web server is configured to serve this web interface only, hence the default values should suffice for most purposes. However, OpenBSD/httpd is a full-featured HTTP server, and you can configure it to serve web sites too.');
	
		$this->Config= array(
			'ServerAdmin' => array(
				'title' => _TITLE2('Server Admin'),
				'info' => _HELPBOX2('ServerAdmin: Your address, where problems with the server should be e-mailed.
		This address appears on some server-generated pages, such as error documents.'),
				),
			'HostnameLookups' => array(
				'title' => _TITLE2('Hostname Lookups'),
				'info' => _HELPBOX2('Log the names of clients or just their IP addresses e.g., www.apache.org (on) or 204.62.129.132 (off).
		The default is off because it\'d be overall better for the net if people had to knowingly turn this feature on, since enabling it means that each client request will result in AT LEAST one lookup request to the nameserver.'),
				),
			'LogLevel' => array(
				'title' => _TITLE2('Log Level'),
				'info' => _HELPBOX2('LogLevel: Control the number of messages logged to the error_log.
		Possible values include: debug, info, notice, warn, error, crit, alert, emerg.'),
				),
			'Timeout' => array(
				'title' => _TITLE2('Timeout'),
				'info' => _HELPBOX2('The number of seconds before receives and sends time out.'),
				),
			'KeepAlive' => array(
				'title' => _TITLE2('KeepAlive'),
				'info' => _HELPBOX2('Whether or not to allow persistent connections (more than one request per connection). Set to "Off" to deactivate.'),
				),
			'MaxKeepAliveRequests' => array(
				'title' => _TITLE2('Max KeepAlive Requests'),
				'info' => _HELPBOX2('The maximum number of requests to allow during a persistent connection. Set to 0 to allow an unlimited amount. We recommend you leave this number high, for maximum performance.'),
				),
			'KeepAliveTimeout' => array(
				'title' => _TITLE2('KeepAlive Timeout'),
				'info' => _HELPBOX2('Number of seconds to wait for the next request from the same client on the same connection.'),
				),
			'MinSpareServers' => array(
				'title' => _TITLE2('Min Spare Servers'),
				'info' => _HELPBOX2('Server-pool size regulation.
		Rather than making you guess how many server processes you need, Apache dynamically adapts to the load it sees --- that is, it tries to maintain enough server processes to handle the current load, plus a few spare servers to handle transient load spikes (e.g., multiple simultaneous requests from a single Netscape browser).

		It does this by periodically checking how many servers are waiting for a request.  If there are fewer than MinSpareServers, it creates a new spare.  If there are more than MaxSpareServers, some of the spares die off.  The default values in httpd.conf-dist are probably OK for most sites.'),
				),
			'MaxSpareServers' => array(
				'title' => _TITLE2('Max Spare Servers'),
				),
			'StartServers' => array(
				'title' => _TITLE2('Start Servers'),
				'info' => _HELPBOX2('Number of servers to start initially --- should be a reasonable ballpark figure.'),
				),
			'MaxClients' => array(
				'title' => _TITLE2('Max Clients'),
				'info' => _HELPBOX2('Limit on total number of servers running, i.e., limit on the number of clients who can simultaneously connect --- if this limit is ever reached, clients will be LOCKED OUT, so it should NOT BE SET TOO LOW. It is intended mainly as a brake to keep a runaway server from taking the system with it as it spirals down...'),
				),
			);
	}
	
	function FormatLogCols(&$cols)
	{
		$cols['Log']= htmlspecialchars($cols['Log']);
	}
}

$View= new Httpd();
?>
