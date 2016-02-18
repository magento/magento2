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
    'uiRegistry',
    'mage/translate'
], function (ko, utils, _, layout, uiCollection, registry, $t) {
    'use strict';

    return uiCollection.extend({
        defaults: {
            defaultRecord: false,
            columnsHeader: true,
            columnsHeaderAfterRender: false,
            labels: [],
            recordTemplate: 'record',
            collapsibleHeader: false,
            additionalClasses: {},
            visible: true,
            disabled: false,
            fit: false,
            addButton: true,
            addButtonLabel: $t('Add'),
            recordData: [],
            recordIterator: 0,
            maxPosition: 0,
            deleteProperty: 'delete',
            identificationProperty: 'record_id',
            deleteValue: true,
            dndConfig: {
                name: '${ $.name }_dnd',
                component: 'Magento_Ui/js/dynamic-rows/dnd',
                template: 'ui/dynamic-rows/cells/dnd',
                recordsProvider: '${ $.name }',
                enabled: true
            },
            templates: {
                record: {
                    parent: '${ $.$data.collection.name }',
                    name: '${ $.$data.index }',
                    dataScope: '${ $.$data.collection.index }.${ $.name }',
                    nodeTemplate: '${ $.parent }.${ $.$data.collection.recordTemplate }'
                }
            },
            links: {
                recordData: '${ $.provider }:${ $.dataScope }.${ $.index }'
            },
            listens: {
                visible: 'setVisible',
                disabled: 'setDisabled',
                childTemplate: 'initHeader',
                recordTemplate: 'onUpdateRecordTemplate'
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
                .initChildren()
                .initDnd()
                .isColumnsHeader()
                .initDefaultRecord();

            return this;
        },

        /**
         * Calls 'initObservable' of parent
         *
         * @returns {Object} Chainable.
         */
        initObservable: function () {
            this._super()
                .track('childTemplate')
                .observe([
                    'recordData',
                    'columnsHeader',
                    'visible',
                    'disabled',
                    'labels'
                ]);

            return this;
        },

        /**
         * Init DND module
         *
         * @returns {Object} Chainable.
         */
        initDnd: function () {
            if (this.dndConfig.enabled) {
                layout([this.dndConfig]);
            }

            return this;
        },

        /**
         * Check columnsHeaderAfterRender property,
         * and set listener on elems if needed
         *
         * @returns {Object} Chainable.
         */
        isColumnsHeader: function () {
            if (this.columnsHeaderAfterRender) {
                this.on('elems', this.renderColumnsHeader.bind(this));
            }

            return this;
        },

        /**
         * Render column header
         */
        renderColumnsHeader: function () {
            this.elems().length ? this.columnsHeader(true) : this.columnsHeader(false);
        },

        /**
         * Init default record
         */
        initDefaultRecord: function () {
            if (this.defaultRecord && !this.recordData().length) {
                this.addChild();
            }
        },

        /**
         * Create header template
         *
         * @param {Object} prop - instance obj
         *
         * @returns {Object} Chainable.
         */
        createHeaderTemplate: function (prop) {
            var visible = _.isUndefined(prop.visible) ? this.visible() : prop.visible,
                disabled = _.isUndefined(prop.disabled) ? this.disabled() : prop.disabled;

            return {
                visible: ko.observable(visible),
                disabled: ko.observable(disabled)
            };
        },

        /**
         * Init header elements
         */
        initHeader: function () {
            var data;

            if (!this.labels().length) {
                _.each(this.childTemplate.children, function (cell) {
                    data = this.createHeaderTemplate(cell.config);

                    cell.config.labelVisible = false;
                    _.extend(data, {
                        label: cell.config.label,
                        name: cell.name
                    });

                    this.labels.push(data);
                }, this);
            }
        },

        /**
         * Set max element position
         *
         * @param {Number} position - element position
         * @param {Object} elem - instance
         */
        setMaxPosition: function (position, elem) {
            if (position) {
                this.checkMaxPosition(position);
                this.sort(position, elem);
            } else {
                this.maxPosition += 1;
            }
        },

        /**
         * Sort element by position
         *
         * @param {Number} position - element position
         * @param {Object} elem - instance
         */
        sort: function (position, elem) {
            var that = this,
                sorted,
                updatedCollection;

            if (!elem.containers.length) {
                registry.get(elem.name, function () {
                    that.sort(position, elem);
                });

                return false;
            }

            sorted = this.elems().sort(function (propOne, propTwo) {
                return parseInt(propOne.position, 10) - parseInt(propTwo.position, 10);
            });
            updatedCollection = this.updatePosition(sorted, position, elem.name);
            this.elems(updatedCollection);
        },

        /**
         * Check dependency and set position to elements
         *
         * @param {Array} collection - elems
         * @param {Number} position - current position
         * @param {String} elemName - element name
         *
         * @returns {Array} collection
         */
        updatePosition: function (collection, position, elemName) {
            var curPos,
                parsePosition = parseInt(position, 10),
                result = _.filter(collection, function (record) {
                    return parseInt(record.position, 10) === parsePosition;
                });

            if (result[1]) {
                curPos = parsePosition + 1;
                result[0].name === elemName ? result[1].position = curPos : result[0].position = curPos;
                this.updatePosition(collection, curPos);
            }

            return collection;
        },

        /**
         * Check max elements position and set if max
         *
         * @param {Number} position - current position
         */
        checkMaxPosition: function (position) {
            var max = 0,
                pos;

            this.elems.each(function (record) {
                pos = parseInt(record.position, 10);
                pos > max ? max = pos : false;
            });

            max < position ? max = position : false;
            this.maxPosition = max;
        },

        /**
         * Remove and set new max position
         */
        removeMaxPosition: function () {
            this.maxPosition = 0;
            this.elems.each(function (record) {
                this.maxPosition < record.position ? this.maxPosition = parseInt(record.position, 10) : false;
            }, this);
        },

        /**
         * Update record template and rerender elems
         *
         * @param {String} recordName - record name
         */
        onUpdateRecordTemplate: function (recordName) {
            if (recordName) {
                this.recordTemplate = recordName;
                this.reload();
            }
        },

        /**
         * Delete record
         *
         * @param {Number} index - row index
         *
         */
        deleteRecord: function (index, recordId) {
            var recordInstance,
                lastRecord,
                recordsData;

            if (this.deleteProperty) {
                recordInstance = _.find(this.elems(), function (elem) {
                    return elem.index === index;
                });
                recordInstance.destroy();
                this.removeMaxPosition();
                this.recordData()[recordInstance.index][this.deleteProperty] = this.deleteValue;
                this.recordData.valueHasMutated();
            } else {
                lastRecord =
                    _.findWhere(this.elems(), {
                        index: this.recordIterator - 1
                    }) ||
                    _.findWhere(this.elems(), {
                        index: (this.recordIterator - 1).toString()
                    });
                lastRecord.destroy();
                this.removeMaxPosition();
                recordsData = this._getDataByProp(recordId);
                this._updateData(recordsData);
                this._sortAfterDelete();
                --this.recordIterator;
            }
        },

        /**
         * Get data object by some property
         *
         * @param {Number} id - element id
         * @param {String} prop - property
         */
        _getDataByProp: function (id, prop) {
            prop = prop || this.identificationProperty;

            return _.reject(this.source.get(this.dataScope + '.' + this.index), function (recordData) {
                return parseInt(recordData[prop], 10) === parseInt(id, 10);
            }, this);
        },

        /**
         * Sort elems by position property
         */
        _sortAfterDelete: function () {
            this.elems(this.elems().sort(function (propOne, propTwo) {
                return parseInt(propOne.position, 10) - parseInt(propTwo.position, 10);
            }));
        },

        /**
         * Set new data to dataSource,
         * delete element
         *
         * @param {Object} data - record data
         */
        _updateData: function (data) {
            var elems = utils.copy(this.elems()),
                path;

            this.recordData([]);
            elems = utils.copy(this.elems());
            data.each(function (rec, idx) {
                elems[idx].recordId = rec[this.identificationProperty];
                path = this.dataScope + '.' + this.index + '.' + idx;
                this.source.set(path, rec);
            }, this);

            this.elems(elems);
        },

        /**
         * Rerender dynamic-rows elems
         */
        reload: function () {
            this.clear();
            this.initChildren(false, true);
        },

        /**
         * Destroy all dynamic-rows elems
         *
         * @returns {Object} Chainable.
         */
        clear: function () {
            this.elems.each(function (elem) {
                elem.destroy();
            }, this);
            this.recordIterator = 0;

            return this;
        },

        /**
         * Reset data to initial value.
         * Call method reset on child elements.
         */
        reset: function () {
            var elems = this.elems();

            _.each(elems, function (elem) {
                if (_.isFunction(elem.reset)) {
                    elem.reset();
                }
            });
        },

        /**
         * Set classes
         *
         * @param {Object} data
         *
         * @returns {Object} Classes
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
                '_fit': data.fit,
                '_required': data.required,
                '_error': data.error
            });

            return data.additionalClasses;
        },

        /**
         * Initialize children
         */
        initChildren: function () {
            this.recordData.each(this.addChild, this);

            return this;
        },

        /**
         * Set visibility to dynamic-rows child
         *
         * @param {Boolean} state
         */
        setVisible: function (state) {
            this.elems.each(function (record) {
                record.setVisible(state);
            }, this);
        },

        /**
         * Set disabled property to dynamic-rows child
         *
         * @param {Boolean} state
         */
        setDisabled: function (state) {
            this.elems.each(function (record) {
                record.setDisabled(state);
            }, this);
        },

        /**
         * Set visibility to column
         *
         * @param {Number} index - column index
         * @param {Boolean} state
         */
        setVisibilityColumn: function (index, state) {
            this.elems.each(function (record) {
                record.setVisibilityColumn(index, state);
            }, this);
        },

        /**
         * Set disabled property to column
         *
         * @param {Number} index - column index
         * @param {Boolean} state
         */
        setDisabledColumn: function (index, state) {
            this.elems.each(function (record) {
                record.setDisabledColumn(index, state);
            }, this);
        },

        /**
         * Add child components
         *
         * @param {Object} data - component data
         * @param {Number} index - record(row) index
         *
         * @returns {Object} Chainable.
         */
        addChild: function (data, index, prop) {
            var template = this.templates.record,
                child;

            index = !index && !_.isNumber(index) ? this.recordIterator : index;
            prop = _.isNumber(prop) ? prop : index;

            _.extend(this.templates.record, {
                recordId: prop
            });

            child = utils.template(template, {
                collection: this,
                index: index
            });

            ++this.recordIterator;
            layout([child]);

            return this;
        }
    });
});
