/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/**
 * Is being used by knockout template engine to store template to.
 */
define([
    'ko',
    'uiClass'
], function (ko, Class) {
    'use strict';

    return Class.extend({

        /**
         * Initializes templateName, _data, nodes properties.
         *
         * @param  {template} template - identifier of template
         */
        initialize: function (template) {
            this.templateName = template;
            this._data = {};
            this.nodes = ko.observable([]);
        },

        /**
         * Data setter. If only one arguments passed, returns corresponding value.
         * Else, writes into it.
         * @param  {String} key - key to write to or to read from
         * @param  {*} value
         * @return {*} - if 1 arg provided, returnes _data[key] property
         */
        data: function (key, value) {
            if (arguments.length === 1) {
                return this._data[key];
            }

            this._data[key] = value;
        }
    });
});
