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
 * Defines the renderer for the quiz module.
 *
 * @package    mod_adaptivequiz
 * @copyright  2017 Jana Vatter <jana.vatter@stud.tu-darmstadt.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/mod/adaptivequiz/locallib.php');


/**
 * The renderer for the quiz module.
 *
 * @copyright  2017 Jana Vatter <jana.vatter@stud.tu-darmstadt.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_adaptivequiz_renderer extends plugin_renderer_base {
    /**
     * Generates the view page.
     *
     * @param array $quiz Array containing quiz data.
     * @param mod_quiz_view_object $viewobj the information required to display the view page.
     */
    public function view_page($quiz, $viewobj) {
        $output = '';
        $output .= $this->heading($quiz->name);
        $output .= $this->view_page_buttons($viewobj);
        return $output;
    }

    /**
     * Work out, and render, whatever buttons, and surrounding info, should appear.
     * at the end of the review page.
     *
     * @param mod_quiz_view_object $viewobj the information required to display the view page.
     * @return string HTML to output.
     */
    public function view_page_buttons($viewobj) {
        global $CFG;
        $output = '';
        $url = new \moodle_url('/mod/adaptivequiz/startattempt.php', array('cmid' => $viewobj->cmid));
        if ($viewobj->buttontext) {
            if ($viewobj->unfinishedattempt) {
                $attempturl = new moodle_url('/mod/adaptivequiz/attempt.php', array('attempt' => $viewobj->unfinishedattempt));
                $output .= $this->start_attempt_button($viewobj->buttontext, $attempturl);
            }
            else {
                $output .= $this->start_attempt_button($viewobj->buttontext, $url);
            }
            if ($viewobj->canmanage) {
                $output .= $this->edit_quiz_button($viewobj);
            }
        }
        if (!$viewobj->buttontext) {
            $output .= 'Quiz has no questions.';
            $output .= $this->edit_quiz_button($viewobj);
        }
        return $output;
    }

    /**
     * Generates the view attempt button.
     *
     * @param string $buttontext the label to display on the button.
     * @param moodle_url $url The URL to POST to in order to start the attempt.
     * @return string HTML fragment.
     */
    public function start_attempt_button($buttontext, $url) {
        $button = new single_button($url, $buttontext);
        $button->class .= ' quizstartbuttondiv';
        return $this->render($button);
    }

    /**
     * Generates the edit quiz button.
     *
     * @param mod_adaptivequiz_view_object $viewobj the information required to display the view page.
     * @return string HTML fragment.
     */
    public function edit_quiz_button($viewobj) {
        $url = new \moodle_url('/mod/adaptivequiz/edit.php', array('cmid' => $viewobj->cmid));
        $buttontext = get_string('editquiz', 'adaptivequiz');
        $button = new single_button($url, $buttontext);
        $button->class .= ' quizstartbuttondiv';
        return $this->render($button);
    }

    /**
     * Generates the page of the attempt.
     *
     * @param attempt $attempt the attempt.
     * @param int $slot the current slot.
     * @param question_display_options $options options that control how a question is displayed.
     * @param int $cmid the course module id.
     * @return string HTML fragment.
     *
     */
    public function attempt_page(attempt $attempt, $slot, $options, $cmid) {
        $output = '';

        $processurl = new \moodle_url('/mod/adaptivequiz/processslot.php');

        $output .= html_writer::start_tag('form',
           array('action' => $processurl, 'method' => 'post',
               'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8',
               'id' => 'responseform'));
        $output .= html_writer::start_tag('div');

        $output .= $attempt->get_quba()->render_question($slot, $options);

        $output .= $this->attempt_navigation_buttons();

        // Some hidden fields to track what is going on.
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'attempt',
           'value' => $attempt->get_id()));
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'slot',
           'value' => $slot));
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'cmid',
           'value' => $cmid));

        $output .= html_writer::end_tag('div');
        $output .= html_writer::end_tag('form');

        return $output;
    }

    /**
     * Generates the attempt navigation buttons.
     *
     * @return string HTML fragment.
     */
    public function attempt_navigation_buttons() {
        $output = '';

        $output .= html_writer::start_tag('div');

        /*if ($islast) {
            $nextlabel = get_string('endtest', 'adaptivequiz');
        } else {*/
            $nextlabel = get_string('nextpage', 'adaptivequiz');
        // }
        $output .= html_writer::empty_tag('input', array('type' => 'submit', 'name' => 'next',
            'value' => $nextlabel));
        $output .= html_writer::end_tag('div');

        return $output;
    }
    
    /**
     * Builds the review page.
     *
     * @param attempt $attempt the attempt this review belongs to.
     * @param question_display_options $options the display options.
     * @param array $summarydata contains all table data
     * @return $output containing HTML data.
     */
    public function review_page(attempt $attempt, $options, $summarydata) {
        $output = '';
        $output .= $this->heading(get_string('quizfinished', 'adaptivequiz'));
        $output .= $this->review_summary_table($summarydata);
        $output .= $this->review_block($attempt->get_quiz()->get_main_block(), $attempt, $options);
        $output .= $this->finish_review_button($attempt->get_quiz()->get_cmid());
        
        return $output;
    }
    
    /**
     * Outputs the table containing data from summary data array
     *
     * @param array $summarydata contains row data for table
     * @return $output containing HTML data.
     */
    public function review_summary_table($summarydata) {
        if (empty($summarydata)) {
            return '';
        }
        
        $output = '';
        $output .= html_writer::start_tag('table', array(
            'class' => 'generaltable generalbox quizreviewsummary'));
        $output .= html_writer::start_tag('tbody');
        foreach ($summarydata as $rowdata) {
            if ($rowdata['title'] instanceof renderable) {
                $title = $this->render($rowdata['title']);
            } else {
                $title = $rowdata['title'];
            }
            
            if ($rowdata['content'] instanceof renderable) {
                $content = $this->render($rowdata['content']);
            } else {
                $content = $rowdata['content'];
            }
            
            $output .= html_writer::tag('tr',
                html_writer::tag('th', $title, array('class' => 'cell', 'scope' => 'row')) .
                html_writer::tag('td', $content, array('class' => 'cell'))
                );
        }
        
        $output .= html_writer::end_tag('tbody');
        $output .= html_writer::end_tag('table');
        return $output;
    }
    
    /**
     * Renders the feedback for a block.
     * 
     * @param block $block the block to generate the feedback for.
     * @param attempt $attempt the attempt this review belongs to.
     * @param question_display_options $options the display options.
     * @return string HTML to output.
     */    
    protected function review_block(block $block, attempt $attempt, $options) {
        $output = '';
        foreach ($block->get_children() as $child) {
            if ($child->is_block()) {
                $childblock = $child->get_element();
                $condition = $childblock->get_condition();
                if ($condition->is_fullfilled($attempt)) {
                    $output .= $this->review_block($childblock, $attempt, $options);
                }
            } else if ($child->is_question()) {
                $slot = $block->get_slot_for_element($child->get_id());
                $output .= $attempt->get_quba()->render_question($slot, $options);
            }
        }
        return $output;
    }
    
    /**
     * Generates the finish review button.
     * 
     * @param int $cmid the course module id.
     * @return string HTML fragment.
     */
    public function finish_review_button($cmid) {
        $url = new moodle_url('/mod/adaptivequiz/view.php', array('id' => $cmid));
        $buttontext = get_string('finishreview', 'adaptivequiz');
        $button = new single_button($url, $buttontext);
        return $this->render($button);
    }
}

