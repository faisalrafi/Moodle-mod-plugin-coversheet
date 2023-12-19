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
 * Coversheet module configuration form.
 *
 * @package    mod_coversheet
 * @copyright  2023, Brain Station-23 Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
global $CFG;
defined('MOODLE_INTERNAL') || die();
require_once ($CFG->dirroot.'/course/moodleform_mod.php');
require_once("$CFG->libdir/formslib.php");

class mod_coversheet_mod_form extends moodleform_mod {

    public function definition() {
        $mform = $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Name of the mod.
        $mform->addElement('text', 'name', get_string('name'), array('size' => '64',
            'placeholder' => get_string('placeholder', 'coversheet')));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('pageinfo', 'coversheet'), 'required', null, 'server');

        // Adding the standard "intro" and "introformat" fields.
        $this->standard_intro_elements();
        $element = $mform->getElement('introeditor');
        $attributes = $element->getAttributes();
        $attributes['rows'] = 5;
        $element->setAttributes($attributes);

        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
    }
}