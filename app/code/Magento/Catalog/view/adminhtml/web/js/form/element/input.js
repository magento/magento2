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
         * @returns {Object} Chainable.
         */
        initConfig: function () {
            this._super();
            this.configureDataScope();

            return this;
        },

        /**
         * Configure data scope.
         */
        configureDataScope: function () {
            var recordId,
                prefixName,
                suffixName;

            // Get recordId
            recordId = this.parentName.split('.').last();

            prefixName = this.dataScopeToHtmlArray(this.prefixName);
            this.elementName = this.prefixElementName + recordId;

            suffixName = '';
            if (!_.isEmpty(this.suffixName) || _.isNumber(this.suffixName)) {
                suffixName = '[' + this.suffixName + ']';
            }
            this.inputName = prefixName + '[' + this.elementName + ']' + suffixName;

            suffixName = '';
            if (!_.isEmpty(this.suffixName) || _.isNumber(this.suffixName)) {
                suffixName = '.' + this.suffixName;
            }
            this.dataScope = 'data.' + this.prefixName + '.' + this.elementName + this.suffixName;

            this.links.value = this.provider + ':' + this.dataScope;
        },

        /**
         * Get HTML array from data scope.
         *
         * @param {String} dataScopeString
         * @returns {String}
         */
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
