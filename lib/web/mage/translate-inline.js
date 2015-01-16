/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true */
(function (factory) {
    if (typeof define === 'function' && define.amd) {
        define([
            "jquery",
            "jquery/ui",
            "jquery/template",
            "mage/translate"
        ], factory);
    } else {
        factory(jQuery);
    }
}(function ($) {
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
            dialogClass: "dialog",
            width: 650,
            title: $.mage.__('Translate'),
            height: 470,
            zIndex: 2100,
            buttons: [{
                text: $.mage.__('Submit'),
                'class': 'form-button button',
                click: function(e) {
                    $(this).translateInline('submit');
                }
            },
            {
                text: $.mage.__('Close'),
                'class': 'form-button button',
                click: function() {
                    $(this).translateInline('close');
                }
            }]
        },
        /**
         * Translate Inline creation
         * @protected
         */
        _create: function() {
            $(this.options.translateForm.template).template('translateInline');
            (this.options.translateArea && $(this.options.translateArea).length ?
                $(this.options.translateArea) :
                this.element.closest('body'))
                    .on('edit.editTrigger', $.proxy(this._onEdit, this));
            this._super();
        },

        _prepareContent: function(templateData) {
            return $.tmpl("translateInline", $.extend({
                items: templateData,
                escape: $.mage.escapeHTML
            }, this.options.translateForm.data));
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

    return $.mage.translateInline;
}));
