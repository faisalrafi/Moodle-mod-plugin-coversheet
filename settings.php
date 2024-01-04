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
 * Coversheet plugin settings
 *
 * @package    mod_coversheet
 * @copyright  2023, BrainStation-23
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_heading('mod_coversheet/license', get_string('headerlicense', 'coversheet'),
        get_string('headerlicenseinfo', 'coversheet')));

    $settings->add(new admin_setting_configtext('coversheet/license_key',
        get_string('setting:license_key', 'coversheet'),
        get_string('setting:license_key_desc', 'coversheet'), '', PARAM_TEXT));

}