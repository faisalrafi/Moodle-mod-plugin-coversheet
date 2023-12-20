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
 * update resource of mod_coversheet.
 *
 * @package    mod_coversheet
 * @copyright  2023, Brain Station-23 Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $CFG, $USER, $DB, $PAGE, $OUTPUT;
require_once('../../../config.php');
require_once($CFG->libdir . '/formslib.php');

$id = required_param('id', PARAM_INT);
$cmid = required_param('cmid', PARAM_INT);
$action = required_param('action', PARAM_ALPHA);

require_login();

$PAGE->set_url('/mod/coversheet/adminpages/update_resource.php', array('id' => $id, 'cmid' => $cmid, 'action' => $action));
$PAGE->set_context(context_system::instance());
$PAGE->set_title("Update Resource");
$PAGE->requires->css('/mod/coversheet/mod_coversheet_style.css');

$redirect_url = new moodle_url('/mod/coversheet/adminpages/resource_list.php', ['id' => $cmid]);

class edit_resource_form extends moodleform {
    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('text', 'resource', 'Resource', array('size' => '100%'));
        $mform->setType('resource', PARAM_TEXT);

        $this->add_action_buttons('Cancel', 'Update Resource');
    }
}

if($action === 'delete') {
    $fields = $DB->get_record('coversheet_requirements', ['id' => $id]);
//    var_dump($fields); die();
    $DB->delete_records('coversheet_reqcheck', ['reqid' => $fields->id]);
    $DB->delete_records('coversheet_requirements', ['id' => $id]);
    redirect(($redirect_url), 'Successfully Deleted the resource');
}
else {
    $form = new edit_resource_form(new moodle_url('/mod/coversheet/adminpages/update_resource.php', array('id' => $id, 'cmid' => $cmid, 'action' => $action)));
    $fieldData = $DB->get_record('coversheet_requirements', ['id' => $id]);
    $form->set_data($fieldData);

    if ($form->is_cancelled()) {
        redirect($redirect_url,'Cancelled the form');
    } elseif ($data = $form->get_data()) {
        $resource = new stdClass();
        $resource->id = $id;
        $resource->resource = $data->resource;
        $resource->timemodified = time();

        $DB->update_record('coversheet_requirements', $resource);
        redirect($redirect_url, 'Resource Updated');
    }
}
echo $OUTPUT->header();
$form->display();

echo $OUTPUT->footer();