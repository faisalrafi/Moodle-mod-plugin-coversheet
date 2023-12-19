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

$cmid = $_POST['cmid'];
$fieldid = $_POST['fieldid'];
$checkboxValue = $_POST['checkboxValue'];
$datatype = $_POST['datatype'];
$textareaValue = $_POST['textareaValue'];
$textareaId = $_POST['textareaId'];
$textareaDataType = $_POST['textareaDataType'];

//$textId = $_POST['textId'];
//$inputvalue = $_POST['inputvalue'];
//$textDataType = $_POST['textDataType'];

$textDatas = $_POST['textDataArray'];
$radioDatas = $_POST['radioDataArray'];
$dropdownDatas = $_POST['dropdownDataArray'];

if ($textareaDataType) {
    $data = new stdClass();
    $data->cmid = $cmid;
    $data->fieldid = $textareaId;
    $data->student_id = $USER->id;
    $data->value = $textareaValue;
    $data->timecreated = time();

    $result = $DB->insert_record('coversheet_field_data', $data);
}
if ($textDatas) {
    foreach ($textDatas as $textData) {
        $data = new stdClass();
        $data->cmid = $cmid;
        $data->fieldid = $textData['id'];
        $data->student_id = $USER->id;
        $data->value = $textData['inputvalue'];
        $data->timecreated = time();
        $result = $DB->insert_record('coversheet_field_data', $data);
    }

}
if ($radioDatas) {
    foreach ($radioDatas as $radioData) {
        $data = new stdClass();
        $data->cmid = $cmid;
        $data->fieldid = $radioData['id'];
        $data->student_id = $USER->id;
        $data->value = $radioData['value'];
        $data->timecreated = time();
        $result = $DB->insert_record('coversheet_field_data', $data);
    }

}

if($dropdownDatas){
    foreach ($dropdownDatas as $dropdownData){
        $data = new stdClass();
        $data->cmid = $cmid;
        $data->fieldid = $dropdownData['id'];
        $data->student_id = $USER->id;
        $data->value = $dropdownData['value'];
        $data->timecreated = time();
        $result = $DB->insert_record('coversheet_field_data', $data);
    }
}

if ($datatype === 'checkbox'){
    $data = new stdClass();
    $data->cmid = $cmid;
    $data->fieldid = $fieldid;
    $data->student_id = $USER->id;
    $data->value = $checkboxValue;
    $data->timecreated = time();

    $result = $DB->insert_record('coversheet_field_data', $data);
}
