/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'uiRegistry',
    './abstract'
], function (_, registry, Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            imports: {
                update: '${ $.parentName }.country_id:value'
            }
        },

        /**
         * @param {String} value
         */
        update: function (value) {
            var country = registry.get(this.parentName + '.' + 'country_id'),
                regionSelect = registry.get(this.parentName + '.' + 'region_id'),
                options = country.indexedOptions,
                option;

            if (!value) {
                this.setVisible(false);
                return;
            }

            option = options[value];

            if (regionSelect.visible()) {
                this.setVisible(false);
            } else {
                this.setVisible(true);
            }
        }
    });
});
