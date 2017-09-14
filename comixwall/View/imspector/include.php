<?php
/* $ComixWall: include.php,v 1.11 2009/11/11 14:54:44 soner Exp $ */

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

class Imspector extends View
{
	public $Model= 'imspector';
	public $Layout= 'imspector';
	
	function Imspector()
	{
		$this->LogsHelpMsg= _HELPWINDOW('IM proxy logs contain process errors and notices.');
	}
}

$View= new Imspector();

$basicConfig = array(
	'icq_protocol' => array(
		'title' => _TITLE2('ICQ'),
		'info' => _HELPBOX2('Enable protocols'),
		),
	'irc_protocol' => array(
		'title' => _TITLE2('IRC'),
		),
	'msn_protocol' => array(
		'title' => _TITLE2('MSN'),
		),
	'yahoo_protocol' => array(
		'title' => _TITLE2('Yahoo'),
		),
	'gg_protocol' => array(
		'title' => _TITLE2('GG'),
		),
	'jabber_protocol' => array(
		'title' => _TITLE2('Jabber'),
		),
	'https_protocol' => array(
		'title' => _TITLE2('HTTPs'),
		'info' => _HELPBOX2('MSN via HTTP proxy needs https'),
		),
	'responder_filename=.*' => array(
		'title' => _TITLE2('Notice responses'),
		'info' => _HELPBOX2('Enable or disable automated responses configured below. You need to enable either Notice days or Filtered minutes too.'),
		),
	'notice_days' => array(
		'title' => _TITLE2('Notice days'),
		'info' => _HELPBOX2('Inform parties that chats are monitored every N days (default is never)'),
		),
	'filtered_mins' => array(
		'title' => _TITLE2('Filtered minutes'),
		'info' => _HELPBOX2('Inform of a blocked event, but upto a max of every N mins (default is never)'),
		),
	'response_prefix' => array(
		'title' => _TITLE2('Response prefix'),
		'info' => _HELPBOX2('Prefix to all responses using all responder plugins'),
		),
	'response_postfix' => array(
		'title' => _TITLE2('Response postfix'),
		'info' => _HELPBOX2('Postfix to all responses using all responder plugins'),
		),
	'notice_response' => array(
		'title' => _TITLE2('Notice response'),
		'info' => _HELPBOX2('Customised notice text'),
		),
	'filtered_response' => array(
		'title' => _TITLE2('Filtered response'),
		'info' => _HELPBOX2('Customised filtered text (message text or filename follows in response)'),
		),
	'block_files' => array(
		'title' => _TITLE2('Block all filetransfers'),
		),
	'block_webcams' => array(
		'title' => _TITLE2('Block webcams'),
		'info' => _HELPBOX2('Only webcam sessions on Yahoo are recognized and blocked'),
		),
	'log_typing_events' => array(
		'title' => _TITLE2('Log typing events'),
		),
);

$badwordsConfig = array(
	'badwords_filename=.*' => array(
		'title' => _TITLE2('Badwords filtering'),
		'info' => _HELPBOX2('Enable or disable badwords filtering'),
		),
	'badwords_replace_character' => array(
		'title' => _TITLE2('Badwords replace character'),
		'info' => _HELPBOX2('Badwords found are replaced with this single character'),
		),
	'badwords_block_count' => array(
		'title' => _TITLE2('Badwords block count'),
		'info' => _HELPBOX2('If a message contains more then this many bad words then the message will be completely blocked, not just replaced'),
		),
);

$aclConfig = array(
	'acl_filename=.*' => array(
		'title' => _TITLE2('Access control list'),
		'info' => _HELPBOX2('Enable or disable ACL'),
		),
);
?>
