/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/template',
    'mage/utils/misc',
    'mage/translate',
    'jquery-ui-modules/dialog'
], function ($, mageTemplate, miscUtils) {
    'use strict';

    $.widget('mage.translateInline', $.ui.dialog, {
        options: {
            translateForm: {
                template: '#translate-form-template',
                data: {
                    id: 'translate-inline-form',
                    message: 'Please refresh the page to see your changes after submitting this form.'
                }
            },
            autoOpen: false,
            translateArea: null,
            modal: true,
            dialogClass: 'popup-window',
            width: '75%',
            title: $.mage.__('Translate'),
            height: 470,
            position: {
                my: 'left top',
                at: 'center top',
                of: 'body'
            },
            buttons: [{
                text: $.mage.__('Submit'),
                'class': 'action-primary',

                /**
                 * Click
                 */
                click: function () {
                    $(this).translateInline('submit');
                }
            },
            {
                text: $.mage.__('Close'),
                'class': 'action-close',

                /**
                 * Click.
                 */
                click: function () {
                    $(this).translateInline('close');
                }
            }],

            /**
             * Open.
             */
            open: function () {
                var $uiDialog = $(this).closest('.ui-dialog'),
                    topMargin = $uiDialog.children('.ui-dialog-titlebar').outerHeight() + 45;

                $uiDialog
                    .addClass('ui-dialog-active')
                    .css('margin-top', topMargin);
            },

            /**
             * Close.
             */
            close: function () {
                $(this).closest('.ui-dialog').removeClass('ui-dialog-active');
            }
        },

        /**
         * Translate Inline creation
         * @protected
         */
        _create: function () {
            var $translateArea = $(this.options.translateArea);

            if (!$translateArea.length) {
                $translateArea = $('body');
            }
            $translateArea.on('edit.editTrigger', $.proxy(this._onEdit, this));

            this.tmpl = mageTemplate(this.options.translateForm.template);

            this._super();
        },

        /**
         * @param {*} templateData
         * @return {*|jQuery|HTMLElement}
         * @private
         */
        _prepareContent: function (templateData) {
            var data = $.extend({
                items: templateData,
                escape: miscUtils.escape
            }, this.options.translateForm.data);

            this.data = data;

            return $(this.tmpl({
                data: data
            }));
        },

        /**
         * Render translation form and open dialog
         * @param {Object} e - object
         * @protected
         */
        _onEdit: function (e) {
            this.target = e.target;
            this.element.html(this._prepareContent($(e.target).data('translate')));
            this.open(e);
        },

        /**
         * Submit.
         */
        submit: function () {
            if (this.formIsSubmitted) {
                return;
            }
            this._formSubmit();
        },

        /**
         * Send ajax request on form submit
         * @protected
         */
        _formSubmit: function () {
            var parameters = $.param({
                    area: this.options.area
                }) + '&' + $('#' + this.options.translateForm.data.id).serialize();

            this.formIsSubmitted = true;

            $.ajax({
                url: this.options.ajaxUrl,
                type: 'POST',
                data: parameters,
                loaderContext: this.element,
                showLoader: true
            }).always($.proxy(this._formSubmitComplete, this));
        },

        /**
         * @param {Object} response
         * @private
         */
        _formSubmitComplete: function (response) {
            this.close();
            this.formIsSubmitted = false;
            this._updatePlaceholder(response.responseJSON[this.data.items[0].original]);
        },

        /**
         * @param {*} newValue
         * @private
         */
        _updatePlaceholder: function (newValue) {
            var $target = $(this.target),
                translateObject = $target.data('translate')[0];

            translateObject.shown = newValue;
            translateObject.translated = newValue;

            $target.html(newValue);
        },

        /**
         * Destroy translateInline
         */
        destroy: function () {
            this.element.off('.editTrigger');
            this._super();
        }
    });

    return $.mage.translateInline;
});
