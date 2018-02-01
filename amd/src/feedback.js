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
 * Javascript for the question type chooser, when adding a new question to a block.
 *
 * @package    mod_adaptivequiz
 * @copyright  2017 Luca Gladiator <lucamarius.gladiator@stud.tu-darmstadt.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery'], function($) {
    var index = 0;
    
    return {
        
        init: function() {
            $('.addusedquestion').click(function (e) {
                e.preventDefault();
                var newusespart = $('.usesquestioncontainer').find('.usesquestion').clone();
                $('.usedquestions').append(newusespart);
                // Upcounting letters
                var lastletter = $('.usesquestioncontainer').find('.usesquestionletter').html();
                $('.usesquestioncontainer').find('.usesquestionletter').html(String.fromCharCode(lastletter.charCodeAt(0) + 1));
                // Increase submit index
                index++;
                newusespart.find('.usesquestionselector').attr('name', 'usesquestions[newparts' + index + ']');
            });
        }
    };
});