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

#[\PHPUnit\Framework\Attributes\CoversClass(manager::class)]
/**
 * Tests for the manager: telling Jobe servers apart, and connecting and switching back without losing the site's
 * own settings. The test run itself needs a Jobe server, so it is not covered here.
 *
 * @package    tool_onlinecompiler
 * @copyright  2026 RunCode Inc.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \tool_onlinecompiler\manager
 */
final class manager_test extends \advanced_testcase {
    /** A made-up key in the form of an OnlineCompiler.io key. */
    private const KEY = '0123456789abcdef0123456789abcdef';

    /**
     * Jobe server settings, as admins type them, and the kind of server each points at.
     *
     * @return array
     */
    public static function server_kind_provider(): array {
        return [
            'empty' => ['', manager::SERVER_NONE],
            'ours' => ['https://api.onlinecompiler.io/api', manager::SERVER_ONLINECOMPILER],
            'ours without the scheme' => ['api.onlinecompiler.io/api', manager::SERVER_ONLINECOMPILER],
            'ours with a slash' => ['https://API.onlinecompiler.io/api/', manager::SERVER_ONLINECOMPILER],
            'canterbury' => ['jobe2.cosc.canterbury.ac.nz', manager::SERVER_CANTERBURY],
            'canterbury with a scheme' => ['http://jobe2.cosc.canterbury.ac.nz', manager::SERVER_CANTERBURY],
            'own server with a port' => ['jobe.example.edu:4010', manager::SERVER_OTHER],
            'own server with https' => ['https://jobe.example.edu/', manager::SERVER_OTHER],
        ];
    }

    /**
     * Each setting is put in the right group.
     *
     * @dataProvider server_kind_provider
     * @param string $host The Jobe server setting.
     * @param string $expected The kind of server.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('server_kind_provider')]
    public function test_server_kind(string $host, string $expected): void {
        $this->assertSame($expected, manager::server_kind($host));
    }

    /**
     * Keys are checked for form only, and CodeRunner's 40-character limit holds.
     */
    public function test_valid_apikey(): void {
        $this->assertTrue(manager::valid_apikey(self::KEY));
        $this->assertFalse(manager::valid_apikey(''));
        $this->assertFalse(manager::valid_apikey('abc'));
        $this->assertFalse(manager::valid_apikey(str_repeat('a', 41)));
        $this->assertFalse(manager::valid_apikey('0123456789abcdef 0123456789abcdef'));
    }

    /**
     * Connecting points CodeRunner at OnlineCompiler.io, and switching back restores the site's own settings.
     */
    public function test_connect_and_switch_back(): void {
        $this->resetAfterTest();
        set_config('jobe_host', 'jobe.example.edu:4010', 'qtype_coderunner');
        set_config('jobe_apikey', 'the-site-key', 'qtype_coderunner');
        set_config('jobesandbox_enabled', 0, 'qtype_coderunner');

        $manager = new manager();
        $this->assertFalse($manager->has_previous());
        $manager->connect(self::KEY);

        $this->assertSame(manager::JOBE_SERVER, get_config('qtype_coderunner', 'jobe_host'));
        $this->assertSame(self::KEY, get_config('qtype_coderunner', 'jobe_apikey'));
        $this->assertSame('1', get_config('qtype_coderunner', 'jobesandbox_enabled'));
        $this->assertTrue($manager->has_previous());
        $this->assertSame('jobe.example.edu:4010', $manager->previous_host());

        // A second connect, to change the key, keeps the first settings to put back.
        $manager->connect(strrev(self::KEY));
        $this->assertSame(strrev(self::KEY), get_config('qtype_coderunner', 'jobe_apikey'));
        $this->assertSame('jobe.example.edu:4010', $manager->previous_host());

        $this->assertTrue($manager->switch_back());
        $this->assertSame('jobe.example.edu:4010', get_config('qtype_coderunner', 'jobe_host'));
        $this->assertSame('the-site-key', get_config('qtype_coderunner', 'jobe_apikey'));
        $this->assertSame('0', get_config('qtype_coderunner', 'jobesandbox_enabled'));
        $this->assertFalse($manager->has_previous());
        $this->assertFalse($manager->switch_back());
    }

    /**
     * A site already on OnlineCompiler.io has nothing of its own to put back.
     */
    public function test_connect_when_already_connected(): void {
        $this->resetAfterTest();
        set_config('jobe_host', manager::JOBE_SERVER, 'qtype_coderunner');
        set_config('jobe_apikey', self::KEY, 'qtype_coderunner');

        $manager = new manager();
        $manager->connect(strrev(self::KEY));
        $this->assertFalse($manager->has_previous());
        $this->assertSame(strrev(self::KEY), get_config('qtype_coderunner', 'jobe_apikey'));
    }

    /**
     * Each change is in the config changes report, with the key hidden.
     */
    public function test_changes_are_logged_with_the_key_hidden(): void {
        $this->resetAfterTest();
        set_config('jobe_host', 'jobe.example.edu', 'qtype_coderunner');
        set_config('jobe_apikey', 'the-site-key', 'qtype_coderunner');

        (new manager())->connect(self::KEY);

        // The newest entry: installing CodeRunner logged its defaults too.
        $host = $this->latest_log('jobe_host');
        $this->assertSame('jobe.example.edu', $host->oldvalue);
        $this->assertSame(manager::JOBE_SERVER, $host->value);
        $key = $this->latest_log('jobe_apikey');
        $this->assertSame('********', $key->oldvalue);
        $this->assertSame('********', $key->value);
    }

    /**
     * The newest config changes report entry for one of CodeRunner's settings.
     *
     * @param string $name The setting.
     * @return \stdClass
     */
    private function latest_log(string $name): \stdClass {
        global $DB;
        $records = $DB->get_records('config_log', ['plugin' => 'qtype_coderunner', 'name' => $name], 'id DESC', '*', 0, 1);
        $this->assertNotEmpty($records);
        return reset($records);
    }

    /**
     * Anything that isn't a key is refused before CodeRunner's settings change.
     */
    public function test_connect_refuses_a_bad_key(): void {
        $this->resetAfterTest();
        set_config('jobe_host', 'jobe.example.edu', 'qtype_coderunner');
        $this->expectException(\invalid_parameter_exception::class);
        try {
            (new manager())->connect('not a key');
        } finally {
            $this->assertSame('jobe.example.edu', get_config('qtype_coderunner', 'jobe_host'));
        }
    }
}
