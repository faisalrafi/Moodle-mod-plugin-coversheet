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
 * report of mod_coversheet.
 *
 * @package    mod_coversheet
 * @copyright  2023, Brain Station-23 Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $DB, $PAGE, $OUTPUT, $CFG, $COURSE;
require(__DIR__ . '/../../../config.php');
require_once("../lib.php");
require_once("$CFG->libdir/formslib.php");

$id = required_param("id", PARAM_INT); // Course_module ID.
$studentid = required_param('studentid', PARAM_INT); // Student ID.

require_login();
$context = context_module::instance($id);
$PAGE->set_url('/mod/coversheet/adminpages/report.php', array('id' => $id));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('reporttitle', 'coversheet'));
$PAGE->requires->css('/mod/coversheet/mod_coversheet_style.css');

echo $OUTPUT->header();

$query = "SELECT cc.id as contentid, cc.html FROM {coversheet_contents} cc 
          WHERE cc.cmid = :cmid";
$contents = $DB->get_records_sql($query, ['cmid' => $id]);

foreach ($contents as $content) {
    $html = coversheet_prepare_html_data_for_view($content, $context);
}

$sql = "SELECT * FROM {coversheet_attempts} ca
             WHERE ca.cmid = :cmid AND ca.student_id = :studentid";
$details = $DB->get_record_sql($sql, ['cmid' => $id, 'studentid' => $studentid]);
//var_dump($details); die();
$student_name = $details->candidate_name;
$student_sign = $details->candidate_sign;

$resource_query = "SELECT * FROM {coversheet_requirements} WHERE cmid = '$id'";
$resources = $DB->get_records_sql($resource_query);

$feedback_query = "SELECT assessor_name, assessor_sign FROM {coversheet_feedbacks} cf 
                   WHERE cf.cmid= '$id' AND cf.student_id = '$studentid'";
$feedback = $DB->get_records_sql($feedback_query);

$query = "SELECT cft.name, cfd.value, cft.datatype FROM {coversheet_field_type} cft
          LEFT JOIN {coversheet_field_data} cfd ON cft.id = cfd.fieldid
          WHERE cft.cmid = :cmid AND cfd.student_id = :studentid";
$datas = $DB->get_records_sql($query, ['cmid' => $id, 'studentid' => $studentid]);
//echo "<pre>";var_dump($datas); die();
foreach ($datas as $data) {
    $data->datatype = ($data->datatype === 'checkbox');
    $data->isCheckbox = ($data->datatype === 'checkbox');
    $data->isChecked = ($data->datatype === 'checkbox' && $data->value == 1);
}

$currentdate = date('d F Y');

$instance = $DB->get_record('course_modules', array('id' => $id));
$grading_enabled = $DB->get_record('coversheet', array('id' => $instance->instance));

$wantgrade = 0;

if ($grading_enabled->wantgrade == 1)


if ($grading_enabled->wantgrade == 1) {
    $gradeitem = grade_item::fetch(array('itemtype' => 'mod',
                    'itemmodule' => 'coversheet',
                    'iteminstance' => $instance->instance,
                    'itemnumber' => 0,
                    'courseid' => $instance->course));

    // echo $gradeitem->gradetype ."<br>";
    // echo $gradeitem->grademax ."<br>";
    // echo $gradeitem->grademin ."<br>";
    // echo $gradeitem->scaleid ."<br>";   
    
    
    if ($gradeitem->scaleid) {
        $scales = grade_scale::fetch(array('id' => $gradeitem->scaleid))->load_items();

        for ($i = 0; $i < count($scales); $i++) {
            $scaleObject = new stdClass();
            $scaleObject->value = $i + 1;
            $scaleObject->data = $scales[$i];
            $scaleObjects[] = $scaleObject;
        }

        $gradeitem->scaleObjects = $scaleObjects;
    } 
}

$display = [
    'contents' => array_values($contents),
    'resources' => array_values($resources),
    'feedbacks' => array_values($feedback),
    'datas' => array_values($datas),
    'gradingEnabled' => $grading_enabled->wantgrade,
    'gradeItem' => $gradeitem,
    'cmid' => $id,
    'studentid' => $studentid,
    'currentDate' => $currentdate,
    'student_name' => $student_name,
    'student_sign' => $student_sign,
    'webroot' => $CFG->wwwroot
];
echo $OUTPUT->render_from_template('mod_coversheet/report', $display);
echo $OUTPUT->footer();