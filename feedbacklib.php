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
    /** @var array the feedback blocks of this feedback. */
    protected $feedbackblocks = null;
    /** @var int $quiz the quiz this feedback belongs to. */
    protected $quiz = null;

    /**
     * Constructor, assuming we already have the necessary data loaded.
     *
     * @param adaptivequiz $quiz the quiz the feedback belongs to.   
     */
    public function __construct($quiz) {
        $this->quiz = $quiz;
    }

    /**
     * Gets the specialized feedback for an adaptivequiz.
     *
     * @param adaptivequiz $quiz the adaptivequiz to get the feedback for.
     * @return feedback the feedback for this quiz.
     */
    public static function get_feedback(adaptivequiz $quiz) {
        return new feedback($quiz);
    }

    /**
     * Returns the feedback blocks of this feedback.
     *
     * @return array the feedback_blocks.
     */
    public function get_blocks() {
        if (is_null($this->feedbackblocks)) {
            global $DB;

            $records = $DB->get_records('adaptivequiz_feedback_block', array('quizid' => $this->quiz->get_id()));
            $blocks = array_map(function ($block) {
                return feedback_block::load($block->id, $this->quiz);
            }, $records);

            $this->feedbackblocks = $blocks;
        }
        return $this->feedbackblocks;
    }

    /**
     * Checks whether specialized feedback exist for a block element.
     *
     * @param block_element $blockelement the block element to check.
     * @return bool true if specialized feedback for the block element exists.
     */
    public function has_specialized_feedback(block_element $blockelement, $attempt) {
        foreach ($this->get_blocks() as $block) {
            foreach ($block->get_used_question_instances() as $qi) {
                if ($qi->get_id() == $blockelement->get_id() && $block->get_condition()->is_fullfilled($attempt)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Returns the specialized feedback to be displayed in turn of the feedback for a blockelement.
     *
     * @param block_element $blockelement the element to get the replacement feedback for.
     * @param attempt $attempt the attempt for which to get the specialized feedback.
     * @return array an array of specialized_feedback objects.
     */
    public function get_specialized_feedback_at_element(block_element $blockelement, attempt $attempt) {
        $ret = array();
        foreach ($this->get_blocks() as $block) {
            $usedqinstances = $block->get_used_question_instances();
            if (count($usedqinstances) < 1) {
                continue;
            }
            $qi = array_values($usedqinstances)[0];
            if ($block->get_condition()->is_fullfilled($attempt) &&
                $qi->get_id() == $blockelement->get_id()) {
                    array_push($ret, new specialized_feedback($block));
            }
        }
        return $ret;
    }

    /**
     * Removes a feedbackblock from this feedback.
     *
     * @param int $id the id of the block to remove.
     */
    public function remove_block($id) {
        global $DB;

        $DB->delete_records('adaptivequiz_feedback_block', array('id' => $id));

        $this->feedbackblocks = null;
    }
    
    /**
     * Finds the feedback block where the element is the first part of the uses.
     * 
     * @param block_element $element the block element.
     * @return null|feedback_block the feedback block or null.
     */
    public function search_uses($elem) {
        foreach($this->get_blocks() as $block) {
            $first = array_values($block->get_used_question_instances())[0];
            if ($first->get_id() == $elem->get_id()) {
                return $block;
            }
        }
        return null;
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
    /** @var array the ids of the question instances for which the feedback is replaced by this block. */
    protected $uses = null;

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
     * @param array $usesquestions the questions used by this feedback.
     */
    public function update($name, $feedbacktext, $usesquestions) {
        global $DB;

        if ($this->name != $name || $this->feedbacktext != $feedbacktext) {
            $record = new stdClass();
            $record->id = $this->id;
            $record->name = $name;
            $record->feedbacktext = $feedbacktext;

            $DB->update_record('adaptivequiz_feedback_block', $record);
        }

        $old = $DB->get_records('adaptivequiz_feedback_uses', array('feedbackblockid' => $this->id), 'id');
        for ($i = 0; $i < max(array(count($usesquestions), count($old))); $i++) {
            if ($i >= count($old)) {
                $record = new stdClass();
                $record->feedbackblockid = $this->id;
                $record->questioninstanceid = $usesquestions[array_keys($usesquestions)[$i]];
                $DB->insert_record('adaptivequiz_feedback_uses', $record);
            } else if ($i >= count($usesquestions)) {
                $record = $old[array_keys($old)[$i]];
                $DB->delete_records('adaptivequiz_feedback_uses', array('id' => $record->id));
            } else {
                $record = $old[array_keys($old)[$i]];
                if ($record->questioninstanceid != $usesquestions[array_keys($usesquestions)[$i]]) {
                    $record->questioninstanceid = $usesquestions[array_keys($usesquestions)[$i]];
                    $DB->update_record('adaptivequiz_feedback_uses', $record);
                }
            }

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
     * Sets the name of the feedbackblock.
     *
     * @param string $name new name of the block.
     */
    public function set_name($name) {
        global $DB;

        $this->name = $name;

        $record = new stdClass();
        $record->id = $this->id;
        $record->name = $name;
        $DB->update_record('adaptivequiz_feedback_block', $record);
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
     * Returns the quiz this block belongs to.
     *
     * @return adaptivequiz the quiz this block belongs to.
     */
    public function get_quiz() {
        return $this->quiz;
    }

    /**
     * Gets the feedback text.
     *
     * @return string the feedback text.
     */
    public function get_feedback_text() {
        return $this->feedbacktext;
    }

    /**
     * Returns the block elements of the question instances whos feedback is replaced by this block.
     *
     * @return array the block_elements of the question instances.
     */
    public function get_used_question_instances() {
        if (!$this->uses) {
            global $DB;
            $records = $DB->get_records('adaptivequiz_feedback_uses', array('feedbackblockid' => $this->id), 'id');
            $this->uses = array_map(function ($obj) {
                return block_element::load($this->quiz, $obj->questioninstanceid);
            }, $records);
        }
        return $this->uses;
    }

    /**
     * Adds a question instance to the ones used by this feedback.
     *
     * @param int $questioninstanceid the id of the question instance.
     */
    public function add_question_instance($questioninstanceid) {
        global $DB;

        $record = new stdClass();
        $record->feedbackblockid = $this->id;
        $record->questioninstanceid = $questioninstanceid;

        $DB->insert_record('adaptivequiz_feedback_uses', $record);

        array_push($this->uses, $questioninstanceid);
    }
    
    /**
     * Calculates the adapted grade for the first element in the uses.
     * 
     * @return int the adapted grade.
     */
    public function get_adapted_grade() {
        $uses = $this->uses;
        $qid = array_shift($uses)->get_element()->id;
        $first = question_bank::load_question($qid, false);
        $mark = $first->defaultmark;
        $sum = 0;
        foreach ($uses as $element) {
            if ($element->is_question()) {
                $question = question_bank::load_question($element->get_element()->id, false);
                $sum += $question->defaultmark;
            } else if ($element->is_block()) {
                $sum += $element->get_element()->get_maxgrade();
            }
        }
        return $mark - $sum;
    }
}

/**
 * A class encapsulating a specialized feedback.
 *
 * @copyright  2017 Luca Gladiator <lucamarius.gladiator@stud.tu-darmstadt.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.1
 */
class specialized_feedback {
    /** @var feedback_block the feedback block this feedback is constructed from. */
    protected $block = null;
    /**
     * Constructor.
     *
     * @param feedback_block $block the block to get the specialized feedback from.
     */
    public function __construct(feedback_block $block) {
        $this->block = $block;
    }

    /**
     * Returns the parts this feedback consists of.
     *
     * @return array an array of strings and block_elements being the parts of this feedback.
     */
    public function get_parts() {
        $ret = array();

        $raw = $this->block->get_feedback_text();
        $parts = explode('[[', $raw);

        foreach ($parts as $part) {
            if (substr($part, 1, 2) == ']]') {
                array_push($ret, $this->block_element_from_char(substr($part, 0, 1)));
                array_push($ret, substr($part, 3));
            } else {
                array_push($ret, $part);
            }
        }

        /*return array_map(function ($part) {
            if (substr($part, 0, 2) == '[[') {
                return $this->block_element_from_char(substr($part, 2, 1));
            } else {
                return $part;
            }
        }, $parts);
        return array($raw);*/
        return $ret;
    }

    /**
     * Gets the block element represented by a character.
     *
     * @param string $char the character to get the block element for.
     * @return block_element the block element represented by the char.
     */
    protected function block_element_from_char($char) {
        $order = ord(strtoupper($char));
        $index = $order - ord('A');
        $usedqinstances = $this->block->get_used_question_instances();
        if (ord('A') <= $order && $order <= ord('Z') && $index < count($usedqinstances)) {
            return $usedqinstances[array_keys($usedqinstances)[$index]];
        } else {
            return null;
        }
    }
}