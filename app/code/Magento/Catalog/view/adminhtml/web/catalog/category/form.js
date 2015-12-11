/**
 * Copyright © 2015 Magento. All rights reserved.
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

    return function (config) {
        var categoryForm = {
            options: {
                categoryIdSelector: 'input[name="general[id]"]',
                categoryPathSelector: 'input[name="general[path]"]'
            },

            /**
             * Sending ajax to server to refresh field 'general[path]'
             * @protected
             */
            refreshPath: function () {
                if (!$(this.options.categoryIdSelector)) {
                    return false;
                }
                // @TODO delete this prototype functional
                new Ajax.Request(
                    this.options.refreshUrl,
                    {
                        method: 'POST',
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
            _refreshPathSuccess: function (transport) {
                if (transport.responseText.isJSON()) {
                    var response = transport.responseText.evalJSON();
                    if (response.error) {
                        alert({
                            content: response.message
                        });
                    } else {
                        if ($(this.options.categoryIdSelector).val(response.id)) {
                            $(this.options.categoryPathSelector).val(response.path);
                        }
                    }
                }
            }
        };

        $('body').on('categoryMove.tree', $.proxy(categoryForm.refreshPath.bind(categoryForm), this));
    }
});