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

namespace tool_onlinecompiler\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\metadata\types\external_location;

#[\PHPUnit\Framework\Attributes\CoversClass(provider::class)]
/**
 * Tests for the privacy provider.
 *
 * @package    tool_onlinecompiler
 * @copyright  2026 RunCode Inc.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \tool_onlinecompiler\privacy\provider
 */
final class provider_test extends \core_privacy\tests\provider_testcase {
    /**
     * The external location is declared, and every string it names exists.
     */
    public function test_get_metadata(): void {
        $items = provider::get_metadata(new collection('tool_onlinecompiler'))->get_collection();
        $this->assertCount(1, $items);
        $location = reset($items);
        $this->assertInstanceOf(external_location::class, $location);
        $this->assertSame('onlinecompiler', $location->get_name());
        $this->assertTrue(get_string_manager()->string_exists($location->get_summary(), 'tool_onlinecompiler'));
        foreach ($location->get_privacy_fields() as $field => $string) {
            $this->assertTrue(get_string_manager()->string_exists($string, 'tool_onlinecompiler'), $field);
        }
    }

    /**
     * The plugin holds data in no context.
     */
    public function test_no_contexts(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $this->assertCount(0, provider::get_contexts_for_userid($user->id));
    }
}
