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
 * @copyright  2022 Willian Mano - http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_cfp\output;

defined('MOODLE_INTERNAL') || die();

use mod_cfp\util\cfp;
use mod_cfp\util\question;
use renderable;
use templatable;
use renderer_base;

/**
 * Competency Self Assessment renderable class.
 *
 * @copyright  2021 Willian Mano - http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class questions implements renderable, templatable {

    public $cfp;
    public $context;

    public function __construct($cfp, $context) {
        $this->cfp = $cfp;
        $this->context = $context;
    }

    /**
     * Export the data
     *
     * @param renderer_base $output
     *
     * @return array|\stdClass
     *
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function export_for_template(renderer_base $output) {
        $cfputil = new cfp();
        $questionutil = new question();

        return [
            'id' => $this->cfp->id,
            'contextid' => $this->context->id,
            'course' => $this->cfp->course,
            'name' => $this->cfp->name,
            'cmid' => $this->context->instanceid,
            'canedit' => !$cfputil->activity_has_submission($this->cfp->id),
            'questions' => $questionutil->get_activity_questions($this->cfp->id)
        ];
    }
}
