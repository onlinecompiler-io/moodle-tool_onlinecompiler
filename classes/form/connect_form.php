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

namespace tool_onlinecompiler\form;

/**
 * The form that takes an OnlineCompiler.io API key and points CodeRunner at OnlineCompiler.io.
 *
 * The page that shows it loads lib/formslib.php first.
 *
 * @package    tool_onlinecompiler
 * @copyright  2026 RunCode Inc.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class connect_form extends \moodleform {
    /**
     * The key field and the button.
     */
    protected function definition() {
        $mform = $this->_form;
        $connected = !empty($this->_customdata['connected']);

        $mform->addElement('passwordunmask', 'apikey', get_string('apikey', 'tool_onlinecompiler'), ['size' => 44]);
        $mform->setType('apikey', PARAM_RAW_TRIMMED);
        $mform->addRule('apikey', get_string('required'), 'required', null, 'client');
        $mform->addHelpButton('apikey', 'apikey', 'tool_onlinecompiler');

        $this->add_action_buttons(false, get_string($connected ? 'changekey' : 'connect', 'tool_onlinecompiler'));
    }

    /**
     * Checks the key's form before anything is saved.
     *
     * @param array $data The submitted values.
     * @param array $files Uploaded files (none).
     * @return array Errors by field.
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if (!\tool_onlinecompiler\manager::valid_apikey(trim($data['apikey'] ?? ''))) {
            $errors['apikey'] = get_string('apikey_invalid', 'tool_onlinecompiler');
        }
        return $errors;
    }
}
