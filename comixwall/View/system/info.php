<?php
/* $ComixWall: info.php,v 1.21 2009/11/21 21:55:58 soner Exp $ */

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

require_once('include.php');

/** Parses the uptime and hw info output from pfw.
 *
 * @param[in]	$infolines	string uptime output, run elsewhere
 * @return $info array with parsed info
 */
function cw_parse_info($infolines)
{
	preg_match('/up (.*), \d+ user.*averages: (\d[\d\.,]+)[,\s]+(\d[\d\.,]+)[,\s]+(\d[\d\.,]+)/', $infolines[0], $match);
	$info['uptime'] = $match[1];
	$info['load_1'] = $match[2];
	$info['load_5'] = $match[3];
	$info['load_15'] = $match[4];
	$info['date'] = $infolines[1];
	$info['securelevel'] = $infolines[2];
	$info['ip4forward'] = $infolines[3];
	$info['ip6forward'] = $infolines[4];
	$info['hostname'] = $infolines[5];
	$info['os'] = $infolines[6];
	$info['cpu'] = $infolines[7];

	return $info;
}

$Reload= TRUE;
require_once($VIEW_PATH.'header.php');
		
$View->Controller($Output, 'GetSystemInfo');
$System= cw_parse_info($Output);

GetHwInfo(array('machine', 'ncpu', 'cpuspeed', 'physmem', 'diskcount', 'disknames', 'product', 'vendor', 'uuid'), $Hardware);
?>
<table id="nvp" style="width: 600px;">
	<tr class="oddline">
		<td class="title"><?php echo _TITLE('Version') ?></td>
		<td>ComixWall <?php echo VERSION ?></td>
	</tr>
	<tr class="oddline">
		<td class="title"><?php echo _TITLE('Operating System') ?></td>
		<td><?php echo $System['os'] ?></td>
	</tr>
	<tr class="evenline">
		<td class="title"><?php echo _TITLE('Hostname') ?></td>
		<td><?php echo $System['hostname'] ?></td>
	</tr>
	<tr class="oddline">
		<td class="title"><?php echo _TITLE('Uptime') ?></td>
		<td><?php echo $System['uptime'] ?></td>
	</tr>
	<tr class="oddline">
		<td class="title"><?php echo _TITLE('Date') ?></td>
		<td><?php echo $System['date'] ?></td>
	</tr>
	<tr class="evenline">
		<td class="title"><?php echo _TITLE('Machine') ?></td>
		<td><?php echo $Hardware['machine'] ?></td>
	</tr>
	<tr class="evenline">
		<td class="title"><?php echo _TITLE('Processor') ?></td>
		<td><?php echo $System['cpu'] ?></td>
	</tr>
	<tr class="evenline">
		<td class="title"><?php echo _TITLE('CPUs') ?></td>
		<td><?php echo $Hardware['ncpu'] ?> @ <?php echo $Hardware['cpuspeed'] ?> MHz</td>
	</tr>
	<tr class="evenline">
		<td class="title"><?php echo _TITLE('CPU Load') ?></td>
		<td>
		<?php echo _TITLE('1 minute average').': '.$System['load_1'] ?><br />
		<?php echo _TITLE('5 minute average').': '.$System['load_5'] ?><br />
		<?php echo _TITLE('15 minute average').': '.$System['load_15'] ?></td>
	</tr>
	<tr class="oddline">
		<td class="title"><?php echo _TITLE('Physical Memory') ?></td>
		<td><?php echo round($Hardware['physmem']/1048576) ?> MB</td>
	</tr>
	<tr class="evenline">
		<td class="title"><?php echo _TITLE('Disks') ?></td>
		<td><?php echo $Hardware['diskcount'] ?>: <?php echo $Hardware['disknames'] ?></td>
	</tr>
	<tr class="evenline">
		<td class="title"><?php echo _TITLE('Partitions') ?></td>
		<td>
		<?php
		if ($View->Controller($Output, 'GetPartitionsPfw')) {
			?>
			<table>
			<?php
			foreach ($Output as $Partition) {
				if (preg_match('/^(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)$/', $Partition, $Match)) {
					?>
					<tr>
					<?php
					for ($i= 1; $i <= 6; $i++) {
						?>
						<td class="partitions"><?php echo $Match[$i] ?></td>
						<?php
					}
					?>
					</tr>
					<?php
				}
			}
			?>
			</table>
			<?php
		}
		?>
		</td>
	</tr>
	<tr class="oddline">
		<td class="title"><?php echo _TITLE('Motherboard') ?></td>
		<td><?php echo $Hardware['product'] ?> by <?php echo $Hardware['vendor'] ?></td>
	</tr>
	<tr class="oddline">
		<td class="title"><?php echo _TITLE('Serial Number') ?></td>
		<td><?php echo $Hardware['uuid'] ?></td>
	</tr>
	<tr class="evenline">
		<td class="title"><?php echo _TITLE('Secure Level') ?></td>
		<td><?php echo $System['securelevel'] ?></td>
	</tr>
	<tr class="evenline">
		<td class="title"><?php echo _TITLE('IP Forwarding') ?></td>
		<td>
		<?php
			$Status= $System['ip4forward'] === '1' ? _TITLE('enabled'):_TITLE('disabled');
			echo _('IPv4').' '.$Status;
		?>
		<br />
		<?php
			$Status= $System['ip6forward'] === '1' ? _TITLE('enabled'):_TITLE('disabled');
			echo _('IPv6').' '.$Status;
		?>
		</td>
	</tr>
</table>
<?php
require_once($VIEW_PATH.'footer.php');
?>
