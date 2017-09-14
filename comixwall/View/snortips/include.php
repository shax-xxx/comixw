<?php
/* $ComixWall: include.php,v 1.22 2009/12/01 15:00:15 soner Exp $ */

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

class Snortips extends View
{
	public $Model= 'snortips';
	public $Layout= 'snortips';
	
	function Snortips()
	{
		$this->LogsHelpMsg= _HELPWINDOW("Here are the definitions of a few terms used in the logs:<ul class='nomargin'><li class='nomargin'>Blocking a host means adding it to IPS pf table as blocked</li><li class='nomargin'>Unblocking means deleting a blocked host from the table</li><li class='nomargin'>Deblocking means adding a whitelisted host to the table</li><li class='nomargin'>Undeblocking means deleting a whitelisted host from the table</li></ul>
		Failure to block a host does not necessarily indicate an error; the host may be in the table already.");
		
		$this->GraphHelpMsg= _HELPWINDOW('SnortIPS is a perl process. These graphs display data from all perl processes.');
		
		$this->ConfHelpMsg= _HELPWINDOW('The IDS produces many alerts. Some alerts may be more serious than others, hence most alerts have priorities. You can configure the IPS to block only alerts at a certain priority and up. Each alert also contains log and classification text. You can add keywords to match within such text. The IP in the matching alert is blocked. If the alert does not contain an IP address, no action is taken.');
	
		$this->Config = array(
			'Priority' => array(
				'title' => _TITLE2('Priority'),
				'info' => _HELPBOX2('This is the priority in the alerts. Alerts at this priority and up will be used to block IPs.'),
				),
			'BlockDuration' => array(
				'title' => _TITLE2('Block Duration'),
				'info' => _HELPBOX2('Temporary block duration in seconds on each alert.'),
				),
			'MaxBlockDuration' => array(
				'title' => _TITLE2('Max Block Duration'),
				'info' => _HELPBOX2('Total of block extensions cannot be higher than this value.'),
				),
		);
	}

	/** Displays parsed log line.
	 *
	 * @param[in]	$cols		Columns parsed.
	 * @param[in]	$linenum	Line number to print as the first column.
	 */
	function PrintLogLine($cols, $linenum)
	{
		$this->PrintLogLineClass($cols['Log']);

		PrintLogCols($linenum, $cols);
		echo '</tr>';
	}

	/** Displays white or black listed IPs form.
	 *
	 * @param[in]	$list		string Name: white or black list
	 * @param[in]	$title		string Title to display
	 * @param[in]	$helpmsg	string Help string
	 */
	function PrintListedIPsForm($list, $title, $helpmsg)
	{
		global $Row;

		$class= $Row++ % 2 == 0 ? 'evenline' : 'oddline';
		?>
		<tr class="<?php echo $class ?>">
			<td class="title">
				<?php echo $title.':' ?>
			</td>
			<td>
				<?php
				$cmd= $list == 'whitelist' ? 'GetAllowedIps' : 'GetRestrictedIps';
				$this->Controller($ips, $cmd);
				/// @attention The first invisible Add button is identical to the second
				/// to make Add the default form action, so that we save 3 html lines.
				?>
				<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
					<input style="display:none;" type="submit" name="Add" value="<?php echo _CONTROL('Add') ?>"/>
					<select name="IPs[]" multiple style="width: 200px; height: 100px;">
						<?php
						foreach ($ips as $ip) {
							?>
							<option value="<?php echo $ip ?>"><?php echo $ip ?></option>
							<?php
						}
						?>
					</select>
					<input type="submit" name="Delete" value="<?php echo _CONTROL('Delete') ?>"/><br />
					<input type="text" name="IPToAdd" style="width: 200px;" maxlength="18"/>
					<input type="submit" name="Add" value="<?php echo _CONTROL('Add') ?>"/>
					<input type="hidden" name="List" value="<?php echo $list ?>" />
				</form>
			</td>
			<td class="none">
				<?php
				PrintHelpBox($helpmsg);
				?>
			</td>
		</tr>
		<?php
	}

	/** Displays a list of blocked, blacklisted, or whitelisted IPs.
	 *
	 * Also allows the user to add or delete blocked IPs.
	 */
	function PrintBlockedIPsForm()
	{
		global $ADMIN;
		?>
		<td>
			<?php
			/// @todo Do not run this command if SnortIPS is not running
			if ($this->Controller($output, 'GetInfo')) {
				$info= unserialize($output[0]);

				$blocked= count($info['Blocked']);
				$whitelisted= count($info['Whitelisted']);
				$blacklisted= count($info['Blacklisted']);
				$managed= $whitelisted + $blocked + $blacklisted;

				echo $managed.' '._TITLE2('managed').': '.$whitelisted.' '._TITLE2('whitelisted').', '.$blocked.' '._TITLE2('blocked').', '.$blacklisted.' '._TITLE2('blacklisted');
				?>
				<table id="ipsmanaged">
					<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
						<tr>
							<th><?php echo _TITLE2('Host') ?></th>
							<th><?php echo _TITLE2('Time to expire (secs)') ?></th>
						</tr>
						<?php
						foreach ($info['Whitelisted'] as $host) {
							?>
							<tr class="whitelisted">
								<td>
									<?php echo $host ?>
								</td>
								<td>
									<?php echo _TITLE2('Whitelisted') ?>
								</td>
							</tr>
							<?php
						}

						foreach ($info['Blacklisted'] as $host) {
							?>
							<tr class="blacklisted">
								<td>
									<?php echo $host ?>
								</td>
								<td>
									<?php echo _TITLE2('Blacklisted') ?>
								</td>
							</tr>
							<?php
						}

						foreach ($info['Blocked'] as $host => $time) {
							?>
							<tr class="blocked">
								<td>
									<?php
									/// Only admin can delete/add hosts
									if (in_array($_SESSION['USER'], $ADMIN)) {
										?>
										<input name="ItemsToDelete[]" type="checkbox" value="<?php echo $host ?>"/><?php echo $host ?>
										<?php
									}
									else {
										?>
										<?php echo $host ?>
										<?php
									}
									?>
								</td>
								<td>
									<?php echo $time ?>
								</td>
							</tr>
							<?php
						}
						/// Only admin can delete/add hosts
						if (in_array($_SESSION['USER'], $ADMIN)) {
							?>
							<tr>
								<td>
									<input type="submit" name="Unblock" value="<?php echo _CONTROL('Unblock') ?>"/><br />
									<?php echo _TITLE2('Unblock selected') ?>
								</td>
								<td>
									<input type="submit" name="UnblockAll" value="<?php echo _CONTROL('Unblock All') ?>"/><br />
									<?php echo _TITLE2('Unblock all blocked entries') ?>
								</td>
							</tr>
							<?php
						}
						?>
					</form>
				</table>
				<?php
				/// Only admin can delete/add hosts
				if (in_array($_SESSION['USER'], $ADMIN)) {
					?>
					<br />
					<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
						<?php echo _TITLE2('IP or Net').':' ?>
						<input type="text" name="ItemToAdd" style="width: 100px;" maxlength="20"/>
						<?php echo _TITLE2('Time to expire').':' ?>
						<input type="text" name="TimeToAdd" style="width: 100px;" maxlength="20"/>
						<?php echo _TITLE('secs') ?>
						<input type="submit" name="Block" value="<?php echo _CONTROL('Block') ?>" />
					</form>
					<?php
				}
			}
			?>
		</td>
		<?php
	}
}

$View= new Snortips();
?>
