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
 * Edit and Delete user profile field of mod_coversheet.
 *
 * @package    mod_coversheet
 * @copyright  2023, Brain Station-23 Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $CFG, $USER, $DB, $OUTPUT, $PAGE;
require_once('../../../config.php');
require_once($CFG->libdir . '/formslib.php');

$id = required_param('id', PARAM_INT);
$cmid = required_param('cmid', PARAM_INT);
$datatype = required_param('datatype', PARAM_ALPHA);
$action = optional_param('action', '', PARAM_ALPHA);

require_login();

$PAGE->set_url('/mod/coversheet/adminpages/update_profile.php', ['id' => $id, 'cmid' => $cmid, 'datatype' => $datatype, 'action' => $action]);
$PAGE->set_context(context_system::instance());
$PAGE->set_title("Update Profile field");

$redirect_url = new moodle_url('/mod/coversheet/adminpages/user_information.php', ['id' => $cmid]);
class edit_field_form extends moodleform {
    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'datatype');
        $mform->setType('datatype', PARAM_ALPHA);

        $mform->addElement('text', 'name', 'Name');
        $mform->setType('name', PARAM_TEXT);

        $mform->addElement('text', 'shortname', 'Shortname');
        $mform->setType('shortname', PARAM_TEXT);

        $mform->addElement('text', 'param', 'Param');
        $mform->setType('param', PARAM_TEXT);

        $this->add_action_buttons('Cancel', 'Update Profile Field');
    }
}

if($action === 'delete'){
    $fields = $DB->get_record('coversheet_field_type', ['id' => $id]);
    $DB->delete_records('coversheet_field_data', ['fieldid' => $fields->id]);
    $DB->delete_records('coversheet_field_type', ['id' => $id]);
    redirect(($redirect_url), 'Successfully Deleted the field and its data');
}
else {
    $form = new edit_field_form(new moodle_url('/mod/coversheet/adminpages/update_profile.php', array('id' => $id, 'cmid' => $cmid, 'datatype' => $datatype, 'action' => $action)));
    $fieldData = $DB->get_record('coversheet_field_type', ['id' => $id]);
    $form->set_data($fieldData);

    if ($form->is_cancelled()) {
        redirect($redirect_url,'Cancelled the form');
    } elseif ($data = $form->get_data()) {
        $profile = new stdClass();
        $profile->id = $id;
        $profile->name = $data->name;
        $profile->shortname = $data->shortname;
        $profile->param = $data->param;
        $profile->timecreated = time();

        $DB->update_record('coversheet_field_type', $profile);
        redirect($redirect_url, 'Profile Field Updated');
    }
}
echo $OUTPUT->header();
$form->display();

echo $OUTPUT->footer();
