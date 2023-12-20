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
 * Add content page of mod_coversheet.
 *
 * @package    mod_coversheet
 * @copyright  2023, Brain Station-23 Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $DB, $PAGE, $OUTPUT, $CFG;
require(__DIR__ . '/../../../config.php');
require_once("../lib.php");
require_once("$CFG->libdir/formslib.php");

$id = required_param("id", PARAM_INT); // Course_module ID.

$cm = get_coursemodule_from_id('coversheet', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$moduleinstance = $DB->get_record('coversheet', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
$PAGE->set_url('/mod/coversheet/adminpages/add_content.php', array('id' => $id));
$PAGE->set_title("Add Content");
$PAGE->set_heading("Add Content");

echo $OUTPUT->header();

class add_content_form extends moodleform {
    public function definition() {

        $editoroption = array("subdirs"=>1, "maxfiles" => -1);
        $mform = $this->_form;
        $mform->addElement('editor', 'html_editor', 'Content', null, $editoroption);
        $mform->setType('html_editor', PARAM_RAW);
        $mform->setDefault('html_editor', $this->_customdata['html'] ?? '');
        $mform->addRule('html_editor', 'Please enter the content', 'required');

        $this->add_action_buttons();
    }
}

$mform = new add_content_form(new moodle_url('/mod/coversheet/adminpages/add_content.php', array('id' => $id)));
if ($mform->is_cancelled()) {
    redirect(new moodle_url('/mod/coversheet/view.php', array('id' => $id)));
}
elseif ($data = $mform->get_data()) {
    $phase_id = coversheet_insert_content($data, $context, $id);
    redirect(new moodle_url('/mod/coversheet/adminpages/content_list.php', array('id' => $id)));
}
$mform->display();
echo "<a href='/mod/coversheet/adminpages/content_list.php?id=$id' class='btn btn-md btn-outline-info mt-4'>Content List</a>";
echo $OUTPUT->footer();