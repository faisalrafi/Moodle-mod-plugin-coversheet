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
 * review page for student of mod_coversheet.
 *
 * @package    mod_coversheet
 * @copyright  2023, Brain Station-23 Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $DB, $PAGE, $OUTPUT, $CFG;
require(__DIR__ . '/../../../config.php');
require_once("../lib.php");
require_once("$CFG->libdir/formslib.php");

$id = required_param("id", PARAM_INT); // Course_module ID.
$studentid = required_param('studentid', PARAM_INT); // Student ID.
$attempt = required_param('attempt', PARAM_INT); // Student ID.
//var_dump($attempt); die();

require_login();
$context = context_module::instance($id);
$PAGE->set_url('/mod/coversheet/studentpages/view_student.php', array('id' => $id));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('review', 'coversheet'));
$PAGE->requires->css('/mod/coversheet/mod_coversheet_style.css');