<?php
/*
 * Copyright (c) 2004 Allard Consulting.  All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *
 * 3. All advertising materials mentioning features or use of this
 *    software must display the following acknowledgement: This
 *    product includes software developed by Allard Consulting
 *    and its contributors.
 *
 * 4. Neither the name of Allard Consulting nor the names of
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

$inst_dir = dirname(__FILE__). "/../../../";
require_once('simpletest/unit_tester.php');
require_once('simpletest/reporter.php');
require_once($inst_dir. '/include/packetfilter.inc.php');

class FilterTest extends UnitTestCase {
  function FilterTest() {
    $this->UnitTestCase();
  }

  //
  // Basic parsing of rules
  //
  function test_basic_parsing() {
    $pfw = new pfw();
    $pfw->parseRulebase('pass in from any to any');
    $this->assertEqual(trim($pfw->generate(false, false)), 'pass in from any to any');

    $pfw = new pfw();
    $pfw->parseRulebase('block in from any to any');
    $this->assertEqual(trim($pfw->generate(false, false)),'block in from any to any');

    $pfw = new pfw();
    $pfw->parseRulebase('antispoof for lo0');
    $this->assertEqual(trim($pfw->generate(false, false)),'antispoof for lo0');
  }

  //
  // Test different features
  //
  function test_log() {
    $pfw = new pfw();
    $pfw->parseRulebase('pass log all');
    $this->assertEqual(trim($pfw->generate(false, false)),'pass log all');

    $pfw = new pfw();
    $pfw->parseRulebase('pass log-all all');
    $this->assertEqual(trim($pfw->generate(false, false)),'pass log-all all');
  }
  
  function test_returns() {
    $pfw = new pfw();
    $pfw->parseRulebase('pass return all');
    $this->assertEqual(trim($pfw->generate(false, false)),'pass return all');

    $pfw = new pfw();
    $pfw->parseRulebase('pass return-rst all');
    $this->assertEqual(trim($pfw->generate(false, false)),'pass return-rst all');

    $pfw = new pfw();
    $pfw->parseRulebase('pass return-icmp all');
    $this->assertEqual(trim($pfw->generate(false, false)),'pass return-icmp all');

    $pfw = new pfw();
    $pfw->parseRulebase('pass return-icmp6 all');
    $this->assertEqual(trim($pfw->generate(false, false)),'pass return-icmp6 all');
  }

  function test_directions() {
    $pfw = new pfw();
    $pfw->parseRulebase('pass in all');
    $this->assertEqual(trim($pfw->generate(false, false)),'pass in all');

    $pfw = new pfw();
    $pfw->parseRulebase('pass out all');
    $this->assertEqual(trim($pfw->generate(false, false)),'pass out all');

    $pfw = new pfw();
    $pfw->parseRulebase('pass all');
    $this->assertEqual(trim($pfw->generate(false, false)),'pass all');
  }

  function test_states() {
    $pfw = new pfw();
    $pfw->parseRulebase('pass out all keep state');
    $this->assertEqual(trim($pfw->generate(false, false)),'pass out all keep state');

    $pfw = new pfw();
    $pfw->parseRulebase('pass out all synproxy state');
    $this->assertEqual(trim($pfw->generate(false, false)),'pass out all synproxy state');

    $pfw = new pfw();
    $pfw->parseRulebase('pass out all modulate state');
    $this->assertEqual(trim($pfw->generate(false, false)),'pass out all modulate state');
  }

  function test_mulitples() {
    $pfw = new pfw();
    $pfw->parseRulebase('pass in on {$extif $intif} all');
    $this->assertEqual(trim($pfw->generate(false, false)),'pass in on { $extif $intif } all');

    $pfw = new pfw();
    $pfw->parseRulebase('pass in proto {tcp udp} all');
    $this->assertEqual(trim($pfw->generate(false, false)),'pass in proto { tcp udp } all');

    $pfw = new pfw();
    $pfw->parseRulebase('pass in from {1.2.3.4 2.3.4.5} to {1.2.3.4 2.3.4.5}');
    $this->assertEqual(trim($pfw->generate(false, false)),'pass in from { 1.2.3.4 2.3.4.5 } to { 1.2.3.4 2.3.4.5 }');

    $pfw = new pfw();
    $pfw->parseRulebase('pass in from any port {22 23} to any port {22 23}');
    $this->assertEqual(trim($pfw->generate(false, false)),'pass in from any port { 22 23 } to any port { 22 23 }');
  }

  function test_other() {
    $pfw = new pfw();
    $pfw->parseRulebase('pass all flags S/SA');
    $this->assertEqual(trim($pfw->generate(false, false)),'pass all flags S/SA');

    $pfw = new pfw();
    $pfw->parseRulebase('pass inet all');
    $this->assertEqual(trim($pfw->generate(false, false)),'pass inet all');

    $pfw = new pfw();
    $pfw->parseRulebase('pass inet6 all');
    $this->assertEqual(trim($pfw->generate(false, false)),'pass inet6 all');

    $pfw = new pfw();
    $pfw->parseRulebase('pass all user root');
    $this->assertEqual(trim($pfw->generate(false, false)),'pass all user root');

    $pfw = new pfw();
    $pfw->parseRulebase('pass all user {root www}');
    $this->assertEqual(trim($pfw->generate(false, false)),'pass all user { root www }');

    $pfw = new pfw();
    $pfw->parseRulebase('pass all group wheel');
    $this->assertEqual(trim($pfw->generate(false, false)),'pass all group wheel');

    $pfw = new pfw();
    $pfw->parseRulebase('pass all queue default');
    $this->assertEqual(trim($pfw->generate(false, false)),'pass all queue default');

    $pfw = new pfw();
    $pfw->parseRulebase('pass all queue (default, small)');
    $this->assertEqual(trim($pfw->generate(false, false)),'pass all queue (default, small)');

    $pfw = new pfw();
    $pfw->parseRulebase('pass all label test');
    $this->assertEqual(trim($pfw->generate(false, false)),'pass all label "test"');

    $pfw = new pfw();
    $pfw->parseRulebase('pass all tag test');
    $this->assertEqual(trim($pfw->generate(false, false)),'pass all tag "test"');

    $pfw = new pfw();
    $pfw->parseRulebase('pass all tagged test');
    $this->assertEqual(trim($pfw->generate(false, false)),'pass all tagged "test"');

    $pfw = new pfw();
    $pfw->parseRulebase('pass all probability 10');
    $this->assertEqual(trim($pfw->generate(false, false)),'pass all probability 10');

    $pfw = new pfw();
    $pfw->parseRulebase('pass fastroute all');
    $this->assertEqual(trim($pfw->generate(false, false)),'pass fastroute all');

    $pfw = new pfw();
    $pfw->parseRulebase('pass route-to 1.2.3.4 all');
    $this->assertEqual(trim($pfw->generate(false, false)),'pass route-to 1.2.3.4 all');

    $pfw = new pfw();
    $pfw->parseRulebase('pass reply-to 1.2.3.4 all');
    $this->assertEqual(trim($pfw->generate(false, false)),'pass reply-to 1.2.3.4 all');

    $pfw = new pfw();
    $pfw->parseRulebase('pass dup-to 1.2.3.4 all');
    $this->assertEqual(trim($pfw->generate(false, false)),'pass dup-to 1.2.3.4 all');

    $pfw = new pfw();
    $pfw->parseRulebase('pass all allow-opts');
    $this->assertEqual(trim($pfw->generate(false, false)),'pass all allow-opts');

    $pfw = new pfw();
    $pfw->parseRulebase('block in from any os "nmap" to any');
    $this->assertEqual(trim($pfw->generate(false, false)),'block in from any os "nmap" to any');

    $pfw = new pfw();
    $pfw->parseRulebase('block in from any os { "nmap", "Windows 95" } to any');
    $this->assertEqual(trim($pfw->generate(false, false)),'block in from any os { "nmap" "Windows 95" } to any');

    $pfw = new pfw();
    $pfw->parseRulebase('pass in \
     all');
    $this->assertEqual(trim($pfw->generate(false, false)),'pass in all');

    $pfw = new pfw();
    $pfw->parseRulebase('pass in route-to ($inf1 $gw1) all');
    $this->assertEqual(trim($pfw->generate(false, false)),'pass in route-to { ($inf1 $gw1) } all');

    $pfw = new pfw();
    $pfw->parseRulebase('pass in route-to { ($inf1 $gw1), ($inf2, $gw2) } all');
    $this->assertEqual(trim($pfw->generate(false, false)),'pass in route-to { ($inf1 $gw1) ($inf2 $gw2) } all');

  }
  
}
    
$test = &new FilterTest();
$test->run(new TextReporter());

?>
