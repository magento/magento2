/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(['underscore'],
    function (_) {
        "use strict";
        return {
            options: [],
            addOption: function(option) {
                if(!this.options.hasOwnProperty(option.itemId)) {
                    this.options[option.itemId] = option;
                }
            },
            getOptionByItemId: function(itemId) {
                return this.options.hasOwnProperty(itemId) ? this.options[itemId] : null;
            }
        }
    }
);
