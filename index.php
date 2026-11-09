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
 * Site administration, Plugins, Admin tools, OnlineCompiler.io for CodeRunner.
 *
 * @package    tool_onlinecompiler
 * @copyright  2026 RunCode Inc.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/formslib.php');

use tool_onlinecompiler\manager;

admin_externalpage_setup('tool_onlinecompiler');

$url = new moodle_url('/admin/tool/onlinecompiler/index.php');
$manager = new manager();
$action = optional_param('action', '', PARAM_ALPHA);

if ($action === 'switchback') {
    require_sesskey();
    $done = $manager->switch_back();
    redirect(
        $url,
        get_string($done ? 'switchedback' : 'nothingtoswitchback', 'tool_onlinecompiler'),
        null,
        $done ? \core\output\notification::NOTIFY_SUCCESS : \core\output\notification::NOTIFY_INFO
    );
}

$current = $manager->current();
$kind = manager::server_kind($current->host);
$connected = $kind === manager::SERVER_ONLINECOMPILER;

$form = new \tool_onlinecompiler\form\connect_form($url, ['connected' => $connected]);
if ($data = $form->get_data()) {
    $manager->connect($data->apikey);
    redirect($url, get_string('connected', 'tool_onlinecompiler'), null, \core\output\notification::NOTIFY_SUCCESS);
}

$testresult = null;
if ($action === 'test') {
    require_sesskey();
    $testresult = $manager->test();
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'tool_onlinecompiler'));
echo html_writer::tag('p', get_string('intro', 'tool_onlinecompiler'));

// Where CodeRunner sends code now.
echo $OUTPUT->heading(get_string('status_heading', 'tool_onlinecompiler'), 3, 'mt-4');
$status = get_string('status_' . $kind, 'tool_onlinecompiler', s($current->host));
if ($current->host !== '' && !$current->enabled) {
    $status .= ' ' . get_string('status_disabled', 'tool_onlinecompiler');
}
echo $OUTPUT->notification($status, $connected && $current->enabled ? 'success' : 'info', false);

// The test.
if ($current->host !== '') {
    echo $OUTPUT->heading(get_string('test_heading', 'tool_onlinecompiler'), 3, 'mt-4');
    echo html_writer::tag('p', get_string('test_text', 'tool_onlinecompiler'));
    if ($testresult !== null) {
        $message = get_string($testresult->reason, 'tool_onlinecompiler', s(trim($testresult->output)));
        if ($testresult->detail !== '') {
            $message .= html_writer::tag('pre', s($testresult->detail), ['class' => 'mt-2 mb-0']);
        }
        echo $OUTPUT->notification($message, $testresult->ok ? 'success' : 'error', false);
    }
    echo $OUTPUT->single_button(new moodle_url($url, ['action' => 'test']), get_string('test', 'tool_onlinecompiler'), 'post');
}

// Connect, or change the key.
echo $OUTPUT->heading(get_string($connected ? 'changekey_heading' : 'connect_heading', 'tool_onlinecompiler'), 3, 'mt-4');
echo html_writer::tag('p', get_string('connect_text', 'tool_onlinecompiler', manager::APIKEYS_URL));
$form->display();

// Put the site's own settings back.
if ($connected && $manager->has_previous()) {
    $previous = $manager->previous_host();
    echo $OUTPUT->heading(get_string('switchback_heading', 'tool_onlinecompiler'), 3, 'mt-4');
    echo html_writer::tag('p', get_string(
        'switchback_text',
        'tool_onlinecompiler',
        $previous === '' ? get_string('switchback_none', 'tool_onlinecompiler') : s($previous)
    ));
    echo $OUTPUT->single_button(
        new moodle_url($url, ['action' => 'switchback']),
        get_string('switchback', 'tool_onlinecompiler'),
        'post'
    );
}

// What is different from a standard Jobe server.
echo $OUTPUT->heading(get_string('differences_heading', 'tool_onlinecompiler'), 3, 'mt-4');
echo html_writer::tag('p', get_string('differences', 'tool_onlinecompiler', manager::DIFFERENCES_URL));

echo $OUTPUT->footer();
