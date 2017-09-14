<?php
/* $ComixWall: dante.php,v 1.7 2009/11/16 12:05:36 soner Exp $ */

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
 * Socks proxy.
 */

require_once($MODEL_PATH.'model.php');

class Dante extends Model
{
	public $Name= 'dante';
	public $User= '_dante';
	
	public $ConfFile= '/etc/sockd.conf';
	public $LogFile= '/var/log/sockd.log';
	public $VersionCmd= '/usr/local/sbin/sockd -v';
	
	public $PidFile= '/var/run/sockd.pid';
	
	function Dante()
	{
		global $TmpFile;
		
		parent::Model();
		
		$this->Proc= 'sockd';
		
		$this->StartCmd= "/usr/local/sbin/sockd -D > $TmpFile 2>&1 &";
	
		
		$this->Commands= array_merge(
			$this->Commands,
			array(
				'SetIfs'	=> array(
					'argv'	=> array(NAME, NAME),
					'desc'	=> _('Set ifs'),
					),
				
				'SetIntnet'	=> array(
					'argv'	=> array(IPRANGE),
					'desc'	=> _('Set ifs'),
					),
				)
			);
	}
	
	function SetIfs($lanif, $wanif)
	{
        $re= '/(\h*internal:\h*)(\w+\d+)(\h+.*external:\h*)(\w+\d+)(\s+)/ms';
		return $this->ReplaceRegexp($this->ConfFile, $re, '${1}'.$lanif.'${3}'.$wanif.'${5}');
	}

	function SetIntnet($net)
	{
		global $Re_Ip, $Re_Net;

        $re= "/(\h*client\h+pass\h*\{\s*\h+from:\h+)($Re_Ip|$Re_Net)(\h+.*)/ms";
		return $this->ReplaceRegexp($this->ConfFile, $re, '${1}'.$net.'${3}');
	}
}
?>
