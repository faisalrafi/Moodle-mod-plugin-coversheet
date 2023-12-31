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
 * Add submission page of mod_coversheet.
 *
 * @package    mod_coversheet
 * @copyright  2023, Brain Station-23 Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $DB, $PAGE, $OUTPUT, $CFG;
require(__DIR__ . '/../../../config.php');
require_once("../lib.php");
require_once("$CFG->libdir/formslib.php");
require_once($CFG->dirroot . '/mod/coversheet/classes/form/add_submission.php');

$cmid = required_param("id", PARAM_INT); // Course_module ID.

require_login();

$PAGE->set_url('/mod/coversheet/adminpages/add_submission.php', array('id' => $cmid));
$PAGE->set_context(context_system::instance());
$PAGE->set_title("Add Submission Details");

$form = new add_submission_form(new moodle_url('/mod/coversheet/adminpages/add_submission.php', array('id'=>$cmid)));

if ($form->is_cancelled()) {
    redirect(new moodle_url('/mod/coversheet/view.php', array('id' => $cmid)));
} elseif ($data = $form->get_data()) {
    $submission = new stdClass();
    $submission->cmid = $cmid;
    $submission->submission_date = $data->submission_date;
    $submission->candidate_name = '';
    $submission->competency = $data->competency;
    $submission->timecreated = time();
    $submission->timemodified = time();

    $DB->insert_record('coversheet_submissions', $submission);

    redirect(new moodle_url('/mod/coversheet/view.php', array('id' => $cmid)));

}

echo $OUTPUT->header();
$form->display();
echo $OUTPUT->footer();