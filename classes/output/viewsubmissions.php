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
 * Assign Form Assessment
 *
 * @package    mod_cfp
 * @copyright  2022 Willian Mano - http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_cfp\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;
use mod_cfp\util\group;
use mod_cfp\tables\submissions as submissions_table;

/**
 * Competency Self Assessment renderable class.
 *
 * @copyright  2021 Willian Mano - http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class viewsubmissions implements renderable, templatable {

    public $cfp;
    public $context;
    public $coursemodule;

    public function __construct($cfp, $context, $coursemodule) {
        $this->cfp = $cfp;
        $this->context = $context;
        $this->coursemodule = $coursemodule;
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
        $table = new submissions_table(
            'mod-cfp-viwesubmissions-table',
            $this->context,
            $this->cfp->course,
            $this->coursemodule,
            $this->cfp
        );

        $table->collapsible(false);

        ob_start();
        $table->out(20, true);
        $participantstable = ob_get_contents();
        ob_end_clean();

        $data = [
            'cmid' => $this->coursemodule->id,
            'name' => $this->cfp->name,
            'participantstable' => $participantstable
        ];

        return $data;
    }
}
