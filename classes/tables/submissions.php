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
 * Base class for the table.
 *
 * @package    mod_cfp
 * @copyright  2022 Willian Mano - http://conecti.me
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_cfp\tables;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/tablelib.php');

use mod_cfp\util\user;
use table_sql;
use moodle_url;
use html_writer;

class submissions extends table_sql {

    protected $courseid;
    protected $context;
    protected $coursemodule;
    protected $cfp;

    public function __construct($uniqueid, $context, $courseid, $coursemodule, $cfp) {
        parent::__construct($uniqueid);

        $this->courseid = $courseid;
        $this->context = $context;
        $this->coursemodule = $coursemodule;
        $this->cfp = $cfp;

        $columns = array('id', 'fullname', 'email', 'status');
        $this->define_columns($columns);

        $headers = array('ID', get_string('fullname'), 'E-mail', 'Status');
        $this->define_headers($headers);

        $this->no_sorting('status');

        $urlparamms = ['id' => $coursemodule->id];
        $this->define_baseurl(new moodle_url('/mod/cfp/viewsubmissions.php', $urlparamms));

        $this->base_sql();

        $this->set_attribute('class', 'table table-bordered table-submissions');
    }

    public function base_sql() {
        $fields = 'DISTINCT u.id, u.firstname, u.lastname, u.email';

        $capjoin = get_enrolled_with_capabilities_join($this->context, '', 'mod/cfp:submit');

        $from = ' {user} u ' . $capjoin->joins;

        $params = $capjoin->params;

        $this->set_sql($fields, $from, $capjoin->wheres, $params);
    }

    public function col_fullname($user) {
        return $user->firstname . ' ' . $user->lastname;
    }

    public function col_status($user) {
        $url = new moodle_url('/mod/cfp/submission.php', ['id' => $this->coursemodule->id, 'userid' => $user->id]);

        $userutil = new user($this->cfp->id);

        $hassubmission = $userutil->get_attempt($user->id);
        $hasevaluation = $userutil->activity_evaluated($user->id);

        $output = "<span class='badge badge-pill badge-dark py-2'>".get_string('notsubmitted', 'mod_cfp')."</span>";
        if ($hassubmission) {
            $output = html_writer::link($url, 'Ver atividade', ['class' => 'btn btn-primary btn-sm']);
        }

        if ($hasevaluation) {
            $output .= "<span class='ml-2 badge badge-pill badge-success py-2'>".get_string('evaluated', 'mod_cfp')."</span>";
        }

        return $output;
    }
}
