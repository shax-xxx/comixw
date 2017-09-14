<?php
/* $ComixWall: blacklists.php,v 1.8 2009/11/23 11:21:22 soner Exp $ */

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
 * Search blacklists used by E2sguardian.
 */

require_once($MODEL_PATH.'e2guardian.php');

class Blacklists extends E2guardian
{
	public $Name= 'blacklists';
	
	public $LogFile= '/var/tmp/comixwall/search.out';
	
	private $blacklistsPath= '';

	function Blacklists()
	{
		parent::Model();
		
		$this->blacklistsPath= $this->confDir.'lists/blacklists/';
		
		$this->Commands= array_merge(
			$this->Commands,
			array(
				'SearchSite'=>	array(
					'argv'	=>	array(REGEXP),
					'desc'	=>	_('Search site'),
					),
				)
			);
	}

	/** Searches a given site/url in blacklists.
	 *
	 * Skips *.processed files generated by E2guardian at first startup.
	 *
	 * @param[in]	$site string search string
	 * @return File path of search results or FALSE on failure
	 */
	function SearchSite($site)
	{
		$dir= dirname($this->LogFile);
		if (!file_exists($dir)) {
			exec('/bin/mkdir -p '.$dir);
		}

		// Do not list results from domain/url.processed files,
		// *.processed files are generated by e2guardian
		$site = '.*'.$site.'.*'; // build regexp
		$site= escapeshellarg($site);
		$cmd= "/usr/bin/find $this->blacklistsPath \! -name \"*.processed\" -exec /usr/bin/grep -IoH -E $site {} \; > $this->LogFile 2>&1";
		exec($cmd, $output, $retval);

		return $retval <= 1 ? $this->LogFile : FALSE;
	}

	/** Search result parser.
	 *
	 * This method does what all other ParseLogLine() methods do on log files,
	 * except that this works on search results. Function name and signature
	 * should be the same, because blacklists search results are displayed by
	 * other logs methods.
	 */
	function ParseLogLine($logline, &$cols)
	{
		$re= "|^$this->blacklistsPath(\S+)/(\S+):(.*)$|";
		if (preg_match($re, $logline, $match)) {
			$cols['Category']= ucwords($match[1].' '.$match[2]);
			$cols['Site']= $match[3];
			return TRUE;
		}
		return FALSE;
	}
}
?>
