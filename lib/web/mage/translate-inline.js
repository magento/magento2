/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

(function (root, factory) {
    'use strict';

    if (typeof define === 'function' && define.amd) {
        define([
            'jquery',
            'mage/template',
            'jquery/ui',
            'mage/translate'
        ], factory);
    } else {
        factory(root.jQuery, root.mageTemplate);
    }
}(this, function ($, mageTemplate) {
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
                var topMargin;

                $(this).closest('.ui-dialog').addClass('ui-dialog-active');
                topMargin = jQuery(this).closest('.ui-dialog').children('.ui-dialog-titlebar').outerHeight() + 45;
                jQuery(this).closest('.ui-dialog').css('margin-top', topMargin);
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
            this.tmpl = mageTemplate(this.options.translateForm.template);
            (this.options.translateArea && $(this.options.translateArea).length ?
                $(this.options.translateArea) :
                this.element.closest('body'))
                    .on('edit.editTrigger', $.proxy(this._onEdit, this));
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
                escape: $.mage.escapeHTML
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
            var parameters;

            this.formIsSubmitted = true;
            parameters = $.param({
                area: this.options.area
            }) + '&' + $('#' + this.options.translateForm.data.id).serialize();

            $.ajax({
                url: this.options.ajaxUrl,
                type: 'POST',
                data: parameters,
                loaderContext: this.element,
                showLoader: true
            }).complete($.proxy(this._formSubmitComplete, this));
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
            var target = jQuery(this.target);

            target.data('translate')[0].shown = newValue;
            target.data('translate')[0].translated = newValue;
            target.html(newValue);
        },

        /**
         * Destroy translateInline
         */
        destroy: function () {
            this.element.off('.editTrigger');
            this._super();
        }
    });
    // @TODO move the "escapeHTML" method into the file with global utility functions
    $.extend(true, $, {
        mage: {
            /**
             * @param {String} str
             * @return {Boolean}
             */
            escapeHTML: function (str) {
                return str ?
                    jQuery('<div/>').text(str).html().replace(/"/g, '&quot;') :
                    false;
            }
        }
    });

    $.widget('ui.button', $.ui.button, {
        /**
         * @private
         */
        _create: function () {
            this._super();
            // Decode HTML entities to prevent incorrect rendering of dialog button label
            this.options.label = this.options.label ?
                jQuery('<div/>').html(this.options.label).text() : this.options.label;
            //Reset button to make decoded label visible
            this._resetButton();
        }
    });

    $.widget('ui.dialog', $.ui.dialog, {
        /**
         * Prevent rendering of dialog title as escaped HTML
         */
        _title: function (title) {
            this._super(title);

            if (this.options.title) {
                title.html(this.options.title);
            }
        }
    });

    return $.mage.translateInline;
}));
