<?php
/* $ComixWall: view.php,v 1.21 2009/11/23 23:33:47 soner Exp $ */

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
 * View base class.
 */

class View
{
	public $Model= 'model';
	
	public $LogsHelpMsg= '';
	
	public $GraphHelpMsg= '';
	public $Layout= '';
	
	public $ConfHelpMsg= '';

	/** Configuration.
	 *
	 * If title field is missing, index string is used as title.
	 *
	 * @param	title	Configuration title displayed.
	 * @param	info	Info text displayed in help box on the right.
	 */
	public $Config= array();
	
	/** Stops and restarts module process(es).
	 */
	function Restart()
	{
		if ($this->Stop()) {
			return $this->Start();
		}
		return FALSE;
	}

	/** Stops module process(es)
	 */
	function Stop()
	{
		if ($this->Controller($output, 'Stop')) {
			return TRUE;
		}
		return FALSE;
	}

	/** Starts module process(es).
	 */
	function Start()
	{
		if ($this->Controller($output, 'Start')) {
			return TRUE;
		}
		return FALSE;
	}

	/** Generic date array to string formatter.
	 *
	 * Assumes standard syslog date format for the output string.
	 *
	 * @param[in]	$date	array Datetime struct
	 * @return	string Date string
	 */
	function FormatDate($date)
	{
		global $MonthNames;

		return $MonthNames[$date['Month']].' '.sprintf('% 2d', $date['Day']);
	}

	/** Date string to array formatter.
	 *
	 * Assumes standard syslog date format for the output string.
	 * Does the opposite of FormatDate().
	 *
	 * @param[in]	$datestr	string Date as string
	 * @param[out]	$date		array Datetime output
	 *
	 * @todo Check if empty Year is ok around new year's day
	 */
	function FormatDateArray($datestr, &$date)
	{
		global $MonthNumbers;
		
		if (preg_match('/(\d+)\s+(\d+)/', $datestr, $match)) {
			$date['Month']= $match[1];
			$date['Day']= sprintf('%02d', $match[2]);
			return TRUE;
		}
		else if (preg_match('/(\w+)\s+(\d+)/', $datestr, $match)) {
			if (array_key_exists($match[1], $MonthNumbers)) {
				$date['Month']= $MonthNumbers[$match[1]];
				$date['Day']= sprintf('%02d', $match[2]);
				return TRUE;
			}
		}
		return FALSE;
	}

	/** Checks if the date array contains a range, meaning an empty string.
	 *
	 * Assumes standard syslog date format for the output string.
	 *
	 * @param[in]	$date	array Datetime struct
	 * @return	TRUE if a range
	 */
	function IsDateRange($date)
	{
		return ($date['Month'] == '') || ($date['Day'] == '');
	}
	
	/** Post-processes log columns for display.
	 *
	 * @param[out]	$cols	array Parsed log line in columns
	 */
	function FormatLogCols(&$cols)
	{
		if (isset($cols['Log'])) {
			$cols['Log']= wordwrap($cols['Log'], 80, '<br />', TRUE);
		}
	}

	/** Prints log line color tr tag.
	 *
	 * Keywords are obtained from arrays in $Modules
	 *
	 * @param[in]	$logstr string Log string to search for keywords
	 */
	function PrintLogLineClass($logstr)
	{
		global $Modules;

		$genericres= array(
			'red' => array('\berror\b'),
			'yellow' => array('\bwarning\b'),
			'green' => array('\bsuccess'),
		);

		$logres= isset($Modules[$this->Model]['HighlightLogs']['REs']) ? $Modules[$this->Model]['HighlightLogs']['REs'] : $genericres;

		$done= FALSE;
		$class= '';
		foreach ($logres as $color => $res) {
			foreach ($res as $re) {
				$r= Escape($re, '/');
				if (preg_match("/$r/", $logstr)) {
					$class= $color;
					/// Exit on first match, i.e. precedence: red, yellow, green
					$done= TRUE;
					break;
				}
			}
			if ($done) {
				// Exit on first match
				break;
			}
		}
		echo $class == '' ? '<tr>' : "<tr class=\"$class\">";
	}

