<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * English strings for OnlineCompiler.io for CodeRunner.
 *
 * @package    tool_onlinecompiler
 * @copyright  2026 RunCode Inc.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['apikey'] = 'OnlineCompiler.io API key';
$string['apikey_help'] = 'An API key from the API keys page of your OnlineCompiler.io dashboard, not a widget key. CodeRunner sends it with every run as its Jobe API key.';
$string['apikey_invalid'] = 'That doesn\'t look like an OnlineCompiler.io API key. Copy it from the API keys page of your dashboard.';
$string['changekey'] = 'Save the new key';
$string['changekey_heading'] = 'Change the API key';
$string['connect'] = 'Use OnlineCompiler.io for CodeRunner';
$string['connect_heading'] = 'Connect CodeRunner to OnlineCompiler.io';
$string['connect_text'] = 'Create a free API key on the <a href="{$a}">API keys page</a> of your OnlineCompiler.io dashboard (sign in with Google), paste it here and press the button. This sets CodeRunner\'s Jobe server and Jobe API key for you; your questions don\'t change.';
$string['connected'] = 'CodeRunner now runs code on OnlineCompiler.io. Press Test to check it.';
$string['differences'] = 'Questions using CodeRunner\'s built-in question types run as they are. The few differences from a standard Jobe server are listed on the <a href="{$a}">Moodle CodeRunner page</a>.';
$string['differences_heading'] = 'Before you switch';
$string['intro'] = 'This page points CodeRunner at OnlineCompiler.io\'s hosted Jobe server, so your site needs no Jobe server of its own. It tests the connection, and puts your own settings back if you change your mind.';
$string['nothingtoswitchback'] = 'There are no earlier settings to put back.';
$string['pluginname'] = 'OnlineCompiler.io for CodeRunner';
$string['privacy:metadata:onlinecompiler'] = 'Once CodeRunner is connected to OnlineCompiler.io, CodeRunner sends each answer a student checks or submits in a CodeRunner question to OnlineCompiler.io\'s Jobe server, which compiles and runs it and sends back the output. The answer is sent with the site\'s API key, not with the student\'s name, email or Moodle account. This plugin stores no personal data in Moodle; its own test sends a fixed program with no user data.';
$string['privacy:metadata:onlinecompiler:code'] = 'The program code of the student\'s answer.';
$string['privacy:metadata:onlinecompiler:input'] = 'The input the question gives the program, and any files the question attaches.';
$string['privacy:metadata:onlinecompiler:language'] = 'The programming language of the question.';
$string['status_canterbury'] = 'CodeRunner sends students\' code to the University of Canterbury\'s demo Jobe server ({$a}). It is meant for trying CodeRunner out, and accepts only a limited number of runs an hour from one site, which a class soon uses up.';
$string['status_disabled'] = 'CodeRunner\'s Jobe sandbox is turned off, so its questions can\'t run. Connecting turns it on.';
$string['status_heading'] = 'Where CodeRunner runs code now';
$string['status_none'] = 'No Jobe server is set, so CodeRunner questions can\'t run.';
$string['status_onlinecompiler'] = 'CodeRunner sends students\' code to OnlineCompiler.io ({$a}).';
$string['status_other'] = 'CodeRunner sends students\' code to another Jobe server, probably your own ({$a}).';
$string['switchback'] = 'Put my earlier settings back';
$string['switchback_heading'] = 'Switch back';
$string['switchback_none'] = 'no Jobe server';
$string['switchback_text'] = 'Before you connected, CodeRunner used {$a}. This puts that Jobe server, its key and the sandbox setting back.';
$string['switchedback'] = 'CodeRunner\'s earlier Jobe settings are back.';
$string['test'] = 'Test';
$string['test_badkey'] = 'The Jobe server refused the API key. Copy the key again from the API keys page of your OnlineCompiler.io dashboard, and check that it is an API key, not a widget key.';
$string['test_busy'] = 'The Jobe server is busy, or the key has reached its limit for now. Try again in a minute. For an exam, book its hours.';
$string['test_disabled'] = 'CodeRunner\'s Jobe sandbox is turned off. Turn it on in CodeRunner\'s settings, or connect with the form below.';
$string['test_failed'] = 'The test run didn\'t complete.';
$string['test_heading'] = 'Test the connection';
$string['test_nohost'] = 'No Jobe server is set. Connect with the form below first.';
$string['test_ok'] = 'It works: CodeRunner ran a Python program on the Jobe server and got the right output ({$a}).';
$string['test_text'] = 'Runs a one-line Python program through CodeRunner\'s own Jobe connection, as a quiz question would.';
$string['test_unreachable'] = 'Moodle couldn\'t reach the Jobe server. Check the Jobe server address, and that your Moodle server may make outgoing HTTPS requests: a firewall or proxy can block them, and Moodle\'s own proxy settings apply.';
$string['test_wrongoutput'] = 'The program ran but didn\'t print what it should. It printed: {$a}';
