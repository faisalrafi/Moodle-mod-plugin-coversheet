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
 * Plugin upgrade steps are defined here.
 *
 * @package    mod_coversheet
 * @copyright  2023, Brain Station-23 Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Execute mod_coversheet upgrade from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_coversheet_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2023122100) {
        $table = new xmldb_table('coversheet');
        $grade_field = new xmldb_field('grade', XMLDB_TYPE_INTEGER, '10', null, true, false, 0, null);

        // Conditionally launch add field forcedownload.
        if (!$dbman->field_exists($table, $grade_field)) {
            $dbman->add_field($table, $grade_field);
        }
    }

    if ($oldversion < 2023122101) {
        $table = new xmldb_table('coversheet');
        $grade_field = new xmldb_field('wantgrade', XMLDB_TYPE_INTEGER, '10', null, true, false, 0, null);

        // Conditionally launch add field forcedownload.
        if (!$dbman->field_exists($table, $grade_field)) {
            $dbman->add_field($table, $grade_field);
        }
    }

    if ($oldversion < 2023122103) {
        $table = new xmldb_table('coversheet_templates');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, true, true, null, null);
        $table->add_field('cmid', XMLDB_TYPE_INTEGER, '10', null, true, false, null, null);
        $table->add_field('title', XMLDB_TYPE_TEXT, '100', null, true, false, null, null);
        $table->add_field('template', XMLDB_TYPE_TEXT, '10000', null, true, false, null, null);
        $table->add_field('active', XMLDB_TYPE_INTEGER, '10', null, true, false, 0, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, true, false, 0, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, true, false, 0, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('cmid', XMLDB_KEY_FOREIGN, ['cmid'], 'coversheet', ['id']);

        // Conditionally launch create table for fees.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
    }

    return true;
}