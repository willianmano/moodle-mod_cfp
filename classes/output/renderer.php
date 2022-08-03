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
 * @package    mod_cfp
 * @copyright  2019 Willian Mano {@link http://conecti.me}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {
    /**
     * Return the page content for the view page.
     *
     * @param \renderable $page The page renderable
     *
     * @return string HTML string
     *
     * @throws \moodle_exception
     */
    public function render_submission(\renderable $page) {
        return $this->render_from_template('mod_cfp/submission', $page->export_for_template($this));
    }

    /**
     * Return the page content for the view page.
     *
     * @param \renderable $page The page renderable
     *
     * @return string HTML string
     *
     * @throws \moodle_exception
     */
    public function render_manage(\renderable $page) {
        return $this->render_from_template('mod_cfp/manage', $page->export_for_template($this));
    }

    /**
     * Return the page content for the view page.
     *
     * @param \renderable $page The page renderable
     *
     * @return string HTML string
     *
     * @throws \moodle_exception
     */
    public function render_view(\renderable $page) {
        $data = $page->export_for_template($this);

        if (has_capability('mod/cfp:evaluate', $page->context)) {
            return parent::render_from_template('mod_cfp/view_admin', $data);
        }

        return parent::render_from_template('mod_cfp/view', $data);

    }

    /**
     * Return the page content for the submit form page.
     *
     * @param \renderable $page The page renderable
     *
     * @return string HTML string
     *
     * @throws \moodle_exception
     */
    public function render_submit(\renderable $page) {
        return $this->render_from_template('mod_cfp/submit', $page->export_for_template($this));
    }
}
