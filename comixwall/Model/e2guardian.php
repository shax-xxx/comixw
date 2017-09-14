<?php
/* $ComixWall: e2guardian.php,v 1.37 2009/11/26 20:50:19 soner Exp $ */

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

class E2guardian extends Model
{
	public $Name= 'e2guardian';
	// Need root at startup
	public $User= 'root|_e2guardian';
	
	private $ipLists= array();
	private $listConfig= array();
	
	public $NVPS= '=';
	public $ConfFile= '/etc/e2guardian/e2guardian.conf';
	
	protected $confDir= '/etc/e2guardian/';
	
	private $re_DgNet= '';
	private $re_DgRange= '';
	
	public $LogFile= '/var/log/e2guardian/e2guardian.log';
						
	public $VersionCmd= '/usr/local/sbin/e2guardian -v';

	public $PidFile= '/var/run/e2guardian.pid';
	
	function e2guardian()
	{
		global $TmpFile, $Re_Ip;
		
		parent::Model();
		
		$this->StartCmd= "/usr/local/sbin/e2guardian > $TmpFile 2>&1 &";
		
		$this->re_DgNet= "$Re_Ip\/$Re_Ip";
		$this->re_DgRange= "$Re_Ip-$Re_Ip";
		
		$this->ipLists= array(
				'exceptionlist'	=> 'exceptioniplist',
				'bannedlist' 	=> 'bannediplist',
			);

		$this->listConfig= array(
			'sites'			=> array(
				'exception'			=> 'exceptionsitelist',
				'gray'				=> 'greysitelist',
				'banned'			=> 'bannedsitelist',
				'GetFunc'			=> 'GetSiteList',
				'DelFunc'			=> 'DelSiteList',
				'AddFunc'			=> 'AddSiteList',
				),

			'urls'			=>	array(
				'exception'			=> 'exceptionurllist',
				'gray'				=> 'greyurllist',
				'banned'			=> 'bannedurllist',
				'GetFunc'			=> 'GetUrlList',
				'DelFunc'			=> 'DelUrlList',
				'AddFunc'			=> 'AddUrlList',
				),

			'exts'			=>	array(
				'exception'			=> 'exceptionextensionlist',
				'gray'				=> 'greyextensionlist',
				'banned'			=> 'bannedextensionlist',
				),

			'mimes'			=>	array(
				'exception'			=> 'exceptionmimetypelist',
				'gray'				=> 'greymimetypelist',
				'banned'			=> 'bannedmimetypelist',
				),

			'dm_exts'		=>	array(
				'MetaConfigFile'	=> $this->confDir.'downloadmanagers/fancy.conf',
				'exception'			=> 'managedextensionlist',
				),

			'dm_mimes'		=>	array(
				'MetaConfigFile'	=> $this->confDir.'downloadmanagers/fancy.conf',
				'exception'			=> 'managedmimetypelist',
				),

			'virus_sites'	=>	array(
				'MetaConfigFile'	=> $this->confDir.'contentscanners/clamdscan.conf',
				'exception'			=> 'exceptionvirussitelist',
				'GetFunc'			=> 'GetSiteList',
				'DelFunc'			=> 'DelSiteList',
				'AddFunc'			=> 'AddSiteList',
				),

			'virus_urls'	=>	array(
				'MetaConfigFile'	=> $this->confDir.'contentscanners/clamdscan.conf',
				'exception'			=> 'exceptionvirusurllist',
				'GetFunc'			=> 'GetUrlList',
				'DelFunc'			=> 'DelUrlList',
				'AddFunc'			=> 'AddUrlList',
				),

			'virus_exts'	=>	array(
				'MetaConfigFile'	=> $this->confDir.'contentscanners/clamdscan.conf',
				'exception'			=> 'exceptionvirusextensionlist',
				),

			'virus_mimes'	=>	array(
				'MetaConfigFile'	=> $this->confDir.'contentscanners/clamdscan.conf',
				'exception'			=> 'exceptionvirusmimetypelist',
				),

			// Used by Cats only
			'phrases'		=>	array(
				'exception'			=> 'exceptionphraselist',
				'banned'			=> 'bannedphraselist',
				'weighted'			=> 'weightedphraselist',
				),
			);

		$this->Commands= array_merge(
			$this->Commands,
			array(
				'DelIpFilterGrp'=>	array(
					'argv'	=>	array(IPADR|DGIPRANGE),
					'desc'	=>	_('Delete filter group IP'),
					),

				'SetIpFilterGrp'=>	array(
					'argv'	=>	array(NUM, IPADR|DGIPRANGE),
					'desc'	=>	_('Add filter group IP'),
					),

				'DelIp'		=>	array(
					'argv'	=>	array(NAME, IPADR|DGIPRANGE),
					'desc'	=>	_('Delete IP'),
					),

				'AddIp'		=>	array(
					'argv'	=>	array(NAME, IPADR|DGIPRANGE),
					'desc'	=>	_('Add IP'),
					),

				'CreateNewGroup'=>	array(
					'argv'	=>	array(NUM),
					'desc'	=>	_('Create new group'),
					),
				
				'GetGroupCount'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get group count'),
					),

				'GetGroupIpList'=>	array(
					'argv'	=>	array(NUM),
					'desc'	=>	_('Get group ip list'),
					),

				'GetAuthIpList'=>	array(
					'argv'	=>	array(NAME),
					'desc'	=>	_('Get auth ip list'),
					),
				
				'GetList'		=>	array(
					'argv'	=>	array(NUM, NAME, NAME),
					'desc'	=>	_('Get list'),
					),

				'GetExtMimeList'=>	array(
					'argv'	=>	array(NUM, NAME, NAME),
					'desc'	=>	_('Get ext/mime list'),
					),

				'DelSiteUrl'	=>	array(
					'argv'	=>	array(NUM, NAME, NAME, URL),
					'desc'	=>	_('Delete list item'),
					),
				
				'AddSiteUrl'	=>	array(
					'argv'	=>	array(NUM, NAME, NAME, URL),
					'desc'	=>	_('Add list item'),
					),

				'DisableExtMime'	=>	array(
					'argv'	=>	array(NUM, NAME, NAME, EXT|MIME),
					'desc'	=>	_('Disable ext/mime'),
					),

				'EnableExtMime'	=>	array(
					'argv'	=>	array(NUM, NAME, NAME, EXT|MIME),
					'desc'	=>	_('Enable ext/mime'),
					),

				'DelExtMime'	=>	array(
					'argv'	=>	array(NUM, NAME, NAME, EXT|MIME),
					'desc'	=>	_('Delete ext/mime'),
					),

				'AddExtMime'	=>	array(
					/// @todo EXT|MIME accepts ext for mime or visa versa, fix this
					/// @todo Is there any pattern or size for comment, 5th param?
					'argv'	=>	array(NUM, NAME, NAME, EXT|MIME, STR),
					'desc'	=>	_('Add ext/mime'),
					),

				'GetEnabledCats'=>	array(
					'argv'	=>	array(NUM, NAME, NAME),
					'desc'	=>	_('Get active cats'),
					),

				'GetDisabledCats'=>	array(
					'argv'	=>	array(NUM, NAME, NAME),
					'desc'	=>	_('Get inactive cats'),
					),

				'TurnOnCats'	=>	array(
					'argv'	=>	array(NUM, NAME, NAME, NAME, NAME),
					'desc'	=>	_('Turn on cats'),
					),

				'TurnOffCats'	=>	array(
					'argv'	=>	array(NUM, NAME, NAME, NAME, NAME),
					'desc'	=>	_('Turn off cats'),
					),

				'GroupExists'	=>	array(
					'argv'	=>	array(NUM),
					'desc'	=>	_('Group exists'),
					),

				'SetTemplateIps'	=>	array(
					'argv'	=>	array(IPADR),
					'desc'	=>	_('Set template ips'),
					),
				)
			);
	}

	function GetConfFile($confname, $group)
	{
		if ($confname === 'GeneraldownloadsConfig') {
			$file= $this->confDir.'downloadmanagers/fancy.conf';
		}
		else if ($confname == 'blanketConfig') {
			$file= $this->GetGroupFile($group, 'sites', 'banned');
		}
		else if (preg_match('/^General.*Config$/', $confname)) {
			$file= $this->ConfFile;
		}
		else {
			$file= $this->confDir.'e2guardianf'.$group.'.conf';
		}
		return $file;
	}

	function SetConfig($confname)
	{
		global $GeneralbasicConfig, $GeneralfilterConfig, $GeneralscanConfig, $GenerallogsConfig, $GeneraldownloadsConfig, $GeneraladvancedConfig,
			$blanketConfig, $basicConfig, $scanConfig, $bypassConfig, $emailConfig;
		
		if ($confname !== '') {
			$this->Config= ${$confname};
		}
	}

	/** Returns the list of ips in exception or banned lists.
	 */
	function GetAuthIpList($list)
	{
		if ($filepath= $this->GetListFilePath($list)) {
			return $this->GetIps($filepath);
		}
		return FALSE;
	}

	/** Reads the list of IPs.
	 *
	 * @param[in]	$file	Config file pathname.
	 * @return List of IPs.
	 */
	function GetIps($file)
	{
		global $Re_Ip;

		return $this->SearchFileAll($file, "/^\h*($Re_Ip|$this->re_DgNet|$this->re_DgRange)\b\h*(|$this->COMC.*)$/m");
	}

	function AddIp($list, $ip)
	{
		if ($filepath= $this->GetListFilePath($list)) {
			$this->DelIp($list, $ip);
			return $this->AppendToFile($filepath, $ip);
		}
		return FALSE;
	}

	function DelIp($list, $ip)
	{
		if ($filepath= $this->GetListFilePath($list)) {
			$ip= Escape($ip, '/.');
			return $this->ReplaceRegexp($filepath, "/^(\h*$ip\b.*(\s|))/m", '');
		}
		return FALSE;
	}

	function GetListFilePath($list)
	{
		if ($filepath= $this->GetFilterConfFilePath($this->confDir.'e2guardian.conf', $this->ipLists[$list])) {
			return $filepath;
		}
		else {
			ViewError(_('Cannot read config').": $list");
		}
		return FALSE;
	}

	function AddSiteList($file, $site)
	{
		$this->DelSiteList($file, $site);
		return $this->AppendToFile($file, $site);
	}

	function AddUrlList($file, $url)
	{
		$this->DelUrlList($file, $url);
		return $this->AppendToFile($file, $url);
	}

	function DisableExtMime($group, $list, $type, $item)
	{
		if (($groupfile= $this->GetGroupFile($group, $list, $type)) !== FALSE) {
			return $this->DisableExt($groupfile, $item);
		}
		return FALSE;
	}

	function DisableExt($file, $ext)
	{
		$ext= Escape($ext, './');
		return $this->ReplaceRegexp($file, "/^(\h*$ext(\h*$this->COMC.*|\h*))$/m", $this->COMC.'${1}');
	}

	function EnableExtMime($group, $list, $type, $item)
	{
		if (($groupfile= $this->GetGroupFile($group, $list, $type)) !== FALSE) {
			return $this->EnableExt($groupfile, $item);
		}
		return FALSE;
	}

	function EnableExt($file, $ext)
	{
		$ext= Escape($ext, './');
		return $this->ReplaceRegexp($file, "/^\h*$this->COMC(\h*$ext(\h*$this->COMC.*|\h*))$/m", '${1}');
	}

	function AddExt($file, $ext, $cmt)
	{
		$this->DelExt($file, $ext);
		return $this->AppendToFile($file, "$ext $this->COMC $cmt");
	}

	function DelExt($file, $ext)
	{
		// Both extensions and mime types are deleted by this method
		$ext= Escape($ext, './');
		return $this->ReplaceRegexp($file, "/^(\h*($this->COMC\h*|)$ext\b.*(\s|))/m", '');
	}

	function DelSiteList($file, $site)
	{
		$site= Escape($site, '/.');
		return $this->ReplaceRegexp($file, "/^(\h*$site\b.*(\s|))/m", '');
	}

	function DelUrlList($file, $url)
	{
		$url= Escape($url, '/.');
		return $this->ReplaceRegexp($file, "/^(\h*$url\b.*(\s|))/m", '');
	}

	/** Gets the list of DG categories and subcats.
	 *
	 * Returns a list of all cats or subcats if those args are not provided.
	 *
	 * @param[in]	$file	Config file pathname.
	 * @param[in]	$cat	Category to get. Assumes all categories if not provided.
	 * @param[in]	$subcat	SubCategory to get. Assumes all subcats if not provided.
	 * @return List of cats/subcats.
	 */
	function GetCats($file, $cat= '[^#\s]+', $subcat= '[^#\s]+')
	{
		return $this->SearchFileAll($file, "/^\h*\.Include.*lists\/($cat\/$subcat)>.*$/m");
	}

	/** Reads DG configuration file pathname setting.
	 *
	 * @param[in]	$file	Config file pathname.
	 * @param[in]	$name	Name of the NVP.
	 * @return Value of the setting, file pathname.
	 */
	function GetFilterConfFilePath($file, $name)
	{
		return $this->GetNVP($file, $name, "'");
	}

	/** Gets the list off (commented) DG cats/subcats.
	 *
	 * @param[in]	$file	Config file pathname.
	 * @param[in]	$cat	Category to get. Assumes all categories if not provided.
	 * @param[in]	$subcat	SubCategory to get. Assumes all subcats if not provided.
	 * @return List of off cats/subcats.
	 */
	function GetOffCats($file, $cat= '[^#\s]+', $subcat= '[^#\s]+')
	{
		return $this->SearchFileAll($file, "/^\h*$this->COMC\h*\.Include.*lists\/($cat\/$subcat)>.*$/m");
	}

	/** Checks if site exists or gets the list of sites.
	 *
	 * @param[in]	$file	Config file pathname.
	 * @param[in]	$site	Site to get. Assumes all sites if not provided.
	 * @return Site name (i.e. site exits) or list of sites.
	 */
	function GetSiteList($file, $site= '[^.#\s]+[^\s]*')
	{
		$site= Escape($site, '/.');
		return $this->SearchFileAll($file, "/^\h*($site)\b.*\v*$/m");
	}

	/** Checks if url exists or gets the list of urls.
	 */
	function GetUrlList($file, $url= '[^.#\s]+[^\s]*')
	{
		$url= Escape($url, '/.');
		return $this->SearchFileAll($file, "/^\h*($url)\b.*\v*$/m");
	}

	/** Gets the list of extensions.
	 *
	 * @param[in]	$file	Config file pathname.
	 * @return List of extensions.
	 */
	function GetExts($file)
	{
		$re_ext= '\.[a-z0-9A-Z][a-z0-9A-Z_.]{0,10}';
		$re_mime= '[a-zA-Z][a-z0-9A-Z_-]{0,20}\/[a-z0-9A-Z_.-]{0,20}';
		
		$result= array();

		$contents= file_get_contents($file);
		
		$re= "($re_ext|$re_mime)\b\h*(|$this->COMC.*)";
		if (preg_match_all("/^\h*($re)$/m", $contents, $match)) {
			$output= array_values($match[1]);
			foreach ($output as $line) {
				if (preg_match("/^$re$/", $line, $match)) {
					$result[$match[1]]= array(
						'Comment'	=> $match[2],
						'Enabled'	=> TRUE,
					);
				}
			}
		}
		
		if (preg_match_all("/^\h*$this->COMC\h*($re)$/m", $contents, $match)) {
			$output= array_values($match[1]);
			foreach ($output as $line) {
				if (preg_match("/^$re$/", $line, $match)) {
					$result[$match[1]]= array(
						'Comment'	=> $match[2],
						'Enabled'	=> FALSE,
					);
				}
			}
		}
		return serialize($result);
	}

	/** Gets the list of enabled or disabled categories.
	 */
	function GetFuncCats($func, $group, $list, $type)
	{
		if (($groupfile= $this->GetGroupFile($group, $list, $type)) !== FALSE) {
			if (($output= $this->$func($groupfile)) !== FALSE) {
				return $output;
			}
			else {
				return '';
			}
		}
		return FALSE;
	}

	function TurnOffCats($group, $list, $type, $cat, $subcat)
	{
		return $this->FuncTurnCats('TurnOffCat', $group, $list, $type, $cat, $subcat);
	}

	function TurnOnCats($group, $list, $type, $cat, $subcat)
	{
		return $this->FuncTurnCats('TurnOnCat', $group, $list, $type, $cat, $subcat);
	}

	/** Enables or disables the given category.
	 */
	function FuncTurnCats($func, $group, $list, $type, $cat, $subcat)
	{
		if (($groupfile= $this->GetGroupFile($group, $list, $type)) !== FALSE) {
			if (($output= $this->$func($groupfile, $cat, $subcat)) !== FALSE) {
				return $output;
			}
			else {
				return '';
			}
		}
		return FALSE;
	}

	/** Turns on (uncomments) DG cat/subcat.
	 *
	 * @param[in]	$file	Config file pathname.
	 * @param[in]	$cat	Category.
	 * @param[in]	$subcat	SubCategory. Assumes all subcats if not provided.
	 */
	function TurnOnCat($file, $cat, $subcat= '[^#\s]+')
	{
		/// @todo No need to send CAT and SUBCAT separately, escape slashes in PHP and send
		return $this->ReplaceRegexp($file, "/^\h*$this->COMC(\s*\.Include.*lists\/$cat\/$subcat>.*)$/m", '${1}');
	}

	/** Turns off (comments out) DG cat/subcat.
	 *
	 * @param[in]	$file	Config file pathname.
	 * @param[in]	$cat	Category.
	 * @param[in]	$subcat	SubCategory. Assumes all subcats if not provided.
	 */
	function TurnOffCat($file, $cat, $subcat= '[^#\s]+')
	{
		/// @todo No need to send CAT and SUBCAT separately, escape slashes in View and send here
		return $this->ReplaceRegexp($file, "/^(\h*\.Include.*lists\/$cat\/$subcat>.*)$/m", $this->COMC.'${1}');
	}

	function GetList($group, $list, $type)
	{
		return $this->GetFuncList($this->listConfig[$list]['GetFunc'], $group, $list, $type);
	}

	function GetExtMimeList($group, $list, $type)
	{
		return $this->GetFuncList('GetExts', $group, $list, $type);
	}

	/** Gets the list of sites or urls.
	 */
	function GetFuncList($func, $group, $list, $type)
	{
		if (($groupfile= $this->GetGroupFile($group, $list, $type)) !== FALSE) {
			if (($output= $this->$func($groupfile)) !== FALSE) {
				return $output;
			}
			else {
				return '';
			}
		}
		return FALSE;
	}

	function DelSiteUrl($group, $list, $type, $item)
	{
		return $this->FuncSiteUrl($this->listConfig[$list]['DelFunc'], $group, $list, $type, $item);
	}

	function AddSiteUrl($group, $list, $type, $item)
	{
		return $this->FuncSiteUrl($this->listConfig[$list]['AddFunc'], $group, $list, $type, $item);
	}

	/** Adds or deletes the given site or url.
	 */
	function FuncSiteUrl($func, $group, $list, $type, $item)
	{
		if (($groupfile= $this->GetGroupFile($group, $list, $type)) !== FALSE) {
			return $this->$func($groupfile, $item);
		}
		return FALSE;
	}

	function DelExtMime($group, $list, $type, $item)
	{
		if (($groupfile= $this->GetGroupFile($group, $list, $type)) !== FALSE) {
			return $this->DelExt($groupfile, $item);
		}
		return FALSE;
	}

	function AddExtMime($group, $list, $type, $item, $comment)
	{
		if (($groupfile= $this->GetGroupFile($group, $list, $type)) !== FALSE) {
			return $this->AddExt($groupfile, $item, $comment);
		}
		return FALSE;
	}

	function GetEnabledCats($group, $list, $type)
	{
		return $this->GetFuncCats('GetCats', $group, $list, $type);
	}

	function GetDisabledCats($group, $list, $type)
	{
		return $this->GetFuncCats('GetOffCats', $group, $list, $type);
	}

	/** Gets ips listed in group
	 */
	function GetGroupIpList($group)
	{
		if ($filepath= $this->GetIpgroupsFilePath()) {
			return $this->GetGrpIps($filepath, $group);
		}
		return FALSE;
	}

	/** Gets the list of group IPs.
	 *
	 * @param[in]	$file	Config file pathname.
	 * @param[in]	$group	DG group.
	 * @return List of IPs.
	 */
	function GetGrpIps($file, $group= '[^#\s]+')
	{
		global $Re_Ip;

		return $this->SearchFileAll($file, "/^\h*($Re_Ip|$this->re_DgNet|$this->re_DgRange)\h*$this->NVPS\h*filter$group\b\h*(|$this->COMC.*)$/m");
	}

	/** Gets the ip.conf file pathname from master file.
	 */
	function GetIpgroupsFilePath()
	{
		if ($conffile= $this->GetFilterConfFilePath($this->confDir.'e2guardian.conf', 'authplugin')) {
			if ($filepath= $this->GetFilterConfFilePath($conffile, 'ipgroups')) {
				return $filepath;
			}
			else {
				ViewError(_('Cannot read config').': ipgroups');
			}
		}
		else {
			ViewError(_('Cannot read config').': authplugin');
		}
		return FALSE;
	}

	/** Adds IP and filter group pair.
	 *
	 * @param[in]	$group	Group to add IP to.
	 * @param[in]	$ip	IP to add.
	 * @todo Arg order differ from DelIpFilterGrp().
	 */
	function SetIpFilterGrp($group, $ip)
	{
		if ($file= $this->GetIpgroupsFilePath()) {
			$this->DelIpFilterGrp($ip, $group);
			$this->DelIpFilterGrp($ip, '.*');
			return $this->AppendToFile($file, "$ip = filter$group");
		}
		return FALSE;
	}

	/** Deletes IP and filter group pair.
	 *
	 * @param[in]	$ip	IP to delete.
	 * @param[in]	$group	Group to delete IP from.
	 */
	function DelIpFilterGrp($ip, $group= '[^#\s]+')
	{
		if ($file= $this->GetIpgroupsFilePath()) {
			$ip= Escape($ip, '/.');
			return $this->ReplaceRegexp($file, "/^(\h*$ip\b\h*$this->NVPS\h*filter$group\b\h*.*(\s|))/m", '');
		}
		return FALSE;
	}

	function GetGroupFile($group, $list, $type)
	{
		$metafile= $this->GetMetaFile($group, $list);

		$filename= $this->listConfig[$list][$type];
		if (($groupfile= $this->GetFilterConfFilePath($metafile, $filename)) !== FALSE) {
			return $groupfile;
		}
		ViewError(_('Cannot find group configuration file').": $type $list");
		return FALSE;
	}

	/** Returns meta file for the given group or list.
	 *
	 * e2guardian configuration is divided into many files,
	 * both group meta files and lists for groups.
	 *
	 * @param[in]	$group int Group
	 * @param[in]	$list string List name used as array index: sites, ext, etc.
	 */
	function GetMetaFile($group, $list)
	{
		if (isset($this->listConfig[$list]['MetaConfigFile'])) {
			$metafile= $this->listConfig[$list]['MetaConfigFile'];
		}
		else {
			$metafile= $this->confDir.'e2guardianf'.$group.'.conf';
		}
		return $metafile;
	}

	/** Gets number of groups
	 */
	function GetGroupCount()
	{
		return $this->GetNVP($this->confDir.'e2guardian.conf', 'filtergroups');
	}

	/** Checks if the meta file exists for the given group.
	 */
	function GroupExists($group)
	{
		return file_exists($this->confDir.'e2guardianf'.$group.'.conf');
	}

	/** Creates a new web filter group.
	 *
	 * Creates a new web filter group based on the active group for
	 * the current session.
	 *
	 * Makes a copy of some of the configuration files listed in
	 * $filenames array. Missing files cause warnings, but are not fatal,
	 * so that restricted and privileged groups can be copied too.
	 *
	 * @param[in]	$group int Group to copy
	 */
	function CreateNewGroup($group)
	{
		$filenames= array(
			'bannedphraselist',
			'weightedphraselist',
			'exceptionphraselist',
			'bannedsitelist',
			'greysitelist',
			'exceptionsitelist',
			'bannedurllist',
			'greyurllist',
			'exceptionurllist',
 			//'exceptionregexpurllist',
 			//'bannedregexpurllist',
 			//'picsfile',
 			//'contentregexplist',
 			//'urlregexplist',
			'bannedextensionlist',
			'bannedmimetypelist',
			'exceptionfilesitelist',
			'exceptionfileurllist',
 			//'headerregexplist',
 			//'bannedregexpheaderlist'
			);

		$result= TRUE;
		$fatal= FALSE;
		$info= "GetNVP: $this->ConfFile: filtergroups";
		if (($output= $this->GetNVP($this->ConfFile, 'filtergroups')) !== FALSE) {
			ViewError(_('SUCCESS').": $info");
			cwc_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "SUCCESS: $info");

			$newgroup= $output + 1;

			$conffile= $this->confDir."e2guardianf$group.conf";
			$newconffile= $this->confDir."e2guardianf$newgroup.conf";

			$info= 'File exists check: '.$newconffile;
			if (!file_exists($newconffile)) {
				ViewError(_('SUCCESS').": $info");
				cwc_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "SUCCESS: $info");

				$info= "File copy: $conffile $newconffile";
				exec("/bin/cp -p $conffile $newconffile 2>&1", $output, $retval);
				if ($retval === 0) {
					ViewError(_('SUCCESS').": $info");
					cwc_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "SUCCESS: $info");

					foreach ($filenames as $Name) {
						$info= "GetNVP: $conffile: $Name";
						if (($output= $this->GetNVP($conffile, $Name)) !== FALSE) {
							ViewError(_('SUCCESS').": $info");
							cwc_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "SUCCESS: $info");

							$groupfile= rtrim(trim($output, "'"), $group);
							$newgroupfile= $groupfile.$newgroup;

							$info= "File exists check: $newgroupfile";
							if (!file_exists($newgroupfile)) {
								ViewError(_('SUCCESS').": $info");
								cwc_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "SUCCESS: $info");

								$info= "File copy: $groupfile $newgroupfile";
								exec("/bin/cp -p $groupfile $newgroupfile 2>&1", $output, $retval);
								if ($retval === 0) {
									ViewError(_('SUCCESS').": $info");
									cwc_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "SUCCESS: $info");

									$info= "SetNVP: $newconffile: $Name= $newgroupfile";
									if ($this->SetNVP($newconffile, $Name, "'$newgroupfile'")) {
										ViewError(_('SUCCESS').": $info");
										cwc_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "SUCCESS: $info");
									}
									else {
										$result= FALSE;
										$fatal= TRUE;
										ViewError(_('FAILED').": $info");
										cwc_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "FAILED: $info");
									}
								}
								else {
									$result= FALSE;
									$fatal= TRUE;
									ViewError(_('FAILED').": $info ".implode("\n", $output));
									cwc_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "FAILED: $info ".implode("\n", $output));
								}
							}
							else {
								$result= FALSE;
								ViewError(_('FAILED').": $info");
								cwc_syslog(LOG_WARNING, __FILE__, __FUNCTION__, __LINE__, "FAILED: $info");
							}
						}
						else {
							$result= FALSE;
							ViewError(_('FAILED').": $info");
							cwc_syslog(LOG_WARNING, __FILE__, __FUNCTION__, __LINE__, "FAILED: $info");
						}
					}
				}
				else {
					$result= FALSE;
					$fatal= TRUE;
					ViewError(_('FAILED').": $info ".implode("\n", $output));
					cwc_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "FAILED: $info ".implode("\n", $output));
				}
			}
			else {
				$result= FALSE;
				$error= _('FAILED').": $info";
				ViewError(_('FAILED').": $info");
				cwc_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "FAILED: $info");
			}
		}
		else {
			$result= FALSE;
			$fatal= TRUE;
			ViewError(_('FAILED').": $info");
			cwc_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "FAILED: $info");
		}

		if (!$fatal) {
			$info= "SetNVP: $this->ConfFile: filtergroups $newgroup";
			if ($this->SetNVP($this->ConfFile, 'filtergroups', $newgroup)) {
					ViewError(_('SUCCESS').": $info");
					cwc_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "SUCCESS: $info");
			}
			else {
				$result= FALSE;
				$fatal= TRUE;
				ViewError(_('FAILED').": $info");
				cwc_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "FAILED: $info");
			}

			if (!$result) {
				ViewError("\n"._("One or more actions failed while creating new group\nExamine the report above to make sure this is the intended result!"));
			}
			else {
				ViewError("\n"._('Group created successfully').": $newgroup");
			}
		}

		if ($fatal) {
			ViewError("\n"._('FATAL ERRORS in creating new group'));
		}
		return !$fatal && $result;
	}

	/** Updates template files with the given ip.
	 *
	 * Used by installer or when system interface configuration is modified.
	 */
	function SetTemplateIps($ip)
	{
		global $Re_Ip;
		
		$langpath= '/usr/local/share/e2guardian/languages/';
	
		$re= "|(http://)($Re_Ip)(/images/comix.png)|m";
		$retval=  $this->ReplaceRegexp($langpath.'ukenglish/fancydmtemplate.html', $re, '${1}'.$ip.'${3}');
		$retval&= $this->ReplaceRegexp($langpath.'ukenglish/template.html', $re, '${1}'.$ip.'${3}');
		$retval&= $this->ReplaceRegexp($langpath.'ukenglish/template_nobypass.html', $re, '${1}'.$ip.'${3}');
		$retval&= $this->ReplaceRegexp($langpath.'turkish/fancydmtemplate.html', $re, '${1}'.$ip.'${3}');
		$retval&= $this->ReplaceRegexp($langpath.'turkish/template.html', $re, '${1}'.$ip.'${3}');
		$retval&= $this->ReplaceRegexp($langpath.'turkish/template_nobypass.html', $re, '${1}'.$ip.'${3}');
		
		$re= "|(http://)($Re_Ip)(/images/info.png)|m";
		$retval&= $this->ReplaceRegexp($langpath.'ukenglish/fancydmtemplate.html', $re, '${1}'.$ip.'${3}');
		$retval&= $this->ReplaceRegexp($langpath.'turkish/fancydmtemplate.html', $re, '${1}'.$ip.'${3}');
	
		
		$re= "|(http://)($Re_Ip)(/images/error.png)|m";
		$retval&= $this->ReplaceRegexp($langpath.'ukenglish/template.html', $re, '${1}'.$ip.'${3}');
		$retval&= $this->ReplaceRegexp($langpath.'ukenglish/template_nobypass.html', $re, '${1}'.$ip.'${3}');
		$retval&= $this->ReplaceRegexp($langpath.'turkish/template.html', $re, '${1}'.$ip.'${3}');
		$retval&= $this->ReplaceRegexp($langpath.'turkish/template_nobypass.html', $re, '${1}'.$ip.'${3}');

		return $retval;
	}
}

