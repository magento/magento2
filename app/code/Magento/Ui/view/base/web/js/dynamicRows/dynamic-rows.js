/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'mageUtils',
    'underscore',
    'uiLayout',
    'uiCollection',
    'mage/utils/arrays',
    'mage/translate'
], function (ko, utils, _, layout, uiCollection, arrayUtils, $t) {
    'use strict';

    return uiCollection.extend({
        defaults: {
            visible: true,
            disabled: false,
            fit: false,
            addButton: true,
            addButtonLabel: $t('Add'),
            deleteButtonLabel: $t('Delete'),
            renderDefaultRecord: false,
            renderColumnsHeader: true,
            collapsibleHeader: false,
            defaultRowsIndex: [],
            labels: [],
            sorted: 0,
            draggable: true,
            childTemplate: '',
            updateTemplateValue: null,
            cacheElems: [],
            additionalClasses: {},
            columnVisibility: 'columnVisibility',
            rowsIterator: 0,
            previousRecordName: null,
            recordData: [],
            dndConfig: {
                name: '${ $.name }_dnd',
                component: 'Magento_Ui/js/dynamicRows/dnd',
                template: 'ui/dynamicRows/cells/dnd',
                recordsProvider: '${ $.name }',
                enabled: true
            },
            templates: {
                record: {
                    parent: '${ $.$data.collection.name }',
                    name: '${ $.$data.index }',
                    dataScope: '${ $.$data.collection.index }.${ $.name }',
                    nodeTemplate: '${ $.parent }.${ $.$data.collection.itemTemplate }'
                }
            },
            links: {
                recordData: '${ $.provider }:${ $.dataScope }.${ $.index }'
            },
            listens: {
                'childTemplate': 'childPreprocessing',
                'disabled': 'setDisabled',
                'updateTemplateValue': 'updateRecordTemplate'
            },
            modules: {
                dnd: '${ $.dndConfig.name }'
            }
        },

        /**
         * Extends instance with default config, calls initialize of parent
         * class, calls initChildren method, set observe variable.
         * Use parent "track" method - wrapper observe array
         *
         * @returns {Object} Chainable.
         */
        initialize: function () {
            this._super()
                .setDisabled()
                .initDnd()
                .initChildren();

            return this;
        },

        /**
         * Get maximal records sort order
         */
        getMaxSortOrder: function () {
            var max = 0,
                value;

            this.elems.each(function (elem) {
                value = parseInt(elem[this.curSortOrderProperty], 10);
                value > max ? max = value : false;
            }, this);

            return max;
        },

        /**
         * Initialize dnd if enabled
         *
         * @returns {Object}
         */
        initDnd: function () {
            if (this.dndConfig.enabled) {
                layout([this.dndConfig]);
            }

            return this;
        },

        /**
         * Processing records data, add visible,
         * disabled and label visible property
         */
        childPreprocessing: function () {
            var visible,
                disabled;

            _.each(this.childTemplate.children, function (cell) {
                visible = !_.isUndefined(cell.config.visible) ? cell.config.visible : true;
                disabled = !_.isUndefined(cell.config.disabled) ? cell.config.disabled : this.disabled();
                cell.config.visible = ko.observable(visible);
                cell.config.disabled = ko.observable(disabled);
                cell.config.labelVisible = false;
                _.keys(this.childTemplate.children).length !== this.labels().length ? this.labels.push(cell) : false;
            }, this);
        },

        /**
         * Calls 'initObservable' of parent, initializes 'options' and 'initialOptions'
         *     properties, calls 'setOptions' passing options to it
         *
         * @returns {Object} Chainable.
         */
        initObservable: function () {
            this._super()
                .track('childTemplate')
                .observe([
                    'visible',
                    'labels',
                    'disabled',
                    'recordData',
                    'sortedCache',
                    'updateTemplateValue'
                ]);

            return this;
        },

        /**
         * Set classes to dynamic-rows component
         *
         * @param {Array} data - array with records data
         * @returns {Object} Chainable.
         */
        setClasses: function (data) {
            var additional;

            if (_.isString(data.additionalClasses)) {
                additional = data.additionalClasses.split(' ');
                data.additionalClasses = {};

                additional.forEach(function (name) {
                    data.additionalClasses[name] = true;
                });
            }

            if (!data.additionalClasses) {
                data.additionalClasses = {};
            }

            _.extend(data.additionalClasses, {
                '_hidden': !data.visible(),
                '_fit': data.fit,
                '_required': data.required,
                '_disabled': data.disabled,
                '_error': data.error
            });

            return data.additionalClasses;
        },

        /**
         * Disabled columns
         *
         * @param {Boolean} state - enabled or disabled columns
         * @returns {Object} Chainable.
         */
        setDisabled: function (state) {
            state = _.isUndefined(state) ? this.disabled() : state;

            this.elems.each(function (row) {
                row.elems.each(function (cell) {
                    cell.disabled(state);
                }, this);
            }, this);

            return this;
        },

        /**
         * Disabled column
         *
         * @param {String|Number} index - column index
         * @param {Boolean} state - enabled or disabled
         */
        setColumnDisabled: function (index, state) {
            if (_.isNumber(parseFloat(index))) {
                this.elems.each(function (row) {
                    row.elems()[parseFloat(index)].disabled(state);
                });
            } else {
                this.elems.each(function (elem) {
                    _.find(elem.elems(), function (row) {
                        return row.index === index;
                    }).disabled(state);
                });
            }
        },

        /**
         * Set visibility column
         *
         * @param {String|Number} index - column index
         * @param {Boolean} state - show or hide
         */
        setColumnVisibility: function (index, state) {
            if (_.isNumber(parseFloat(index))) {
                this.labels()[parseFloat(index)].config.visible(state);
                this.elems.each(function (row) {
                    row.elems()[parseFloat(index)].visible(state);
                });
            } else {
                this.labels()[index].config.visible(state);
                this.elems.each(function (elem) {
                    _.find(elem.elems(), function (row) {
                        return row.index === index;
                    }).visible(state);
                });
            }
        },

        /**
         * Reset to data from server
         */
        reset: function () {
            var cache = this.cacheElems.slice();

            this._setDefaultFieldsData()
                ._elems = [];
            this.elems(this._elems);
            this.initChildren(true)
                .recordData(cache);
            this.rowsIterator = !this.cacheElems.length && this.renderDefaultRecord ?  1 : cache.length;
        },

        /**
         * Reload elems instance
         */
        reload: function () {
            this.elems.each(function (elem) {
                elem.destroy();
            }, this);
            this.rowsIterator = 0;
            this.initChildren(false, true);
        },

        /**
         * Update records template
         *
         * @param {String} childName - record name
         */
        updateRecordTemplate: function (childName) {
            this.itemTemplate = childName;
            this.reload();
        },

        /**
         * Set initialValue to fields
         *
         * @returns {Object}
         */
        _setDefaultFieldsData: function () {
            this.elems.each(function (row) {
                row.elems.each(function (field) {
                    field.reset();
                });
            });

            return this;
        },

        /**
         * Delete record (row)
         *
         * @param {Number} index - row index
         *
         */
        deleteRecord: function (index) {
            var rowInstance = _.find(this.elems(), function (elem) {
                return elem.index === index;
            });

            rowInstance.destroy();
            this.recordData()[rowInstance.index].delete = true;
            this.recordData.valueHasMutated();
        },

        /**
         * Initialize children components (records),
         *
         */
        initChildren: function (reset) {
            var data = this.recordData();

            this._cacheDefaultElems(data);

            reset ? data = this.cacheElems : false;
            this.isRenderDefault(data) ?  data.push({}) : false;

            _.each(data, this.addChild, this);

            return this;
        },

        /**
         * Check need or not render default record element
         *
         * @param {Array} data - array with records data
         */
        isRenderDefault: function (data) {
            return !data && this.renderDefaultRecord ||
                typeof data === 'object' && data.length === 0 && this.renderDefaultRecord;
        },

        /**
         * Save data from server
         *
         * @param {Array} data - array with records data
         */
        _cacheDefaultElems: function (data) {
            if (!this.cacheElems.length && !this.defaultEmpty) {
                _.isArray(data) && data.length ? this.cacheElems = data.slice() : this.defaultEmpty = true;
            }
        },

        /**
         * Add child components
         *
         * @param {Object} data - component data
         * @param {Number} index - record(row) index
         *
         * @returns {Object} Chainable.
         */
        addChild: function (data, index) {
            var template = this.templates.record,
                child;

            if (!!this.previousRecordName && this.previousRecordName !== this.itemTemplate) {
                this.previousRecordName = this.itemTemplate;
                this.rowsIterator++;
                this.reload();

                return this;
            }

            this.previousRecordName = this.itemTemplate;

            index = !index && parseFloat(index) !== 0 ? this.rowsIterator : index;

            child = utils.template(template, {
                collection: this,
                index: index
            });

            this.rowsIterator++;

            layout([child]);

            return this;
        }
    });
});
