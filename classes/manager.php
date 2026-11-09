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

namespace tool_onlinecompiler;

/**
 * Points CodeRunner at the OnlineCompiler.io Jobe server, puts the site's own settings back, and tests the connection.
 *
 * It only reads and writes CodeRunner's own settings (qtype_coderunner jobe_host, jobe_apikey and
 * jobesandbox_enabled), so everything it does can also be done, or undone, by hand on CodeRunner's settings page.
 *
 * @package    tool_onlinecompiler
 * @copyright  2026 RunCode Inc.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manager {
    /** The Jobe server CodeRunner uses once connected. */
    public const JOBE_SERVER = 'https://api.onlinecompiler.io/api';

    /** Where an admin creates an OnlineCompiler.io API key. */
    public const APIKEYS_URL = 'https://api.onlinecompiler.io/compilers/api-keys/';

    /** Where an admin books a CodeRunner exam. */
    public const BOOK_EXAM_URL = 'https://api.onlinecompiler.io/exams/new/';

    /** How booked CodeRunner exams work. */
    public const EXAMS_URL = 'https://onlinecompiler.io/coderunner-exams';

    /** How OnlineCompiler.io differs from a standard Jobe server. */
    public const DIFFERENCES_URL = 'https://onlinecompiler.io/moodle-coderunner#differences';

    /** The host of CodeRunner's built-in default Jobe server, at the University of Canterbury. */
    public const CANTERBURY_HOST = 'jobe2.cosc.canterbury.ac.nz';

    /** Where CodeRunner sends code: OnlineCompiler.io. */
    public const SERVER_ONLINECOMPILER = 'onlinecompiler';

    /** Where CodeRunner sends code: the University of Canterbury's demo server. */
    public const SERVER_CANTERBURY = 'canterbury';

    /** Where CodeRunner sends code: another Jobe server, usually the site's own. */
    public const SERVER_OTHER = 'other';

    /** Where CodeRunner sends code: nowhere, no Jobe server is set. */
    public const SERVER_NONE = 'none';

    /** The program the test runs. */
    public const TEST_PROGRAM = 'print("OnlineCompiler.io test: " + str(6 * 7))';

    /** What the test program prints. */
    public const TEST_OUTPUT = 'OnlineCompiler.io test: 42';

    /**
     * CodeRunner's current Jobe settings.
     *
     * @return \stdClass With host, apikey and enabled (whether CodeRunner's Jobe sandbox is on).
     */
    public function current(): \stdClass {
        return (object) [
            'host' => trim((string) get_config('qtype_coderunner', 'jobe_host')),
            'apikey' => trim((string) get_config('qtype_coderunner', 'jobe_apikey')),
            'enabled' => (bool) get_config('qtype_coderunner', 'jobesandbox_enabled'),
        ];
    }

    /**
     * Which kind of server a Jobe host setting points at.
     *
     * @param string $host A value of CodeRunner's Jobe server setting, with or without http:// or https://.
     * @return string One of the SERVER_ constants.
     */
    public static function server_kind(string $host): string {
        $host = strtolower(trim($host));
        if ($host === '') {
            return self::SERVER_NONE;
        }
        $plain = rtrim(preg_replace('#^https?://#', '', $host), '/');
        if ($plain === 'api.onlinecompiler.io/api') {
            return self::SERVER_ONLINECOMPILER;
        }
        $hostname = explode('/', explode(':', $plain)[0])[0];
        if ($hostname === self::CANTERBURY_HOST) {
            return self::SERVER_CANTERBURY;
        }
        return self::SERVER_OTHER;
    }

    /**
     * Whether a string looks like an OnlineCompiler.io API key. CodeRunner takes keys of up to 40 characters.
     *
     * @param string $apikey The key as pasted.
     * @return bool
     */
    public static function valid_apikey(string $apikey): bool {
        return (bool) preg_match('/^[A-Za-z0-9_-]{16,40}$/', $apikey);
    }

    /**
     * Whether the site's settings from before the first connect are kept, ready to be put back.
     *
     * @return bool
     */
    public function has_previous(): bool {
        return (bool) get_config('tool_onlinecompiler', 'haveprevious');
    }

    /**
     * The Jobe server the site used before it was connected, or '' when none was set.
     *
     * @return string
     */
    public function previous_host(): string {
        return (string) get_config('tool_onlinecompiler', 'previoushost');
    }

    /**
     * Points CodeRunner at OnlineCompiler.io with the given API key, and turns its Jobe sandbox on.
     *
     * The first time, the site's own Jobe server and key are kept so that switch_back() can put them back.
     * Connecting again (to change the key) keeps those first settings.
     *
     * @param string $apikey An OnlineCompiler.io API key.
     */
    public function connect(string $apikey): void {
        $apikey = trim($apikey);
        if (!self::valid_apikey($apikey)) {
            throw new \invalid_parameter_exception('Not an OnlineCompiler.io API key');
        }
        $current = $this->current();
        if (!$this->has_previous() && self::server_kind($current->host) !== self::SERVER_ONLINECOMPILER) {
            set_config('previoushost', $current->host, 'tool_onlinecompiler');
            set_config('previousapikey', $current->apikey, 'tool_onlinecompiler');
            set_config('previousenabled', $current->enabled ? 1 : 0, 'tool_onlinecompiler');
            set_config('haveprevious', 1, 'tool_onlinecompiler');
        }
        $this->set_coderunner(self::JOBE_SERVER, $apikey, true, $current);
    }

    /**
     * Puts back the Jobe server, key and sandbox setting the site had before it was first connected.
     *
     * @return bool False when there was nothing to put back.
     */
    public function switch_back(): bool {
        if (!$this->has_previous()) {
            return false;
        }
        $this->set_coderunner(
            (string) get_config('tool_onlinecompiler', 'previoushost'),
            (string) get_config('tool_onlinecompiler', 'previousapikey'),
            (bool) get_config('tool_onlinecompiler', 'previousenabled'),
            $this->current()
        );
        foreach (['previoushost', 'previousapikey', 'previousenabled', 'haveprevious'] as $name) {
            unset_config($name, 'tool_onlinecompiler');
        }
        return true;
    }

    /**
     * Writes CodeRunner's settings, and records each change in the site's config changes report as an admin
     * changing them by hand would. The API key is recorded as asterisks.
     *
     * @param string $host The Jobe server.
     * @param string $apikey The Jobe API key.
     * @param bool $enabled Whether CodeRunner's Jobe sandbox is on.
     * @param \stdClass $old The settings being replaced, from current().
     */
    private function set_coderunner(string $host, string $apikey, bool $enabled, \stdClass $old): void {
        global $CFG;
        require_once($CFG->libdir . '/adminlib.php');

        set_config('jobe_host', $host, 'qtype_coderunner');
        set_config('jobe_apikey', $apikey, 'qtype_coderunner');
        set_config('jobesandbox_enabled', $enabled ? 1 : 0, 'qtype_coderunner');

        if ($old->host !== $host) {
            add_to_config_log('jobe_host', $old->host, $host, 'qtype_coderunner');
        }
        if ($old->apikey !== $apikey) {
            add_to_config_log(
                'jobe_apikey',
                $old->apikey === '' ? '' : '********',
                $apikey === '' ? '' : '********',
                'qtype_coderunner'
            );
        }
        if ($old->enabled !== $enabled) {
            add_to_config_log('jobesandbox_enabled', $old->enabled ? '1' : '0', $enabled ? '1' : '0', 'qtype_coderunner');
        }
    }

    /**
     * Runs a small Python program through CodeRunner's own Jobe sandbox, as a quiz question would, with the
     * Jobe server and key CodeRunner has now.
     *
     * @return \stdClass With ok (bool), reason (a tool_onlinecompiler string id), detail (text for the admin, may be
     *     empty) and output (what the program printed).
     */
    public function test(): \stdClass {
        $current = $this->current();
        if ($current->host === '') {
            return $this->result(false, 'test_nohost');
        }
        if (!$current->enabled) {
            return $this->result(false, 'test_disabled');
        }

        $sandbox = new \qtype_coderunner_jobesandbox();
        $languages = $sandbox->get_languages();
        if ($languages->error !== \qtype_coderunner_sandbox::OK) {
            // A Jobe server that checks keys can refuse the key here already.
            return $this->result(false, self::reason_for($languages->error, 'test_unreachable'));
        }
        if (!in_array('python3', $languages->languages, true)) {
            return $this->result(false, 'test_unreachable');
        }

        $run = $sandbox->execute(self::TEST_PROGRAM, 'python3', '', null, null, false);
        $output = isset($run->output) ? (string) $run->output : '';
        $stderr = isset($run->stderr) ? trim((string) $run->stderr) : '';
        if ($run->error !== \qtype_coderunner_sandbox::OK) {
            return $this->result(false, self::reason_for($run->error, 'test_failed'), $stderr);
        }
        if ((int) $run->result !== \qtype_coderunner_sandbox::RESULT_SUCCESS || trim($output) !== self::TEST_OUTPUT) {
            return $this->result(false, 'test_wrongoutput', $stderr, $output);
        }
        return $this->result(true, 'test_ok', '', $output);
    }

    /**
     * The message for one of CodeRunner's sandbox error codes.
     *
     * @param int $error A qtype_coderunner_sandbox error constant.
     * @param string $otherwise The string id for any other error.
     * @return string A tool_onlinecompiler string id.
     */
    private static function reason_for(int $error, string $otherwise): string {
        switch ($error) {
            case \qtype_coderunner_sandbox::AUTH_ERROR:
            case \qtype_coderunner_sandbox::ACCESS_DENIED:
                return 'test_badkey';
            case \qtype_coderunner_sandbox::SUBMISSION_LIMIT_EXCEEDED:
            case \qtype_coderunner_sandbox::SERVER_OVERLOAD:
                return 'test_busy';
            default:
                return $otherwise;
        }
    }

    /**
     * A test result.
     *
     * @param bool $ok Whether the test passed.
     * @param string $reason A tool_onlinecompiler string id.
     * @param string $detail Text for the admin.
     * @param string $output What the program printed.
     * @return \stdClass
     */
    private function result(bool $ok, string $reason, string $detail = '', string $output = ''): \stdClass {
        return (object) ['ok' => $ok, 'reason' => $reason, 'detail' => $detail, 'output' => $output];
    }
}
