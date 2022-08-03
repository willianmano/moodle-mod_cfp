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
 * Questions utility class
 *
 * @package    mod_cfp
 * @copyright  2022 Willian Mano - http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_cfp\util;

defined('MOODLE_INTERNAL') || die();

/**
 * Questions class
 *
 * @package    mod_cfp
 * @copyright  2022 Willian Mano - http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class question {
    const INPUT_TEXT = 1;
    const INPUT_TEXTAREA = 2;
    const INPUT_SELECT = 3;
    const INPUT_RADIO = 4;

    public $options = [
        0 => 'Choose an option',
        self::INPUT_TEXT => 'Text',
        self::INPUT_TEXTAREA => 'Text area',
        self::INPUT_SELECT => 'Select',
        self::INPUT_RADIO => 'Radio button',
    ];

    public function activity_has_questions($cfpid) {
        $records = $this->get_activity_questions($cfpid);

        if ($records) {
            return true;
        }

        return false;
    }

    public function get_activity_questions($cfpid) {
        global $DB;

        $records = $DB->get_records('cfp_fields', ['cfpid' => $cfpid]);

        if (!$records) {
            return false;
        }

        foreach ($records as $record) {
            $record->typename = $this->options[$record->type];

            if ($record->options) {
                $record->options = preg_split('/\r\n|\r|\n/', $record->options);
            }
        }

        return array_values($records);
    }

    public function create_question($data) {
        global $DB;

        $fieldid = $DB->insert_record('cfp_fields', $data);

        $data->id = $fieldid;

        $data->typename = $this->options[$data->type];

        if ($data->options) {
            $options = preg_split('/\r\n|\r|\n/', $data->options);

            $optionshtml = '<ol class="mb-0">';
            foreach ($options as $option) {
                $optionshtml .= "<li>{$option}</li>";
            }

            $optionshtml .= '</ol>';

            $data->options = $optionshtml;
        }

        return $data;
    }
}
