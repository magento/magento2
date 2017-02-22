/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint jquery:true browser:true*/
/*global Ajax:true alert:true*/
define([
    "jquery",
    'Magento_Ui/js/modal/alert',
    "mage/backend/form",
    "jquery/ui",
    "prototype"
], function($, alert){
    "use strict";

    $.widget("mage.categoryForm", $.mage.form, {
        options: {
            categoryIdSelector : 'input[name="general[id]"]',
            categoryPathSelector : 'input[name="general[path]"]'
        },

        /**
         * Form creation
         * @protected
         */
        _create: function() {
            this._super();
            $('body').on('categoryMove.tree', $.proxy(this.refreshPath, this));
        },

        /**
         * Sending ajax to server to refresh field 'general[path]'
         * @protected
         */
        refreshPath: function() {
            if (!this.element.find(this.options.categoryIdSelector).prop('value')) {
                return false;
            }
            // @TODO delete this prototype functional
            new Ajax.Request(
                this.options.refreshUrl,
                {
                    method:     'POST',
                    evalScripts: true,
                    onSuccess: this._refreshPathSuccess.bind(this)
                }
            );
        },

        /**
         * Refresh field 'general[path]' on ajax success
         * @param {Object} The XMLHttpRequest object returned by ajax
         * @protected
         */
        _refreshPathSuccess: function(transport) {
            if (transport.responseText.isJSON()) {
                var response = transport.responseText.evalJSON();
                if (response.error) {
                    alert({
                        content: response.message
                    });
                } else {
                    if (this.element.find(this.options.categoryIdSelector).prop('value') == response.id) {
                        this.element.find(this.options.categoryPathSelector)
                            .prop('value', response.path);
                    }
                }
            }
        }
    });
    
    return $.mage.categoryForm;
});
