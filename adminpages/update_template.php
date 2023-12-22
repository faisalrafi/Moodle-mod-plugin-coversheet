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
 * Update template page of mod_coversheet.
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
$templateid = required_param("templateid", PARAM_INT); // Content ID
$action = optional_param('action', '', PARAM_RAW);

$cm = get_coursemodule_from_id('coversheet', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$moduleinstance = $DB->get_record('coversheet', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

$PAGE->set_url('/mod/coversheet/update_template.php', array('id' => $id, 'templateid' => $templateid));
$PAGE->set_title("Coversheet - Update template");

echo $OUTPUT->header();

class update_template_form extends moodleform
{
    public function definition()
    {
        $mform = $this->_form;

        $mform->addElement('text', 'title', get_string('template_title', 'coversheet'), array());
        $mform->addRule('title', 'Please enter the title', 'required');

        $mform->addElement('editor', 'html_editor', 'Content', null, [
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            'accepted_types' => '*',
            'maxbytes' => 0, // Maximum file size in bytes (1MB)
        ]);

        $mform->setType('html_editor', PARAM_RAW);
        $mform->setDefault('html_editor', $this->_customdata['html'] ?? '');

        $mform->addElement('checkbox', 'active', get_string('template_active', 'coversheet'));

        $this->add_action_buttons();
    }
}

$template = $DB->get_record('coversheet_templates', array('id' => $templateid));

$mform = new update_template_form(new moodle_url('/mod/coversheet/adminpages/update_template.php', array('id' => $id, 'templateid' => $templateid)));
if ($mform->is_cancelled()) {
    redirect(new moodle_url('/mod/coversheet/adminpages/upload_template.php', array('id' => $id,)));
} elseif ($data = $mform->get_data()) {
    $template->title = $data->title;
    $template->template = "";
    if (!empty($data->html_editor)) {
        $template->template = $data->html_editor;
        $template = file_postupdate_standard_editor($template, 'html', coversheet_editor_options(), $context, 'mod_coversheet', 'html_editor', $template->id);
    }
    if (!isset($data->active)) {
        $template->active = 0;
    } else {
        $template->active = $data->active;
    }
    $template->timemodified = time();

    $DB->update_record('coversheet_templates', $template);

    redirect(new moodle_url('/mod/coversheet/adminpages/upload_template.php', array('id' => $id)), 'Template Updated Successfully');
}
if ($action === 'delete') {
    $DB->delete_records('coversheet_templates',['id' => $templateid]);
    redirect(new moodle_url('/mod/coversheet/adminpages/upload_template.php', array('id' => $id)), 'Template Deleted Successfully', null, \core\output\notification::NOTIFY_SUCCESS);
}
if ($templateid) {
    $content = coversheet_get_html_data($templateid);

    $formData = file_prepare_standard_editor($content, 'html', coversheet_editor_options(), $context, 'mod_coversheet', 'html_editor', $content->id);
    $mform->set_data($formData);
}
$mform->display();

echo $OUTPUT->footer();
