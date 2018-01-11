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
 * The mod_adaptivequiz attempt started event.
 *
 * @package    mod_adaptivequiz
 * @copyright  2018 Johanna Heinz <johanna.heinz@stud.tu-darmstadt.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_adaptivequiz\event;
defined('MOODLE_INTERNAL') || die();

/**
 * The mod_quiz attempt started event class.
 *
 * @property-read array $other {
 *      Extra information about event.
 *
 *      - int quizid: (optional) the id of the quiz.
 * }
 *
 * @package    mod_adaptivequiz
 * @since      Moodle 2.6
 * @copyright  2018 Johanna Heinz <johanna.heinz@stud.tu-darmstadt.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class attempt_started extends \core\event\base {

    /**
     * Init method.
     */
    protected function init() {
        $this->data['objecttable'] = 'adaptivequiz_attempts';
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }
    
    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->relateduserid' has started the attempt with id '$this->objectid' for the " .
        "quiz with course module id '$this->contextinstanceid'.";
    }
    
    /**
     * Returns localised general event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventadaptivequizattemptstarted', 'adaptivequiz');
    }
    }