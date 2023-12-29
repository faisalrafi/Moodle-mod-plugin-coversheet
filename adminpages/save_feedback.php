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
 * Save Feedback file.
 *
 * @package    mod_coversheet
 * @copyright  2023, Brain Station-23 Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $CFG, $USER, $DB;
require_once('../../../config.php');
require_once($CFG->libdir . '/gradelib.php');
header('Content-Type: application/json');

$grade_type = $_POST['grade_type'];
$final_grade = $_POST['final_grade'];
$comment = $_POST['comment'];
$signature = $_POST['signatureData'];
$teacherName = $_POST['teacherName'];
$cmid = $_POST['cmid'];
$studentid = $_POST['studentid'];
$checkedResources = array();

if (isset($_POST['checkedResources'])) {
    $checkedResources = $_POST['checkedResources'];
}

$error_message = "";

$instance = $DB->get_record('course_modules', array('id' => $cmid));

$gradeitem = grade_item::fetch(array('itemtype' => 'mod',
                'itemmodule' => 'coversheet',
                'iteminstance' => $instance->instance,
                'itemnumber' => 0,
                'courseid' => $instance->course));

if ($grade_type != 0) {
    
    $gradeRecord = new stdClass();
    $gradeRecord->itemid = $gradeitem->id;
    $gradeRecord->userid = $studentid;
    $gradeRecord->rawgrade = NULL;
    $gradeRecord->usermodified = $USER->id;
    $gradeRecord->rawscaleid = NULL;
    
    if ($grade_type == 1) {
        if ($final_grade > $gradeitem->grademax) {
            echo json_encode(["message" => "Given grade is more than maximum grade (Out of " . $gradeitem->grademax . ")"]);
            die();
        } 

        if ($final_grade < $gradeitem->grademin) {
            echo json_encode(["message" => "Given grade is less than minimum grade (Out of " . $gradeitem->grademin . ")"]);
            die();
        }

        $gradeRecord->rawgrademax = $gradeitem->grademax;
        $gradeRecord->rawgrademin = $gradeitem->grademin;
        $gradeRecord->finalgrade = $final_grade;

    } else if ($grade_type == 2) {
        $scales = grade_scale::fetch(array('id' => $gradeitem->scaleid))->load_items();

        $gradeRecord->rawscaleid = $gradeitem->scaleid;
        $gradeRecord->rawgrademax = count($scales);
        $gradeRecord->rawgrademin = 1;
        $gradeRecord->finalgrade = $final_grade;
    }

    $existingGrade = $DB->get_record('grade_grades', ['itemid' => $gradeitem->id, 'userid' => $studentid]);

    if ($existingGrade) {
        $existingGrade->rawscaleid = $gradeRecord->rawscaleid;
        $existingGrade->rawgrademax = $gradeRecord->rawgrademax;
        $existingGrade->rawgrademin = $gradeRecord->rawgrademin;
        $existingGrade->finalgrade = $gradeRecord->finalgrade;
        $existingGrade->usermodified = $USER->id;
        $existingGrade->timemodified = time();

        $DB->update_record('grade_grades', $existingGrade);
    } else {
        $DB->insert_record('grade_grades', $gradeRecord);
    }
}                

$data = new stdClass();
$data->cmid = $cmid;
$data->attempt_id = 1;
$data->student_id = $studentid;
$data->status = 1;
$data->result = $final_grade;
$data->comment = $comment;
$data->assessor_name = $teacherName;
$data->assessor_sign = $signature;
$data->timecreated = time();

$feedback_result = $DB->insert_record('coversheet_feedbacks', $data);

foreach ($checkedResources as $resource_id) {
//    $sql = "UPDATE {coversheet_requirements}
//            SET status = 1, student_id = :studentid
//            WHERE cmid = :cmid AND resource = :resource";
//    $params = array('studentid' => $studentid, 'cmid' => $cmid, 'resource' => $resource);
//    $DB->execute($sql, $params);
    $data = new stdClass();
    $data->cmid = $cmid;
    $data->reqid = $resource_id;
    $data->student_id = $studentid;
    $data->status = 1;
    $data->timecreated = time();
    $DB->insert_record('coversheet_reqcheck', $data);
}

if ($feedback_result){
    echo json_encode(["message" => "Success"]);
}