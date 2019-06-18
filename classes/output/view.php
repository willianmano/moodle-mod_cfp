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

use mod_cfp\util;
use renderable;
use renderer_base;
use templatable;
use core_completion\progress;

/**
 * Class containing data for Recently accessed items block.
 *
 * @package    block_recently_course
 * @copyright  2018 onwards Willian Mano
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class view implements renderable, templatable {

    protected $course;
    protected $cfp;

    public function __construct($course, $cfp)
    {
        $this->course = $course;
        $this->cfp = $cfp;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     *
     * @return stdClass
     *
     * @throws \dml_exception
     */
    public function export_for_template(renderer_base $output) {
        global $DB, $USER, $PAGE;

        $sql = 'SELECT q.id, q.name, q.grademethod, q.timelimit, q.sumgrades, q.grade, q.decimalpoints, cm.id as cmid, cm.course, cm.section
                FROM mdl_course_modules cm
                INNER JOIN mdl_modules m ON m.id = cm.module
                INNER JOIN mdl_quiz q ON cm.instance = q.id
                WHERE cm.course = :course AND m.name = :modulename
                ORDER BY cm.section';
        $params = ['course' => $this->course->id, 'modulename' => 'quiz'];

        $quizes = array_values($DB->get_records_sql($sql, $params));

        $haswarning = false;
        $warning = null;
        if (!$quizes) {
            $haswarning = true;
            $warning = 'Não existem atividades do tipo Quiz neste curso.';
        } else {
            foreach ($quizes as $key => $quiz) {
                $util = new util($quiz, $USER);
                $bestattempt = $util->get_best_attempt();

                if ($bestattempt === false) {
                    $haswarning = true;
                    $warning = 'Você precisa responder todos os quizes do curso para poder acesar este módulo.';

                    break;
                }

                if ($bestattempt === null) {
                    $haswarning = true;
                    $warning = 'Um dos quizes do curso está usando o método de avaliação de média das notas e este método não é permitido para este módulo de comparação.';

                    break;
                }

                $summarydata = $util->get_summary_data($bestattempt);
                $quizes[$key]->summarydata = $summarydata;

                $attemptobj = $util->get_attempt_object($bestattempt->id);

                $quizrenderer = $PAGE->get_renderer('mod_quiz');
                $slots = $attemptobj->get_slots();
                $displayoptions = $attemptobj->get_display_options(false);

                $quizes[$key]->questions = $quizrenderer->questions($attemptobj, true, $slots, 0, true, $displayoptions);
            }
        }

        return [
            'course' => $this->course,
            'cfp' => $this->cfp,
            'quizes' => $quizes,
            'haswarning' => $haswarning,
            'warning' => $warning
        ];
    }
}
