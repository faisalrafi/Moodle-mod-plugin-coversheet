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

$result = $_POST['overall_result'];
$comment = $_POST['comment'];
$satisfactory = $_POST['isSatisfactory'];
$signature = $_POST['signatureData'];
$teacherName = $_POST['teacherName'];
$cmid = $_POST['cmid'];
$studentid = $_POST['studentid'];
$checkedResources = $_POST['checkedResources'];

$data = new stdClass();
$data->cmid = $cmid;
$data->attempt_id = 1;
$data->student_id = $studentid;
$data->status = $satisfactory;
$data->result = $result;
$data->comment = $comment;
$data->assessor_name = $teacherName;
$data->assessor_sign = $signature;
$data->timecreated = time();

$result = $DB->insert_record('coversheet_feedbacks', $data);

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

if ($result){
    echo 'success';
}