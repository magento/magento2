/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/components/fieldset',
    'Magento_Ui/js/core/app'
], function (Fieldset, app) {
    'use strict';

    return Fieldset.extend({
        defaults: {
            listens: {
                '${ $.provider }:additionalAttributes': 'onAttributeAdd'
            }
        },

        /**
         * On attribute add trigger
         *
         * @param {Object} listOfNewAttributes
         */
        onAttributeAdd: function (listOfNewAttributes) {
            app(listOfNewAttributes, true);
        }
    });
});
