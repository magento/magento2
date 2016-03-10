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
            this._super();

            this.configureDataScope();

            return this;
        },

        configureDataScope: function () {
            var recordId,
                prefixName;

            // Get recordId
            recordId = this.parentName.split('.').last();

            prefixName = this.dataScopeToHtmlArray(this.prefixName);
            this.elementName = this.prefixElementName + recordId;

            this.inputName = prefixName + '[' + this.elementName + ']' + '[' + this.suffixName + ']';
            this.dataScope = 'data.' + this.prefixName + '.' + this.elementName + '.' + this.suffixName;

            this.links.value = this.provider + ':' + this.dataScope;
        },

        dataScopeToHtmlArray: function (dataScopeString) {
            var dataScopeArray, dataScope, reduceFunction;

            reduceFunction = function (prev, curr) {
                return prev + '[' + curr + ']';
            };

            dataScopeArray = dataScopeString.split('.');

            dataScope = dataScopeArray.shift();
            dataScope += dataScopeArray.reduce(reduceFunction, '');

            return dataScope;
        }
    });
});
