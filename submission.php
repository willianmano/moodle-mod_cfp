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
 * Prints a particular instance of nps
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_cfp
 * @copyright  2019 Willian Mano {@link http://conecti.me}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Replace nps with the name of your module and remove this line.

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = required_param('id', PARAM_INT); // The submission ID

$submission = $DB->get_record('cfp_submissions', ['id' => $id], '*', MUST_EXIST);

$cfp = $DB->get_record('cfp', array('id' => $submission->cfpid), '*', MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cfp->course), '*', MUST_EXIST);
$cm = get_coursemodule_from_instance('cfp', $cfp->id, $course->id, false, MUST_EXIST);

require_login($course, true, $cm);

require_capability('mod/cfp:addinstance', $PAGE->context);

// Print the page header.
$PAGE->set_url('/mod/cfp/submission.php', array('id' => $id));
$PAGE->set_title(format_string($cfp->name));
$PAGE->set_heading(format_string($course->fullname));

$context = context_module::instance($cm->id);

$viewrenderable = new mod_cfp\output\submission($course, $cfp, $submission);

$renderer = $PAGE->get_renderer('mod_cfp');

echo $renderer->header();

echo $renderer->render($viewrenderable);

echo $renderer->footer();