/** Basic group configuration.
 */
$basicConfig = array(
    'groupmode' => array(
        'type' => UINT_0_2,
		),
    'groupname' => array(
        'type' => STR_SING_QUOTED,
		),
);

/** Content filtering configuration.
 */
$scanConfig = array(
    'naughtynesslimit' => array(
        'type' => UINT,
		),
    'categorydisplaythreshold' => array(
        'type' => INT_M1_0_UP,
		),
    'embeddedurlweight' => array(
        'type' => UINT_0_1,
		),
    'enablepics' => array(
        'type' => STR_on_off,
		),
    'disablecontentscan' => array(
        'type' => STR_on_off,
		),
    'deepurlanalysis' => array(
        'type' => STR_on_off,
		),
);

/** Bypass configuration.
 */
$bypassConfig = array(
    'bypass' => array(
        'type' => INT_M1_0_UP,
		),
    'bypasskey' => array(
        'type' => STR_SING_QUOTED,
		),
    'infectionbypass' => array(
        'type' => INT_M1_0_UP,
		),
    'infectionbypasskey' => array(
        'type' => STR_SING_QUOTED,
		),
    'infectionbypasserrorsonly' => array(
        'type' => STR_on_off,
		),
);

/** E-mail configuration.
 */
$emailConfig = array(
    'usesmtp' => array(
        'type' => STR_on_off,
		),
    'mailfrom' => array(
        'type' => STR_SING_QUOTED,
		),
    'avadmin' => array(
        'type' => STR_SING_QUOTED,
		),
    'contentadmin' => array(
        'type' => STR_SING_QUOTED,
		),
    'avsubject' => array(
        'type' => STR_SING_QUOTED,
		),
    'contentsubject' => array(
        'type' => STR_SING_QUOTED,
		),
    'notifyav' => array(
        'type' => STR_on_off,
		),
    'notifycontent' => array(
        'type' => STR_on_off,
		),
    'thresholdbyuser' => array(
        'type' => STR_on_off,
		),
    'violations' => array(
        'type' => UINT,
		),
    'threshold' => array(
        'type' => UINT,
		),
);