	/** Generic parser, highlighter, and printer for the log line.
	 *
	 * If there is no PrintLogLine() defined in module's include file,
	 * this one is used instead.
	 *
	 * @param[in]	$cols		array Parsed log line
	 * @param[in]	$linenum	int Line number of the log line
	 */
	function PrintLogLine($cols, $linenum)
	{
		global $Modules;

		$logstr= isset($Modules[$this->Model]['HighlightLogs']['Col']) ?
			$cols[$Modules[$this->Model]['HighlightLogs']['Col']] :
			implode(' ', $cols);

		$this->PrintLogLineClass($logstr);

		PrintLogCols($linenum, $cols);
		echo '</tr>';
	}
	
	/** Processes user posts for process restart and stop.
	 *
	 * Used on all info pages.
	 */
	function ProcessRestartStopRequests()
	{
		if ($_POST['Model']) {
			if ($_POST['Model'] == $this->Model) {
				if ($_POST['Start']) {
					$this->Restart();
				}
				else if ($_POST['Stop']) {
					$this->Stop();
				}
			}
		}
	}
	
	/** Displays module status, software version, Restart/Stop buttons, and process table.
	 *
	 * @param[in]	$printcount		boolean Whether to print number of running processes too
	 * @param[in]	$showbuttons	boolean Show Start/Stop buttons
	 */
	function PrintStatusForm($printcount= FALSE, $showbuttons= TRUE)
	{
		global $Modules, $IMG_PATH, $ADMIN;

		if ($running= $this->Controller($output, 'IsRunning')) {
			$imgfile= 'run.png';
			$name= 'Running';
			$info= _TITLE('is running');
			$confirm= _NOTICE('Are you sure you want to stop the <NAME>?');
			if ($showbuttons) {
				$button= 'Stop';
			}
		}
		else {
			$imgfile= 'stop.png';
			$name= 'Stopped';
			$info= _TITLE('is not running');
			$confirm= _NOTICE('Are you sure you want to start the <NAME>?');
			if ($showbuttons) {
				$button= 'Start';
			}
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
						if ($button && in_array($_SESSION['USER'], $ADMIN)) {
							?>
							<input type="submit" name="<?php echo $button ?>" value="<?php echo _($button) ?>" onclick="return confirm('<?php echo $confirm ?>')"/>
							<?php
						}
						?>
						<input type="hidden" name="Model" value=<?php echo $this->Model ?> />
					</form>
				</td>
				<?php
				if ($this->Controller($output, 'GetVersion')) {
					?>
					<td class="version">
						<strong><?php echo _TITLE('Software Version') ?></strong>
						<?php
						foreach ($output as $line) {
							echo '<br />'.$line;
						}
						?>
					</td>
					<?php
				}
				?>
			</tr>
		</table>
		<?php
		if ($running) {
			$this->Controller($output, 'GetProcList');
			$this->PrintProcessTable(unserialize($output[0]), $printcount);
		}
	}

	/** Gets and lists processes for daemons/services.
	 *
	 * Used on module status pages.
	 * Perl processes are hard to list.
	 * @warning Spaces in $output lines were replaced by |'s elsewhere.
	 *
	 * @param[in]	$output	array ps output, modified by SelectProcesses()
	 * @param[in]	$printcount	boolean whether to print process count at top
	 */
	function PrintProcessTable($output, $printcount= FALSE)
	{
		$total= count($output);
		if ($total > 0) {
			if ($printcount) {
				echo _TITLE('Number of processes').': '.$total;
			}
			?>
			<table id="processes">
			<?php
			$this->PrintProcessTableHeader();
			$linenum= 0;
			foreach ($output as $cols) {
				$class= ($linenum++ % 2 == 0) ? 'evenline' : 'oddline';
				?>
				<tr class="<?php echo $class ?>">
				<?php
				$count= 1;
				foreach ($cols as $c) {
					if (in_array($count++, array(8, 11, 12, 13))) {
						// Left align stat, user, group, and command columns
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

	/** Prints headers for processes table.
	 *
	 * PID STAT  %CPU      TIME %MEM   RSS   VSZ STARTED  PRI  NI USER     COMMAND
	 */
	function PrintProcessTableHeader()
	{
		?>
		<tr id="processes">
			<th><?php echo _('PID') ?></th>
			<th><?php echo _TITLE2('STARTED') ?></th>
			<th><?php echo _('%CPU') ?></th>
			<th><?php echo _TITLE2('TIME') ?></th>
			<th><?php echo _TITLE2('%MEM') ?></th>
			<th><?php echo _('RSS') ?></th>
			<th><?php echo _('VSZ') ?></th>
			<th><?php echo _('STAT') ?></th>
			<th><?php echo _TITLE2('PRI') ?></th>
			<th><?php echo _('NI') ?></th>
			<th><?php echo _TITLE2('USER') ?></th>
			<th><?php echo _TITLE2('GROUP') ?></th>
			<th><?php echo _TITLE2('COMMAND') ?></th>
		</tr>
		<?php
	}

	/** Calls the controller.
	 *
	 * Both command and arguments are passed as variable arguments.
	 *
	 * @param[out]	$output			Output of the command
	 * @param[in]	Variable_Args	Command line in variable arguments
	 * @return boolean Return value of shell command (adjusted for PHP)
	 */
	function Controller(&$output)
	{
		global $ROOT;

		try {
			$cwc= $ROOT.'/Controller/cwc.php';

			$argv= func_get_args();
			// Arg 0 is $output, skip it
			$argv= array_slice($argv, 1);

			if ($this->EscapeArgs($argv, $cmdline)) {
				$locale= $_SESSION['Locale'];
				$cmdline= "sudo $cwc $locale $this->Model $cmdline";
				
				// Init command output
				$output= array();
				/// @bug http://bugs.php.net/bug.php?id=49847, fixed/closed in SVN on 141009, patched on ComixWall
				exec($cmdline, $output, $retval);

				// (exit status 0 in shell) == (TRUE in php)
				if ($retval === 0) {
					return TRUE;
				}
				else {
					$reason= implode(', ', $output);
					cwwui_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "Shell command exit status: $retval: ($reason), ($cmdline)");
					
					// Show $reason if any
					if ($reason !== '') {
						PrintHelpWindow(_NOTICE('FAILED').': '.implode('<br />', $output), 'auto', 'ERROR');
					}
				}
			}
		}
		catch (Exception $e) {
			echo 'Exception: '.__FILE__.' '.__FUNCTION__.' ('.__LINE__.'): '.$e->getMessage()."\n";
			cwwui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Exception: '.$e->getMessage());
		}
		return FALSE;
	}
	
	/** Escapes the arguments passed to Controller() and builds the command line.
	 *
	 * @param[in]	$argv		Command and arguments array
	 * @param[out]	$cmdline	Actual command line to run
	 * @return boolean
	 */
	function EscapeArgs($argv, &$cmdline)
	{
		if (count($argv) > 0) {
			$cmd= $argv[0];
			$argv= array_slice($argv, 1);
  	
			$cmdline= $cmd;
			foreach ($argv as $arg) {
				$cmdline.= ' '.escapeshellarg($arg);
			}
			return TRUE;
		}
		cwwui_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, '$argv is empty');
		return FALSE;
	}
	
	function SetSessionFilterGroup()
	{
	}

	/** Prints general text statistics.
	 *
	 * @param[in]	$file	string Log file if different from the one in $Modules
	 */
	function PrintStats($file= '')
	{
		$this->Controller($output, 'GetProcStatLines', $file);
		$stats= unserialize($output[0]);
		PrintNVPs($stats, _STATS('General Statistics'));
	}

	/** Uploads selected log file.
	 */
	function UploadLogFile()
	{
		/// Do not send anything yet if download requested (header is modified below).
		if ($_POST['Download']) {
			if ($_POST['LogFile']) {
				if ($this->Controller($output, 'PrepareFileForDownload', $_POST['LogFile'])) {
					$tmpfile= $output[0];
					/// @warning Clear the output buffer first
					ob_clean();

					if (preg_match('/MSIE/', $_SERVER['HTTP_USER_AGENT'])) {
						// Without this header, IE cannot even download the file
						header("Pragma: public");
					}

					if (preg_match('/.*\.gz$/', $tmpfile)) {
						 header('Content-Type: application/x-gzip');
					}
					else if (preg_match('/.*\.pdf$/', $tmpfile)) {
						 header('Content-Type: application/pdf');
					}
					else {
						 header('Content-Type: text/plain');
					}
					header('Content-Disposition: attachment; filename="'.basename($tmpfile).'"');
					header('Content-Length: '.filesize($tmpfile));
					readfile($tmpfile);
					flush();
					/// @warning Do not send anything else, otherwise it is attached to the file
					exit;
				}
			}
		}
	}
}
?>
