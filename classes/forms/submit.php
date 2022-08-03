<?php
// This file is part of BBCalendar block for Moodle - http://moodle.org/
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
 * Assign Tutor edit page renderer
 *
 * @package    mod_cfp
 * @copyright  2021 Willian Mano - http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_cfp\forms;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir. '/formslib.php');

use mod_cfp\util\question;
use mod_cfp\util\user;

class submit extends \moodleform {
    /**
     * Form definition. Abstract method - always override!
     */
    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'cfpid', $this->_customdata['cfpid']);
        $mform->setType('cfpid', PARAM_INT);

        $this->add_activity_fields();

        $this->add_action_buttons(true);
    }

    private function add_activity_fields() {
        global $DB;

        $cfp = $DB->get_record('cfp', ['id' => $this->_customdata['cfpid']], '*', MUST_EXIST);
        $userutil = new user($cfp);

        $activitysubmitted = false;
        $answers = null;
        if ($userutil->activity_submitted()) {
            $activitysubmitted = true;
            $submitedanswers = $userutil->get_submission();

            foreach ($submitedanswers as $submitedanswer) {
                $answers[$submitedanswer->name] = $submitedanswer->answer;
            }
        }

        $questionutil = new question();

        $mform = $this->_form;

        $fields = $questionutil->get_activity_questions($this->_customdata['cfpid']);

        foreach ($fields as $field) {
            if ($field->type == question::INPUT_TEXT) {
                $mform->addElement('text', $field->name, $field->label);
            }

            if ($field->type == question::INPUT_TEXTAREA) {
                $mform->addElement('textarea', $field->name, $field->label);
            }

            if ($field->type == question::INPUT_SELECT) {
                $options = [];
                foreach ($field->options as $option) {
                    $options[$option] = $option;
                }
                $mform->addElement('select', $field->name, $field->label, $options);
            }

            if ($field->type == question::INPUT_RADIO) {
                $radioarray = [];
                foreach ($field->options as $option) {
                    $radioarray[] = $mform->createElement('radio', $field->name, '', $option, $option);
                }
                $mform->addGroup($radioarray, $field->name, $field->label, array(' '), false);
            }

            $mform->addRule($field->name, get_string('required'), 'required', null, 'client');
            $mform->setType($field->name, PARAM_TEXT);

            if ($activitysubmitted) {
                $mform->setDefault($field->name, $answers[$field->name]);
            }
        }
    }
}
