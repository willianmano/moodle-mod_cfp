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

use renderable;
use renderer_base;
use templatable;

/**
 * Class containing data for cfp manage page.
 *
 * @package    mod_cfp
 * @copyright  2019 Willian Mano {@link http://conecti.me}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manage implements renderable, templatable {

    protected $course;
    protected $cfp;

    public function __construct($course, $cfp)
    {
        $this->course = $course;
        $this->cfp = $cfp;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     *
     * @return array
     *
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function export_for_template(renderer_base $output) {
        global $DB;

        $sql = 'SELECT s.*, u.email, u.firstname, u.lastname
                FROM {cfp_submissions} s
                INNER JOIN {user} u ON u.id = s.userid 
                WHERE cfpid = :cfpid';
        $params = ['cfpid' => $this->cfp->id];

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

        return [
            'course' => $this->course,
            'cfp' => $this->cfp,
            'submissions' => $submissions,
            'hassubmissions' => count($submissions) ? true : false,
        ];
    }
}
