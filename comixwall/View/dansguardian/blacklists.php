<?php
/* $ComixWall: blacklists.php,v 1.12 2009/11/25 14:27:21 soner Exp $ */

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

class Blacklists extends View
{
	public $Model= 'blacklists';
	
	function Blacklists()
	{
		$this->ConfHelpMsg= _HELPWINDOW('You can search categories on this page.');
	}
	
	function FormatLogCols(&$cols)
	{
		$link= 'http://'.$cols['Site'];
		$cols['Site']= '<a href="'.$link.'">'.wordwrap($cols['Site'], 100, '<br />', TRUE).'</a>';
	}
}

$View= new Blacklists();

/** Displays a form to search sites/urls in black lists.
 *
 * @param[in]	$site	Search string.
 */
function PrintSiteCategorySearchForm($site)
{
	?>
	<table>
		<tr>
			<td>
				<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
					<?php echo _TITLE('Search site').': ' ?>
					<input type="text" name="SearchSite" style="width: 200px;" maxlength="100" value="<?php echo $site ?>"/>
					<input type="submit" name="Search" value="<?php echo _CONTROL('Search') ?>"/>
				</form>
			</td>
			<td>
				<?php
				PrintHelpBox(_HELPBOX2('Here you can search sites and urls in category listings. Regexp below can help you further refine your search.'));
				?>
			</td>
		</tr>
	</table>
	<?php
}

/** Appends (prefixes) blacklist category descriptions table to cats page help boxes.
 *
 * @param[out]	$msg	Help box string to append to.
 */
function AppendCatsDescTable(&$msg)
{
	$catdescs= array(
		'ads'				=>	_HELPWINDOW('Advert servers and banned URLs'),
		'adult'				=>	_HELPWINDOW('Sites containing adult material such as swearing but not porn'),
		'aggressive'		=>	_HELPWINDOW('Similar to violence but more promoting than depicting'),
		'antispyware'		=>	_HELPWINDOW('Sites that remove spyware'),
		'artnudes'			=>	_HELPWINDOW('Art sites containing artistic nudity'),
		'audio-video'		=>	_HELPWINDOW('Sites with audio or video downloads'),
		'banking'			=>	_HELPWINDOW('Banking websites'),
		'beerliquorinfo'	=>	_HELPWINDOW('Sites with information only on beer or liquors'),
		'beerliquorsale'	=>	_HELPWINDOW('Sites with beer or liquors for sale'),
		'cellphones'		=>	_HELPWINDOW('stuff for mobile/cell phones'),
		'chat'				=>	_HELPWINDOW('Sites with chat rooms etc'),
		'childcare'			=>	_HELPWINDOW('Sites to do with childcare'),
		'clothing'			=>	_HELPWINDOW('Sites about and selling clothing'),
		'culnary'			=>	_HELPWINDOW('Sites about cooking et al'),
		'dating'			=>	_HELPWINDOW('Sites about dating'),
		'desktopsillies'	=>	_HELPWINDOW('Sites containing screen savers, backgrounds, cursers, pointers. desktop themes and similar timewasting and potentially dangerous content'),
		'dialers'			=>	_HELPWINDOW('Sites with dialers such as those for pornography or trojans'),
		'drugs'				=>	_HELPWINDOW('Drug related sites'),
		'ecommerce'			=>	_HELPWINDOW('Sites that provide online shopping'),
		'entertainment'		=>	_HELPWINDOW('Sites that promote movies, books, magazine, humor'),
		'frencheducation'	=>	_HELPWINDOW('Sites to do with french education'),
		'gambling'			=>	_HELPWINDOW('Gambling sites including stocks and shares'),
		'gardening'			=>	_HELPWINDOW('Gardening sites'),
		'government'		=>	_HELPWINDOW('Military and schools etc'),
		'hacking'			=>	_HELPWINDOW('Hacking/cracking information'),
		'homerepair'		=>	_HELPWINDOW('Sites about home repair'),
		'hygiene'			=>	_HELPWINDOW('Sites about hygiene and other personal grooming related stuff'),
		'instantmessaging'	=>	_HELPWINDOW('Sites that contain messenger client download and web-based messaging sites'),
		'jewelry'			=>	_HELPWINDOW('Sites about and selling jewelry'),
		'jobsearch'			=>	_HELPWINDOW('Sites for finding jobs'),
		'kidstimewasting'	=>	_HELPWINDOW('Sites kids often waste time on'),
		'mail'				=>	_HELPWINDOW('Webmail and email sites'),
		'naturism'			=>	_HELPWINDOW('Sites that contain nude pictures and/or promote a nude lifestyle'),
		'news'				=>	_HELPWINDOW('News sites'),
		'onlineauctions'	=>	_HELPWINDOW('Online auctions'),
		'onlinegames'		=>	_HELPWINDOW('Online gaming sites'),
		'onlinepayment'		=>	_HELPWINDOW('Online payment sites'),
		'personalfinance'	=>	_HELPWINDOW('Personal finance sites'),
		'pets'				=>	_HELPWINDOW('Pet sites'),
		'phishing'			=>	_HELPWINDOW('Sites attempting to trick people into giving out private information.'),
		'porn'				=>	_HELPWINDOW('Pornography'),
		'proxy'				=>	_HELPWINDOW('Sites with proxies to bypass filters'),
		'radio'				=>	_HELPWINDOW('non-news related radio and television'),
		'religion'			=>	_HELPWINDOW('Sites promoting religion'),
		'ringtones'			=>	_HELPWINDOW('Sites containing ring tones, games, pictures and other'),
		'searchengines'		=>	_HELPWINDOW('Search engines such as google'),
		'sexuality'			=>	_HELPWINDOW('Sites dedicated to sexuality, possibly including adult material'),
		'sportnews'			=>	_HELPWINDOW('Sport news sites'),
		'sports'			=>	_HELPWINDOW('All sport sites'),
		'spyware'			=>	_HELPWINDOW('Sites who run or have spyware software to download'),
		'updatesites'		=>	_HELPWINDOW('Sites where software updates are downloaded from including virus sigs'),
		'vacation'			=>	_HELPWINDOW('Sites about going on holiday'),
		'violence'			=>	_HELPWINDOW('Sites containing violence'),
		'virusinfected'		=>	_HELPWINDOW('Sites who host virus infected files'),
		'warez'				=>	_HELPWINDOW('Sites with illegal pirate software'),
		'weather'			=>	_HELPWINDOW('Weather news sites and weather related'),
		'weapons'			=>	_HELPWINDOW('Sites detailing or selling weapons'),
		'webmail'			=>	_HELPWINDOW('Just webmail sites'),
		'whitelist'			=>	_HELPWINDOW('Contains site specifically 100% suitable for kids'),
	);
	
	$msg.= ''._HELPWINDOW("Although this is called a 'blacklist', the categories can be used as white or grey lists also. Being listed does not infer that the site is bad, these are just lists of sites.\n\n");
	
	$deschtml= '<table class="table"><tr><th>'._HELPWINDOW('Category').'</th><th>'._HELPWINDOW('Description').'</th></tr>';
	foreach ($catdescs as $cat => $desc) {
		$deschtml.= "<tr><td id=\"cat\">$cat</td><td id=\"catdesc\">$desc</td></tr>";
	}
	$deschtml.= "</table>";

	$msg.= $deschtml;
}

