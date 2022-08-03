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
 * Assign Tutor submissions page
 *
 * @package    mod_cfp
 * @copyright  2021 Willian Mano - http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');

$id = required_param('id', PARAM_INT);

list ($course, $cm) = get_course_and_cm_from_cmid($id, 'cfp');
$cfp = $DB->get_record('cfp', ['id' => $cm->instance], '*', MUST_EXIST);

require_course_login($course, true, $cm);

$context = context_module::instance($cm->id);

require_capability('mod/cfp:evaluate', $context);

$url = new moodle_url('/mod/cfp/viewsubmissions.php', ['id' => $id]);

$PAGE->set_url($url);
$PAGE->set_title(format_string($course->shortname) . ': ' .format_string($cfp->name));
$PAGE->set_heading(format_string($course->fullname));

$PAGE->navbar->add(get_string('submissions', 'mod_cfp'));

echo $OUTPUT->header();

$renderer = $PAGE->get_renderer('mod_cfp');

$contentrenderable = new \mod_cfp\output\viewsubmissions($cfp, $context, $cm);

echo $renderer->render($contentrenderable);

echo $OUTPUT->footer();