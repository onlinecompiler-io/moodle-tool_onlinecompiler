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
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;

/**
 * Declares what reaches OnlineCompiler.io once CodeRunner is connected to it.
 *
 * The plugin stores no personal data in Moodle, so there is nothing to export or delete here. Once connected,
 * CodeRunner sends students' answers to OnlineCompiler.io's Jobe server to be run, without names or accounts.
 *
 * @package    tool_onlinecompiler
 * @copyright  2026 RunCode Inc.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\plugin\provider {
    /**
     * The data sent to OnlineCompiler.io.
     *
     * @param collection $collection The collection to add to.
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_external_location_link('onlinecompiler', [
            'code' => 'privacy:metadata:onlinecompiler:code',
            'input' => 'privacy:metadata:onlinecompiler:input',
            'language' => 'privacy:metadata:onlinecompiler:language',
        ], 'privacy:metadata:onlinecompiler');
        return $collection;
    }

    /**
     * No context holds data of this plugin.
     *
     * @param int $userid The user.
     * @return contextlist An empty list.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        return new contextlist();
    }

    /**
     * No user has data of this plugin in any context.
     *
     * @param userlist $userlist The list to fill.
     */
    public static function get_users_in_context(userlist $userlist) {
    }

    /**
     * Nothing to export: the plugin stores no personal data.
     *
     * @param approved_contextlist $contextlist The approved contexts.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
    }

    /**
     * Nothing to delete: the plugin stores no personal data.
     *
     * @param \context $context The context.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
    }

    /**
     * Nothing to delete: the plugin stores no personal data.
     *
     * @param approved_contextlist $contextlist The approved contexts.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
    }

    /**
     * Nothing to delete: the plugin stores no personal data.
     *
     * @param approved_userlist $userlist The approved users.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
    }
}
