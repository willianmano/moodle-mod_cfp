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

use mod_cfp\util\attempt;
use mod_cfp\util\cfp;
use mod_cfp\util\question;
use mod_cfp\util\user;
use renderable;
use renderer_base;
use templatable;

/**
 * Class containing data for cfp view page.
 *
 * @package    mod_cfp
 * @copyright  2019 Willian Mano {@link http://conecti.me}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class view implements renderable, templatable {

    public $course;
    public $context;
    public $cfp;

    public function __construct($course, $context, $cfp)
    {
        $this->course = $course;
        $this->context = $context;

        $cfp->humanstartdate = userdate($cfp->startdate);
        $cfp->humanduedate = userdate($cfp->duedate);

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
        $cfputil = new cfp();
        $questionutil = new question();


        $hasquestions = $questionutil->activity_has_questions($this->cfp->id);

        $timeremaining = $this->cfp->duedate - time();
        $isdelayed = true;
        if ($timeremaining > 0) {
            $isdelayed = false;
        }

        $data = [
            'id' => $this->cfp->id,
            'name' => $this->cfp->name,
            'intro' => format_module_intro('cfp', $this->cfp, $this->context->instanceid),
            'duedate' => userdate($this->cfp->duedate),
            'timeremaining' => format_time($timeremaining),
            'cmid' => $this->context->instanceid,
            'course' => $this->cfp->course,
            'hasquestions' => $hasquestions,
            'isdelayed' => $isdelayed
        ];

        // If student, return current data.
        if (!has_capability('mod/cfp:evaluate', $this->context)) {
            $userutil = new user($this->cfp->id);
            $attemptutil = new attempt();

            $attempt = $userutil->get_attempt();

            $data['hasattempt'] = $attempt !== false;
            $data['status'] = $attempt ? $attemptutil->get_status_string($attempt->status) : $attemptutil->get_status_string();
            $data['statusalert'] = $attempt ? $attemptutil->get_status_alert($attempt->status) : $attemptutil->get_status_alert();

            return $data;
        }

        $coursemodule = get_coursemodule_from_instance('cfp', $this->cfp->id);

        $submissions = $cfputil->get_total_submissions($this->cfp->id);

        $data['hide'] = $coursemodule->visible;
        $data['submissions'] = $submissions;

        return $data;
    }
}
