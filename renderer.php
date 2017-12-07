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
 * Defines the renderer for the adaptive quiz module.
 *
 * @package    mod_adaptivequiz
 * @copyright  2017 Jana Vatter <jana.vatter@stud.tu-darmstadt.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/mod/adaptivequiz/locallib.php');

/**
 * The renderer for the adaptive quiz module.
 *
 * @copyright  2017 Jana Vatter <jana.vatter@stud.tu-darmstadt.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_adaptivequiz_renderer extends plugin_renderer_base {
    /**
     * Render the edit page
     *
     * @param block $blockobj object containing all the block information.
     * @return string HTML to output.
     */
    public function edit_page(block $blockobj, $pageurl) {
        $output = '';
        //TODO Page title.
        $output .= $blockobj->get_name();
        $output .= html_writer::start_tag('ul');

        $children = $blockobj->get_children();
        foreach($children as $child) {
            $output .= $this->block_elem($child);
        }
        $addmenu = $this->add_menu($pageurl);
        $output .= html_writer::tag('li', $addmenu);
        $output .= html_writer::end_tag('ul');

        $output .= $this->question_chooser();
        $this->page->requires->js_call_amd('mod_adaptivequiz/questionchooser', 'init');
        return $output;
    }

    /**
     * Render one element of a block.
     *
     * @param block_elem $blockelem
     * @return string HTML to display this element.
     */
    public function block_elem(block_element $blockelem, $pageurl) {
        //Description of the element.
        $element_html = '';
        $edit_html = '';
        $cmid = 1;
        if ($blockelem->is_question()) {
            $element_html = $this->question($blockelem->get_element());
            $edit_html = $this->question_edit_button($blockelem->get_element(), $pageurl, $cmid);
        }
        else if ($blockelem->is_block()) {
            $element_html = block($blockelem);
            $edit_html = block_edit_button($blockelem->get_element(), $pageurl, $cmid);
        }
        else {
            $element_html = 'This elementtype is not supported.';
        }
        return html_writer::tag('li', $element_html . $edit_html);
    }

    /**
     *
     *
     * @param block_elem $blockelem
     */
    public function question($blockelem) {
        return $element_html = $blockelem->name;
    }

    /**
     *
     *
     * @param block_elem $blockelem
     */
    public function block($blockelem) {
        $element_html = $blockelem->get_name();
    }

    /**
     * Outputs the edit button HTML for a question.
     *
     * @param question $question
     */
    public function question_edit_button($question, $returnurl, $cmid) {
        global $OUTPUT, $CFG;
        // Minor efficiency saving. Only get strings once, even if there are a lot of icons on one page.
        static $stredit = null;
        static $strview = null;
        if ($stredit === null) {
            $stredit = get_string('edit');
            $strview = get_string('view');
        }

        // What sort of icon should we show?
        $action = '';
        if (!empty($question->id) &&
                (question_has_capability_on($question, 'edit', $question->category) ||
                        question_has_capability_on($question, 'move', $question->category))) {
            $action = $stredit;
            $icon = '/t/edit';
        } else if (!empty($question->id) &&
                question_has_capability_on($question, 'view', $question->category)) {
            $action = $strview;
            $icon = '/i/info';
        }

        // Build the icon.
        if ($action) {
            if ($returnurl instanceof moodle_url) {
                $returnurl = $returnurl->out_as_local_url(false);
            }
            $questionparams = array('returnurl' => $returnurl, 'cmid' => $cmid, 'id' => $question->id);
            $questionurl = new moodle_url("$CFG->wwwroot/question/question.php", $questionparams);
            return '<a title="' . $action . '" href="' . $questionurl->out() . '" class="questioneditbutton"><img src="' .
                $OUTPUT->pix_url($icon) . '" alt="' . $action . '" />' . $contentaftericon .
                '</a>';
        } else if ($contentaftericon) {
            return '<span class="questioneditbutton">' . $contentaftericon . '</span>';
        } else {
            return '';
        }
    }

    /**
     * Outputs the edit button HTML for a block.
     *
     * @param block $block
     */
    public function block_edit_button($block) {

    }

    protected function add_menu(\moodle_url $pageurl) {
        $menu = new \action_menu();
        $menu->set_alignment(\action_menu::TL, \action_menu::TL);
        $trigger = html_writer::tag('span', get_string('add', 'adaptivequiz'));
        $menu->set_menu_trigger($trigger);
        // The menu appears within an absolutely positioned element causing width problems.
        // Make sure no-wrap is set so that we don't get a squashed menu.
        $menu->set_nowrap_on_items(true);
        $params = array('returnurl' => $pageurl->out_as_local_url(false),
            'cmid' => 3, //TODO
            'category' => 2,//TODO
            'appendqnumstring' => 'addquestion');
        $addaquestion = new \action_menu_link_secondary(
            new \moodle_url('/question/addquestion.php', $params),
            new \pix_icon('t/add', get_string('addaquestion', 'adaptivequiz'), 'moodle', array('class' => 'iconsmall', 'title' => '')),
            get_string('addaquestion', 'adaptivequiz'),
            array('class' => 'cm-edit-action addquestion', 'data-action' => 'addquestion')
            );
        $menu->add($addaquestion);

        $questionbank =  new \action_menu_link_secondary($pageurl,
            new \pix_icon('t/add', $str->questionbank, 'moodle', array('class' => 'iconsmall', 'title' => '')),
            get_string('questionbank', 'adaptivequiz'),
            array('class' => 'cm-edit-action questionbank', 'data-action' => 'questionbank'));
        $menu->add($questionbank);
        $menu->prioritise = true;
        return html_writer::tag('span', $this->render($menu),
            array('class' => 'add-menu-outer'));
    }

    public function question_chooser() {
        $container = html_writer::div(print_choose_qtype_to_add_form(array(), null, false), '',
            array('id' => 'qtypechoicecontainer'));
        return html_writer::div($container, 'createnewquestion');
    }
}
