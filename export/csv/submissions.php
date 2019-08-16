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
 * View student details page.
 *
 * @package    report_samba
 * @copyright  2018 Willian Mano <willianmanoaraujo@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../config.php');

$courseid = required_param('course', PARAM_INT);
$cfpid  = required_param('cfpid', PARAM_INT);  // CFP instance ID

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$cfp = $DB->get_record('cfp', array('id' => $cfpid), '*', MUST_EXIST);
$cm = get_coursemodule_from_instance('cfp', $cfp->id, $course->id, false, MUST_EXIST);

require_login($course, true, $cm);

require_capability('mod/cfp:addinstance', $PAGE->context);

$url = new \moodle_url('/report/samba/export/csv/submissions.php', ['course' => $courseid, 'cfpid' => $cfpid]);

$PAGE->set_url($url);

$renderable = new \mod_cfp\output\manage($course, $cfp);

$export = new \mod_cfp\export\csv\submissions();

$output = $PAGE->get_renderer('mod_cfp');

$export->export($renderable, $output);
