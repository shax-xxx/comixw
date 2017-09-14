<?php
/* $ComixWall: imspector.php,v 1.13 2009/11/17 18:43:09 soner Exp $ */

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
 * IM proxy.
 */

require_once($MODEL_PATH.'model.php');

class Imspector extends Model
{
	public $Name= 'imspector';
	public $User= '_imspector';
	
	public $ConfFile= '/etc/imspector/imspector.conf';
	public $LogFile= '/var/log/imspector.log';
	
	private $badwordsFile= '/etc/imspector/badwords.txt';
	private $aclFile= '/etc/imspector/acl.txt';

	public $PidFile= '/var/run/imspector.pid';
	
	function Imspector()
	{
		global $TmpFile;
		
		parent::Model();
		
		$this->StartCmd= "/usr/local/sbin/imspector -c $this->ConfFile > $TmpFile 2>&1 &";

		$this->Commands= array_merge(
			$this->Commands,
			array(
				'GetBadwords'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get badwords'),
					),

				'AddBadword'=>	array(
					'argv'	=>	array(STR),
					'desc'	=>	_('Add badword'),
					),

				'DelBadword'=>	array(
					'argv'	=>	array(STR),
					'desc'	=>	_('Delete badword'),
					),

				'GetAcl'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get ACL'),
					),

				'AddAcl'=>	array(
					'argv'	=>	array(STR),
					'desc'	=>	_('Add ACL'),
					),

				'DelAcl'=>	array(
					'argv'	=>	array(STR),
					'desc'	=>	_('Delete ACL'),
					),

				'MoveAclUp'=>	array(
					'argv'	=>	array(STR),
					'desc'	=>	_('Move ACL Up'),
					),

				'MoveAclDown'=>	array(
					'argv'	=>	array(STR),
					'desc'	=>	_('Move ACL Down'),
					),
				)
			);
	}

	function GetVersion()
	{
		return 'IMSpector 0.9';
	}

	function SetConfig($confname)
	{
		global $basicConfig, $badwordsConfig, $aclConfig;

		$this->Config= ${$confname};
	}

	function GetBadwords()
	{
		return $this->GetFile($this->badwordsFile);
	}

	function AddBadword($badword)
	{
		$this->DelBadword($badword);
		return $this->AppendToFile($this->badwordsFile, $badword);
	}

	function DelBadword($badword)
	{
		$badword= Escape($badword, '/.');
		return $this->ReplaceRegexp($this->badwordsFile, "/^($badword\s)/m", '');
	}

	function GetAcl()
	{
		return $this->SearchFileAll($this->aclFile, "/^\h*((allow|deny)\h+[^\n]+)$/m", 1);
	}

	function AddAcl($acl)
	{
		$this->DelAcl($acl);
		return $this->AppendToFile($this->aclFile, $acl);
	}

	function DelAcl($acl)
	{
		$acl= Escape($acl, '/.');
		return $this->ReplaceRegexp($this->aclFile, "/^($acl\n)/m", '');
	}

	function MoveAclUp($acl)
	{
		$acl= Escape($acl, '/.');
		return $this->ReplaceRegexp($this->aclFile, "/^\h*((allow|deny)\h+[^\n]+)\n+\h*($acl)\n/m", '${3}'."\n".'${1}'."\n");
	}

	function MoveAclDown($acl)
	{
		$acl= Escape($acl, '/.');
		return $this->ReplaceRegexp($this->aclFile, "/^\h*($acl)\n+\h*((allow|deny)\h+[^\n]+)\n/m", '${2}'."\n".'${1}'."\n");
	}
}

/** Basic configuration.
 */
$basicConfig = array(
	'icq_protocol' => array(
        'type' => STR_on_off,
		),
	'irc_protocol' => array(
        'type' => STR_on_off,
		),
	'msn_protocol' => array(
        'type' => STR_on_off,
		),
	'yahoo_protocol' => array(
        'type' => STR_on_off,
		),
	'gg_protocol' => array(
        'type' => STR_on_off,
		),
	'jabber_protocol' => array(
        'type' => STR_on_off,
		),
    'https_protocol' => array(
        'type' => STR_on_off,
		),
	'responder_filename=.*' => array(
        'type' => FALSE,
		),
    'notice_days' => array(
        'type' => UINT,
		),
    'filtered_mins' => array(
        'type' => UINT,
		),
    'response_prefix' => array(
		),
    'response_postfix' => array(
		),
    'notice_response' => array(
		),
    'filtered_response' => array(
		),
    'block_files' => array(
        'type' => STR_on_off,
		),
    'block_webcams' => array(
        'type' => STR_on_off,
		),
    'log_typing_events' => array(
        'type' => STR_on_off,
		),
);

/** Badwords configuration.
 */
$badwordsConfig = array(
	'badwords_filename=.*' => array(
        'type' => FALSE,
		),
    'badwords_replace_character' => array(
        'type' => CHAR,
		),
    'badwords_block_count' => array(
        'type' => UINT,
		),
);

/** Access Control List configuration.
 */
$aclConfig = array(
	'acl_filename=.*' => array(
        'type' => FALSE,
		),
);
?>
