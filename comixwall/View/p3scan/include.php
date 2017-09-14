<?php
/* $ComixWall: include.php,v 1.15 2009/11/20 14:27:44 soner Exp $ */

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

class P3scan extends View
{
	public $Model= 'p3scan';
	public $Layout= 'p3scan';
	
	function P3scan()
	{
		$this->LogsHelpMsg= _HELPWINDOW('POP3 proxy logs connection information and process exit status.');
		$this->ConfHelpMsg= _HELPWINDOW('You may want to set the report language for your locale. By default, POP3 proxy scans for both spam and viruses.');
	
		/** P3scan configuration.
		 *
		 * See Dansguardian $basicConfig for details.
		 */
		$this->Config = array(
			'checkspam' => array(
				'title' => _TITLE2('Scan Spam'),
				'info' => _HELPBOX2('If enabled, will scan for Spam before scanning for a virus. You should start spamd before running p3scan.'),
				),
			'justdelete' => array(
				'title' => _TITLE2('Just Delete'),
				'info' => _HELPBOX2('Instead of keeping an infected message in the Virus Directory, delete it after reporting it to the user.
		default: Keep infected messages in Virus Directory'),
				),
			'maxchilds' => array(
				'title' => _TITLE2('Max Childs'),
				'info' => _HELPBOX2('The maximum number of connections we will handle at once. Any further connections will be dropped. Keep in mind that a number of 10 also means that 10 viruscanner can run at once.'),
				),
			'bytesfree' => array(
				'title' => _TITLE2('Bytes Free'),
				'info' => _HELPBOX2('The number of KB\'s there must be free before processing any mail. If there is less than this amount, p3scan will terminate any connections until the problem is resolved.
		default: 100MB'),
				),
			'debug' => array(
				'title' => _TITLE2('Debug'),
				'info' => _HELPBOX2('Turn on debugging.'),
				),
			'quiet' => array(
				'title' => _TITLE2('Quiet'),
				'info' => _HELPBOX2('Disable reporting of normal operating messages. Only report errors or critical information.
		default: display all except debug info'),
				),
		);
	}

	function FormatLogCols(&$cols)
	{
		$cols['Log']= htmlspecialchars($cols['Log']);
	}
}

$View= new P3scan();
?>
