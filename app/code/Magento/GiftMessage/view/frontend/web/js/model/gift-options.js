/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'ko'
], function (_, ko) {
    'use strict';

    return {
        options: ko.observableArray([]),

        /**
         * @param {Object} option
         */
        addOption: function (option) {
            if (!this.options().hasOwnProperty(option.itemId)) {
                this.options.push({
                        id: option.itemId, value: option
                    }
                );
            }
        },

        /**
         * @param {*} itemId
         * @return {*}
         */
        getOptionByItemId: function (itemId) {
            var option = null;

            _.each(this.options(), function (data) {
                if (data.id === itemId) {
                    option = data.value;

                    return false;
                }
            });

            return option;
        }
    };
});
