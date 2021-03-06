<?php
/* $ComixWall: cwlogs.php,v 1.14 2009/11/20 12:01:39 soner Exp $ */

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
 * ComixWall logs.
 */

require_once($MODEL_PATH.'httpd.php');

class Cwlogs extends Httpd
{
	function ParseLogLine($logline, &$cols)
	{
		global $Re_Ip;

		if ($this->ParseSyslogLine($logline, $cols)) {
			$re_user= '(\w+|)';
			$re_host= "($Re_Ip|)";
			$re_loglevel= '(LOG_EMERG|LOG_ALERT|LOG_CRIT|LOG_ERR|LOG_WARNING|LOG_NOTICE|LOG_INFO|LOG_DEBUG)';
			$re_file= '([\w\/\.]+\.php)';
			$re_func= '(\w+)';
			$re_line= '(\d+)';
			
			$re_logheader= "\s+$re_loglevel\s*($re_user@$re_host|)\s+$re_file:\s+$re_func\s+\($re_line\):";
			
			if (preg_match("/$re_logheader/", $logline, $match)) {
				$cols['LogLevel']= $match[1];
				$cols['User']= $match[3];
				$cols['IP']= $match[4];
				$cols['File']= $match[5];
				$cols['Function']= $match[6];
				$cols['Line']= $match[7];
				
				$re_nocolon= '([^:]+)';
				$re_rest= '(.*)';
				
				$re= "/$re_logheader\s+$re_nocolon:\s+$re_rest$/";
				if (preg_match($re, $logline, $match)) {
					$cols['Reason']= $match[8];
					$cols['Log']= $match[9];
				}
				else {
					$re= "/$re_logheader\s+$re_rest$/";
					if (preg_match($re, $logline, $match)) {
						$cols['Reason']= $match[8];
						// Reset Log column set to $logline by ParseSyslogLine()
						$cols['Log']= '';
					}
				}
			}
			return TRUE;
		}
		return FALSE;
	}
}
?>
