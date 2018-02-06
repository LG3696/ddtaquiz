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
 * Displays a page to edit a feedback block.
 *
 * @package    mod_adaptivequiz
 * @copyright  2017 Luca Gladiator <lucamarius.gladiator@stud.tu-darmstadt.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/mod/adaptivequiz/locallib.php');
require_once($CFG->dirroot . '/question/editlib.php');

$blockid = required_param('bid', PARAM_INT);
$save = optional_param('save', 0, PARAM_INT);

list($thispageurl, $contexts, $cmid, $cm, $quiz, $pagevars) =
    question_edit_setup('editq', '/mod/adaptivequiz/editfeedback.php', true);

require_capability('mod/adaptivequiz:manage', $contexts->lowest());

$adaptivequiz = adaptivequiz::load($quiz->id);
$feedbackblock = feedback_block::load($blockid, $adaptivequiz);

$thispageurl->param('bid', $blockid);  // hier vielleicht auch feedbackblick id?

$PAGE->set_url($thispageurl);

$adaptivequiz = adaptivequiz::load($quiz->id);

if (optional_param('save', 0, PARAM_INT)) {
    $name = required_param('blockname', PARAM_TEXT);
    $feedbackblock->set_name($name);

    if (optional_param('done', 0, PARAM_INT)) {
    $nexturl = new moodle_url('/mod/adaptivequiz/edit.php', array('cmid' => $cmid));
    redirect($nexturl);
    }
}

$PAGE->set_pagelayout('incourse');
$PAGE->set_title(get_string('editingfeedback', 'adaptivequiz'));

$output = $PAGE->get_renderer('mod_adaptivequiz', 'edit');

echo $OUTPUT->header();

echo $output->edit_feedback_page($feedbackblock, $thispageurl, $pagevars);

echo $OUTPUT->footer();