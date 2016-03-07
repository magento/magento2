/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/form/element/abstract',
    'uiRegistry'
], function (Acstract, rg) {
    'use strict';

    return Acstract.extend({
        defaults: {
            prefixName: '',
            prefixElementName: '',
            elementName: '',
            suffixName: ''
        },

        /**
         * Parses options and merges the result with instance
         *
         * @param  {Object} config
         * @returns {Object} Chainable.
         */
        initConfig: function (config) {
            var recordId;

            this._super();
            recordId = rg.get(this.parentName).recordId;
            this.elementName = this.prefixElementName + recordId;
            this.inputName = this.prefixName + '[' + this.elementName + ']' + this.suffixName;

            return this;
        }
    });
});
