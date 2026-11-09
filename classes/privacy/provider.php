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

/**
 * The plugin stores no personal data and sends none.
 *
 * It keeps an OnlineCompiler.io API key in the site's settings, and its test sends a fixed program with no user data.
 * Students' code reaches the Jobe server through CodeRunner, whose privacy provider describes it.
 *
 * @package    tool_onlinecompiler
 * @copyright  2026 RunCode Inc.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements \core_privacy\local\metadata\null_provider {
    /**
     * Why the plugin stores no personal data.
     *
     * @return string A string id in this plugin's language file.
     */
    public static function get_reason(): string {
        return 'privacy:metadata';
    }
}
