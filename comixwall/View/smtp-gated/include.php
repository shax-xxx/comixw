<?php
/* $ComixWall: include.php,v 1.18 2009/11/20 14:27:44 soner Exp $ */

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

class Smtpgated extends View
{
	public $Model= 'smtp-gated';
	public $Layout= 'smtp-gated';
	
	function Smtpgated()
	{
		$this->LogsHelpMsg= _HELPWINDOW('You may want to watch these logs carefully to determine infected clients in the internal network.');
		$this->ConfHelpMsg= _HELPWINDOW('If an outgoing e-mail is determined to be infected or identified as spam, the client trying to send that e-mail is blocked from sending any e-mails for a period of time defined by lock duration. You can enter your e-mail address in report e-mail option.');
		
		$this->Config = array(
			'lock_on' => array(
				'title' => _TITLE2('Lock on'),
				),
			'lock_duration' => array(
				'title' => _TITLE2('Lock duration'),
				),
			'abuse' => array(
				'title' => _TITLE2('Report e-mail'),
				),
			'priority' => array(
				'title' => _TITLE2('Priority'),
				),
			'max_connections' => array(
				'title' => _TITLE2('Maximum connections'),
				),
			'max_per_host' => array(
				'title' => _TITLE2('Maximum connections per host'),
				),
			'max_load' => array(
				'title' => _TITLE2('Maximum load'),
				),
			'scan_max_size' => array(
				'title' => _TITLE2('Maximum virus scan size'),
				),
			'spam_max_size' => array(
				'title' => _TITLE2('Maximum spam scan size'),
				),
			'spam_max_load' => array(
				'title' => _TITLE2('Maximum spam load'),
				),
			'spam_threshold' => array(
				'title' => _TITLE2('Spam threshold'),
				),
			'ignore_errors' => array(
				'title' => _TITLE2('Ignore errors'),
				),
			'spool_leave_on' => array(
				'title' => _TITLE2('Leave spool on'),
				),
			'log_helo' => array(
				'title' => _TITLE2('Log helo'),
				),
			'log_mail_from' => array(
				'title' => _TITLE2('Log mail from'),
				),
			'log_rcpt_to' => array(
				'title' => _TITLE2('Log rcpt to'),
				),
			'log_level' => array(
				'title' => _TITLE2('Log level'),
				),
			'nat_header_type' => array(
				'title' => _TITLE2('NAT header type'),
				),
			'locale' => array(
				'title' => _TITLE2('Locale'),
				),
		);
	}
	
	function FormatLogCols(&$cols)
	{
		$cols['Log']= wordwrap(htmlspecialchars($cols['Log']), 80, '<br />', TRUE);
		$cols['Sender']= htmlspecialchars($cols['Sender']);
		$cols['Recipient']= htmlspecialchars($cols['Recipient']);
	}
}

$View= new Smtpgated();
?>
