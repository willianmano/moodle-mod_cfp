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

namespace mod_cfp\export\csv;

defined('MOODLE_INTERNAL') || die();

require_once("{$CFG->dirroot}/lib/csvlib.class.php");

/**
 * Class containing data for cfp submissions page.
 *
 * @package    mod_cfp
 * @copyright  2019 Willian Mano {@link http://conecti.me}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class submissions extends \csv_export_writer {

    protected $reportheaders;

    /**
     * Student details constructor.
     *
     * @param string $delimiter
     * @param string $enclosure
     * @param string $mimetype
     */
    public function __construct($delimiter = 'comma', $enclosure = '"', $mimetype = 'application/download') {
        parent::__construct($delimiter, $enclosure, $mimetype);

        $this->reportheaders = [
            'ID',
            'Proponente',
            'Email',
            'Contato',
            'Minicurriculo',
            'Tipo',
            'Público alvo',
            'Trilha',
            'Nível',
            'Título',
            'Resumo',
            'Observações'
        ];
    }

    /**
     * Builds and exports CSV file.
     *
     * @param $renderable
     * @param $output
     */
    public function export($renderable, $output) {
        $reportname = 'cfp_' . $renderable->course->shortname;

        $this->filename = $reportname . ".csv";

        $data = $renderable->export_for_template($output);

        $this->add_data($this->reportheaders);

        foreach ($data['submissions'] as $cfp) {
            $row = [];

            $row[] = $cfp->id;
            $row[] = $cfp->firstname . ' ' . $cfp->lastname;
            $row[] = $cfp->email;
            $row[] = $cfp->contact;
            $row[] = $cfp->minicurriculum;
            $row[] = $cfp->type;
            $row[] = $cfp->audience;
            $row[] = $cfp->track;
            $row[] = $cfp->level;
            $row[] = $cfp->title;
            $row[] = $cfp->resume;
            $row[] = $cfp->notes;

            $this->add_data($row);
        }

        return $this->download_file();
    }
}
