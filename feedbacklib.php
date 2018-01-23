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
 * Back-end code for handling data about specialized feedback.
 *
 * @package    mod_adaptivequiz
 * @copyright  2017 Luca Gladiator <lucamarius.gladiator@stud.tu-darmstadt.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();



/**
 * A class encapsulating the specialized feedback of an adaptivequiz.
 *
 * @copyright  2017 Luca Gladiator <lucamarius.gladiator@stud.tu-darmstadt.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.1
 */
class feedback {
    /**
     * Constructor, assuming we already have the necessary data loaded.
     */
    public function __construct() {
        //TODO
    }

    /**
     * Gets the specialized feedback for an adaptivequiz.
     *
     * @param adaptivequiz $quiz the adaptivequiz to get the feedback for.
     *
     * @return feedback the feedback for this quiz.
     */
    public static function get_feedback(adaptivequiz $quiz) {
        //TODO
        return new feedback();
    }

    /**
     * Checks whether specialized feedback exist for a block element.
     *
     * @param block_element $blockelement the block element to check.
     *
     * @return bool true if specialized feedback for the block element exists.
     */
    public function has_specialized_feedback(block_element $blockelement) {
        //TODO
        return false;
    }
}

/**
 * A class encapsulating a specialized feedback block.
 *
 * @copyright  2017 Luca Gladiator <lucamarius.gladiator@stud.tu-darmstadt.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.1
 */
class feedback_block {
    /** @var int the id of the feedback block. */
    protected $id = 0;
    /** @var adaptivequiz the quiz, this block belongs to. */
    protected $quiz = null;
    /** @var string the name of this feedback. */
    protected $name = '';
    /** @var condition the condition under which to use this feedback instead of the standard feedback. */
    protected $condition = null;
    /** @var string the feedbacktext. */
    protected $feedbacktext = '';

    /**
     * Constructor, assuming we already have the necessary data loaded.
     *
     * @param int $id the id of the feedback block.
     * @param adaptivequiz $quiz the id of the quiz, this block belongs to.
     * @param string $name the name of this feedback.
     * @param condition $condition the condition under which to use this feedback instead of the standard feedback.
     * @param string $feedbacktext the feedbacktext.
     */
    public function __construct($id, $quiz, $name, condition $condition, $feedbacktext) {
        $this->id = $id;
        $this->quiz = $quiz;
        $this->name = $name;
        $this->condition = $condition;
        $this->feedbacktext = $feedbacktext;
    }

    /**
     * Static function to get a feedback block object from an id.
     *
     * @param int $blockid the feedback block id.
     * @param adaptivequiz $quiz the id of the quiz, this block belongs to.
     * @return feedback_block the new feedback block object.
     */
    public static function load($blockid, adaptivequiz $quiz) {
        global $DB;

        $feedback = $DB->get_record('adaptivequiz_feedback_block', array('id' => $blockid));

        $condition = condition::load($feedback->conditionid);

        return new feedback_block($blockid, $quiz, $feedback->name, $condition, $feedback->feedbacktext);
    }

    /**
     * Creates a new feedback block in the database.
     *
     * @param adaptivequiz $quiz the quiz this feedbackblock belongs to.
     * @param string $name the name of the feedback block.
     * @return feedback_block the created feedback block.
     */
    public static function create(adaptivequiz $quiz, $name) {
        global $DB;

        $condition = condition::create();

        $record = new stdClass();
        $record->name = $name;
        $record->quizid = $quiz->get_id();
        $record->conditionid = $condition->get_id();
        $record->feedbacktext = '';

        $blockid = $DB->insert_record('adaptivequiz_feedback_block', $record);

        return new feedback_block($blockid, $quiz, $name, $condition, '');
    }

    /**
     * Updates the values of this feedback.
     *
     * @param string $name the new name.
     * @param string $feedbacktext the new feedback text.
     */
    public function update($name, $feedbacktext) {
        if ($this->name != $name || $this->feedbacktext != $feedbacktext) {
            global $DB;

            $record = new stdClass();
            $record->id = $this->id;
            $record->name = $name;
            $record->feedbacktext = $feedbacktext;

            $DB->update_record('adaptivequiz_feedback_block', $record);
        }
    }


    /**
     * Returns the id of the feedbackblock.
     *
     * @return int the id of the feedbackblock.
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Gets the name of this feedback.
     *
     * @return string the name.
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * Gets the condition under which to display this feedback.
     *
     * @return condition the condition.
     */
    public function get_condition() {
        return $this->condition;
    }

    /**
     * Gets the feedback text.
     *
     * @return string the feedback text.
     */
    public function get_feedback_text() {
        return $this->feedbacktext;
    }
}