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
 * Coversheet License Key Error Page.
 *
 * @copyright 2023 Brain Station 23
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */

require_once(__DIR__ . '/../../config.php');

$url = '/mod/coversheet/license_key_error.php';

$PAGE->set_url(new moodle_url($url));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title(get_string('license_key_error_title','coversheet'));
$PAGE->set_heading(get_string('license_key_error_title','coversheet'));

$courseid = required_param('courseid', PARAM_INT);
$domainname = required_param('domain_name', PARAM_TEXT);
$cmid = required_param('cmid', PARAM_INT);

echo $OUTPUT->header();

$templatecontext = (object) [
    'domain_name' => $domainname,
    'courseid' => $courseid,
    'course_url' => $CFG->wwwroot . '/course/view.php?id=' . $courseid,
    'admin_setting_url' => $CFG->wwwroot . '/admin/settings.php?section=modsettingcoversheet',
    'quizsettings_url' => $CFG->wwwroot . '/course/modedit.php?update='. $cmid . '&return=1',
    'coversheet_purchase_url' => 'https://elearning23.com/',
];

echo $OUTPUT->render_from_template('mod_coversheet/license_key_error', $templatecontext);

echo $OUTPUT->footer();