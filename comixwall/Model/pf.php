<?php
/* $ComixWall: pf.php,v 1.36 2009/12/08 08:25:01 soner Exp $ */

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

require_once($MODEL_PATH.'model.php');

class Pf extends Model
{
	public $Name= 'pf';
	
	public $LogFile= '/var/log/pflog';
	
	public $NVPS= '=';
	public $ConfFile= '/etc/pf.restrictedips';
	
	public $AfterHoursFile= '/etc/pf.conf.afterhours';
	
	private $PNRG_IMG_DIR;
	
	private $tmpCrontab= '/var/tmp/comixwall/root';

	private $PF_PATH= '';
					
	private $re_BusinessDays;
	private $re_DisabledBusinessDays;

	private $re_Flush;
	private $re_DisabledFlush;

	private $re_Holidays;
	private $re_DisabledHolidays;

	private $pftopCmd= '/usr/local/sbin/pftop -b -a -o pkt -w 120';
	// PR  DIR SRC             DEST          STATE                   AGE      EXP      PKTS BYTES
	// tcp In  10.0.0.11:55802 10.0.0.254:22 ESTABLISHED:ESTABLISHED 00:48:37 24:00:00 4198 584448
	private $re_Pftop= "/^\s*(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\d+)\s+(\d+)$/";

	function Pf()
	{
		global $VIEW_PATH, $TCPDUMP;
		
		parent::Model();
		
		$this->PNRG_IMG_DIR= $VIEW_PATH.'pmacct/pnrg/spool/';
		
		$this->PF_PATH= $VIEW_PATH.'pf';
		
		$re_days= '(\*|[0-7,]*)';
		$re_hour= '(\d+)';
		$re_min= '(\d+)';

		$re= "$re_min\s+$re_hour\s+\*\s+\*\s+$re_days\s+\/sbin\/pfctl -a AfterHours -f \/etc\/pf\.conf\.afterhours";
		$this->re_BusinessDays= "^$re$";
		$this->re_DisabledBusinessDays= "^#$re$";

		$re= "$re_min\s+$re_hour\s+\*\s+\*\s+$re_days\s+\/sbin\/pfctl -a AfterHours -Fr";
		$this->re_Flush= "^$re$";
		$this->re_DisabledFlush= "^#$re$";

		$re= "\*\s+\*\s+\*\s+\*\s+$re_days\s+\/sbin\/pfctl -a AfterHours -f \/etc\/pf\.conf\.afterhours";
		$this->re_Holidays= "^$re$";
		$this->re_DisabledHolidays= "^#$re$";
		
		$this->CmdLogStart= $TCPDUMP.' <LF> | /usr/bin/head -1';
		
		$this->Commands= array_merge(
			$this->Commands,
			array(
				'SetIfs'=>	array(
					'argv'	=>	array(NAME, NAME),
					'desc'	=>	_('Set interfaces'),
					),
				
				'SetIntnet'=>	array(
					'argv'	=>	array(IPRANGE),
					'desc'	=>	_('Set internal net'),
					),
				
				'SetAfterhoursIf'=>	array(
					'argv'	=>	array(NAME),
					'desc'	=>	_('Set afterhours if'),
					),
				
				'GetStateCount'	=>	array(
					'argv'	=>	array(REGEXP|NONE),
					'desc'	=>	_('Get pf state count'),
					),

				'GetStateList'	=>	array(
					'argv'	=>	array(NUM, NUM, REGEXP|NONE),
					'desc'	=>	_('Get pf states'),
					),

				'ApplyPfRules'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Apply pf rules'),
					),

				'GetHostGraphsList'	=>	array(
					'argv'	=>	array(IPADR|EMPTYSTR),
					'desc'	=>	_('Get host graphs'),
					),

				'EnableAfterHours'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Enable AfterHours'),
					),

				'DisableAfterHours'		=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Disable AfterHours'),
					),

