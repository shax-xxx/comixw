<?php
/* $ComixWall: pmacct.php,v 1.6 2009/11/19 18:23:01 soner Exp $ */

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
 * Network monitoring.
 */

require_once($MODEL_PATH.'model.php');

class Pmacct extends Model
{
	public $Name= 'pmacctd';
	public $User= 'root';
	
	public $VersionCmd= '/usr/local/sbin/pmacctd -v 2>&1';
	
	function Pmacct()
	{
		global $TmpFile;
		
		parent::Model();
	
		$this->Commands= array_merge(
			$this->Commands,
			array(
				'SetIf'=>	array(
					'argv'	=>	array(NAME),
					'desc'	=>	_('Set pmacct if'),
					),
				
				'SetNet'=>	array(
					'argv'	=>	array(IPRANGE),
					'desc'	=>	_('Set pmacct net'),
					),
				)
			);
	}

	function GetVersion()
	{
		return $this->RunShellCommand($this->VersionCmd.' | /usr/bin/head -2 | /usr/bin/tail -1');
	}

	function Start()
	{
		global $TmpFile;

		$this->StartCmd= "/usr/local/sbin/pmacctd -f /etc/pmacct/pmacctd-pnrg.conf > $TmpFile 2>&1 &";
		$retval_pnrg= parent::Start();
		
		/// @todo Should modify pmacct code to report which conf file each child process is using in ps output
		$this->StartCmd= "/usr/local/sbin/pmacctd -f /etc/pmacct/pmacctd-protograph.conf > $TmpFile 2>&1 &";
		$retval_protograph= parent::Start();

		// Second Start() needs special treatment due to pmacct not reporting conf file in ps output
		if ($retval_protograph) {
			/// @warning Append error out if $retval_protograph is TRUE, because FALSE condition is handled in Start()
			$errout= $this->GetFile($TmpFile, '');
			ViewError($errout);
		}

		return ($retval_pnrg & $retval_protograph) && ($errout === '');
	}

	function SetIf($if)
	{
		$re= '|^(\s*interface:\s*)(\w+\d+)(\s+)|ms';
		$retval=  $this->ReplaceRegexp('/etc/pmacct/pmacctd-pnrg.conf', $re, '${1}'.$if.'${3}');
		$retval&= $this->ReplaceRegexp('/etc/pmacct/pmacctd-protograph.conf', $re, '${1}'.$if.'${3}');

		return $retval;
	}
	
	function SetNet($net)
	{
		global $Re_Net;
		
		$re= "|(\s+src\s+net\s+)($Re_Net)(\s+)|ms";
		$retval=  $this->ReplaceRegexp('/etc/pmacct/pmacctd-pnrg.conf', $re, '${1}'.$net.'${3}');
		
		$re= "|(\s+dst\s+net\s+)($Re_Net)(\s+)|ms";
		$retval&= $this->ReplaceRegexp('/etc/pmacct/pmacctd-pnrg.conf', $re, '${1}'.$net.'${3}');
		
		return $retval;
	}
}
?>
