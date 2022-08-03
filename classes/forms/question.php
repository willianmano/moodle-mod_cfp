<?php
// This file is part of Timeline course format for moodle - http://moodle.org/
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
 * The mform for creating a question
 *
 * @package    mod_cfp
 * @copyright  2022 onwards Willian Mano {@link https://conecti.me}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_cfp\forms;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');

/**
 * The mform class for creating a post
 *
 * @copyright  2022 onwards Willian Mano {@link https://conecti.me}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class question extends \moodleform {

    /**
     * Class constructor.
     *
     * @param array $formdata
     * @param array $customodata
     */
    public function __construct($formdata, $customodata = null) {
        parent::__construct(null, $customodata, 'post',  '', ['class' => 'cfp-addquestion-form'], true, $formdata);

        $this->set_display_vertical();
    }

    /**
     * The form definition.
     *
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function definition() {
        $mform = $this->_form;
        $cmid = !(empty($this->_customdata['cmid'])) ? $this->_customdata['cmid'] : null;

        $mform->addElement('hidden', 'cmid', $cmid);

        $questionutil = new \mod_cfp\util\question();

        $mform->addElement('select', 'type', get_string('type', 'mod_cfp'), $questionutil->options);
        $mform->addRule('type', get_string('required'), 'required', null, 'client');
        $mform->setType('type', PARAM_TEXT);

        $mform->addElement('text', 'name', get_string('name', 'mod_cfp'));
        $mform->addRule('name', get_string('required'), 'required', null, 'client');
        $mform->setType('name', PARAM_TEXT);

        $mform->addElement('text', 'label', get_string('label', 'mod_cfp'));
        $mform->addRule('label', get_string('required'), 'required', null, 'client');
        $mform->setType('label', PARAM_TEXT);

        $mform->addElement('textarea', 'options', get_string("options", "mod_cfp"), 'wrap="virtual" rows="5" cols="50"');
        $mform->setType('options', PARAM_TEXT);
        $mform->addHelpButton('options', 'options', 'mod_cfp');
        $mform->hideIf('options', 'type', 'eq', 0);
        $mform->hideIf('options', 'type', 'eq', 1);
        $mform->hideIf('options', 'type', 'eq', 2);
    }

    /**
     * A bit of custom validation for this form
     *
     * @param array $data An assoc array of field=>value
     * @param array $files An array of files
     *
     * @return array
     *
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $type = isset($data['type']) ? $data['type'] : null;
        $name = isset($data['name']) ? $data['name'] : null;
        $label = isset($data['label']) ? $data['label'] : null;
        $options = isset($data['options']) ? $data['options'] : null;

        if ($this->is_submitted() && (empty($name) || strlen($name) < 3)) {
            $errors['name'] = get_string('validator_name', 'mod_cfp');
        }

        if ($this->is_submitted() && (empty($label) || strlen($label) < 3)) {
            $errors['label'] = get_string('validator_label', 'mod_cfp');
        }

        if ($this->is_submitted() && ($type && $type > 2) && (empty($options) || strlen($options) < 3)) {
            $errors['options'] = get_string('validator_options', 'mod_cfp');
        }

        return $errors;
    }
}
