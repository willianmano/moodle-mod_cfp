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
 * @package    mod_quizcompare
 * @copyright  2019 Willian Mano {@link http://conecti.me}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("{$CFG->libdir}/formslib.php");

class edit_form extends moodleform {

    public function definition() {
        global $USER;
        global $DB;

        $cmid = optional_param('id', 0, PARAM_INT);

        $answer  = $DB->get_record('nps_answers', array('userid' => $USER->id, 'course_module' => $cmid), '*');

        $mform =& $this->_form;

        $mform->addElement('header', 'displayinfo', 'Avaliação de curso');

        if ($answer) {
            $mform->addElement('html',
                '<div class="qheader"><h3>'.
                get_string('alreadygraded', 'mod_nps').
                ' <b>'.
                $answer->grade.
                '</b></h3></div>'
            );
        } else {
            $mform->addElement('html',
                '<div class="qheader">'.
                get_string('whichgrade', 'mod_nps').
                '</div><br>'
            );

            $radioarray = [];
            for ($i = 1; $i <= 10; $i++) {
                $radioarray[] = $mform->createElement('radio', 'grade', '', $i, $i, $attributes);
            }
            $mform->addGroup($radioarray, 'grade', 'Nota', array(' '), false);
            $mform->addRule('grade', null, 'required', null, 'client');
            $mform->addElement('hidden', 'id', $cmid);
            $mform->addElement('hidden', 'userid', optional_param('userid', 0, PARAM_INT));
            $this->add_action_buttons(false);
        }
    }
}
