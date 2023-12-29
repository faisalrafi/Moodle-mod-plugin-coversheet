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
 * User Information of mod_coversheet.
 *
 * @package    mod_coversheet
 * @copyright  2023, Brain Station-23 Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $DB, $PAGE, $OUTPUT, $CFG;
require_once('../../../config.php');
require_once('../lib.php');

$cmid = required_param('id', PARAM_INT);

require_login();
$context = context_module::instance($cmid);
$PAGE->set_url('/mod/coversheet/adminpages/user_information.php', array('id' => $cmid));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('userinfotitle', 'coversheet'));
$PAGE->requires->css('/mod/coversheet/mod_coversheet_style.css');

echo $OUTPUT->header();

$details = $DB->get_records('coversheet_field_type', ['cmid' => $cmid]);
//var_dump($details); die();
$display = [
    'cmid' => $cmid,
    'details' => array_values($details),
    'webroot' => $CFG->wwwroot
];

echo $OUTPUT->render_from_template('mod_coversheet/user_information', $display);
echo $OUTPUT->footer();