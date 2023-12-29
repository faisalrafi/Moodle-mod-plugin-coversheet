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
 * Preview Templates of mod_coversheet.
 *
 * @package    mod_coversheet
 * @copyright  2023, Brain Station-23 Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../../config.php');

global $OUTPUT, $DB, $PAGE, $CFG;

$id = required_param("id", PARAM_INT);// Course_module ID, or.
$studentid = required_param("studentid", PARAM_INT); // Student ID
$templateid = required_param("templateid", PARAM_INT); // Template ID

$cm = get_coursemodule_from_id('coversheet', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

require_login($course, true, $cm);

$context = context_module::instance($cm->id);
$PAGE->set_url('/mod/coversheet/adminpages/template_report.php', array('id' => $id, 'studentid' => $studentid, 'templateid' => $templateid));
$PAGE->set_title("Report");
$PAGE->requires->css('/mod/coversheet/mod_coversheet_style.css');

echo $OUTPUT->header();

$template = $DB->get_record('coversheet_templates', array('id' => $templateid));

$user = $DB->get_record('user', array('id' => $studentid));

$query = "SELECT cft.shortname, cfd.value, cft.datatype FROM {coversheet_field_type} cft
          LEFT JOIN {coversheet_field_data} cfd ON cft.id = cfd.fieldid
          WHERE cft.cmid = :cmid AND cfd.student_id = :studentid";
$custom_values = $DB->get_records_sql($query, ['cmid' => $id, 'studentid' => $studentid]);

foreach ($custom_values as $custom_value) {
    $key = $custom_value->shortname;
    $user->$key = $custom_value->value;
}

$feedback_query = "SELECT assessor_name, assessor_sign FROM {coversheet_feedbacks} cf 
                   WHERE cf.cmid= '$id' AND cf.student_id = '$studentid'";
$feedback = $DB->get_record_sql($feedback_query);

$user->teacher_name = $feedback->assessor_name;
$user->teacher_signature = $feedback->assessor_sign;

$sql = "SELECT * FROM {coversheet_attempts} ca
             WHERE ca.cmid = :cmid AND ca.student_id = :studentid";
$details = $DB->get_record_sql($sql, ['cmid' => $id, 'studentid' => $studentid]);

$user->student_name = $details->candidate_name;
$user->student_signature = $details->candidate_sign;

$inputString = $template->template;

$pattern = "/\[(.*?)\]/";

preg_match_all($pattern, $inputString, $matches);

$extractedValues = $matches[1];

foreach ($extractedValues as $index => $value) {
    if ($value == "student_signature" || $value == "teacher_signature") {
        $replace_value = '<img src="' . $user->$value . '" alt="signature" height="200px" width="500px" class="conclusion-image">';
        $inputString = str_replace("[" . $value . "]", $replace_value, $inputString);
    } else {
        $inputString = str_replace("[" . $value . "]", $user->$value, $inputString);
    }
}

$query = "SELECT cc.id as contentid, cc.html FROM {coversheet_contents} cc 
          WHERE cc.cmid = :cmid";
$contents = $DB->get_records_sql($query, ['cmid' => $id]);

foreach ($contents as $content) {
    $html = coversheet_prepare_html_data_for_view($content, $context);
}

$resource_query = "SELECT * FROM {coversheet_requirements} WHERE cmid = '$id'";
$resources = $DB->get_records_sql($resource_query);

$display = [
    'cmid' => $id,
    'templateid' => $template->id,
    'webroot' => $CFG->wwwroot
];
echo $OUTPUT->render_from_template('mod_coversheet/template_report', $display);
echo "<div id='printableArea'>";
echo $inputString;
echo "</div>";
echo '<button class="btn btn-primary mr-2" onclick="printdiv(\'printableArea\')">Print this page</button>';
echo '<a href="'. $CFG->wwwroot .'/mod/coversheet/adminpages/view_template.php?id=' . $id .'" class="btn btn-danger">Back</a>';
echo "<script>
function printdiv(elem) {
  var header_str = '<html><head><title>' + document.title  + '</title></head><body>';
  var footer_str = '</body></html>';
  var new_str = document.getElementById(elem).innerHTML;
  var old_str = document.body.innerHTML;
  document.body.innerHTML = header_str + new_str + footer_str;
  window.print();
  document.body.innerHTML = old_str;
  return false;
}
</script>";
echo $OUTPUT->footer();