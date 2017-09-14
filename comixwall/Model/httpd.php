<?php
/* $ComixWall: httpd.php,v 1.17 2009/11/16 12:05:36 soner Exp $ */

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

require_once($MODEL_PATH.'model.php');

class Httpd extends Model
{
	public $Name= 'httpd';
	public $User= 'root|www';
	
	public $NVPS= '\h';
	public $ConfFile= '/etc/httpd.conf';
	public $LogFile= '/var/www/logs/error.log';

	public $VersionCmd= '/usr/sbin/nginx -v 2>&1';
	private $phpVersionCmd= '/usr/local/bin/php -v';

	function Httpd()
	{
		parent::Model();
		
		$this->Proc= 'httpd';
	
		$this->Commands= array_merge(
			$this->Commands,
			array(
				'SetWebalizerHostname'=>	array(
					'argv'	=>	array(IPADR),
					'desc'	=>	_('Set webalizer hostname'),
					),
				)
			);
	}

	function GetVersion()
	{
		return $this->RunShellCommand($this->VersionCmd)."\n".
			$this->RunShellCommand($this->phpVersionCmd.' | /usr/bin/head -1');
	}
	
	function Restart()
	{
		return $this->RunShellCommand('/etc/rc.d/httpd restart');
	}

	function Stop()
	{
		return $this->RunShellCommand('/etc/rc.d/httpd stop');
	}
	
	function ParseLogLine($logline, &$cols)
	{
		//2014/11/06 01:38:34 [notice] 11079#0: signal process started
		$re_datetime= '(\d+\/\d+\/\d+)\s+(\d+:\d+:\d+)';
		$re_loglevel= '\[([a-zA-Z]+)\]';
		$re_rest= '(.*)';

		$re= "/^$re_datetime\s+$re_loglevel\s+$re_rest$/";
		if (preg_match($re, $logline, $match)) {
			$cols['Date']= $match[1];
			$cols['Time']= $match[2];
			$cols['Level']= $match[3];
			$cols['Log']= $match[4];
		}
		else {
			if ($retval= $this->ParseSyslogLine($logline, $cols)) {
				$cols['DateTime']= $cols['Date'].' '.$cols['Time'];
			}
			else {
				// There are very simple log lines too, e.g. "man: Formatting manual page..."
				// So parser never fails
				$cols['Log']= $logline;
			}
		}
		return TRUE;
	}
	
	function SetWebalizerHostname($ip)
	{
		global $Re_Ip;
        
        $re= "/^(\h*HostName\h+)($Re_Ip|[\w\.]+)(\s+.*)/ms";
		return $this->ReplaceRegexp('/etc/webalizer.conf', $re, '${1}'.$ip.'${3}');
	}
}

/** Configuration.
 *
 * If type field is missing, default type, STR, is assumed.
 *
 * If type field is FALSE, the configuration does not have a Value, it may just
 * be an enable/disable configuration.
 *
 * @param[out]	type	Configuration Value type, regexp definition, defaults to STR.
 */
$ModelConfig = array(
    'ServerAdmin' => array(
		),
    'HostnameLookups' => array(
        'type' => STR_On_Off,
		),
    'LogLevel' => array(
		),
    'Timeout' => array(
        'type' => UINT,
		),
    'KeepAlive' => array(
        'type' => STR_On_Off,
		),
    'MaxKeepAliveRequests' => array(
        'type' => UINT,
		),
    'KeepAliveTimeout' => array(
        'type' => UINT,
		),
    'MinSpareServers' => array(
        'type' => UINT,
		),
    'MaxSpareServers' => array(
        'type' => UINT,
		),
    'StartServers' => array(
        'type' => UINT,
		),
    'MaxClients' => array(
        'type' => UINT,
		),
);
?>
