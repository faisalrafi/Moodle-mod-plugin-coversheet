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
 * Save User Data which is submitted by students page of mod_coversheet.
 *
 * @package    mod_coversheet
 * @copyright  2023, Brain Station-23 Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $CFG, $USER, $DB;
require_once('../../config.php');

$cmid = $_POST['cmid']?? '';
$attemptid = $_POST['attemptid']?? '';
$studentid = $_POST['studentid'] ?? '';
//$newcmid = $_POST['newcmid'];
//$fieldid = $_POST['fieldid'];
//$checkboxValue = $_POST['checkboxValue'];
//$datatype = $_POST['datatype'];
//$textareaValue = $_POST['textareaValue']?? '';
//$textareaId = $_POST['textareaId']?? '';
//$textareaDataType = $_POST['textareaDataType']?? '';

$checkboxDatas = $_POST['checkboxDataArray']?? [];
$textareaDatas = $_POST['textareaDataArray']?? [];
$textDatas = $_POST['textDataArray']?? [];
$radioDatas = $_POST['radioDataArray']?? [];
$notRequiredDataArray = $_POST['notRequiredDataArray']?? [];
$dropdownDatas = $_POST['dropdownDataArray']?? [];

if (!empty($textareaDatas)) {
    foreach ($textareaDatas as $textareaData) {
        if (!empty($textareaData['inputvalue'])) {
            $data = new stdClass();
            $data->cmid = $cmid;
            $data->fieldid = $textareaData['id'];
            $data->attempt = $attemptid;
            $data->student_id = $USER->id;
            $data->value = $textareaData['inputvalue'];
            $data->timecreated = time();

            $result = $DB->insert_record('coversheet_field_data', $data);
        }
    }
}
if (!empty($textDatas)) {
    foreach ($textDatas as $textData) {
        if (!empty($textData['inputvalue'])) {
            $data = new stdClass();
            $data->cmid = $cmid;
            $data->fieldid = $textData['id'];
            $data->attempt = $attemptid;
            $data->student_id = $USER->id;
            $data->value = $textData['inputvalue'];
            $data->timecreated = time();
            $result = $DB->insert_record('coversheet_field_data', $data);
        }
    }

}
if (!empty($radioDatas)) {
    foreach ($radioDatas as $radioData) {
        $data = new stdClass();
        $data->cmid = $cmid;
        $data->fieldid = $radioData['id'];
        $data->attempt = $attemptid;
        $data->student_id = $USER->id;
        $data->value = $radioData['value'];
        $data->timecreated = time();
        $result = $DB->insert_record('coversheet_field_data', $data);
    }

}
if (!empty($notRequiredDataArray)) {
    foreach ($notRequiredDataArray as $newradioData) {
        $data = new stdClass();
        $data->cmid = $cmid;
        $data->fieldid = $newradioData['id'];
        $data->attempt = $attemptid;
        $data->student_id = $USER->id;
        $data->value = $newradioData['value'];
        $data->timecreated = time();
        $result = $DB->insert_record('coversheet_field_data', $data);
    }

}

if(!empty($dropdownDatas)) {
    foreach ($dropdownDatas as $dropdownData){
        if (!empty($dropdownData['value'])) {
            $data = new stdClass();
            $data->cmid = $cmid;
            $data->fieldid = $dropdownData['id'];
            $data->attempt = $attemptid;
            $data->student_id = $USER->id;
            $data->value = $dropdownData['value'];
            $data->timecreated = time();
            $result = $DB->insert_record('coversheet_field_data', $data);
        }
    }
}

if (!empty($checkboxDatas)) {
    foreach ($checkboxDatas as $checkboxData) {
        $data = new stdClass();
        $data->cmid = $cmid;
        $data->fieldid = $checkboxData['id'];
        $data->attempt = $attemptid;
        $data->student_id = $USER->id;
        $data->value = $checkboxData['inputvalue'];
        $data->timecreated = time();

        $result = $DB->insert_record('coversheet_field_data', $data);
    }
}
