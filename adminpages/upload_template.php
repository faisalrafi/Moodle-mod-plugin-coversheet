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
 * Templates of mod_coversheet.
 *
 * @package    mod_coversheet
 * @copyright  2023, Brain Station-23 Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../../config.php');

global $OUTPUT, $DB, $PAGE, $CFG;

$id = required_param("id", PARAM_INT);// Course_module ID, or.
$cm = get_coursemodule_from_id('coversheet', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

require_login($course, true, $cm);

$context = context_module::instance($cm->id);
$PAGE->set_url('/mod/coversheet/adminpages/upload_template.php', array('id' => $id));
$PAGE->set_title(get_string('uploadtemplatetitle', 'coversheet'));
$PAGE->requires->css('/mod/coversheet/mod_coversheet_style.css');

echo $OUTPUT->header();

$sql = "SELECT ct.id as templateid, ct.title, ct.active FROM {coversheet_templates} ct WHERE ct.cmid ='$id'";
$templates = $DB->get_records_sql($sql);

$display = [
    'templates' => array_values($templates),
    'cmid' => $id,
    'webroot' => $CFG->wwwroot
];
echo $OUTPUT->render_from_template('mod_coversheet/template_list', $display);
echo $OUTPUT->footer();