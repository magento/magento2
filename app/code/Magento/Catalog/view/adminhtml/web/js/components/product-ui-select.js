/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/ui-select',
    'jquery',
    'underscore'
], function (Select, $, _) {
    'use strict';

    return Select.extend({
        defaults: {
            validationUrl: false
        },

        /**
         * Get selected element labels
         *
         * @returns {Array} array labels
         */
        getSelected: function () {
            var options = this._super();

            if (this.validationUrl && _.isEmpty(options)) {
                $.ajax({
                    url: this.validationUrl,
                    type: 'GET',
                    dataType: 'json',
                    context: this,
                    data: {
                        productId: this.value()
                    },

                    /** @param {Object} response */
                    success: function (response) {
                        if (!_.isEmpty(response)) {
                            options = [response];
                        }
                    },

                    /** set empty array if error occurs */
                    error: function () {
                        options = [];
                    }
                });
            }

            return options;
        }
    });
});
