<?php
/* $ComixWall: snortalerts.php,v 1.3 2009/11/15 15:24:50 soner Exp $ */

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

require_once($MODEL_PATH.'snort.php');

class Snortalerts extends Snort
{
	public $Name= 'snortalerts';
	
	public $LogFile= '/var/log/snort/alert.log';
	
	function ParseLogLine($logline, &$cols)
	{
		global $Re_Ip;

		if ($this->ParseSyslogLine($logline, $cols)) {
			$logline= $cols['Log'];

			$re_triplet= '\[[:\d]+\]';
			$re_report= '(.*)';
			
			$re_priority= '\[Priority:\s*(\d+)\]';
			$re_proto= '\{([^\}]*)\}';
			$re_from= "($Re_Ip)";
			$re_to= "($Re_Ip)";
			$re_port= '(:\d+|)';

			//Jun 27 18:12:41 comixwall snort[2875]: [122:1:0] (portscan) TCP Portscan[Priority: 3]: {RESERVED} 10.0.0.11 -> 10.0.0.13
			//Jun 27 18:12:45 comixwall snort[2875]: [116:59:1] (snort_decoder): Tcp Window Scale Option found with length > 14[Priority: 3]: {TCP} 10.0.0.11:52936 -> 10.0.0.13:25
			//Aug  6 12:35:41 comixwall snort[2875]: [1:853:10] WEB-CGI wrap access [Classification: Attempted Information Leak] [Priority: 2]: {TCP} 10.0.0.11:35690 -> 209.85.129.147:80
			$re= "/$re_triplet\s*$re_report\s*$re_priority:\s*$re_proto\s+$re_from$re_port\s*->\s*$re_to$re_port$/";
			if (preg_match($re, $logline, $match)) {
				$cols['Log']= $match[1];
				$cols['Prio']= $match[2];
				$cols['Proto']= $match[3];
				$cols['SrcIP']= $match[4];
				$cols['SPort']= ltrim($match[5], ':');
				$cols['DstIP']= $match[6];
				$cols['DPort']= ltrim($match[7], ':');
			}
			return TRUE;
		}
		return FALSE;
	}
}
?>
