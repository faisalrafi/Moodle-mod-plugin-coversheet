<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This script deals with starting a new attempt at a coversheet.
 *
 *
 * @package   mod_coversheet
 * @copyright 2023, Brain Station-23 Ltd.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
global $CFG, $OUTPUT, $PAGE, $DB;

require_once(__DIR__ . '/../../config.php');
require_once('lib.php');

// Get submitted parameters.
$id = required_param('id', PARAM_INT); // Course module id
$prev_attempt = optional_param('prev_attempt', 0, PARAM_INT); // Used to force a new preview
//var_dump($prev_attempt); die();

$cm = get_coursemodule_from_id('coversheet', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$moduleinstance = $DB->get_record('coversheet', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

$PAGE->set_url('/mod/coversheet/startattempt.php', array('id' => $cm->id));
$PAGE->set_title("Coversheet");
echo $OUTPUT->header();
$attempt = coversheet_create_new_attempt($prev_attempt, $id);
//var_dump($attempt); die();

$params = array('id' => $id);
$url = new moodle_url('/mod/coversheet/view.php', $params);
redirect($url);
echo $OUTPUT->footer();