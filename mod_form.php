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
require_once ("$CFG->libdir/formslib.php");

use core_grades\component_gradeitems;

class mod_coversheet_mod_form extends moodleform_mod {

    public function definition() {
        global $CFG, $COURSE;
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

        $mform->addElement('header', 'modstandardgrade', get_string('gradenoun'));
        
        $itemnumber = 0;
        $component = "mod_coversheet";
        $gradefieldname = component_gradeitems::get_field_name_for_itemnumber($component, $itemnumber, 'grade');
        $isupdate = !empty($this->_cm);
        $gradeoptions = array('isupdate' => $isupdate,
                              'currentgrade' => false,
                              'hasgrades' => false,
                              'canrescale' => $this->_features->canrescale,
                              'useratings' => $this->_features->rating);
        if ($isupdate) {
            $gradeitem = grade_item::fetch(array('itemtype' => 'mod',
                                                    'itemmodule' => $this->_cm->modname,
                                                    'iteminstance' => $this->_cm->instance,
                                                    'itemnumber' => 0,
                                                    'courseid' => $COURSE->id));
            if ($gradeitem) {
                $gradeoptions['currentgrade'] = $gradeitem->grademax;
                $gradeoptions['currentgradetype'] = $gradeitem->gradetype;
                $gradeoptions['currentscaleid'] = $gradeitem->scaleid;
                $gradeoptions['hasgrades'] = $gradeitem->has_grades();
            }
        }

        $mform->addElement('checkbox', 'wantgrade', get_string('wantgrading', 'coversheet'));

        $mform->addElement('modgrade', $gradefieldname, get_string('gradenoun'), $gradeoptions);
        $mform->addHelpButton($gradefieldname, 'modgrade', 'grades');
        $mform->setDefault($gradefieldname, $CFG->gradepointdefault);

        $mform->hideIf($gradefieldname, 'wantgrade', 'notchecked');
        // Number of attempts.
        $attemptoptions = ['0' => get_string('unlimited')];
        for ($i = 1; $i <= COVERSHEET_MAX_ATTEMPT_OPTION; $i++) {
            $attemptoptions[$i] = $i;
        }
        $mform->addElement('select', 'submissions', get_string('submissionsallowed', 'coversheet'),
            $attemptoptions);

        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
    }
}