/** Blanket block configuration.
 */
$blanketConfig = array(
    // **
    '\*\*' => array(
        'type' => FALSE,
		),
    // **s
    '\*\*s' => array(
        'type' => FALSE,
		),
    // *ip
    '\*ip' => array(
        'type' => FALSE,
		),
    // *ips
    '\*ips' => array(
        'type' => FALSE,
		),
);

/** General log configuration.
 */
$GenerallogsConfig = array(
    'reportinglevel' => array(
        'type' => INT_M1_0_3,
		),
    'loglevel' => array(
        'type' => UINT_0_3,
		),
    'logexceptionhits' => array(
        'type' => STR_on_off,
		),
    'logfileformat' => array(
        'type' => UINT_1_4,
		),
    'maxlogitemlength' => array(
        'type' => UINT,
		),
    'anonymizelogs' => array(
        'type' => STR_on_off,
		),
    'loglocation' => array(),
    'statlocation' => array(),
    'logclienthostnames' => array(
        'type' => STR_on_off,
		),
    'logconnectionhandlingerrors' => array(
        'type' => STR_on_off,
		),
    'logchildprocesshandling' => array(
        'type' => STR_on_off,
		),
    'logadblocks' => array(
        'type' => STR_on_off,
		),
);

/** General basic configuration.
 */
