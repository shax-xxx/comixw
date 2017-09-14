<?php
/* $ComixWall: include.php,v 1.14 2009/11/23 23:33:47 soner Exp $ */

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

class Pf extends View
{
	public $Model= 'pf';

	function Pf()
	{
		$this->LogsHelpMsg= _HELPWINDOW('What is recorded in packet filter logs is determined by pf rules you can configure under Rules tab.');
	}
	
	/** Builds PF (tcpdump) specific string from $date.
	 *
	 * @param[in]	$date	array Datetime struct
	 */
	function FormatDate($date)
	{
		global $MonthNames;

		return $MonthNames[$date['Month']].' '.$date['Day'];
	}
	
	function FormatLogCols(&$cols)
	{
	}

	function PrintStatusForm()
	{
		global $Modules, $IMG_PATH, $ADMIN, $HeadStart, $StartLine, $StateCount, $LinesPerPage, $SearchRegExp;

		if ($running= $this->Controller($output, 'IsRunning')) {
			$imgfile= 'run.png';
			$name= 'Running';
			$info= _TITLE('is running');
			$confirm= _NOTICE('Are you sure you want to stop the <NAME>?');
			$button= 'Stop';
		}
		else {
			$imgfile= 'stop.png';
			$name= 'Stopped';
			$info= _TITLE('is not running');
			$confirm= _NOTICE('Are you sure you want to start the <NAME>?');
			$button= 'Start';
		}
		$confirm= preg_replace('/<NAME>/', _($Modules[$this->Model]['Name']), $confirm);
		?>
		<table id="status">
			<tr>
				<td class="image">
					<img src="<?php echo $IMG_PATH.$imgfile ?>" name="<?php echo $name ?>" alt="<?php echo $name ?>" border="0">
				</td>
				<td>
					<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
						<strong><?php echo _($Modules[$this->Model]['Name'])." $info " ?></strong>
						<?php
						/// Only admin can start/stop the processes
						if (in_array($_SESSION['USER'], $ADMIN)) {
							?>
							<input type="submit" name="<?php echo $button ?>" value="<?php echo _($button) ?>" onclick="return confirm('<?php echo $confirm ?>')"/>
							<?php
						}
						?>
						<input type="hidden" name="Model" value=<?php echo $this->Model ?> />
					</form>
				</td>
			</tr>
		</table>
		<?php
	}

	/** Gets and lists pf states.
	 */
	function PrintStatesTable()
	{
		global $HeadStart, $StartLine, $StateCount, $LinesPerPage, $SearchRegExp;

		PrintLogHeaderForm($StartLine, $StateCount, $LinesPerPage, $SearchRegExp, '');
		$this->Controller($output, 'GetStateList', $HeadStart, $LinesPerPage, $SearchRegExp);
		$output= unserialize($output[0]);

		$total= count($output);
		if ($total > 0) {
			?>
			<table id="processes">
			<?php
			$this->PrintStatesTableHeader();
			$linenum= 0;
			foreach ($output as $cols) {
				$class= ($linenum++ % 2 == 0) ? 'evenline' : 'oddline';
				?>
				<tr class="<?php echo $class ?>">
					<td>
						<?php echo $linenum + $StartLine ?>
					</td>
					<?php
					$count= 1;
					foreach ($cols as $c) {
						if (in_array($count, array(5, 6, 7))) {
							// Center a state, age, exp
							$class= 'class="center"';
						}
						else if (in_array($count, array(1, 2, 3, 4))) {
							// Left align pr, dir, src, dest
							$class= 'class="left"';
						}
						else {
							$class= '';
						}
						?>
						<td <?php echo $class ?>>
							<?php echo $c ?>
						</td>
						<?php
						$count++;
					}
					?>
				</tr>
				<?php
			}
			?>
			</table>
			<?php
		}
	}

	/** Prints headers for states table.
	 *
	 * PR    D SRC   DEST  STATE   AGE   EXP  PKTS BYTES
	 */
	function PrintStatesTableHeader()
	{
		?>
		<tr id="processes">
			<th><?php echo _('Line') ?></th>
			<th><?php echo _('Proto') ?></th>
			<th><?php echo _('Dir') ?></th>
			<th><?php echo _('Source') ?></th>
			<th><?php echo _('Dest') ?></th>
			<th><?php echo _('State') ?></th>
			<th><?php echo _('Age') ?></th>
			<th><?php echo _('Expr') ?></th>
			<th><?php echo _('Packets') ?></th>
			<th><?php echo _('Bytes') ?></th>
		</tr>
		<?php
	}
}

$View= new Pf();
?>
