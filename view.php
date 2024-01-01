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
 * View Page of mod_coversheet.
 *
 * @package    mod_coversheet
 * @copyright  2023, Brain Station-23 Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
global $DB, $PAGE, $OUTPUT, $CFG, $USER;
require_once('../../config.php');
require_once('lib.php');
require_once($CFG->libdir . '/dmllib.php');

$id = required_param('id', PARAM_INT);    // Course Module ID.

$cm = get_coursemodule_from_id('coversheet', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$moduleinstance = $DB->get_record('coversheet', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

$PAGE->set_url('/mod/coversheet/view.php', array('id' => $cm->id));
$PAGE->set_title(get_string('viewtitle', 'coversheet'));
$PAGE->requires->css('/mod/coversheet/mod_coversheet_style.css');
$hasCapabilityViewPage = has_capability('mod/coversheet:viewpage', $context);

//$date = $DB->get_field('coversheet_submissions' , 'submission_date', ['cmid' => $id]);
//$updated_date = userdate($date, get_string('strftimedate'));
//var_dump($updated_date); die();
if ($hasCapabilityViewPage) {
    echo $OUTPUT->header();

    $sql = "SELECT ca.student_id, u.firstname, u.lastname
    FROM {coversheet_attempts} ca
    LEFT JOIN {user} u ON u.id = ca.student_id
    WHERE ca.cmid = :cmid 
    GROUP BY ca.student_id";
    $details = $DB->get_records_sql($sql, ['cmid' => $id]);
//    echo "<pre>";var_dump($details); die();

    $display = [
        'cmid' => $id,
        'details' => array_values($details),
        'webroot' => $CFG->wwwroot
    ];
    echo $OUTPUT->render_from_template('mod_coversheet/view', $display);
} else {
    echo $OUTPUT->header();
    $sql = "SELECT * FROM {coversheet_contents} cc WHERE cc.cmid = :cmid";
    $contents = $DB->get_records_sql($sql, ['cmid' => $id]);
    $html = '';
    foreach ($contents as $content){
        $html .= coversheet_load_content($content, $context, $id);

    }
    $info = $DB->get_record('user', ['id' => $USER->id]);
    $name = $info->firstname . ' ' . $info->lastname;
    $email = $info->email;
    $phone = $info->phone1;
    $currentdate = date('d F Y');

    $sql = "SELECT ft.id, ft.name, ft.datatype, ft.param, ft.required
            FROM {coversheet_field_type} ft WHERE ft.cmid = '$id'";
    $info = $DB->get_records_sql($sql);
//    echo "<pre>";var_dump($info);die();
    foreach ($info as $field) {
        $field->isCheckbox = ($field->datatype === get_string('checkbox', 'coversheet'));
        $field->isTextarea = ($field->datatype === get_string('textarea', 'coversheet'));
        $field->isTextinput = ($field->datatype === get_string('text', 'coversheet'));
        $field->isRadio = ($field->datatype === get_string('radio', 'coversheet'));

        $field->isRequired = ($field->required == 1);

        if ($field->isRadio) {
            $field->radioOptions = explode(',', $field->param);
        }
        $field->isDropdown = ($field->datatype === get_string('dropdown', 'coversheet'));
        if ($field->isDropdown) {
            $field->dropdownList = explode(',', $field->param);
        }
    }

    $sql1 = "SELECT * FROM {coversheet_attempts} ca
             WHERE ca.cmid= '$id' AND ca.student_id = '$USER->id'";
    $results = $DB->get_records_sql($sql1);

    $display = [
        'cmid' => $id,
        'html' => $html,
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'currentDate' => $currentdate,
        'webroot' => $CFG->wwwroot,
        'infos' => array_values($info),
        'results' => array_values($results),
    ];
    echo $OUTPUT->render_from_template('mod_coversheet/view_student', $display);

}

echo $OUTPUT->footer();