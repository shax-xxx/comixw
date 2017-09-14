<?php
/* $ComixWall: include.php,v 1.18 2009/11/20 14:06:35 soner Exp $ */

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

class Spamassassin extends View
{
	public $Model= 'spamassassin';
	public $Layout= 'spamassassin';
	
	function Spamassassin()
	{
		$this->LogsHelpMsg= _HELPWINDOW('Detailed results of spam content scans are recorded by SpamAssassin, such as spam scores and their reasons.');
		$this->GraphHelpMsg= _HELPWINDOW('SpamAssassin is a perl process. These graphs display data from all perl processes.');
		$this->ConfHelpMsg= _HELPWINDOW('An important setting may be the rewrite header which is used on the subject line of an e-mail identified as spam. Note that spam identification is a guess based on statistical calculations, hence there may be false positives.');
	
		$this->Config = array(
			'rewrite_header Subject' => array(
				'title' => _TITLE2('Rewrite header'),
				'info' => _HELPBOX2('Add *****SPAM***** to the Subject header of spam e-mails'),
				),
			'report_safe' => array(
				'title' => _TITLE2('Report safe'),
				'info' => _HELPBOX2('Save spam messages as a message/rfc822 MIME attachment instead of modifying the original message (0: off, 2: use text/plain instead)'),
				),
			'trusted_networks' => array(
				'title' => _TITLE2('Trusted networks'),
				'info' => _HELPBOX2('Set which networks or hosts are considered \'trusted\' by your mail server (i.e. not spammers)'),
				),
			'required_score' => array(
				'title' => _TITLE2('Required score'),
				'info' => _HELPBOX2('Set the threshold at which a message is considered spam (default: 5.0)'),
				),
			'use_bayes' => array(
				'title' => _TITLE2('Use bayes'),
				'info' => _HELPBOX2('Use Bayesian classifier (default: 1)'),
				),
			'bayes_auto_learn' => array(
				'title' => _TITLE2('Bayes auto learn'),
				'info' => _HELPBOX2('Bayesian classifier auto-learning (default: 1)'),
				),
			'bayes_ignore_header X-Bogosity' => array(
				'title' => _TITLE2('X-Bogosity'),
				'info' => _HELPBOX2('Set headers which may provide inappropriate cues to the Bayesian classifier'),
				),
			'bayes_ignore_header X-Spam-Flag' => array(
				'title' => _TITLE2('X-Spam-Flag'),
				),
			'bayes_ignore_header X-Spam-Status' => array(
				'title' => _TITLE2('X-Spam-Status'),
				),
		);
	}
	
	function FormatLogCols(&$cols)
	{
		$cols['Log']= wordwrap(htmlspecialchars($cols['Log']), 80, '<br />', TRUE);
	}
}

$View= new Spamassassin();
?>
