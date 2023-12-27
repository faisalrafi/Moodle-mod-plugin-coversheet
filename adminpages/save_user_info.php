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
 * Save User Information Page of mod_coversheet.
 *
 * @package    mod_coversheet
 * @copyright  2023, Brain Station-23 Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $CFG, $USER, $DB;
require_once('../../../config.php');

//$cmid = $_POST['cmid'];
$cmid = $_POST['cmid'] ?? '';
$shortname = $_POST['shortname']?? '';
$name = $_POST['name']?? '';
$requireCheckbox = $_POST['requireCheckbox']?? '';
$requireRadio = $_POST['requireRadio']?? '';
$requireTextarea = $_POST['requireTextarea']?? '';
$requireText = $_POST['requireText']?? '';
$requireDropdown = $_POST['requireDropdown']?? '';

$options = $_POST['radioOptions']?? '';
$dropdownList = $_POST['dropdownList']?? '';
$action = $_POST['action']?? '';

$existingShortname = $DB->get_records_sql(
    "SELECT * FROM {coversheet_field_type} 
    WHERE cmid = :cmid AND shortname = :shortname",
    array('cmid' => $cmid, 'shortname' => $shortname)
);
if ($existingShortname){
    echo 'duplicate';
}
else {
    // Set 'datatype' based on the 'action' parameter
    switch ($action) {
        case 'checkbox':
            $data = new stdClass();
            $data->cmid = $cmid;
            $data->name = $name;
            $data->shortname = $shortname;
            $data->datatype = 'checkbox';
            $data->param = '';
            $data->required = $requireCheckbox;
            $data->timecreated = time();
            $result = $DB->insert_record('coversheet_field_type', $data);
            break;
        case 'dropdown':
            $data = new stdClass();
            $data->cmid = $cmid;
            $data->name = $name;
            $data->shortname = $shortname;
            $data->datatype = 'dropdown';
            $optionsString = implode(',', $dropdownList);
            $data->param = $optionsString;
            $data->required = $requireDropdown;
            $data->timecreated = time();
            $result = $DB->insert_record('coversheet_field_type', $data);
            break;
        case 'radio':
            $data = new stdClass();
            $data->cmid = $cmid;
            $data->name = $name;
            $data->shortname = $shortname;
            $data->datatype = 'radio';
            $optionsString = implode(',', $options);
            $data->param = $optionsString;
            $data->required = $requireRadio;
            $data->timecreated = time();
            $result = $DB->insert_record('coversheet_field_type', $data);
            break;
        case 'textarea':
            $data = new stdClass();
            $data->cmid = $cmid;
            $data->name = $name;
            $data->shortname = $shortname;
            $data->datatype = 'textarea';
            $data->param = '';
            $data->required = $requireTextarea;
            $data->timecreated = time();
            $result = $DB->insert_record('coversheet_field_type', $data);
            break;
        case 'text':
            $data = new stdClass();
            $data->cmid = $cmid;
            $data->name = $name;
            $data->shortname = $shortname;
            $data->datatype = 'text';
            $data->param = '';
            $data->required = $requireText;
            $data->timecreated = time();
            $result = $DB->insert_record('coversheet_field_type', $data);
            break;
        default:
            // Handle other cases or set a default value
            $data = new stdClass();
            $data->cmid = '';
            $data->name = '';
            $data->shortname = '';
            $data->datatype = '';
            $data->param = '';
            $data->required = '';
            $data->timecreated = time();
            $result = $DB->insert_record('coversheet_field_type', $data);
    }

//    $result = $DB->insert_record('coversheet_field_type', $data);

    if ($result) {
        echo 'success';
    } else {
        echo 'error';
    }
}