$GeneralbasicConfig = array(
    'language' => array(
        'type' => STR_SING_QUOTED,
		),
    'languagedir' => array(),
    'filterip' => array(
        'type' => IP,
		),
    'filterport' => array(
        'type' => PORT,
		),
    'proxyip' => array(
        'type' => IP,
		),
    'proxyport' => array(
        'type' => PORT,
		),
    'accessdeniedaddress' => array(
        'type' => STR_SING_QUOTED,
		),
    'nonstandarddelimiter' => array(
        'type' => STR_on_off,
		),
    'usecustombannedimage' => array(
        'type' => UINT_0_1,
		),
    'custombannedimagefile' => array(),
);

/** General filter configuration.
 */
$GeneralfilterConfig = array(
    'showweightedfound' => array(
        'type' => STR_on_off,
		),
    'weightedphrasemode' => array(
        'type' => UINT_0_2,
		),
    'urlcachenumber' => array(
        'type' => UINT,
		),
    'urlcacheage' => array(
        'type' => UINT,
		),
    'scancleancache' => array(
        'type' => STR_on_off,
		),
    'phrasefiltermode' => array(
        'type' => UINT_0_3,
		),
    'preservecase' => array(
        'type' => UINT_0_1,
		),
    'hexdecodecontent' => array(
        'type' => UINT_0_1,
		),
    'forcequicksearch' => array(
        'type' => UINT_0_1,
		),
    'reverseaddresslookups' => array(
        'type' => STR_on_off,
		),
    'reverseclientiplookups' => array(
        'type' => STR_on_off,
		),
    'createlistcachefiles' => array(
        'type' => STR_on_off,
		),
);

