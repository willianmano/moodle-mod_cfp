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
 * Add question js logic.
 *
 * @copyright  2021 onwards Willian Mano {@link http://conecti.me}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([
        'jquery',
        'core/str',
        'core/modal_factory',
        'core/modal_events',
        'core/fragment',
        'core/ajax',
        'mod_cfp/sweetalert',
        'core/yui'],
    function($, Str, ModalFactory, ModalEvents, Fragment, Ajax, Swal, Y) {

        var AddQuestion = function(selector, contextid) {
            this.contextid = contextid;

            this.init(selector);
        };

        /**
         * @var {Modal} modal
         * @private
         */
        AddQuestion.prototype.modal = null;

        /**
         * @var {int} contextid
         * @private
         */
        AddQuestion.prototype.contextid = -1;

        AddQuestion.prototype.init = function(selector) {
            var triggers = $(selector);

            return Str.get_string('addquestion', 'mod_cfp').then(function(title) {
                // Create the modal.
                return ModalFactory.create({
                    type: ModalFactory.types.SAVE_CANCEL,
                    title: title,
                    body: this.getBody()
                }, triggers);
            }.bind(this)).then(function(modal) {
                // Keep a reference to the modal.
                this.modal = modal;

                // We want to reset the form every time it is opened.
                this.modal.getRoot().on(ModalEvents.hidden, function() {
                    this.modal.setBody(this.getBody());
                }.bind(this));

                // We want to hide the submit buttons every time it is opened.
                this.modal.getRoot().on(ModalEvents.shown, function() {
                    this.modal.getRoot().append('<style>[data-fieldtype=submit] { display: none ! important; }</style>');
                }.bind(this));

                // We catch the modal save event, and use it to submit the form inside the modal.
                // Triggering a form submission will give JS validation scripts a chance to check for errors.
                this.modal.getRoot().on(ModalEvents.save, this.submitForm.bind(this));
                // We also catch the form submit event and use it to submit the form with ajax.
                this.modal.getRoot().on('submit', 'form', this.submitFormAjax.bind(this));

                return this.modal;
            }.bind(this));
        };

        AddQuestion.prototype.getBody = function(formdata) {
            if (typeof formdata === "undefined") {
                formdata = {};
            }

            // Get the content of the modal.
            var params = {jsonformdata: JSON.stringify(formdata)};

            return Fragment.loadFragment('mod_cfp', 'question_form', this.contextid, params);
        };

        AddQuestion.prototype.handleFormSubmissionResponse = function(data) {
            this.modal.hide();
            // We could trigger an event instead.
            Y.use('moodle-core-formchangechecker', function() {
                M.core_formchangechecker.reset_form_dirty_state();
            });

            var item = JSON.parse(data.data);

            var tableLine = $('<tr>' +
                '<th scope="row">'+item.id+'</th>' +
                '<td>'+item.typename+'</td>' +
                '<td>'+item.label+'</td>' +
                '<td>'+item.options+'</td>' +
                '<td style="width: 80px; text-align: center;">' +
                    '<a href="#" data-id="'+item.id+'" class="btn btn-danger btn-sm delete-question">' +
                        '<i class="fa fa-trash"></i>' +
                    '</a>' +
                '</td>' +
            '</tr>');

            tableLine
                .appendTo('.table-questions tbody')
                .hide().fadeIn('normal');

            var Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 8000,
                timerProgressBar: true,
                onOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });

            Toast.fire({
                icon: 'success',
                title: data.message
            });
        };

        AddQuestion.prototype.handleFormSubmissionFailure = function(data) {
            // Oh noes! Epic fail :(
            // Ah wait - this is normal. We need to re-display the form with errors!
            this.modal.setBody(this.getBody(data));
        };

        AddQuestion.prototype.submitFormAjax = function(e) {
            // We don't want to do a real form submission.
            e.preventDefault();

            var changeEvent = document.createEvent('HTMLEvents');
            changeEvent.initEvent('change', true, true);

            // Prompt all inputs to run their validation functions.
            // Normally this would happen when the form is submitted, but
            // since we aren't submitting the form normally we need to run client side
            // validation.
            this.modal.getRoot().find(':input').each(function(index, element) {
                element.dispatchEvent(changeEvent);
            });

            // Now the change events have run, see if there are any "invalid" form fields.
            var invalid = $.merge(
                this.modal.getRoot().find('[aria-invalid="true"]'),
                this.modal.getRoot().find('.error')
            );

            // If we found invalid fields, focus on the first one and do not submit via ajax.
            if (invalid.length) {
                invalid.first().focus();
                return;
            }

            // Convert all the form elements values to a serialised string.
            var formData = this.modal.getRoot().find('form').serialize();

            // Now we can continue...
            Ajax.call([{
                methodname: 'mod_cfp_addquestion',
                args: {contextid: this.contextid, jsonformdata: JSON.stringify(formData)},
                done: this.handleFormSubmissionResponse.bind(this),
                fail: this.handleFormSubmissionFailure.bind(this, formData)
            }]);
        };

        AddQuestion.prototype.submitForm = function(e) {
            e.preventDefault();

            this.modal.getRoot().find('form').submit();
        };

        return {
            init: function(selector, contextid) {
                return new AddQuestion(selector, contextid);
            }
        };
    }
);
