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
 * @package    mod_cfp
 * @copyright  2019 Willian Mano {@link http://conecti.me}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("{$CFG->libdir}/formslib.php");

class mod_cfp_submit_form extends moodleform {

    public function definition() {
        $mform =& $this->_form;

        $cfp = $this->_customdata['cfp'];

        $mform->addElement('hidden', 'cfpid', $cfp->id);
        $mform->setType('cfpid', PARAM_INT);

        // Tipo de apresentacao.
        $types = [
            '' => 'Selecione uma opção',
            'palestra' => 'Palestra',
            'minicurso' => 'Minicurso',
            'mesaredonda' => 'Mesa redonda'
        ];
        $mform->addElement('select', 'type', 'Tipo', $types);
        $mform->setType('type', PARAM_RAW);
        $mform->addRule('type', 'Selecione o tipo de apresentação', 'required', null, 'client');

        // Publico alvo.
        $audiences = [
            '' => 'Selecione uma opção',
            'administradores' => 'Administradores',
            'professores' => 'Professores',
            'programadores' => 'Programadores'
        ];
        $mform->addElement('select', 'audience', 'Público alvo', $audiences);
        $mform->setType('audience', PARAM_RAW);
        $mform->addRule('audience', 'Selecione o público alvo', 'required', null, 'client');

        // Trilha.
        $tracks = [
            '' => 'Selecione uma opção',
            'casodeuso' => 'Caso de uso',
            'mobile' => 'Mobile',
            'machinelearning' => 'Machine learning',
            'designeducacional' => 'Design Educacional',
            'integracaodesistemas' => 'Integração de sistemas',
            'gamificacao' => 'Gamificação',
            'outra' => 'Outra'
        ];
        $mform->addElement('select', 'track', 'Trilha', $tracks);
        $mform->setType('track', PARAM_RAW);
        $mform->addRule('track', 'Selecione a trilha', 'required', null, 'client');

        // Nivel.
        $levels = [
            '' => 'Selecione uma opção',
            'iniciante' => 'Iniciante',
            'intermediario' => 'Intermediário',
            'avancado' => 'Avançado'
        ];
        $mform->addElement('select', 'level', 'Nível', $levels);
        $mform->setType('level', PARAM_RAW);
        $mform->addRule('level', 'Selecione o nível da apresentação', 'required', null, 'client');

        // Contato.
        $mform->addElement('text', 'contact', 'Telefone para contato', array('size' => '45'));
        $mform->setType('contact', PARAM_RAW);
        $mform->addRule('contact', null, 'required', null, 'client');

        // Titulo.
        $mform->addElement('text', 'title', 'Título da apresentação', array('size' => '45'));
        $mform->setType('title', PARAM_RAW);
        $mform->addRule('title', null, 'required', null, 'client');

        // Resumo.
        $mform->addElement('textarea', 'resume', 'Resumo da apresentação', array('rows' => '6', 'cols' => 45));
        $mform->setType('resume', PARAM_RAW);
        $mform->addRule('resume', null, 'required', null, 'client');

        // Minicurriculo.
        $mform->addElement('textarea', 'minicurriculum', 'Minicurrículo', array('rows' => '6', 'cols' => 45));
        $mform->setType('minicurriculum', PARAM_RAW);
        $mform->addRule('minicurriculum', null, 'required', null, 'client');

        // Observação.
        $mform->addElement('textarea', 'notes', 'Informações complementares/Observações', array('rows' => '6', 'cols' => 45));
        $mform->setType('notes', PARAM_RAW);

        $this->add_action_buttons(false, 'Enviar proposta');
    }
}
