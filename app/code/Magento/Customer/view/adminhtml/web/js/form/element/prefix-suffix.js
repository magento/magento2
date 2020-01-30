/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'mageUtils',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'uiLayout'
], function (_, utils, registry, Select, layout) {
    'use strict';

    var inputNode = {
        parent: '${ $.$data.parentName }',
        component: 'Magento_Ui/js/form/element/abstract',
        template: '${ $.$data.template }',
        provider: '${ $.$data.provider }',
        name: '${ $.$data.index }_input',
        dataScope: '${ $.$data.index }',
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
    function parseOptions(nodes, captionValue) {
        var caption,
            value;

        nodes = _.map(nodes, function (node) {
            value = node.value;

            if (value !== null && value !== captionValue) {
                return node;
            }

            if (_.isUndefined(caption)) {
                caption = node.label;
            }
        });

        return {
            options: _.compact(nodes),
            caption: _.isString(caption) ? caption : false
        };
    }

    /**
     * Recursively set to object item like value and item.value like key.
     *
     * @param {Array} data
     * @param {Object} result
     * @returns {Object}
     */
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

    return Select.extend({
        defaults: {
            requiredPerWebsite: []
        },

        /**
         * Creates input from template, renders it via renderer.
         *
         * @returns {Object} Chainable.
         */
        initInput: function () {
            layout([utils.template(inputNode, this)]);

            return this;
        },

        /**
         * Matches specified value with existing options
         * or, if value is not specified, returns value of the first option.
         *
         * @returns {*}
         */
        normalizeData: function (value) {
            if (this.filterBy) {
                this.initFilter();
            }

            value = utils.isEmpty(value) ? '' : value;

            if (value !== '' && Object.keys(this.indexedOptions).length === 0) {
                return value;
            }

            return this._super(value);
        },

        /**
         * Filters 'initialOptions' property by 'field' and 'value' passed,
         * calls 'setOptions' passing the result to it
         *
         * @param {*} value
         * @param {String} field
         */
        filter: function (value, field) {
            var result, defaultPrefixSuffix, defaultValue;

            if (!field) { //validate field, if we are on update
                field = this.filterBy.field;
            }

            this._super(value, field);
            result = _.filter(this.initialOptions, function (item) {

                if (item[field]) {
                    return ~item[field].indexOf(value);
                }

                return false;
            });

            this.setOptions(result);
            this.reset();

            if (!this.value()) {
                if (this.indexedOptions.length === 0) {
                    this.value(this.initialValue);

                    return;
                }

                defaultPrefixSuffix = _.filter(result, function (item) {
                    return item['is_default'] && _.contains(item['is_default'], value);
                });

                if (defaultPrefixSuffix.length) {
                    defaultValue = defaultPrefixSuffix.shift();
                    this.value(defaultValue.value);
                }
            }
        },

        /**
         * Sets 'data' to 'options' observable array, if there are no options sets current value
         * in this array to prevent clearing custom option input. If instance has
         * 'customEntry' property set to true, calls 'setHidden' method
         *  passing !options.length as a parameter
         *
         * @param {Array} data
         * @returns {Object} Chainable
         */
        setOptions: function (data) {
            var captionValue = this.captionValue || '',
                result = parseOptions(data, captionValue),
                isVisible;

            this.indexedOptions = indexOptions(result.options);

            if (result.options.length > 0) {
                this.options(result.options);

                if (!this.caption()) {
                    this.caption(result.caption);
                }
            } else {
                this.options([
                    {
                        label: this.value(),
                        value: this.value()
                    }
                ]);
            }

            if (this.customEntry) {
                isVisible = !!result.options.length;

                this.setVisible(isVisible);
                this.toggleInput(!isVisible);
            }

            return this;
        },

        /**
         * Initializes observable properties of instance
         *
         * @returns {Object} Chainable.
         */
        setInitialValue: function () {
            this.initialValue = this.getInitialValue();

            if (this.value.peek() !== this.initialValue) {
                this.value(this.initialValue);
            }

            this.on('value', this.onUpdate.bind(this));
            this.isUseDefault(this.disabled());

            return this;
        },

        /**
         * Updates if input is required based on selected website
         *
         * @param {String} websiteId
         */
        update: function (websiteId) {
            var isRequired = this.requiredPerWebsite[websiteId] === true;

            this.required(isRequired);
            registry.get(this.customName, function (input) {
                input.required(isRequired);
            });
        }
    });
});

