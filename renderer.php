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
     * Generates the view page
     *
     * @param array $quiz Array containing quiz data
     * @param int $context The page context ID
     * @param mod_quiz_view_object $viewobj
     */
    public function view_page($quiz, $context, $viewobj) {
        $output = '';
        $output .= $this->heading($quiz->name);
        $output .= $this->view_page_buttons($viewobj);
        return $output;
    }
    
    /**
     * Work out, and render, whatever buttons, and surrounding info, should appear
     * at the end of the review page.
     * @param mod_quiz_view_object $viewobj the information required to display
     * the view page.
     * @return string HTML to output.
     */
    public function view_page_buttons($viewobj) {
        global $CFG;
        $output = '';
        $url = new \moodle_url('/mod/adaptivequiz/startattempt.php', array('cmid' => $viewobj->cmid));
        if ($viewobj->buttontext) {
            $output .= $this->start_attempt_button($viewobj->buttontext, $url);
        }
        
        return $output;
    }
    
    /**
     * Generates the view attempt button
     *
     * @param string $buttontext the label to display on the button.
     * @param moodle_url $url The URL to POST to in order to start the attempt.
     * @return string HTML fragment.
     */
    public function start_attempt_button ($buttontext, $url) {
        $button = new single_button($url, $buttontext);
        $button->class .= ' quizstartbuttondiv';
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
}
