/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/lib/ko/scope',
    'underscore'
], function (Scope, _) {
    'use strict';
    
    return Scope.extend({

        /**
         * Extends instance with data passed.
         * @param {Object} data - Item of "fields" array from grid configuration
         * @param {Object} config - Filter configuration
         */
        initialize: function (data, config) {
            _.extend(this, data);
            this.config = config;

            this.observe('output', '');
        },

        isEmpty: function () {},

        /**
         * Returns alias for filter item template
         * @return {String}
         */
        getTemplate: function () {
            return this.module + '/filter/' + this.type;
        }
    });
});