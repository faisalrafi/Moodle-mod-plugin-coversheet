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
 * Add resource form of mod_coversheet.
 *
 * @package    mod_coversheet
 * @copyright  2023, Brain Station-23 Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $CFG;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class add_resource_form extends moodleform {
    public function definition()
    {
        $mform = $this->_form;

        $mform->addElement('textarea', 'resource', 'Resource');
        $mform->setType('resource', PARAM_TEXT);
        $mform->addRule('resource', 'Please enter the resource name', 'required');


        $this->add_action_buttons('Cancel', 'Add Resources');
    }
}