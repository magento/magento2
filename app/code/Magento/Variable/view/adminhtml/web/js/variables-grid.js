/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global MagentovariablePlugin */
define([
    'Magento_Ui/js/grid/listing',
    'underscore',
    'Magento_Variable/js/config-directive-generator',
    'jquery'
], function (Listing, _, configGenerator, $) {
    'use strict';

    return Listing.extend({
        defaults: {
            variablesSelector: 'variable_selector'
        },
        
        /**
         * Inserts Config path into div
         *
         * @return {*}
         */
        doInsert: function () {
            var radioSelect = _.filter(this.elems(), function (elem) {
                if (elem.index === this.variablesSelector) {
                    return elem;
                }
            }, this),
            code, directive;

            code = radioSelect[0].selectedVariableCode();
            if (typeof code != 'undefined') {
                radioSelect[0].selectedVariableCode(null);
                directive = configGenerator.processConfig(code);

                return MagentovariablePlugin.insertVariable(directive);
            }
            return;
        },

        /**
         * Close dialog window.
         */
        closeModalWindow: function () {

            var dialogWindowId = 'variables-chooser';
            $('#' + dialogWindowId).modal('closeModal')
        }
    });
});
