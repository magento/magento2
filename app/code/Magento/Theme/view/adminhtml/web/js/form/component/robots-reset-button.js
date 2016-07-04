/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/components/button',
    'uiRegistry'
], function (Button, registry) {
    'use strict';

    return Button.extend({
        defaults: {
            customInstructionField: '${ $.parentName }.custom_instructions',
            label: '',
            buttonTpl: 'ui/form/element/button'
        },

        /**
         * @param {String} json
         * @private
         */
        _parseJson: function (json) {
            return JSON.parse(json);
        },

        /**
         * @param {String} defaultRobotsTxt
         */
        reset: function (defaultRobotsTxt) {
            var customInstructions = registry.get(this.customInstructionField);

            if (customInstructions) {
                customInstructions.set('value', this._parseJson(defaultRobotsTxt));
            }
        }
    });
});
