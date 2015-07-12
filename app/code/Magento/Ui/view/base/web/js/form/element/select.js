/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'mageUtils',
    'uiRegistry',
    './abstract',
    'Magento_Ui/js/core/renderer/layout'
], function (_, utils, registry, Abstract, layout) {
    'use strict';

    var inputNode = {
        parent: '${ $.$data.parentName }',
        type: 'form.input',
        name: '${ $.$data.index }_input',
        dataScope: '${ $.$data.customEntry }',
        customScope: '${ $.$data.customScope }',
        sortOrder: {
            after: '${ $.$data.name }'
        },
        displayArea: 'body',
        label: '${ $.$data.label }'
    };

    /**
     * Parses incoming options, considers options with undefined value property
     *     as caption
     *
     * @param  {Array} nodes
     * @return {Object}
     */
    function parseOptions(nodes) {
        var caption,
            value;

        nodes = _.map(nodes, function (node) {
            value = node.value;

            if (value == null || value === '') {
                if (_.isUndefined(caption)) {
                    caption = node.label;
                }
            } else {
                return node;
            }
        });

        return {
            options: _.compact(nodes),
            caption: caption || false
        };
    }

    /**
     * Recursively loops over data to find non-undefined, non-array value
     *
     * @param  {Array} data
     * @return {*} - first non-undefined value in array
     */
    function findFirst(data) {
        var value;

        data.some(function (node) {
            value = node.value;

            if (Array.isArray(value)) {
                value = findFirst(value);
            }

            return !_.isUndefined(value);
        });

        return value;
    }

    function indexOptions(data, result) {
        var value;

        result = result || {};

        data.forEach(function (item) {
            value = item.value;

            if (Array.isArray(value)) {
                indexOptions(value, result);
            } else {
                result[value] = item;
            }
        });

        return result;
    }

    return Abstract.extend({
        defaults: {
            customName: '${ $.parentName }.${ $.index }_input'
        },

        /**
         * Extends instance with defaults, extends config with formatted values
         *     and options, and invokes initialize method of AbstractElement class.
         *     If instance's 'customEntry' property is set to true, calls 'initInput'
         */
        initialize: function () {
            this._super();

            if (this.customEntry) {
                this.initInput();
            }

            if (this.filterBy) {
                this.initFilter();
            }

            return this;
        },

        /**
         * Parses options and merges the result with instance
         *
         * @param  {Object} config
         * @returns {Select} Chainable.
         */
        initConfig: function (config) {
            var result = parseOptions(config.options);

            if (config.caption) {
                delete result.caption;
            }

            _.extend(config, result);

            this._super();

            return this;
        },

        /**
         * Calls 'initObservable' of parent, initializes 'options' and 'initialOptions'
         *     properties, calls 'setOptions' passing options to it
         *
         * @returns {Select} Chainable.
         */
        initObservable: function () {
            this._super();

            this.initialOptions = this.options;

            this.observe('options')
                .setOptions(this.options());

            return this;
        },

        initFilter: function () {
            var filter = this.filterBy;

            this.filter(this.default, filter.field);
            this.setLinks({
                filter: filter.target
            }, 'imports');

            return this;
        },

        /**
         * Creates input from template, renders it via renderer.
         *
         * @returns {Select} Chainable.
         */
        initInput: function () {
            layout([utils.template(inputNode, this)]);

            return this;
        },

        /**
         * Calls 'getInitialValue' of parent and if the result of it is not empty
         * string, returs it, else returnes caption or first found option's value
         *
         * @returns {Number|String}
         */
        getInitialValue: function () {
            var value = this._super();

            if (value !== '') {
                return value;
            }

            if (!this.caption) {
                return findFirst(this.options);
            }
        },

        /**
         * Filters 'initialOptions' property by 'field' and 'value' passed,
         * calls 'setOptions' passing the result to it
         *
         * @param {*} value
         * @param {String} field
         */
        filter: function (value, field) {
            var source = this.initialOptions,
                result;

            field = field || this.filterBy.field;

            result = _.filter(source, function (item) {
                return item[field] === value;
            });

            this.setOptions(result);
        },

        toggleInput: function (isVisible) {
            registry.get(this.customName, function (input) {
                input.setVisible(isVisible);
            });
        },

        /**
         * Sets 'data' to 'options' observable array, if instance has
         * 'customEntry' property set to true, calls 'setHidden' method
         *  passing !options.length as a parameter
         *
         * @param {Array} data
         * @returns {Select} Chainable.
         */
        setOptions: function (data) {
            var isVisible;

            this.indexedOptions = indexOptions(data);

            this.options(data);

            if (this.customEntry) {
                isVisible = !!data.length;

                this.setVisible(isVisible);
                this.toggleInput(!isVisible);
            }

            return this;
        },

        /**
         * Processes preview for option by it's value, and sets the result
         * to 'preview' observable
         *
         * @param {String} value
         * @returns {Select} Chainable.
         */
        getPreview: function () {
            var value = this.value(),
                option = this.indexedOptions[value],
                preview = option ? option.label : '';

            this.preview(preview);

            return preview;
        }
    });
});
