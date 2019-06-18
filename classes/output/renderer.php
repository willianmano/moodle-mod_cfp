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
 * Module renderer
 *
 * @package    mod_cfp
 * @copyright  2019 Willian Mano {@link http://conecti.me}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_cfp\output;

defined('MOODLE_INTERNAL') || die;

use plugin_renderer_base;

/**
 * Recently accessed items block renderer
 *
 * @package    block_recently_course
 * @copyright  2018 onwards Willian Mano
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {
    /**
     * Return the main content for the view page.
     *
     * @param \renderer_base $main The main renderable
     *
     * @return string HTML string
     *
     * @throws \moodle_exception
     */
    public function render_view(\renderable $main) {
        return $this->render_from_template('mod_cfp/view', $main->export_for_template($this));
    }

    /**
     * Return the main content for the submit form page.
     *
     * @param \renderer_base $main The main renderable
     *
     * @return string HTML string
     *
     * @throws \moodle_exception
     */
    public function render_submit(\renderable $main) {
        return $this->render_from_template('mod_cfp/submit', $main->export_for_template($this));
    }
}