/**
 * Collects data for display by view.php.
 *
 * @copyright  2017 Jana Vatter <jana.vatter@stud.tu-darmstadt.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_adaptivequiz_view_object {
    /** @var bool $quizhasquestions whether the quiz has any questions. */
    public $quizhasquestions;
    /** @var string $buttontext caption for the start attempt button. If this is null, show no
     *      button, or if it is '' show a back to the course button. */
    public $buttontext;
    /** @var bool $unfinished contains 1 if an attempt is unfinished. */
    public $unfinished;
    /** @var array $preventmessages of messages telling the user why they can't
     *       attempt the quiz now. */
    public $preventmessages;
    /** @var int $numattempts contains the total number of attempts. */
    public $numattempts;
    /** @var object $lastfinishedattempt the last attempt from the attempts array. */
    public $lastfinishedattempt;
    /** @var quiz_access_manager $accessmanager contains various access rules. */
    public $accessmanager;
    /** @var int $cmid the course module id. */
    public $cmid;
    /** @var bool $canmanage whether the user is authorized to manage the quiz. */
    public $canmanage;
    /** @var int $unfinishedattempt the id of the unfinished attempt. */
    public $unfinishedattempt;
    /** @var array $attempts contains all the user's attempts at this quiz. */
    public $attempts;
}