/** General content scanner configuration.
 */
$GeneralscanConfig = array(
    'maxcontentfiltersize' => array(
        'type' => UINT,
		),
    'maxcontentramcachescansize' => array(
        'type' => UINT,
		),
    'maxcontentfilecachescansize' => array(
        'type' => UINT,
		),
    'deletedownloadedtempfiles' => array(
        'type' => STR_on_off,
		),
    'initialtrickledelay' => array(
        'type' => UINT,
		),
    'trickledelay' => array(
        'type' => UINT,
		),
    'contentscannertimeout' => array(
        'type' => UINT,
		),
    'contentscanexceptions' => array(
        'type' => STR_on_off,
		),
    'recheckreplacedurls' => array(
        'type' => STR_on_off,
		),
);

/** Fancy download manager plugin configuration.
 */
$GeneraldownloadsConfig = array(
    'useragentregexp' => array(
        'type' => STR_SING_QUOTED,
		),
    'maxdownloadsize' => array(
        'type' => UINT,
		),
);

/** General advanced configuration.
 */
$GeneraladvancedConfig = array(
    'forwardedfor' => array(
        'type' => STR_on_off,
		),
    'usexforwardedfor' => array(
        'type' => STR_on_off,
		),
    'maxchildren' => array(
        'type' => UINT,
		),
    'minchildren' => array(
        'type' => UINT,
		),
    'minsparechildren' => array(
        'type' => UINT,
		),
    'preforkchildren' => array(
        'type' => UINT,
		),
    'maxsparechildren' => array(
        'type' => UINT,
		),
    'maxagechildren' => array(
        'type' => UINT,
		),
    'maxips' => array(
        'type' => UINT,
		),
    'nologger' => array(
        'type' => STR_on_off,
		),
    'softrestart' => array(
        'type' => STR_on_off,
		),
    'mailer' => array(
        'type' => STR_SING_QUOTED,
		),
);
?>
