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

require_once($CFG->libdir . '/formslib.php');

use mod_cfp\util\attempt;
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

        $alertcontent  = \html_writer::start_div('alert alert-info', ['role' => 'alert']);
        $alertcontent .= \html_writer::tag('h2', get_string('submit_useralert', 'mod_cfp'));
        $alertcontent .= '<hr />';
        $alertcontent .= \html_writer::tag('p', get_string('submit_useralert_desc', 'mod_cfp'), ['class' => 'mb-0']);
        $alertcontent .= \html_writer::end_div();
        $mform->addElement('html', $alertcontent);

        $options = [
            'subdirs' => 0,
            'maxfiles' => 1,
            'accepted_types' => ['document', 'presentation']
        ];

        $mform->addElement('filemanager', 'attachment_nonidentified', get_string('attachment_nonidentified', 'mod_cfp'), null, $options);
        $mform->addRule('attachment_nonidentified', get_string('required'), 'required', null, 'client');

        $mform->addElement('filemanager', 'attachment_identified', get_string('attachment_identified', 'mod_cfp'), null, $options);
        $mform->addRule('attachment_identified', get_string('required'), 'required', null, 'client');

        $this->add_action_buttons(true);
    }

    private function add_activity_fields() {
        $userutil = new user($this->_customdata['cfpid']);

        $activitysubmitted = false;
        $answers = null;
        if ($userutil->get_attempt()) {
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

    public function definition_after_data() {
        if (!$this->_customdata['cfpid']) {
            return;
        }

        $mform = $this->_form;

        $userutil = new user($this->_customdata['cfpid']);

        $attempt = $userutil->get_attempt();

        if ($attempt) {
            $cm = get_coursemodule_from_instance('cfp', $this->_customdata['cfpid']);

            $context = \context_module::instance($cm->id);
            $draftitemid = file_get_submitted_draft_itemid('attachments');

            $options = [
                'subdirs' => 0,
                'maxfiles' => 1,
                'accepted_types' => ['document', 'presentation']
            ];

            file_prepare_draft_area($draftitemid, $context->id, 'mod_cfp', 'attachment_nonidentified', $attempt->id, $options);
            $mform->getElement('attachment_nonidentified')->setValue($draftitemid);

            file_prepare_draft_area($draftitemid, $context->id, 'mod_cfp', 'attachment_identified', $attempt->id, $options);
            $mform->getElement('attachment_identified')->setValue($draftitemid);
        }
    }
}
