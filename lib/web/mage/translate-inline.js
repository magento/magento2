/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true*/
(function (root, factory) {
    'use strict';

    if (typeof define === 'function' && define.amd) {
        define([
            "jquery",
            "mage/template",
            "jquery/ui",
            "mage/translate"
        ], factory);
    } else {
        factory(root.jQuery, root.mageTemplate);
    }
}(this, function ($, mageTemplate) {
    'use strict';

    $.widget("mage.translateInline", $.ui.dialog, {
        options: {
            translateForm: {
                template: '#translate-form-template',
                data: {
                    id: 'translate-inline-form',
                    message: 'Please refresh the page to see your changes after submitting this form.'
                }
            },
            autoOpen : false,
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
                click: function(e) {
                    $(this).translateInline('submit');
                }
            },
            {
                text: $.mage.__('Close'),
                'class': 'action-close',
                click: function() {
                    $(this).translateInline('close');
                }
            }],
            open: function () {
                $(this).closest('.ui-dialog').addClass('ui-dialog-active');

                var topMargin = jQuery(this).closest('.ui-dialog').children('.ui-dialog-titlebar').outerHeight() + 45;
                jQuery(this).closest('.ui-dialog').css('margin-top', topMargin);
            },
            close: function () {
                $(this).closest('.ui-dialog').removeClass('ui-dialog-active');
            }
        },
        /**
         * Translate Inline creation
         * @protected
         */
        _create: function() {
            this.tmpl = mageTemplate(this.options.translateForm.template);
            (this.options.translateArea && $(this.options.translateArea).length ?
                $(this.options.translateArea) :
                this.element.closest('body'))
                    .on('edit.editTrigger', $.proxy(this._onEdit, this));
            this._super();
        },

        _prepareContent: function(templateData) {
            var data = $.extend({
                items: templateData,
                escape: $.mage.escapeHTML
            }, this.options.translateForm.data);

            return $(this.tmpl({
                data: data
            }));
        },

        /**
         * Render translation form and open dialog
         * @param {Object} event object
         * @protected
         */
        _onEdit: function(e) {
            this.element.html(this._prepareContent($(e.target).data('translate')));
            this.open(e);
        },

        submit: function() {
            if (this.formIsSubmitted) {
                return;
            }
            this._formSubmit();
        },
        /**
         * Send ajax request on form submit
         * @protected
         */
        _formSubmit: function() {
            this.formIsSubmitted = true;
            var parameters = $.param({area: this.options.area}) +
                '&' + $('#' + this.options.translateForm.data.id).serialize();

            $.ajax({
                url: this.options.ajaxUrl,
                type: 'POST',
                data: parameters,
                loaderContext: this.element,
                showLoader: true
            }).complete($.proxy(this._formSubmitComplete, this));
        },

        _formSubmitComplete: function() {
            this.close();
            this.formIsSubmitted = false;
        },

        /**
         * Destroy translateInline
         */
        destroy: function() {
            this.element.off('.editTrigger');
            this._super();
        }
    });
    /*
     * @TODO move the "escapeHTML" method into the file with global utility functions
     */
    $.extend(true, $, {
        mage: {
            escapeHTML: function(str) {
                return str ?
                    jQuery('<div/>').text(str).html().replace(/"/g, '&quot;'):
                    false;
            }
        }
    });

    $.widget('ui.button', $.ui.button, {
        _create: function () {
            this._super();
            /**
             * Decode HTML entities to prevent incorrect rendering of dialog button label
             */
            this.options.label = this.options.label
                ? jQuery('<div/>').html(this.options.label).text()
                : this.options.label;
            /**
             * Reset button to make decoded label visible
             */
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
