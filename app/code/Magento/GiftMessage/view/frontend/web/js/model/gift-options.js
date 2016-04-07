/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(['underscore', 'ko'],
    function (_, ko) {

        'use strict';

        return {
            options: ko.observableArray([]),
            addOption: function (option) {
                if (!this.options().hasOwnProperty(option.itemId)) {
                    this.options.push({
                            id: option.itemId, value: option
                        }
                    );
                }
            },
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
    }
);
