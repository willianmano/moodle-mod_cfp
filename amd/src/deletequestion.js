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
 * Delete question item js logic.
 *
 * @copyright  2021 onwards Willian Mano {@link http://conecti.me}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/ajax', 'core/str', 'mod_cfp/sweetalert'], function($, Ajax, Str, Swal) {
    var STRINGS = {
        CONFIRM_TITLE: 'Are you sure?',
        CONFIRM_MSG: 'Once deleted, this item cannot be recovered!',
        CONFIRM_YES: 'Yes, delete it!',
        CONFIRM_NO: 'Cancel',
        SUCCESS: 'Item successfully deleted.'
    };

    var componentStrings = [
        {
            key: 'deletequestion_confirm_title',
            component: 'mod_cfp'
        },
        {
            key: 'deletequestion_confirm_msg',
            component: 'mod_cfp'
        },
        {
            key: 'deletequestion_confirm_yes',
            component: 'mod_cfp'
        },
        {
            key: 'deletequestion_confirm_no',
            component: 'mod_cfp'
        },
        {
            key: 'deletequestion_success',
            component: 'mod_cfp'
        },
    ];

    var DeleteQuestion = function() {
        this.getStrings();

        this.registerEventListeners();
    };

    DeleteQuestion.prototype.getStrings = function() {
        var stringsPromise = Str.get_strings(componentStrings);

        $.when(stringsPromise).done(function(strings) {
            STRINGS.CONFIRM_TITLE = strings[0];
            STRINGS.CONFIRM_MSG = strings[1];
            STRINGS.CONFIRM_YES = strings[2];
            STRINGS.CONFIRM_NO = strings[3];
            STRINGS.SUCCESS = strings[4];
        });
    };

    DeleteQuestion.prototype.registerEventListeners = function() {
        $("body").on("click", ".delete-question", function(event) {
            event.preventDefault();

            var eventTarget = $(event.currentTarget);

            Swal.fire({
                title: STRINGS.CONFIRM_TITLE,
                text: STRINGS.CONFIRM_MSG,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: STRINGS.CONFIRM_YES,
                cancelButtonText: STRINGS.CONFIRM_NO
            }).then(function(result) {
                if (result.value) {
                    this.deleteGradeitem(eventTarget);
                }
            }.bind(this));
        }.bind(this));
    };

    DeleteQuestion.prototype.deleteGradeitem = function(eventTarget) {
        var request = Ajax.call([{
            methodname: 'mod_cfp_deletequestion',
            args: {
                question: {
                    id: eventTarget.data('id')
                }
            }
        }]);

        request[0].done(function() {
            this.removeGradeItemLine(eventTarget);
        }.bind(this)).fail(function(error) {
            var message = error.message;

            if (!message) {
                message = error.error;
            }
            this.showToast('error', message);
        }.bind(this));
    };

    DeleteQuestion.prototype.removeGradeItemLine = function(eventTarget) {
        var tableLine = eventTarget.closest('tr');

        tableLine.fadeOut("normal", function() {
            $(this).remove();
        });

        this.showToast('success', STRINGS.SUCCESS);
    };

    DeleteQuestion.prototype.showToast = function(type, message) {
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
            icon: type,
            title: message
        });
    };

    return {
        'init': function() {
            return new DeleteQuestion();
        }
    };
});