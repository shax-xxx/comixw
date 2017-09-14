<?php
/* $ComixWall: include.clamd.php,v 1.16 2009/11/15 21:26:15 soner Exp $ */

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

class Clamd extends View
{
	public $Model= 'clamd';

	public $Layout= 'clamd';

	function Clamd()
	{
		$this->LogsHelpMsg= _HELPWINDOW('Clamd logs virus scan results, virus database checks, and database reloads.');
		$this->GraphHelpMsg= _HELPWINDOW('Since Freshclam wakes up periodically, this page displays graphs for Clamd process only.');
		$this->ConfHelpMsg= _HELPWINDOW('By default, Clamd accepts virus scan requests from processes running on the system only, such as the web filter. Default settings should be suitable for most purposes.');
	
		$this->Config = array(
			'LogClean' => array(
				'title' => _TITLE2('Log Clean'),
				'info' => _HELPBOX2('Also log clean files. Useful in debugging but drastically increases the log size.
		Default: disabled'),
				),
			'LogVerbose' => array(
				'title' => _TITLE2('Log Verbose'),
				'info' => _HELPBOX2('Enable verbose logging.
		Default: disabled'),
				),
			'MaxThreads' => array(
				'title' => _TITLE2('Max Threads'),
				'info' => _HELPBOX2('Maximal number of threads running at the same time.
		Default: 10'),
				),
			'MaxDirectoryRecursion' => array(
				'title' => _TITLE2('Max Directory Recursion'),
				'info' => _HELPBOX2('Maximal depth directories are scanned at.
		Default: 15'),
				),
			'FollowDirectorySymlinks' => array(
				'title' => _TITLE2('Follow Directory Symlinks'),
				'info' => _HELPBOX2('Follow directory symlinks.
		Default: disabled'),
				),
			'FollowFileSymlinks' => array(
				'title' => _TITLE2('Follow File Symlinks'),
				'info' => _HELPBOX2('Follow regular file symlinks.
		Default: disabled'),
				),
			'SelfCheck' => array(
				'title' => _TITLE2('Self Check'),
				'info' => _HELPBOX2('Perform internal sanity check (database integrity and freshness).
		Default: 1800 (30 min)'),
				),
			'Debug' => array(
				'title' => _TITLE2('Debug'),
				'info' => _HELPBOX2('Enable debug messages in libclamav.
		Default: disabled'),
				),
			'LeaveTemporaryFiles' => array(
				'title' => _TITLE2('Leave Temporary Files'),
				'info' => _HELPBOX2('Do not remove temporary files (for debug purposes).
		Default: disabled'),
				),
			'ScanPE' => array(
				'title' => _TITLE2('Scan PE'),
				'info' => _HELPBOX2('PE stands for Portable Executable - it\'s an executable file format used in all 32-bit versions of Windows operating systems. This option allows ClamAV to perform a deeper analysis of executable files and it\'s also required for decompression of popular executable packers such as UPX, FSG, and Petite.
		Default: enabled'),
				),
			'DetectBrokenExecutables' => array(
				'title' => _TITLE2('Detect Broken Executables'),
				'info' => _HELPBOX2('With this option clamav will try to detect broken executables and mark them as Broken.Executable
		Default: disabled'),
				),
			'ScanOLE2' => array(
				'title' => _TITLE2('Scan OLE2'),
				'info' => _HELPBOX2('This option enables scanning of Microsoft Office document macros.
		Default: enabled'),
				),
			'ScanMail' => array(
				'title' => _TITLE2('Scan Mail'),
				'info' => _HELPBOX2('Enable internal e-mail scanner.
		Default: enabled'),
				),
			'MailFollowURLs' => array(
				'title' => _TITLE2('Mail Follow URLs'),
				'info' => _HELPBOX2('If an email contains URLs ClamAV can download and scan them.
		WARNING: This option may open your system to a DoS attack.
		Never use it on loaded servers.
		Default: disabled'),
				),
			'ScanHTML' => array(
				'title' => _TITLE2('Scan HTML'),
				'info' => _HELPBOX2('Perform HTML normalisation and decryption of MS Script Encoder code.
		Default: enabled'),
				),
			'ScanArchive' => array(
				'title' => _TITLE2('Scan Archive'),
				'info' => _HELPBOX2('ClamAV can scan within archives and compressed files.
		Default: enabled'),
				),
			'ScanRAR' => array(
				'title' => _TITLE2('Scan RAR'),
				'info' => _HELPBOX2('Due to license issues libclamav does not support RAR 3.0 archives (only the old 2.0 format is supported). Because some users report stability problems with unrarlib it\'s disabled by default and you must uncomment the directive below to enable RAR 2.0 support.
		Default: disabled'),
				),
			'ArchiveMaxFileSize' => array(
				'title' => _TITLE2('Archive Max File Size'),
				'info' => _HELPBOX2('The options below protect your system against Denial of Service attacks using archive bombs.

		Files in archives larger than this limit won\'t be scanned.
		Value of 0 disables the limit.
		Default: 10M'),
				),
			'ArchiveMaxRecursion' => array(
				'title' => _TITLE2('Archive Max Recursion'),
				'info' => _HELPBOX2('Nested archives are scanned recursively, e.g. if a Zip archive contains a RAR file, all files within it will also be scanned. This options specifies how deep the process should be continued.
		Value of 0 disables the limit.
		Default: 8'),
				),
			'ArchiveMaxFiles' => array(
				'title' => _TITLE2('Archive Max Files'),
				'info' => _HELPBOX2('Number of files to be scanned within an archive.
		Value of 0 disables the limit.
		Default: 1000'),
				),
			'ArchiveMaxCompressionRatio' => array(
				'title' => _TITLE2('Archive Max Compression Ratio'),
				'info' => _HELPBOX2('If a file in an archive is compressed more than ArchiveMaxCompressionRatio times it will be marked as a virus (Oversized.ArchiveType, e.g. Oversized.Zip)
		Value of 0 disables the limit.
		Default: 250'),
				),
			'ArchiveLimitMemoryUsage' => array(
				'title' => _TITLE2('Archive Limit Memory Usage'),
				'info' => _HELPBOX2('Use slower but memory efficient decompression algorithm.
		Only affects the bzip2 decompressor.
		Default: disabled'),
				),
			'ArchiveBlockEncrypted' => array(
				'title' => _TITLE2('Archive Block Encrypted'),
				'info' => _HELPBOX2('Mark encrypted archives as viruses (Encrypted.Zip, Encrypted.RAR).
		Default: disabled'),
				),
			'ArchiveBlockMax' => array(
				'title' => _TITLE2('Archive Block Max'),
				'info' => _HELPBOX2('Mark archives as viruses (e.g. RAR.ExceededFileSize, Zip.ExceededFilesLimit) if ArchiveMaxFiles, ArchiveMaxFileSize, or ArchiveMaxRecursion limit is reached.
		Default: disabled'),
				),
		);
	}
}

$View= new Clamd();
?>
