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
use mod_cfp\util\user;
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
class submission implements renderable, templatable {

    protected $context;
    protected $cfp;
    protected $user;

    public function __construct($context, $cfp, $user)
    {
        $this->context = $context;
        $this->cfp = $cfp;
        $this->user = $user;
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
        $userutil = new user($this->cfp->id);

        $submission = $userutil->get_submission($this->user->id);
        $answers = array_values($submission);

        $attempt = $userutil->get_attempt($this->user->id);

        $attemptutil = new attempt();

        return [
            'context' => $this->context,
            'cfp' => $this->cfp,
            'answers' => $answers,
            'attachment_nonidentified' => $attemptutil->get_attachment($this->context, $attempt->id, 'attachment_nonidentified')
        ];
    }
}
