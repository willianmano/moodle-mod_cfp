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
 * Prints a particular instance of cfp
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_cfp
 * @copyright  2019 Willian Mano {@link http://conecti.me}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Replace cfp with the name of your module and remove this line.

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/submit_form.php');

$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$cfpid  = optional_param('cfpid', 0, PARAM_INT);  // CFP instance ID

if ($id) {
    $cm         = get_coursemodule_from_id('cfp', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $cfp  = $DB->get_record('cfp', array('id' => $cm->instance), '*', MUST_EXIST);
}

if (!$id && $cfpid) {
    $cfp  = $DB->get_record('cfp', array('id' => $cfpid), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cfp->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('cfp', $cfp->id, $course->id, false, MUST_EXIST);
}

if (!$cfp) {
    print_error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);

$event = \mod_cfp\event\course_module_viewed::create(array(
    'objectid' => $PAGE->cm->instance,
    'context' => $PAGE->context,
));

$event->add_record_snapshot('course', $PAGE->course);
$event->add_record_snapshot($PAGE->cm->modname, $cfp);
$event->trigger();

// Print the page header.
$url = new moodle_url('/mod/cfp/submit.php', array('id' => $cm->id));
$PAGE->set_url($url);
$PAGE->set_title(format_string($cfp->name));
$PAGE->set_heading(format_string($course->fullname));

$submitform = new mod_cfp_submit_form($url, ['cfp' => $cfp]);
if ($submitform->is_cancelled()) {
    redirect(new moodle_url('/mod/cfp/view.php', ['id' => $cm->id]));
} else if ($data = $submitform->get_data()) {
    // Creates the new record.
    if ($data) {
        $submissionid = cfp_add_submission($data);

        if ($submissionid) {
            redirect(new moodle_url('/mod/cfp/view.php', ['id' => $cm->id]));
        }
    }
}

$submitrenderable = new mod_cfp\output\submit($cfp, $submitform);

$renderer = $PAGE->get_renderer('mod_cfp');

echo $renderer->header();

echo $renderer->render($submitrenderable);

echo $renderer->footer();
