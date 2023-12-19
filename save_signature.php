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
 * Save Signature file.
 *
 * @package    mod_coversheet
 * @copyright  2023, Brain Station-23 Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $CFG, $USER, $DB;
require_once('../../config.php'); // Adjust the path based on your Moodle configuration
require_once($CFG->libdir . '/gradelib.php');

$signature = $_POST['signatureData'];
$studentName = $_POST['studentName'];
$cmid = $_POST['cmid'];
$date = $_POST['date'];
$declaration = 'To be signed once the assessment is complete and ready for submission';

$data = new stdClass();
$data->cmid = $cmid; // Replace with the actual course module ID
$data->student_id = $USER->id;
$data->status = 1;
$data->declaration = $declaration;
$data->date = $date;
$data->candidate_name = $studentName;
$data->candidate_sign = $signature;
$data->timecreated = time();

$result = $DB->insert_record('coversheet_attempts', $data);

if ($result){
    echo 'success';
}