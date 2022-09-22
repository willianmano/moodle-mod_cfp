<?php

namespace mod_cfp\util;

class user {
    protected $userid;
    protected $cfpid;

    public function __construct($userid, $cfpid) {
        $this->userid = $userid;
        $this->cfpid = $cfpid;
    }

    public function get_attempt() {
        global $DB;

        return $DB->get_record('cfp_attempts', ['cfpid' => $this->cfpid, 'userid' => $this->userid]);
    }

    public function get_submission() {
        global $DB;

        $sql = 'SELECT a.*, fi.name, fi.label
                FROM {cfp_answers} a
                INNER JOIN {cfp_attempts} att ON att.id = a.attemptid
                INNER JOIN {cfp_fields} fi ON fi.id = a.fieldid
                WHERE att.cfpid = :cfpid AND att.userid = :userid';

        $submission = $DB->get_records_sql($sql, ['cfpid' => $this->cfpid, 'userid' => $this->userid]);

        if (!$submission) {
            return false;
        }

        return $submission;
    }

    public function delete_submission() {
        global $DB;

        $sql = 'DELETE a
                FROM {cfp_answers} a
                INNER JOIN {cfp_attempts} att ON att.id = a.attemptid
                WHERE att.cfpid = :cfpid AND att.userid = :userid';

        $DB->execute($sql, ['cfpid' => $this->cfpid, 'userid' => $this->userid]);

        $DB->delete_records('cfp_attempts', ['cfpid' => $this->cfpid, 'userid' => $this->userid]);
    }

    public function activity_evaluated() {
        return false;
        global $USER;

        return $this->evaluationmethod->activity_has_evaluation($this->userid);
    }
}