				'GetAfterHoursPfRules'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get AfterHour pf rules'),
					),

				'GetAfterHours'	=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get AfterHours'),
					),

				'DisableAfterHoursBusinessDays'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Disable AfterHours business days'),
					),

				'EnableAfterHoursBusinessDays'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Enable AfterHours business days'),
					),

				'DisableAfterHoursHolidays'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Disable AfterHours holidays'),
					),

				'EnableAfterHoursHolidays'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Enable AfterHours holidays'),
					),

				'ApplyAfterHours'=>	array(
					'argv'	=>	array(NUM|EMPTYSTR, NUM|EMPTYSTR, NUM|EMPTYSTR, NUM|EMPTYSTR, AFTERHOURS, AFTERHOURS),
					'desc'	=>	_('Apply AfterHours'),
					),
				
				'GetPfwCmdWrapperInfo'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get pfw commandwrapper -info'),
					),
				
				'GetPfwCmdWrapperPfInfo'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get pfw commandwrapper -pfinfo'),
					),
				
				'GetPfwCmdWrapperPfMem'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get pfw commandwrapper -pfmem'),
					),
				
				'GetPfwCmdWrapperPfStates'=>	array(
					'argv'	=>	array(NUM, REGEXP),
					'desc'	=>	_('Get pfw commandwrapper -pfstates'),
					),
				
				'GetPfwCmdWrapperQueues'=>	array(
					'argv'	=>	array(NAME),
					'desc'	=>	_('Get pfw commandwrapper -queues'),
					),
				
				'GetPfwCmdWrapperConntest'=>	array(
					'argv'	=>	array(NAME),
					'desc'	=>	_('Get pfw commandwrapper -conntest'),
					),
				
				'GetPfwCmdWrapperLog'=>	array(
					'argv'	=>	array(NAME, NUM, REGEXP),
					'desc'	=>	_('Get pfw commandwrapper -log'),
					),
				
				'GetPfwPfFileName'=>	array(
					'argv'	=>	array(NAME),
					'desc'	=>	_('Get pfw packetfilter -r'),
					),
				
				'GetPfwPfTest'=>	array(
					'argv'	=>	array(NAME, FILEPATH),
					'desc'	=>	_('Get pfw packetfilter -t'),
					),
				
				'GetPfwPfInstall'=>	array(
					'argv'	=>	array(NAME, FILEPATH),
					'desc'	=>	_('Get pfw packetfilter -i'),
					),
				)
			);
	}

	function IsRunning($proc= '')
	{
		$output= $this->RunShellCommand('/sbin/pfctl -s info');
		if (preg_match('/Status:\s*(Enabled|Disabled)\s*/', $output, $match)) {
			return ($match[1] == 'Enabled');
		}
		return FALSE;
	}

	/** Enable pf.
	 */
	function Start()
	{
		return $this->RunShellCommand('/sbin/pfctl -e');
	}

	/** Disable pf.
	 */
	function Stop()
	{
		return $this->RunShellCommand('/sbin/pfctl -d');
	}

	/** Gets pf state count.
	 *
	 * @param[in]	$re string Regexp to get count of a restricted result set
	 * @return int Line count
	 */
	function GetStateCount($re= '')
	{
		// Skip header lines by grepping for In or Out
		// Empty $re is not an issue for grep, greps all
		$cmd= "$this->pftopCmd | /usr/bin/egrep -a 'In|Out'";
		if ($re !== '') {
			$re= escapeshellarg($re);
			$cmd.= " | /usr/bin/grep -a -E $re";
		}
		$cmd.= ' | /usr/bin/wc -l';
		exec($cmd, $output, $retval);
		// OpenBSD wc returns with leading blanks
		return trim($this->RunShellCommand($cmd));
	}

	/** Gets the pftop output.
	 *
	 * @param[in]	$end	int Head option, start line
	 * @param[in]	$count	int Tail option, page line count
	 * @param[in]	$re		string Regexp to restrict the result set
	 * @return serialized Lines
	 */
	function GetStateList($end, $count, $re= '')
	{
		// Skip header lines by grepping for In or Out
		// Empty $re is not an issue for grep, greps all
		$re= escapeshellarg($re);
		$cmd= "$this->pftopCmd | /usr/bin/egrep -a 'In|Out' | /usr/bin/grep -a -E $re | /usr/bin/head -$end | /usr/bin/tail -$count";
		exec($cmd, $output, $retval);
		if ($retval === 0) {
			return serialize($this->ParsePftop($output));
		}
		ViewError(implode("\n", $output));
		cwc_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, 'Failed running pftop');
		return FALSE;
	}

	/** Parses pftop output.
	 *
	 * @param[in]	$pftopout	arrary pftop output
	 * @return array States
	 */
	function ParsePftop($pftopout)
	{
		$states= array();
		foreach ($pftopout as $line) {
			if (preg_match($this->re_Pftop, $line, $match)) {
				$states[]= array(
					$match[1],
					$match[2],
					$match[3],
					$match[4],
					$match[5],
					$match[6],
					$match[7],
					$match[8],
					$match[9],
					);
			}
		}
		return $states;
	}

	/** Sets interface names.
	 */
	function SetIfs($lanif, $wanif)
	{
		$retval= $this->SetNVP($this->PfRulesFile, 'int_if', '"'.$lanif.'"');
		$retval&= $this->SetNVP($this->PfRulesFile, 'ext_if', '"'.$wanif.'"');
		$retval&= $this->SetNVP($this->PfRulesFile, 'proxy', '"'.$wanif.'"');
		return $retval;
	}

	/** Sets int_net.
	 */
	function SetIntnet($net)
	{
		return $this->SetNVP($this->PfRulesFile, 'int_net', '"'.$net.'"');
	}

	/** Sets afterhours interface name.
	 */
	function SetAfterhoursIf($if)
	{
		return $this->SetNVP($this->AfterHoursFile, 'int_if', '"'.$if.'"');
	}

	/** Applies pf rules.
	 */
	function ApplyPfRules()
	{
		return $this->RunShellCommand("/sbin/pfctl -f $this->PfRulesFile");
	}

	/** Generates host graphs.
	 */
	function GetHostGraphsList($ip)
	{
		global $Re_Ip;

		$iplist= array();
		
		$dh= opendir($this->PNRG_IMG_DIR);
		while (FALSE !== ($filename= readdir($dh))) {
			if (preg_match("/^($Re_Ip)\.cgi$/", $filename, $match)) {
				$iplist[]= $match[1];
			}
		}
		closedir($dh);
		
		sort($iplist);
		
		if (in_array($ip, $iplist)) {
			$this->RunShellCommand($this->PNRG_IMG_DIR.$ip.'.cgi');
		}
		else if (count($iplist) > 0) {
			$this->RunShellCommand($this->PNRG_IMG_DIR.$iplist[0].'.cgi');
		}
		
		return implode("\n", $iplist);
	}

	/** Enables AfterHours.
	 */
	function EnableAfterHours()
	{
		return $this->RunShellCommand('/sbin/pfctl -a AfterHours -f /etc/pf.conf.afterhours');
	}

	/** Disables AfterHours.
	 */
	function DisableAfterHours()
	{
		return $this->RunShellCommand('/sbin/pfctl -a AfterHours -Fr');
	}

	/** Gets pf AfterHour anchor rules.
	 */
	function GetAfterHoursPfRules()
	{
		return $this->RunShellCommand('/sbin/pfctl -a AfterHours -sr');
	}

	/** Gets pf AfterHour definitions from cron file.
	 */
	function GetAfterHours()
	{
		if (($contents= $this->GetCrontab()) !== FALSE) {
			if (preg_match("/$this->re_BusinessDays/ms", $contents, $match)) {
				$businessdaysdisabled= FALSE;
			}
			else if (preg_match("/$this->re_DisabledBusinessDays/ms", $contents, $match)) {
				$businessdaysdisabled= TRUE;
			}
			$endmin= sprintf('%02d', $match[1]);
			$endhour= sprintf('%02d', $match[2]);
			$businessdays= explode(',', $match[3]);

			if (preg_match("/$this->re_Holidays/ms", $contents, $match)) {
				$holidaysdisabled= FALSE;
			}
			else if (preg_match("/$this->re_DisabledHolidays/ms", $contents, $match)) {
				$holidaysdisabled= TRUE;
			}
			$holidays= explode(',', $match[1]);

			if (preg_match("/$this->re_Flush/ms", $contents, $match)) {
				$flushdisabled= FALSE;
			}
			else if (preg_match("/$this->re_DisabledFlush/ms", $contents, $match)) {
				$flushdisabled= TRUE;
			}
			$startmin= sprintf('%02d', $match[1]);
			$starthour= sprintf('%02d', $match[2]);
			$flushdays= explode(',', $match[3]);
			
			return serialize(array($businessdaysdisabled, $holidaysdisabled, $flushdisabled,
				$startmin, $starthour, $endmin, $endhour, $businessdays, $holidays, $flushdays));
		}
		return FALSE;
	}

	/** Gets active crontab.
	 *
	 * Do not read root's crontab file directly, it has header comment lines.
	 */
	function GetCrontab()
	{
		exec('/usr/bin/crontab -l', $output, $retval);
		if ($retval === 0) {
			return implode("\n", $output);
		}
		ViewError(implode("\n", $output));
		cwc_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Failed listing crontab');
		return FALSE;
	}

	function DisableAfterHoursBusinessDays()
	{
		if ($this->CommentAfterHoursLine($this->re_BusinessDays)) {
			/// Flush line should be in sync with business hours line.
			return $this->CommentAfterHoursLine($this->re_Flush);
		}
		return FALSE;
	}

	function EnableAfterHoursBusinessDays()
	{
		if ($this->UncommentAfterHoursLine($this->re_DisabledBusinessDays)) {
			/// Flush line should be in sync with business hours line.
			return $this->UncommentAfterHoursLine($this->re_DisabledFlush);
		}
		return FALSE;
	}

	function DisableAfterHoursHolidays()
	{
		return $this->CommentAfterHoursLine($this->re_Holidays);
	}

	function EnableAfterHoursHolidays()
	{
		return $this->UncommentAfterHoursLine($this->re_DisabledHolidays);
	}

	/** Sets after hours definitions in cron file.
	 *
	 * @param[in]	$sh	string Start hour.
	 * @param[in]	$sm	string Start minute.
	 * @param[in]	$eh	string End hour.
	 * @param[in]	$em	string End minute.
	 * @param[in]	$businessdayslist string Comma separated business days.
	 * @param[in]	$holidayslist string Comma separated holidays.
	 */
	function ApplyAfterHours($sh, $sm, $eh, $em, $businessdayslist, $holidayslist)
	{
		if (($contents= $this->GetCrontab()) !== FALSE) {
			// Remove leading zeros
			$starthour= $sh + 0;
			$startmin= $sm + 0;
			$endhour= $eh + 0;
			$endmin= $em + 0;

			$re_replace= "$endmin	$endhour	*	*	$businessdayslist	/sbin/pfctl -a AfterHours -f /etc/pf.conf.afterhours";
			if ($newcontents= preg_replace("/$this->re_BusinessDays/ms", $re_replace, $contents)) {
				$contents= $newcontents;
			}

			$re_replace= "*	*	*	*	$holidayslist	/sbin/pfctl -a AfterHours -f /etc/pf.conf.afterhours";
			if ($newcontents= preg_replace("/$this->re_Holidays/ms", $re_replace, $contents)) {
				$contents= $newcontents;
			}

			$re_replace= "$startmin	$starthour	*	*	$businessdayslist	/sbin/pfctl -a AfterHours -Fr";
			if ($newcontents= preg_replace("/$this->re_Flush/ms", $re_replace, $contents)) {
				$contents= $newcontents;
			}

			if ($this->CheckAfterHoursOverlap($contents)) {
				return $this->InstallNewCrontab($contents);
			}
		}
		return FALSE;
	}

	/** Comments the line that matches the give regexp.
	 *
	 * @param[in]	$re	string RE of the line to comment.
	 */
	function CommentAfterHoursLine($re)
	{
		if (($contents= $this->GetCrontab()) !== FALSE) {
			if (preg_match("/$re/ms", $contents, $match)) {
				if ($contents= preg_replace("/$re/ms", '#'.$match[0], $contents)) {
					return $this->InstallNewCrontab($contents);
				}
			}
		}
		return FALSE;
	}

	/** Uncomments the line that matches the give regexp.
	 *
	 * @param[in]	$re	string RE of the line to uncomment.
	 */
	function UncommentAfterHoursLine($re)
	{
		if (($contents= $this->GetCrontab()) !== FALSE) {
			if (preg_match("/$re/ms", $contents, $match)) {
				if ($contents= preg_replace("/$re/ms", substr($match[0], 1), $contents)) {
					if ($this->CheckAfterHoursOverlap($contents)) {
						return $this->InstallNewCrontab($contents);
					}
				}
			}
		}
		return FALSE;
	}

	/** Installs given crontab contents.
	 *
	 * Crontab contents should be installed by running crontab,
	 * otherwise they are not activated just by directly editing crontab files.
	 *
	 * @param[in]	$contents	string Crontab contents.
	 */
	function InstallNewCrontab($contents)
	{
		// PutFile() does not create the file if it does not exist already
		exec("/usr/bin/touch $this->tmpCrontab");
		// crontab complains without newline at the end of last line
		if ($this->PutFile($this->tmpCrontab, $contents.PHP_EOL)) {
			exec("/usr/bin/crontab $this->tmpCrontab", $output, $retval);
			if ($retval === 0) {
				return TRUE;
			}
			ViewError(implode("\n", $output));
		}
		cwc_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Failed installing new crontab');
		return FALSE;
	}

	/** Checks if there is any overlap between business days and holidays in
	 * the given file contents.
	 *
	 * @param[in]	$contents	string Cron file contents to check.
	 * @return TRUE if no overlap.
	 */
	function CheckAfterHoursOverlap($contents)
	{
		$businessdays= array();
		if (preg_match("/$this->re_BusinessDays/ms", $contents, $match)) {
			$businessdays= explode(',', $match[3]);
		}
		$holidays= array();
		if (preg_match("/$this->re_Holidays/ms", $contents, $match)) {
			$holidays= explode(',', $match[1]);
		}

		if (count(array_intersect($businessdays, $holidays)) > 0) {
			ViewError(_('Business days and holidays cannot overlap.'));
			return FALSE;
		}
		return TRUE;
	}
	
	function ParseLogLine($logline, &$cols)
	{
		global $Re_Ip;
	
		// Sep 28 03:50:22.683986 rule 39/(match) pass in on em1: 10.0.0.11.40284 > 10.0.0.13.443: S 3537547021:3537547021(0) win 5840 <mss 1460,sackOK,timestamp 6440374[|tcp]> (DF)
		// Sep 27 14:32:21.715363 rule 37/(match) pass in on em1: 10.0.0.11.40546 > 10.0.0.13.22: S 2853072521:2853072521(0) win 5840 <mss 1460,sackOK,timestamp 3609332[|tcp]> (DF)
		// Sep 28 02:57:36.888668 rule 47/(match) pass out on em1: 10.0.0.13.16227 > 194.27.110.130.123: v4 client strat 0 poll 0 prec 0 [tos 0x10]
		// 
		// Sep 26 14:26:28.605638 rule 11/(match) block in on em1: 10.0.0.11.59299 > 239.255.255.250.1900: udp 132 (DF) [ttl 1]
		// 
		// Sep 28 03:50:20.124951 rule 11/(match) block in on em1: 10.0.0.11 > 224.0.0.22: igmp-2 [v2] (DF) [tos 0xc0] [ttl 1]
		// 
		// Sep 28 03:50:16.900084 rule 11/(match) block in on em1: 10.0.0.11.5353 > 224.0.0.251.5353: 0*- [0q] 7/0/0[|domain] (DF)
		// Sep 28 03:30:02.676705 rule 47/(match) pass out on em1: 10.0.0.13.41578 > 10.0.0.2.53: 61952+% [1au][|domain]
		// 
		// Sep 28 03:50:03.858844 rule 11/(match) block in on em0: 10.0.0.2.67 > 255.255.255.255.68: xid:0xf2ed6079 flags:0x8000 [|bootp] (DF)
		// Sep 28 03:50:03.858828 rule 11/(match) block in on em0: 0.0.0.0.68 > 255.255.255.255.67: xid:0xf2ed6079 [|bootp] [tos 0x10]
		// 
		// Sep 28 03:50:03.749086 rule 11/(match) block in on em1: fe80::21f:e2ff:fe61:969a > ff02::2: icmp6: router solicitation
		// Sep 28 03:49:54.705294 rule 11/(match) block in on em0: :: > ff02::1:ff61:969a: [|icmp6]
		// Sep 26 17:46:27.631418 rule 11/(match) block in on em0: :: > ff02::16: HBH icmp6: type-#143 [hlim 1]

		// Oct 18 05:16:52.230791 rule 11/(match) block in on em1: fe80::a00:27ff:fe4f:2af9.546 > ff02::1:2.547:dhcp6 solicit [hlim 1]

		$re_datetime= '(\w+\s+\d+)\s+(\d+:\d+:\d+)\.\d+';
		$re_rule= 'rule\s+([\w\.]+)\/\S+';
		$re_action= '(block|pass|match)';
		$re_direction= '(in|out)';
		$re_if= 'on\s+(\w+\d+):';
		$re_srcipport= "($Re_Ip|[\w:]+)(.(\d+)|)";
		$re_dstipport= "($Re_Ip|[\w:]+)(.(\d+)|)";
		$re_rest= '(.*)';
		
		$re= "/^$re_datetime\s+$re_rule\s+$re_action\s+$re_direction\s+$re_if\s+$re_srcipport\s+>\s+$re_dstipport:\s*$re_rest$/";
		if (preg_match($re, $logline, $match)) {
			$cols['Date']= $match[1];
			$cols['Time']= $match[2];
			$cols['Rule']= $match[3];
			$cols['Act']= $match[4];
			$cols['Dir']= $match[5];
			$cols['If']= $match[6];
			$cols['SrcIP']= $match[7];
			$cols['SPort']= $match[9];
			$cols['DstIP']= $match[10];
			$cols['DPort']= $match[12];
			$rest= $match[13];

			$re= '/(tcp|udp|domain|icmp6|icmp\b|igmp\-2|igmp\b|bootp|\back\b|v4|dhcp\d*)/';
			if (preg_match($re, $rest, $match)) {
				$cols['Type']= $match[1];
			}
			else {
				$cols['Type']= 'other';
			}
			$cols['Log']= $rest;
			return TRUE;
		}
		return FALSE;
	}
	
	function GetDateRegexp($date)
	{
		global $MonthNames;

		if ($date['Month'] == '') {
			$re= '.*';
		}
		else {
			$re= $MonthNames[$date['Month']].'\s+';
			if ($date['Day'] == '') {
				$re.= '.*';
			}
			else {
				$re.= sprintf('%02d', $date['Day']);
			}
		}
		return $re;
	}

	function GetFileLineCount($file, $re= '')
	{
		global $TCPDUMP;
		
		$cmd= "$TCPDUMP $file";
		if ($re !== '') {
			$re= escapeshellarg($re);
			$cmd.= " | /usr/bin/grep -a -E $re";
		}
		$cmd.= ' | /usr/bin/wc -l';
		
		// OpenBSD wc returns with leading blanks
		return trim($this->RunShellCommand($cmd));
	}
	
	function GetLogs($file, $end, $count, $re= '')
	{
		global $TCPDUMP;

		$cmd= "$TCPDUMP $file";
		if ($re !== '') {
			$re= escapeshellarg($re);
			$cmd.= " | /usr/bin/grep -a -E $re";
		}
		$cmd.= " | /usr/bin/head -$end | /usr/bin/tail -$count";
		
		$lines= explode("\n", $this->RunShellCommand($cmd));
		
		$logs= array();
		foreach ($lines as $line) {
			unset($Cols);
			if ($this->ParseLogLine($line, $Cols)) {
				$logs[]= $Cols;
			}
		}
		return serialize($logs);
	}
	
	function GetLiveLogs($file, $count, $re= '')
	{
		global $TCPDUMP;
		
		$cmd= "$TCPDUMP $file";
		if ($re !== '') {
			$re= escapeshellarg($re);
			$cmd.= " | /usr/bin/grep -a -E $re";
		}
		$cmd.= " | /usr/bin/tail -$count";
		
		$lines= explode("\n", $this->RunShellCommand($cmd));
		
		$logs= array();
		foreach ($lines as $line) {
			if ($this->ParseLogLine($line, $Cols)) {
				$logs[]= $Cols;
			}
		}
		return serialize($logs);
	}
	
	function GetPfwCmdWrapperInfo()
	{
		return $this->RunShellCommand("$this->PF_PATH/bin/commandwrapper.sh localhost -info");
	}

	function GetPfwCmdWrapperPfInfo()
	{
		return $this->RunShellCommand("$this->PF_PATH/bin/commandwrapper.sh localhost -pfinfo");
	}

	function GetPfwCmdWrapperPfMem()
	{
		return $this->RunShellCommand("$this->PF_PATH/bin/commandwrapper.sh localhost -pfmem");
	}

	function GetPfwCmdWrapperPfStates($countadjusted, $re)
	{
		$re= escapeshellarg($re);
		return $this->RunShellCommand("$this->PF_PATH/bin/commandwrapper.sh localhost -pfstates $countadjusted $re");
	}

	function GetPfwCmdWrapperQueues($pfhost)
	{
		return $this->RunShellCommand("$this->PF_PATH/bin/commandwrapper.sh $pfhost -queues");
	}

	function GetPfwCmdWrapperConntest($pfhost)
	{
		return $this->RunShellCommand("$this->PF_PATH/bin/commandwrapper.sh $pfhost -conntest");
	}

	function GetPfwCmdWrapperLog($pfhost, $count, $re)
	{
		$re= escapeshellarg($re);
		return $this->RunShellCommand("$this->PF_PATH/bin/commandwrapper.sh $pfhost -log -$count $re");
	}

	function GetPfwPfFileName($pfhost)
	{
		return $this->RunShellCommand("$this->PF_PATH/bin/packetfilter.sh $pfhost -r");
	}
	
	function GetPfwPfTest($pfhost, $file)
	{
		exec("$this->PF_PATH/bin/packetfilter.sh $pfhost -t $file", $output, $retval);
		// Return value of this shell script is not 0, do not use $retval === 0 here
		if ($retval) {
			return implode("\n", $output);
		}
		return FALSE;
	}

	function GetPfwPfInstall($pfhost, $file)
	{
		exec("$this->PF_PATH/bin/packetfilter.sh $pfhost -i $file", $output, $retval);
		// Return value of this shell script is not 0, do not use $retval === 0 here
		if ($retval) {
			return implode("\n", $output);
		}
		return FALSE;
	}
}
?>
