<?php
/* $ComixWall: credits.php,v 1.23 2009/11/21 21:55:59 soner Exp $ */

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
 * Credits page for the open source software used in this project.
 */

require_once('include.php');

$Credits= array(
	array(
		'Name'	=> 'OpenBSD',
		'Desc'	=> _TITLE2('The most secure OS in the world. Underneath ComixWall is pure, untouched OpenBSD.'),
		'Img'	=> 'openbsd.gif',
		'Link'	=> 'http://openbsd.org',
	),
	array(
		'Name'	=> 'OpenSSH',
		'Desc'	=> _TITLE2('Free secure shell provides remote shell access to the system. It is believed that almost 90% of all secure shell traffic on the Internet is provided by OpenSSH. OpenSSH is developed by the OpenBSD project.'),
		'Img'	=> 'openssh.png',
		'Link'	=> 'http://www.openssh.org',
	),
	array(
		'Name'	=> 'Apache',
		'Desc'	=> _TITLE2('The most famous web server.'),
		'Img'	=> 'apache.png',
		'Link'	=> 'http://www.apache.org',
	),
	array(
		'Name'	=> 'ClamAV',
		'Desc'	=> _TITLE2('Open source anti-virus software.'),
		'Img'	=> 'clamav.png',
		'Link'	=> 'http://www.clamav.net',
	),
	array(
		'Name'	=> 'Squid',
		'Desc'	=> _TITLE2('De-facto standard among HTTP proxies.'),
		'Img'	=> 'squid.png',
		'Link'	=> 'http://www.squid-cache.org/',
	),
	array(
		'Name'	=> 'E2Guardian',
		'Desc'	=> _TITLE2('Content scanning web filter.'),
		'Img'	=> 'dansguardian.png',
		'Link'	=> 'http://e2guardian.org/',
	),
	array(
		'Name'	=> 'Snort',
		'Desc'	=> _TITLE2('De-facto standard IDS (Intrusion Detection System).'),
		'Img'	=> 'snort.png',
		'Link'	=> 'http://www.snort.org/',
	),
	array(
		'Name'	=> 'Snort2pf',
		'Desc'	=> _TITLE2('Turns Snort IDS into an IPS (Intrusion Prevention System). Replaced by SnortIPS.'),
		'Img'	=> 'noimage.png',
		'Link'	=> 'http://sourceforge.net/projects/snort2pf/',
	),
	array(
		'Name'	=> 'PHP',
		'Desc'	=> _TITLE2('Free OOP scripting language.'),
		'Img'	=> 'php.gif',
		'Link'	=> 'http://www.php.net/',
	),
	array(
		'Name'	=> 'symon',
		'Desc'	=> _TITLE2('System monitoring software. Most of the rrd based graphs are generated based on symon data.'),
		'Img'	=> 'symon.png',
		'Link'	=> 'http://www.xs4all.nl/~wpd/symon/',
	),
	array(
		'Name'	=> 'pfw',
		'Desc'	=> _TITLE2('Web interface for configuring and monitoring OpenBSD/pf.'),
		'Img'	=> 'pfw.png',
		'Link'	=> 'http://allard.nu/pfw/',
	),
	array(
		'Name'	=> 'pmacct',
		'Desc'	=> _TITLE2('Promiscuous mode accounting daemon collects IP based network usage information. Network usage graphs are generated based on pmacct data.'),
		'Img'	=> 'noimage.png',
		'Link'	=> 'http://www.pmacct.net/',
	),
	array(
		'Name'	=> 'OpenVPN',
		'Desc'	=> _TITLE2('Modern virtual private networking.'),
		'Img'	=> 'openvpn.png',
		'Link'	=> 'http://openvpn.org/',
	),
	array(
		'Name'	=> 'FSF',
		'Desc'	=> _TITLE2('The Free Software Foundation.'),
		'Img'	=> 'gnu.png',
		'Link'	=> 'http://www.fsf.org',
	),
);

require_once($VIEW_PATH.'header.php');
?>
<table id="nvp">
	<?php
	$Row= 1;
	foreach ($Credits as $Software) {
		$Class= $Row++ % 2 == 0 ? 'evenline' : 'oddline';
		?>
		<tr class="<?php echo $Class ?>">
			<td class="creditstitle">
				<?php echo $Software['Name'] ?>
			</td>
			<td>
				<?php echo _($Software['Desc']) ?>
			</td>
			<td class="none">
				<a href="<?php echo $Software['Link'] ?>" name="<?php echo $Software['Name'] ?>"><img src="<?php echo $IMG_PATH.'credits/'.$Software['Img'] ?>" name="<?php echo $Software['Name'] ?>" alt="<?php echo $Software['Name'] ?>" border="0"></a>
			</td>
		</tr>
		<?php
	}
	?>
</table>
<?php
PrintHelpWindow(_HELPWINDOW('ComixWall would not be possible without the generous developers of many Free and Open Source Software (FOSS). ComixWall is developed using FOSS only. Software developed specifically for ComixWall, such as this web user interface and SnortIPS, are released under the BSD license, hence FOSS also.

These credits may not cover all of the software ComixWall uses. If you think one is missing, I would happily add here.

Thanks for the excellent work and good spirit.'));
require_once($VIEW_PATH.'footer.php');
?>
