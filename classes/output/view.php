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
 * View page class renderable.
 *
 * @package    mod_cfp
 * @copyright  2019 Willian Mano {@link http://conecti.me}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_cfp\output;

defined('MOODLE_INTERNAL') || die();

use mod_cfp\util;
use renderable;
use renderer_base;
use templatable;
use core_completion\progress;

/**
 * Class containing data for Recently accessed items block.
 *
 * @package    block_recently_course
 * @copyright  2018 onwards Willian Mano
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class view implements renderable, templatable {

    protected $course;
    protected $cfp;

    public function __construct($course, $cfp)
    {
        $this->course = $course;

        $cfp->humanstartdate = userdate($cfp->startdate);
        $cfp->humanduedate = userdate($cfp->duedate);

        $this->cfp = $cfp;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     *
     * @return stdClass
     *
     * @throws \dml_exception
     */
    public function export_for_template(renderer_base $output) {
        global $DB, $USER;

        $sql = 'SELECT * FROM {cfp_submissions} WHERE cfpid = :cfpid AND userid = :userid';
        $params = ['cfpid' => $this->cfp->id, 'userid' => $USER->id];

        $submissions = array_values($DB->get_records_sql($sql, $params));

        if ($submissions) {
            foreach ($submissions as $key => $submission) {
                switch($submission->status) {
                    case 'naoselecionada':
                        $submissions[$key]->statusclass = 'danger';
                        $submissions[$key]->status = 'Não selecionada';
                    case 'selecionada':
                        $submissions[$key]->statusclass = 'success';
                        $submissions[$key]->status = 'Selecionada';
                    default:
                        $submissions[$key]->statusclass = 'dark';
                        $submissions[$key]->status = 'Em análise';
                }

                $submissions[$key]->type = ucfirst($submission->type);
                $submissions[$key]->audience = ucfirst($submission->audience);
                $submissions[$key]->track = ucfirst($submission->track);
            }
        }

        $issubmissionavailable = true;
        if ($this->cfp->startdate < time() || $this->cfp->duedate > time()) {
            $issubmissionavailable = false;
        }

        return [
            'course' => $this->course,
            'cfp' => $this->cfp,
            'issubmissionavailable' => $issubmissionavailable,
            'submissions' => $submissions,
            'hassubmissions' => count($submissions) ? true : false
        ];
    }
}
