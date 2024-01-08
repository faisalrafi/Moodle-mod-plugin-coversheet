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
 * View Page of mod_coversheet.
 *
 * @package    mod_coversheet
 * @copyright  2023, Brain Station-23 Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
global $DB, $PAGE, $OUTPUT, $CFG, $USER;
require_once('../../config.php');
require_once('lib.php');
require_once($CFG->libdir . '/dmllib.php');
require_once($CFG->libdir . '/gradelib.php');

$id = required_param('id', PARAM_INT);    // Course Module ID.

$cm = get_coursemodule_from_id('coversheet', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$moduleinstance = $DB->get_record('coversheet', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

$PAGE->set_url('/mod/coversheet/view.php', array('id' => $cm->id));
$PAGE->set_title(get_string('viewtitle', 'coversheet'));
$PAGE->requires->css('/mod/coversheet/mod_coversheet_style.css');
$hasCapabilityViewPage = has_capability('mod/coversheet:viewpage', $context);

$licenseKey = get_config('coversheet', 'license_key');

//$date = $DB->get_field('coversheet_submissions' , 'submission_date', ['cmid' => $id]);
//$updated_date = userdate($date, get_string('strftimedate'));
//var_dump($updated_date); die();
if ($hasCapabilityViewPage) {
    echo $OUTPUT->header();

    $sql = "SELECT ca.student_id, u.firstname, u.lastname, ca.status
    FROM {coversheet_attempts} ca
    LEFT JOIN {user} u ON u.id = ca.student_id
    WHERE ca.cmid = :cmid AND ca.status = 1
    GROUP BY ca.student_id";
    $details = $DB->get_records_sql($sql, ['cmid' => $id]);
//    echo "<pre>";var_dump($details); die();

    $display = [
        'cmid' => $id,
        'details' => array_values($details),
        'webroot' => $CFG->wwwroot,
        'licenseKey' => $licenseKey,
        'courseid' => $course->id,
    ];
    echo $OUTPUT->render_from_template('mod_coversheet/view', $display);
} else {
    $attemptid = coversheet_populate_data_in_user_progress_track_table($id);
//    echo "<pre>"; var_dump($attemptid); die();
    if(empty($attemptid)){
        $attempt = coversheet_create_new_attempt(0, $id);
        $attemptid = new stdClass();
        $attemptid->attempt = $attempt;
        $attemptid->status = 0;
//        var_dump($attemptid->attempt); die();
    }

    $attemptnumber = $attemptid->attempt;
    $submissions = $moduleinstance->submissions;
    if ($submissions == 0) {
        $submissions = 99999;
    }

    if ($attemptid->status == 0 || (empty($attemptid) && $attemptid->feedback_submit != 0)){
        echo $OUTPUT->header();
        if (empty($attemptid)) {
            $attempt = coversheet_create_new_attempt(0, $id);
        }
        $sql = "SELECT * FROM {coversheet_contents} cc WHERE cc.cmid = :cmid";
        $contents = $DB->get_records_sql($sql, ['cmid' => $id]);
        $html = '';
        foreach ($contents as $content) {
            $html .= coversheet_load_content($content, $context, $id);
        }
        $info = $DB->get_record('user', ['id' => $USER->id]);
        $name = $info->firstname . ' ' . $info->lastname;
        $email = $info->email;
        $phone = $info->phone1;

        $sql = "SELECT ft.id, ft.name, ft.datatype, ft.param, ft.required
            FROM {coversheet_field_type} ft WHERE ft.cmid = '$id'";
        $info = $DB->get_records_sql($sql);
//    echo "<pre>";var_dump($info);die();
        foreach ($info as $field) {
            $field->isCheckbox = ($field->datatype === get_string('checkbox', 'coversheet'));
            $field->isTextarea = ($field->datatype === get_string('textarea', 'coversheet'));
            $field->isTextinput = ($field->datatype === get_string('text', 'coversheet'));
            $field->isRadio = ($field->datatype === get_string('radio', 'coversheet'));

            $field->isRequired = ($field->required == 1);

            if ($field->isRadio) {
                $field->radioOptions = explode(',', $field->param);
            }
            $field->isDropdown = ($field->datatype === get_string('dropdown', 'coversheet'));
            if ($field->isDropdown) {
                $field->dropdownList = explode(',', $field->param);
            }
        }

        $sql1 = "SELECT * FROM {coversheet_attempts} ca
             WHERE ca.cmid= '$id' AND ca.student_id = '$USER->id' AND ca.status = 1 AND ca.attempt = '$attemptid->attempt'";
        $results = $DB->get_records_sql($sql1);
//    echo "<pre>"; var_dump($results); die();

        $currentdate = date('d F Y');

        $display = [
            'submissions' => $submissions,
            'cmid' => $id,
            'attemptnumber' => $attemptnumber,
            'studentid' => $USER->id,
            'html' => $html,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'currentDate' => $currentdate,
            'webroot' => $CFG->wwwroot,
            'infos' => array_values($info),
            'results' => array_values($results),
        ];
        echo $OUTPUT->render_from_template('mod_coversheet/view_student', $display);
    } else {
        echo $OUTPUT->header();
        $url = new moodle_url('/mod/coversheet/startattempt.php');
        $attempts = $DB->get_record('coversheet_attempts', ['cmid' => $id, 'student_id' => $USER->id, 'attempt' => $attemptid->attempt]);
        $sname = $attempts->candidate_name;
        $sign = $attempts->candidate_sign;
//        echo "<pre>";var_dump($sname); die();

        $sql = "SELECT cfd.id, cfd.value, cfd.attempt, cft.name, cft.datatype FROM {coversheet_field_data} cfd
                LEFT JOIN {coversheet_field_type} cft ON cfd.fieldid = cft.id
                WHERE cfd.attempt = '$attemptid->attempt' AND cfd.student_id = '$USER->id' AND cfd.cmid = '$id'";
        $datas = $DB->get_records_sql($sql);
//        echo "<pre>";var_dump($datas); die();
        foreach ($datas as $data) {
            if ($data->datatype === 'checkbox') {
                $data->value = ($data->value == 1) ? 'Yes' : 'No';
            }
        }

        $enabled = 0;
        
        if ($attemptid->feedback_submit && ($attemptid->attempt < $submissions)){
            $enabled = 1;
        }
        if ($attemptid->attempt >= $submissions){
            $attempt_text = 'You reached your maximum submission limit';
        }
        else{
            $attempt_text = 'You can\'t reattempt now, please wait for teacher\'s feedback';
        }

        
        $instance = $DB->get_record('course_modules', array('id' => $id));

        $gradeitem = grade_item::fetch(array('itemtype' => 'mod',
                        'itemmodule' => 'coversheet',
                        'iteminstance' => $instance->instance,
                        'itemnumber' => 0,
                        'courseid' => $instance->course));
        $grade_record = $DB->get_record('grade_grades', array('itemid' => $gradeitem->id, 'userid' => $USER->id));

        $grade = '';

        if ($grade_record) {
            if ($grade_record->rawscaleid == NULL) {
                $grade = $grade_record->finalgrade;
            } else {
                $scales = grade_scale::fetch(array('id' => $gradeitem->scaleid))->load_items();
                $grade = $scales[$grade_record->finalgrade - 1];
            }
        }

        $hasgrade = true;
        if ($moduleinstance->grade == 0) {
            $hasgrade = false;
        }

        $comment = $DB->get_record('coversheet_feedbacks', array('cmid' => $id, 'student_id' => $USER->id, 'attempt_id' => $attemptid->attempt));
        if ($comment) {
            $comment = $comment->comment;
        } else {
            $comment = 'N/A';
        }

        $display = (object)[
            'formurl' => $url,
            'prev_attempt' => $attemptid->attempt,
            'cmid' => $id,
            'datas' => array_values($datas),
            'enable' => $enabled,
            'feedback_submit' => $attemptid->feedback_submit,
            'hasgrade' => $hasgrade,
            'grade' => $grade,
            'comment' => $comment,
            'studentid' => $USER->id,
            'webroot' => $CFG->wwwroot,
            'sname' => $sname,
            'sign' => $sign,
            'attempt_text' => $attempt_text,
            'viewAttempt_url' => new moodle_url('/mod/coversheet/studentpages/view_student.php'),
        ];
        echo $OUTPUT->render_from_template('mod_coversheet/reattempt', $display);
    }

}

echo $OUTPUT->footer();