$LogFile= $_SESSION[$View->Model]['LogFile'];
$SearchSite= $_SESSION[$View->Model]['SearchSite'];

if ($_POST['SearchSite']) {
	$SearchSite= $_POST['SearchSite'];
	$_SESSION[$View->Model]['SearchSite']= $SearchSite;
	
	if ($View->Controller($Output, 'SearchSite', $SearchSite)) {
		$LogFile= $Output[0];
		$_SESSION[$View->Model]['LogFile']= $LogFile;
	}
}

require_once($VIEW_PATH.'header.php');
		
PrintSiteCategorySearchForm($SearchSite);

if ($LogFile) {
	ProcessStartLine($StartLine);
	UpdateLogsPageSessionVars($LinesPerPage, $SearchRegExp);

	/// @todo GetLogs here, compute LogSize using Logs, this is double work otherwise
	$View->Controller($Output, 'GetFileLineCount', $LogFile, $SearchRegExp);
	$LogSize= $Output[0];

	ProcessNavigationButtons($LinesPerPage, $LogSize, $StartLine, $HeadStart);

	PrintLogHeaderForm($StartLine, $LogSize, $LinesPerPage, $SearchRegExp, $CustomHiddenInputs);
	?>
	<table id="logline">
		<?php
		PrintTableHeaders($View->Model);
		?>
		<?php
		$View->Controller($Output, 'GetLogs', $LogFile, $HeadStart, $LinesPerPage, $SearchRegExp);
		$Logs= unserialize($Output[0]);

		$LineCount= $StartLine + 1;
		foreach ($Logs as $Log) {
			$View->PrintLogLine($Log, $LineCount++);
		}
		?>
	</table>
	<?php
}
AppendCatsDescTable($View->ConfHelpMsg);

PrintHelpWindow($View->ConfHelpMsg);
require_once($VIEW_PATH.'footer.php');
?>
