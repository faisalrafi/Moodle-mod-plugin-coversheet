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
 * lib file of mod_coversheet.
 *
 * @package    mod_coversheet
 * @copyright  2023, Brain Station-23 Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function coversheet_add_instance(stdClass $data, mod_coversheet_mod_form $mform = null): int
{
    global $DB;

    $data->timecreated = time();
    $data->timemodified = $data->timecreated;
    $data->id = $DB->insert_record('coversheet', $data);

    return $data->id;
}

function coversheet_update_instance(stdClass $data, mod_schedule_mod_form $mform = null): int {
    global $DB;
    $cmid = required_param('update', PARAM_INT);

    $module = $DB->get_record('modules', ['name' => 'coversheet']);
    $instance = $DB->get_record('course_modules', ['module' => $module->id, 'id' => $cmid]);
    $instanceid = $instance->instance;

    $table = new stdClass();
    $table->id = $instanceid;
    $table->name = $data->name;
    $table->intro = $data->intro;
    $table->introformat = $data->introformat;
    $table->timecreated = time();
    $table->timemodified = time();
    $table->id = $DB->update_record('coversheet', $table);

    return $table->id;

}

function coversheet_delete_instance($id) {
    global $DB;

    $cmid = required_param('id', PARAM_INT);

    $module = $DB->get_record('modules', ['name' => 'coversheet']);
    $instance = $DB->get_record('course_modules', ['module' => $module->id, 'id' => $cmid]);
    $instanceid = $instance->instance;

    $DB->delete_records('coversheet', ['id' => $instanceid]);

    return true;
}

function coversheet_insert_content($data, $context, $id)
{
    global $DB;
    $content = new stdClass();
    $content->cmid = $id;
    $content->html = "";
    $content->timecreated = time();
    $content->timemodified = time();
    $content->id = $DB->insert_record('coversheet_contents', $content);

    if (!empty($data->html_editor)) {
        $content->html_editor = $data->html_editor;
        $content = file_postupdate_standard_editor($content, 'html', coversheet_editor_options(), $context, 'mod_coversheet', 'html_editor', $content->id);
    }

    $DB->update_record('coversheet_contents', $content);
    return $content->id;

}

function coversheet_editor_options()
{
    return array("subdirs" => true, "maxfiles" => -1, "maxbytes" => 0);
}

function coversheet_extend_settings_navigation(settings_navigation $settings, navigation_node $coversheetnode) {
    global $USER;

    $roles = get_user_roles($settings->get_page()->cm->context, $USER->id);
    $userrole = array_values($roles)[0]->shortname;

    if ($userrole === 'editingteacher' || is_siteadmin()) {
        if ($settings->get_page()->cm->context) {
            $coversheetnode->add(
                get_string('contentslist', 'coversheet'),
                new moodle_url('/mod/coversheet/adminpages/content_list.php', ['id' => $settings->get_page()->cm->id])
            );
        }
        if ($settings->get_page()->cm->context) {
            $coversheetnode->add(
                get_string('userinfo', 'coversheet'),
                new moodle_url('/mod/coversheet/adminpages/user_information.php', ['id' => $settings->get_page()->cm->id])
            );
        }
        if ($settings->get_page()->cm->context) {
            $coversheetnode->add(
                get_string('resourceslist', 'coversheet'),
                new moodle_url('/mod/coversheet/adminpages/resource_list.php', ['id' => $settings->get_page()->cm->id])
            );
        }
        if ($settings->get_page()->cm->context) {
            $coversheetnode->add(
                get_string('uploadtemplate', 'coversheet'),
                new moodle_url('/mod/coversheet/adminpages/upload_template.php', ['id' => $settings->get_page()->cm->id])
            );
        }
    }
//    else if ($userrole === get_string('student', 'schedule')) {
//        if ($settings->get_page()->cm->context) {
//            $reportsnode = $coversheetnode->add(
//                get_string('myslot', 'schedule'),
//                new moodle_url('/mod/schedule/student_booked.php', ['id' => $settings->get_page()->cm->id])
//            );
//        }
//    }
}

function coversheet_prepare_html_data_for_view($data, $context)
{
    $data->id = !empty($data->id) ? $data->id : $data->contentid;
    if (is_array($data)) {
        $data['html'] = file_rewrite_pluginfile_urls($data['html'], 'pluginfile.php', $context->id, 'mod_coversheet', 'html_editor', $data['id']);
        return $data;
    }
    $data->html = file_rewrite_pluginfile_urls($data->html, 'pluginfile.php', $context->id, 'mod_coversheet', 'html_editor', $data->id);

    return $data;
}

function mod_coversheet_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array())
{
    if (in_array($filearea, ["html_editor"])) {
        $itemid = $args[0];
        $filename = array_pop($args);
        $filepath = '/';
        $fs = get_file_storage();
        $file = $fs->get_file($context->id, 'mod_coversheet', $filearea, $itemid, $filepath, $filename);
        if (!$file) {
            return false;
        }
        send_stored_file($file, 0, 0, $forcedownload, $options);
    } else {
        return false;
    }
}

function coversheet_load_content($content, $context, $cmid)
{
    global $DB;
    $html = '';
    $htmlValue = [];
    $htmlValue = $DB->get_record('coversheet_contents', ['id' => $content->id]);
    $content = coversheet_prepare_html_data_for_view($htmlValue, $context);
//    $html .= '<div style="margin-bottom: 10px">';
    $html .= $htmlValue->html;
//    $html .= '</div>';
    return $html;
}

function coversheet_get_html_data($html_id, $is_array = false)
{

    global $DB;
    $html = $DB->get_record("coversheet_contents", ["id" => $html_id]);

    if ($is_array && $html)
        return (array)$html;
    return $html;
}

function coversheet_profile_list_datatypes() {
    $datatypes = array();

    $plugins = core_component::get_plugin_list('profilefield');
    foreach ($plugins as $type => $unused) {
        $datatypes[$type] = get_string('pluginname', 'profilefield_'.$type);
    }
    asort($datatypes);

    return $datatypes;
}

