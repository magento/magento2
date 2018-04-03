/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/form/element/select',
    'underscore',
    'uiRegistry'
], function (Select, _, registry) {
    'use strict';

    return Select.extend({

        /**
         * @inheritdoc
         */
        initialize: function () {
            this._super();
            var self = this;
            var provider = registry.get(this.provider);
            var optionArray = [];

            if (provider.data.sourceCodes) {
                optionArray = provider.data.sourceCodes;
            }

            _.each(optionArray, function (label, value) {
                self.options.push({
                    value: value,
                    label: label
                });
            });

            return this;
        }
    });
});
