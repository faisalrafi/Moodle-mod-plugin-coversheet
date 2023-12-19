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
 * Update content page of mod_coversheet.
 *
 * @package    mod_coversheet
 * @copyright  2023, Brain Station-23 Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $CFG, $DB, $OUTPUT, $PAGE;
require(__DIR__ . '/../../../config.php');
require_once('../lib.php');
require_once("$CFG->libdir/formslib.php");

$id = required_param("id", PARAM_INT); // Course_module ID
$contentid = required_param("contentid", PARAM_INT); // Content ID
$action = optional_param('action', '', PARAM_RAW);

$cm = get_coursemodule_from_id('coversheet', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$moduleinstance = $DB->get_record('coversheet', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

$PAGE->set_url('/mod/coversheet/update_content.php', array('id' => $id, 'contentid' => $contentid));
$PAGE->set_title("Coversheet - Update content");

echo $OUTPUT->header();

class update_content_form extends moodleform
{
    public function definition()
    {
        $mform = $this->_form;

        $mform->addElement('editor', 'html_editor', 'Content', null, [
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            'accepted_types' => '*',
            'maxbytes' => 0, // Maximum file size in bytes (1MB)
        ]);

        $mform->setType('html_editor', PARAM_RAW);
        $mform->setDefault('html_editor', $this->_customdata['html'] ?? '');

        $this->add_action_buttons();
    }
}

$html = $DB->get_record('coversheet_contents', array('id' => $contentid));

$mform = new update_content_form(new moodle_url('/mod/coversheet/adminpages/update_content.php', array('id' => $id, 'contentid' => $contentid)));
if ($mform->is_cancelled()) {
    redirect(new moodle_url('/mod/coversheet/adminpages/content_list.php', array('id' => $id,)));
} elseif ($data = $mform->get_data()) {
    $html->html = "";
    if (!empty($data->html_editor)) {
        $html->html_editor = $data->html_editor;
        $html = file_postupdate_standard_editor($html, 'html', coversheet_editor_options(), $context, 'mod_coversheet', 'html_editor', $html->id);
    }
    $html->timemodified = time();

    $DB->update_record('coversheet_contents', $html);

    redirect(new moodle_url('/mod/coversheet/adminpages/content_list.php', array('id' => $id)), 'Content Updated Successfully');
}
if ($action === 'delete'){
    $DB->delete_records('coversheet_contents',['id' => $contentid]);
    redirect(new moodle_url('/mod/coversheet/adminpages/content_list.php', array('id' => $id)), 'Content Deleted Successfully');
}
if ($contentid) {
    $content = coversheet_get_html_data($contentid);

    $formData = file_prepare_standard_editor($content, 'html', coversheet_editor_options(), $context, 'mod_coversheet', 'html_editor', $content->id);
    $mform->set_data($formData);
}
$mform->display();

echo $OUTPUT->footer();
