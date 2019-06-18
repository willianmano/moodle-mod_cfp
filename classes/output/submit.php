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
class submit implements renderable, templatable {

    protected $cfp;
    protected $form;

    public function __construct($cfp, $form)
    {
        $this->form = $form;
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

        ob_start();
        $this->form->display();
        $form = ob_get_contents();
        ob_end_clean();

        return [
            'cfp' => $this->cfp,
            'form' => $form
        ];
    }
}
