<?php

namespace mod_cfp\util;

class user {
    protected $cfpid;

    public function __construct($cfpid) {
        $this->cfpid = $cfpid;
    }

    public function get_attempt($userid = null) {
        global $USER, $DB;

        if (!$userid) {
            $userid = $USER->id;
        }

        return $DB->get_record('cfp_attempts', ['cfpid' => $this->cfpid, 'userid' => $userid]);
    }

    public function get_submission($userid = null) {
        global $USER, $DB;

        if (!$userid) {
            $userid = $USER->id;
        }

        $sql = 'SELECT a.*, fi.name, fi.label
                FROM {cfp_answers} a
                INNER JOIN {cfp_attempts} att ON att.id = a.attemptid
                INNER JOIN {cfp_fields} fi ON fi.id = a.fieldid
                WHERE att.cfpid = :cfpid AND att.userid = :userid';

        $submission = $DB->get_records_sql($sql, ['cfpid' => $this->cfpid, 'userid' => $userid]);

        if (!$submission) {
            return false;
        }

        return $submission;
    }

    public function delete_submission($userid = null) {
        global $USER, $DB;

        if (!$userid) {
            $userid = $USER->id;
        }

        $sql = 'DELETE a
                FROM {cfp_answers} a
                INNER JOIN {cfp_attempts} att ON att.id = a.attemptid
                WHERE att.cfpid = :cfpid AND att.userid = :userid';

        $DB->execute($sql, ['cfpid' => $this->cfpid, 'userid' => $userid]);

        $DB->delete_records('cfp_attempts', ['cfpid' => $this->cfpid, 'userid' => $userid]);
    }

    public function activity_evaluated($userid = null) {
        return false;
        global $USER;

        if (!$userid) {
            $userid = $USER->id;
        }

        return $this->evaluationmethod->activity_has_evaluation($userid);
    }
}
