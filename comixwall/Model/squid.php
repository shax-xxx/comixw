<?php
/* $ComixWall: squid.php,v 1.17 2009/11/16 12:05:36 soner Exp $ */

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
 * HTTP proxy.
 */

require_once($MODEL_PATH.'model.php');

class Squid extends Model
{
	public $Name= 'squid';
	public $User= 'root|_squid';
	
	public $NVPS= '\h';
	public $ConfFile = '/etc/squid/squid.conf';
	public $LogFile= '/var/squid/logs/access.log';

	public $VersionCmd= '/usr/local/sbin/squid -v';

	public $PidFile= '/var/squid/logs/squid.pid';

	function Squid()
	{
		global $TmpFile;
		
		parent::Model();
		
		$this->StartCmd= "/usr/local/sbin/squid -D > $TmpFile 2>&1 &";
		
		$this->Commands= array_merge(
			$this->Commands,
			array(
				'GetIpPort'	=> array(
					'argv'	=> array(),
					'desc'	=> _('Get HTTP proxy IP:port'),
					),

				'DelIpPort'	=> array(
					'argv'	=> array(IPPORT),
					'desc'	=> _('Delete HTTP proxy IP:port'),
					),

				'AddIpPort'	=> array(
					'argv'	=> array(IPPORT),
					'desc'	=> _('Add HTTP proxy IP:port'),
					),
				)
			);
	}

	/** Adds IP:port to squid config file.
	 *
	 * Adds the line above https_port TAG.
	 * Cleans up duplicates first
	 * 
	 * @param[in]	$if	string Interface IP:port.
	 */
	function AddIpPort($if)
	{
		$this->DelIpPort($if);
		return $this->ReplaceRegexp($this->ConfFile, "/(\h*#\h*TAG:\h*https_port.*)/m", "http_port $if\n".'${1}');
	}

	/** Deletes IP:port from squid config file.
	 *
	 * @warning If port is not provided, deletes all ports with that IP.
	 * 
	 * @param[in]	$if	string Interface IP:port.
	 */
	function DelIpPort($if)
	{
		$if= Escape($if, '.');
		return $this->ReplaceRegexp($this->ConfFile, "/^(\h*http_port\h*$if\b.*(\s|))/m", '');
	}

	/** Extract all http IP:ports which squid listens to.
	 */
	function GetIpPort()
	{
		global $Re_IpPort;

		return $this->SearchFileAll($this->ConfFile, "/^\h*http_port\h*($Re_IpPort)\b.*\h*$/m");
	}
	
	function ParseLogLine($logline, &$cols)
	{
		global $Re_Ip;
	
		//1253140814.385  11463 127.0.0.1 TCP_MISS/200 6796 GET http://openbsd.org/images/cd45-s.gif - DIRECT/199.185.137.3 image/gif
		//1251968551.489      0 127.0.0.1 TCP_HIT/302 823 GET http://fxfeeds.mozilla.com/firefox/headlines.xml - NONE/- text/html
		$re_datetime= '(\d+\.\d+)';
		$re_size= '(\d+)';
		$re_clientip= "($Re_Ip|-)";
		$re_cache= '(\S+)';
		$re_code= '(\d+)';
		$re_mtd= '(GET|POST|\S+)';
		$re_link= '(\S+)';
		$re_direct= '(\S+)';
		$re_targetip= "($Re_Ip|\S+|-)";
		$re_type= '(\S+)';

		$re= "/^$re_datetime\s+\d+\s+$re_clientip\s+$re_cache\/$re_code\s+$re_size\s+$re_mtd\s+$re_link.*\s+$re_direct\/$re_targetip\s+$re_type$/";
		if (preg_match($re, $logline, $match)) {
			$day= sprintf('% 2d', date("j", $match[1]));
			$cols['Date']= date("M", $match[1]).' '.$day;
			$cols['Time']= date("H:i:s", $match[1]);
			$cols['DateTime']= $cols['Date'].' '.$cols['Time'];
			$cols['ClientIP']= $match[2];
			$cols['Cache']= $match[3];
			$cols['Code']= $match[4];
			$cols['Size']= $match[5];
			$cols['Mtd']= $match[6];
			$cols['Link']= $match[7];
			$cols['Direct']= $match[8];
			$cols['Target']= $match[9];
			$cols['Type']= $match[10];
			return TRUE;
		}
		else if ($this->ParseSyslogLine($logline, $cols)) {
			$cols['DateTime']= $cols['Date'].' '.$cols['Time'];
			return TRUE;
		}
		return FALSE;
	}
	
	function PostProcessCols(&$cols)
	{
		preg_match('|http://([^/]*)|', $cols['Link'], $match);
		$cols['Link']= $match[1];
	}
	
}

$ModelConfig = array(
    'no_cache deny localhost' => array(
        'type' => FALSE,
		),
    'log_ip_on_direct' => array(
        'type' => STR_on_off,
		),
    'debug_options' => array(
		),
    'log_fqdn' => array(
        'type' => STR_on_off,
		),
    'client_netmask' => array(
        'type' => IP,
		),
    'http_access allow localhost' => array(
        'type' => FALSE,
		),
    'http_access deny all' => array(
        'type' => FALSE,
		),
    'cache_mgr' => array(
		),
    'logfile_rotate' => array(
        'type' => UINT,
		),
);
?>
