/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define([
    'underscore'
], function (_) {
    'use strict';

    return {
        config: {},

        /**
         * Set configuration
         * @param {Object} config
         */
        setConfig: function (config) {
            this.config = config;
        },

        /**
         * Get List of available card types
         * @returns {*|exports.defaults.availableCardTypes|{}}
         */
        getAvailableCardTypes: function () {
            return this.config.availableCardTypes;
        },

        /**
         * Get list of card types
         * @returns {Object}
         */
        getCcTypesMapper: function () {
            return this.config.ccTypesMapper;
        },

        /**
         * Find mage card type by Braintree type
         * @param {String} type
         * @param {Object} availableTypes
         * @returns {*}
         */
        getMageCardType: function (type, availableTypes) {
            var storedCardType = null,
                mapper = this.getCcTypesMapper();

            if (type && typeof mapper[type] !== 'undefined') {
                storedCardType = mapper[type];

                if (_.indexOf(availableTypes, storedCardType) !== -1) {
                    return storedCardType;
                }
            }

            return null;
        },

        /**
         * Filter list of available card types
         * @param {Object} availableTypes
         * @param {Object} countrySpecificCardTypes
         * @returns {Object}
         */
        collectTypes: function (availableTypes, countrySpecificCardTypes) {
            var key,
                filteredTypes = [];

            for (key in availableTypes) {
                if (_.indexOf(countrySpecificCardTypes, availableTypes[key]) !== -1) {
                    filteredTypes.push(availableTypes[key]);
                }
            }

            return filteredTypes;
        },

        /**
         * Get list of card types for country
         * @param {String} countryId
         * @returns {*}
         */
        getCountrySpecificCardTypes: function (countryId) {
            if (typeof this.config.countrySpecificCardTypes[countryId] !== 'undefined') {

                return this.config.countrySpecificCardTypes[countryId];
            }

            return false;
        }
    };
});
