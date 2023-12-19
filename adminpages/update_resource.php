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
 * update resource checkbox of mod_coversheet.
 *
 * @package    mod_coversheet
 * @copyright  2023, Brain Station-23 Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $CFG, $USER, $DB;
require_once('../../../config.php');
require_once($CFG->libdir . '/gradelib.php');

$resource = $_POST['resource'];
$isChecked = $_POST['isChecked'];
$studentid = $_POST['studentid'];

$data = new stdClass();
$data->status = $isChecked ? 1:0;
$data->studentid = $studentid;

$res = $DB->update_record('coversheet_requirements', $data, ['resource' => $resource]);

if($res) {
    echo 'Success';
}