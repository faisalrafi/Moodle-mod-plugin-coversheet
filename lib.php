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
    if (!isset($data->wantgrade)) {
        $data->wantgrade = 0;
    } 
    $data->id = $DB->insert_record('coversheet', $data);

    $data->instance = $data->id;

    coversheet_grade_item_update($data);

    return $data->id;
}

function coversheet_update_instance(stdClass $data, mod_coversheet_mod_form $mform = null): int {
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
    $table->grade = $data->grade;    
    $table->timecreated = time();
    $table->timemodified = time();
    $table->id = $DB->update_record('coversheet', $table);

    coversheet_grade_item_update($data);

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
        $content = file_postupdate_standard_editor($content, 'html', coversheet_editor_options($context), $context, 'mod_coversheet', 'html_editor', $content->id);
    }

    $DB->update_record('coversheet_contents', $content);
    return $content->id;

}

function coversheet_insert_template($data, $context, $id)
{
    global $DB;

    $template = new stdClass();
    $template->cmid = $id;
    $template->title = $data->title;
    $template->template = "";
    if (!isset($data->active)) {
        $template->active = 0;
    } else {
        $template->active = $data->active;
    }
    $template->timecreated = time();
    $template->timemodified = time();
    $template->id = $DB->insert_record('coversheet_templates', $template);

    if ($template->active == 1) {
        make_inactive_template($template->id);
    }

    if (!empty($data->template_editor)) {
        $template->template_editor = $data->template_editor;
        $template = file_postupdate_standard_editor($template, 'template', coversheet_editor_options($context), $context, 'mod_coversheet', 'template_editor', $template->id);
    }

    $DB->update_record('coversheet_templates', $template);
    return $template->id;
}

function coversheet_editor_options($context)
{
    return array("subdirs" => true, "maxfiles" => -1, "maxbytes" => 0, "context" => $context);
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

function coversheet_get_html_data($table_name, $html_id, $is_array = false)
{

    global $DB;
    $html = $DB->get_record($table_name, ["id" => $html_id]);

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

/**
 * Update grades in central gradebook
 *
 * @param object $coversheet
 * @param int $userid specific user only, 0 means all
 * @param bool $nullifnone
 * @category grade
 */
function coversheet_update_grades($coversheet, $userid = 0, $nullifnone = true)
{
    global $CFG, $DB;
    require_once($CFG->libdir . '/gradelib.php');

    if ($coversheet->grade == 0 || $coversheet->practice) {
        coversheet_grade_item_update($coversheet);

    } else if ($grades = coversheet_get_user_grades($coversheet, $userid)) {
        coversheet_grade_item_update($coversheet, $grades);

    } else if ($userid and $nullifnone) {
        $grade = new stdClass();
        $grade->userid = $userid;
        $grade->rawgrade = null;
        coversheet_grade_item_update($coversheet, $grade);

    } else {
        coversheet_grade_item_update($coversheet);
    }
}

/**
 * Create grade item for given coversheet.
 *
 * @param stdClass $coversheet record with extra cmidnumber
 * @param array $grades optional array/object of grade(s); 'reset' means reset grades in gradebook
 * @return int 0 if ok, error code otherwise
 */
function coversheet_grade_item_update($coversheet, $grades = null)
{
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');

    $params = array('itemname' => $coversheet->name, 'idnumber' => $coversheet->cmidnumber);

    // Check if feedback plugin for gradebook is enabled, if yes then
    // gradetype = GRADE_TYPE_TEXT else GRADE_TYPE_NONE.
    $gradefeedbackenabled = false;

    if (isset($coversheet->gradefeedbackenabled)) {
        $gradefeedbackenabled = $coversheet->gradefeedbackenabled;
    } 

    if ($coversheet->grade > 0) {
        $params['gradetype'] = GRADE_TYPE_VALUE;
        $params['grademax'] = $coversheet->grade;
        $params['grademin'] = 0;

    } else if ($coversheet->grade < 0) {
        $params['gradetype'] = GRADE_TYPE_SCALE;
        $params['scaleid'] = -$coversheet->grade;

    } else if ($gradefeedbackenabled) {
        // $coversheet->grade == 0 and feedback enabled.
        $params['gradetype'] = GRADE_TYPE_TEXT;
    } else {
        // $coversheet->grade == 0 and no feedback enabled.
        $params['gradetype'] = GRADE_TYPE_NONE;
    }

    if ($grades === 'reset') {
        $params['reset'] = true;
        $grades = null;
    }

    return grade_update('mod/coversheet',
        $coversheet->course,
        'mod',
        'coversheet',
        $coversheet->instance,
        0,
        $grades,
        $params);
}

/**
 * Return grade for given user or all users.
 *
 * @param int $quizid id of quiz
 * @param int $userid optional user id, 0 means all users
 * @return array array of grades, false if none. These are raw grades. They should
 * be processed with quiz_format_grade for display.
 */
function coversheet_get_user_grades($coversheet, $userid = 0)
{
    // Add your custom grade item
    $item = new grade_item(array('itemtype' => 'mod', 'itemmodule' => 'pluginname', 'iteminstance' => 1, 'itemnumber' => 0));
    $item->set_source('mod_coversheet');
    $item->set_itemname('mod_coversheet');
    $items[] = $item;

    return $items;
}

function make_inactive_template($id) {
    global $DB;
    $sql = "UPDATE `mdl_coversheet_templates` 
            SET `active` = '0' 
            WHERE {coversheet_templates}.id != :id";
    $params['id'] = $id;
    $DB->execute($sql, $params);
}

function get_short_names($cmid) {
    global $DB;
    $short_names = array(
        'firstname',
        'lastname', 
        'email',
        'phone1',
        'phone2',
        'institution',
        'department',
        'address',
        'city',
        'country', 
        'student_name',
        'student_signature',
        'teacher_name',
        'teacher_signature'
        );
    $db_short_names = $DB->get_records('coversheet_field_type', array('cmid' => $cmid));

    foreach($db_short_names as $db_short_name) {
        $short_names[] = $db_short_name->shortname;
    }
    return $short_names;
}