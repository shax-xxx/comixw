<?php
/* $ComixWall: imlogs.php,v 1.13 2009/11/16 12:05:36 soner Exp $ */

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
 * IMSpector message logs.
 */

require_once($MODEL_PATH.'model.php');

class Imlogs extends Model
{
	private $logsDir= '/var/log/imspector';
	
	function Imlogs()
	{
		parent::Model();
		
		$this->Commands= array_merge(
			$this->Commands,
			array(
				'GetProtocols'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get protocols'),
					),

				'GetLocalUsers'=>	array(
					'argv'	=>	array(NAME),
					'desc'	=>	_('Get local users'),
					),

				'GetRemoteUsers'=>	array(
					/// @todo Is there any pattern or size for localuser, 2nd param?
					'argv'	=>	array(NAME, STR),
					'desc'	=>	_('Get remote users'),
					),

				'GetSessions'=>	array(
					/// @todo Is there any pattern or size for localuser and remoteuser, 2nd and 3rd param?
					'argv'	=>	array(NAME, STR, STR),
					'desc'	=>	_('Get sessions'),
					),

				'GetImLogFile'=>	array(
					/// @todo Is there any pattern or size for localuser and remoteuser, 2nd and 3rd param?
					'argv'	=>	array(NAME, STR, STR, NAME),
					'desc'	=>	_('Get im log file'),
					),
				)
			);
	}

	/// @todo Name clash with the Model method GetFiles(),
	/// but PHP overloading does not allow redeclaration with different signature
	function ImGetFiles($proto= '', $localuser= '', $remoteuser= '')
	{
		/// @attention Double quotes is necessary for group chats, which contain curly braces in the path names
		return $this->GetFiles('"'.$this->logsDir.$proto.$localuser.$remoteuser.'"');
	}

	function GetDirs($proto= '', $localuser= '')
	{
		$path= $proto.$localuser;
		/// @attention Double quotes is necessary for group chats, which contain curly braces in the path names
		$files= $this->GetFiles('"'.$this->logsDir.$path.'"');
		$files= explode("\n", $files);

		$dirs= array();
		foreach ($files as $file) {
			if (is_dir($this->logsDir."$path/$file")) {
				$dirs[]= $file;
			}
		}
		return implode("\n", $dirs);
	}

	function GetProtocols()
	{
		return $this->GetDirs();
	}

	function GetLocalUsers($proto)
	{
		return $this->GetDirs("/$proto");
	}

	function GetRemoteUsers($proto, $localuser)
	{
		return $this->GetDirs("/$proto", "/$localuser");
	}

	function GetSessions($proto, $localuser, $remoteuser)
	{
		return $this->ImGetFiles("/$proto", "/$localuser", "/$remoteuser");
	}
	
	function GetImLogFile($proto, $localuser, $remoteuser, $session)
	{
		return $this->logsDir."/$proto/$localuser/$remoteuser/$session";
	}
	
	function ParseLogLine($logline, &$cols)
	{
		$re_ip= '\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}';
		$re_port= '\d{1,5}';
		$re_ipport= "($re_ip:$re_port)";
		$re_datetime= '(\d+)';
		$re_num= '(\d+|)';
		$re_rest= '(.*)';
		
		$re= "/^$re_ipport,$re_datetime,$re_num,$re_num,$re_num,$re_num,$re_rest$/";

		if (preg_match($re, $logline, $match)) {
			$cols['IPPort']= $match[1];
			$cols['Date']= date("Y.m.d", $match[2]);
			$cols['Time']= date("H:i:s", $match[2]);
			/// v0.7 log format confuses old format parser, converting to old User
			//$cols['User']= $match[3];
			$cols['User']= $match[3] + 1;
			$cols['Log']= $match[7];
			return TRUE;
		}
		else {
			/// @attention For old log format (< v0.4)
			$re= "/^$re_ipport,$re_datetime,$re_num,$re_num,$re_rest$/";

			if (preg_match($re, $logline, $match)) {
				$cols['IPPort']= $match[1];
				$cols['Date']= date("Y.m.d", $match[2]);
				$cols['Time']= date("H:i:s", $match[2]);
				$cols['User']= $match[3];
				$cols['Log']= $match[5];
				return TRUE;
			}
		}
		return FALSE;
	}
}
?>
