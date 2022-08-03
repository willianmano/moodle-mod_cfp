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
 * CFP Utility class
 *
 * @package    mod_cfp
 * @copyright  2021 Willian Mano - http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_cfp\util;

defined('MOODLE_INTERNAL') || die();

/**
 * Utility Assign Tutor class.
 *
 * @copyright  2021 Willian Mano - http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cfp {

    public function get_total_submissions($assingformid) {
        global $DB;

        return 1;

        $sql = 'SELECT a.userid
                FROM {cfp_answers} a
                INNER JOIN {cfp_fields} fi ON fi.id = a.fieldid
                INNER JOIN {cfp} f ON f.id = fi.formid
                WHERE f.id = :formid GROUP BY a.userid';

        $records = $DB->get_records_sql($sql, ['formid' => $assingformid]);

        if ($records) {
            return count($records);
        }

        return 0;
    }

    public function activity_has_submission($assingformid) {
        return false;
        $submissions = $this->get_total_submissions($assingformid);

        if ($submissions) {
            return true;
        }

        return false;
    }
}