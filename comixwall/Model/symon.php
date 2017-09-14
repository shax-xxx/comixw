<?php
/* $ComixWall: symon.php,v 1.6 2009/11/16 12:05:36 soner Exp $ */

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
 * System monitoring.
 */

require_once($MODEL_PATH.'model.php');

class Symon extends Model
{
	public $Name= 'symon';
	public $User= '_symon';
	
	public $VersionCmd= '/usr/local/libexec/symon -v 2>&1';
	
	private $layoutsPath= '/var/www/htdocs/comixwall/View/symon/layouts';
	private $rrdsPath= '/var/www/htdocs/comixwall/View/symon/rrds/localhost';
	
	function Symon()
	{
		global $TmpFile;
		
		parent::Model();
		
		$this->StartCmd= "/usr/local/libexec/symon > $TmpFile 2>&1 &";
		
		$this->Commands= array_merge(
			$this->Commands,
			array(
				'SetCpus'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Set symon cpus'),
					),
				
				'SetIfs'=>	array(
					'argv'	=>	array(NAME, NAME),
					'desc'	=>	_('Set symon ifs'),
					),
				
				'SetSensors'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Set symon sensors'),
					),
				
				'SetPartitions'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Set symon partitions'),
					),
				
				'SetConf'=>	array(
					'argv'	=>	array(NAME, NAME),
					'desc'	=>	_('Set symon conf'),
					),
				)
			);
	}
	
	function SetCpus()
	{
		$ncpu= 1;
		if (($hwncpu= $this->GetSysCtl('hw.ncpu')) !== FALSE) {
			if (preg_match('|^hw.ncpu=(\d+)$|ms', $hwncpu, $match)) {
				$ncpu= $match[1];
			}
		}
		
		$layout= '';
		for ($c= 0; $c < $ncpu; $c++) {
			$layout.= "graph	rrdfile=$this->rrdsPath/cpu$c.rrd, title=\"CPU $c\";\n";
		}

		$re= '|(\s*group\h+name\h*=\h*"CPU\h+Load";\s*)(.*)|ms';
		return $this->ReplaceRegexp($this->layoutsPath.'/cpus.layout', $re, '${1}'.$layout);
	}

	function SetIfs($lanif, $wanif)
	{
		$re= '|^(\s*graph\s+rrdfile\s*=\s*[\w/]+/if_)(\w+\d+)(\.rrd\s*,\s*title\s*=\s*"Internal Interface\s+\()(\w+\d+)(\)\s*"\s*;)|ms';
		$retval=  $this->ReplaceRegexp($this->layoutsPath.'/ifs.layout', $re, '${1}'.$lanif.'${3}'.$lanif.'${5}');
		
		$re= '|^(\s*graph\s+rrdfile\s*=\s*[\w/]+/if_)(\w+\d+)(\.rrd\s*,\s*title\s*=\s*"External Interface\s+\()(\w+\d+)(\)\s*"\s*;)|ms';
		$retval&= $this->ReplaceRegexp($this->layoutsPath.'/ifs.layout', $re, '${1}'.$wanif.'${3}'.$wanif.'${5}');

		$re= '|(\s+lan_rrd\s*=\s*[\w/]+/if_)(\w+\d+)(\.rrd\s*,\s*)|ms';
		$retval&= $this->ReplaceRegexp($this->layoutsPath.'/states.layout', $re, '${1}'.$lanif.'${3}');
		
		$re= '|(\s+wan_rrd\s*=\s*[\w/]+/if_)(\w+\d+)(\.rrd\s*,\s*)|ms';
		$retval&= $this->ReplaceRegexp($this->layoutsPath.'/states.layout', $re, '${1}'.$wanif.'${3}');
		
		return $retval;
	}

	function SetSensors()
	{
		if (($sensorslist= $this->GetSensors()) !== FALSE) {
			$layout= '';
			if (isset($sensorslist['temp']) && count($sensorslist['temp']) > 0) {
				foreach ($sensorslist['temp'] as $s) {
					$layout.= "graph	rrdfile=$this->rrdsPath/sensor_$s.rrd, title=\"Temperature ($s)\";\n";
				}
			}
			
			$layout.= 'group	name="Fan";'."\n";
			if (isset($sensorslist['fan']) && count($sensorslist['fan']) > 0) {
				foreach ($sensorslist['fan'] as $s) {
					$layout.= "graph	rrdfile=$this->rrdsPath/sensor_$s.rrd, title=\"Fan ($s)\";\n";
				}
			}
		
			$re= '|(\s*group\h+name\h*=\h*"Temperature";\s*)(.*)|ms';
			return $this->ReplaceRegexp($this->layoutsPath.'/sensors.layout', $re, '${1}'.$layout);
		}
		return FALSE;
	}

	function SetPartitions()
	{
		$partitions= $this->GetPartitions();
		
		$layout= '';
		$disks= array();
		foreach ($partitions as $part => $mdir) {
			if (preg_match('|/dev/((\w+\d+)[a-z]+)|', $part, $match)) {
				$p= $match[1];
				$layout.= "graph	rrdfile=$this->rrdsPath/df_$p.rrd, title=\"Partition $p ($mdir)\";\n";
				
				if (!in_array($match[2], $disks)) {
					$disks[]= $match[2];
				}
			}
		}
		
		$re= '|(\s*group\h+name\h*=\h*"Partitions\h+Usages";\s*)(.*)|ms';
		$retval=  $this->ReplaceRegexp($this->layoutsPath.'/partitions.layout', $re, '${1}'.$layout);
	
		$layout= '';
		foreach ($disks as $d) {
			$layout.= "graph	rrdfile=$this->rrdsPath/io_$d.rrd, title=\"Disk $d\";\n";
		}
		
		$re= '|(\s*group\h+name\h*=\h*"Disk\h+I/O";\s*)(.*)|ms';
		$retval&= $this->ReplaceRegexp($this->layoutsPath.'/disks.layout', $re, '${1}'.$layout);

		return $retval;
	}

	function SetConf($lanif, $wanif)
	{
		/// @todo See the new OpenBSD installer to get this and sensors paths
		$ncpu= 1;
		if (($hwncpu= $this->GetSysCtl('hw.ncpu')) !== FALSE) {
			if (preg_match('|^hw.ncpu=(\d+)$|ms', $hwncpu, $match)) {
				$ncpu= $match[1];
			}
		}

		$cpus= '';
		for ($i= 0; $i < $ncpu; $i++) {
			$cpus.= "	cpu($i),\n";
		}
		
		$others= "	mem,\n	pf,\n	mbuf,\n";
		
		$ifs= "	if(lo0),\n	if($lanif),\n	if($wanif),\n";
		
		$partitions= $this->GetPartitions();
		$parts= '';
		$disks= array();
		foreach ($partitions as $part => $mdir) {
			if (preg_match('|/dev/((\w+\d+)[a-z]+)|', $part, $match)) {
				$parts.= '	df('.$match[1]."),\n";
				if (!in_array($match[2], $disks)) {
					$disks[]= $match[2];
				}
			}
		}

		$ios= '';
		foreach ($disks as $io) {
			$ios.= "	io($io),\n";
		}
		
		$sensors= '';
		if (($sensorslist= $this->GetSensors()) !== FALSE) {
			if (isset($sensorslist['temp']) && count($sensorslist['temp']) > 0) {
				foreach ($sensorslist['temp'] as $s) {
					$sensors.= "	sensor($s),\n";
				}
			}
			
			if (isset($sensorslist['fan']) && count($sensorslist['fan']) > 0) {
				foreach ($sensorslist['fan'] as $s) {
					$sensors.= "	sensor($s),\n";
				}
			}
		}

		$proclist= array(
			'httpd',
			'dansguardian',
			'snort',
			'clamd',
			'p3scan',
			'perl',
			'sshd',
			'squid',
			'smtp-gated',
			'named',
			'dhcpd',
			'sockd',
			'imspector',
			'ftp-proxy',
			'openvpn',
		);
		$procs= '';
		foreach ($proclist as $p) {
			$procs.= "	proc($p),\n";
		}
		
		$conf= "\n".$cpus.$others.$ifs.$ios.$parts.$sensors.$procs;
		
		$re= '|(\s*monitor\s*\{\h*)([^\}]*)(\s*\})|ms';
		$retval=  $this->ReplaceRegexp('/etc/symon.conf', $re, '${1}'.$conf.'${3}');

		$conf= preg_replace('/(	)/ms', '		', $conf);
		
		$re= '|(\s*source\s*127\.0\.0\.1\s*\{\s*accept\s*\{\h*)([^\}]*)(\h*\})|ms';
		$retval&= $this->ReplaceRegexp('/etc/symux.conf', $re, '${1}'.$conf.'	${3}');

		return $retval;
	}
}
?>
