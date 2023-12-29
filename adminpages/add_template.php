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
 * Add template page of mod_coversheet.
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
$PAGE->set_url('/mod/coversheet/adminpages/add_template.php', array('id' => $id));
$PAGE->set_title(get_string('addtemplatetitle', 'coversheet'));

class add_template_form extends moodleform {
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('html', '<h4>Available short names</h4>');
        $short_names = get_short_names($this->_customdata['cmid']);
        foreach ($short_names as $short_name) {
            $mform->addElement('html', '<code class="btn btn-outline-primary mr-2 mt-2" style="user-select: text"> ' . $short_name . '</code>');
        }
        $mform->addElement('html', '<div class="mb-5"></div>');

        $mform->addElement('text', 'title', get_string('template_title', 'coversheet'), array());
        $mform->setType('title', PARAM_TEXT);
        $mform->addRule('title', get_string('templateaddruletitle', 'coversheet'), get_string('addcontentruletype', 'coversheet'));

        $editoroption = array("subdirs"=>1, "maxfiles" => -1);

        $mform->addElement('editor', 'template_editor', 'Template', null, $editoroption, 'rows="20" cols="50"');
        $mform->setType('template_editor', PARAM_RAW);
        $mform->setDefault('template_editor', $this->_customdata['template'] ?? '');
        $mform->addRule('template_editor', get_string('templateaddrule', 'coversheet'), get_string('addcontentruletype', 'coversheet'));

        $mform->addElement('checkbox', 'active', get_string('template_active', 'coversheet'));

        $this->add_action_buttons();
    }
}

$mform = new add_template_form(new moodle_url('/mod/coversheet/adminpages/add_template.php', array('id' => $id)), array('cmid' => $id));
if ($mform->is_cancelled()) {
    redirect(new moodle_url('/mod/coversheet/adminpages/upload_template.php', array('id' => $id)));
}
else if ($data = $mform->get_data()) {
    $phase_id = coversheet_insert_template($data, $context, $id);
    redirect(new moodle_url('/mod/coversheet/adminpages/upload_template.php', array('id' => $id)));
}
echo $OUTPUT->header();
$mform->display();
echo "<a href='$CFG->wwwroot/mod/coversheet/adminpages/upload_template.php?id=$id' class='btn btn-md btn-outline-info mt-4'>Template List</a>";
echo $OUTPUT->footer();