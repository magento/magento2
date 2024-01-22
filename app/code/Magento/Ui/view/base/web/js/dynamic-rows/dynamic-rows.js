/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'ko',
    'mageUtils',
    'underscore',
    'uiLayout',
    'uiCollection',
    'uiRegistry',
    'mage/translate',
    'jquery'
], function (ko, utils, _, layout, uiCollection, registry, $t, $) {
    'use strict';

    /**
     * Checks value type and cast to boolean if needed
     *
     * @param {*} value
     *
     * @returns {Boolean|*} casted or origin value
     */
    function castValue(value) {
        if (_.isUndefined(value) || value === '' || _.isNull(value)) {
            return false;
        }

        return value;
    }

    /**
     * Compares arrays.
     *
     * @param {Array} base - array as method bases its decision on first argument.
     * @param {Array} current - second array
     *
     * @returns {Boolean} result - is current array equal to base array
     */
    function compareArrays(base, current) {
        var index = 0,
            length = base.length;

        if (base.length !== current.length) {
            return false;
        }

        /*eslint-disable max-depth, eqeqeq, no-use-before-define */
        for (index; index < length; index++) {
            if (_.isArray(base[index]) && _.isArray(current[index])) {
                if (!compareArrays(base[index], current[index])) {
                    return false;
                }
            } else if (typeof base[index] === 'object' && typeof current[index] === 'object') {
                if (!compareObjects(base[index], current[index])) {
                    return false;
                }
            } else if (castValue(base[index]) != castValue(current[index])) {
                return false;
            }
        }/*eslint-enable max-depth, eqeqeq, no-use-before-define */

        return true;
    }

    /**
     * Compares objects. Compares only properties from origin object,
     * if current object has more properties - they are not considered
     *
     * @param {Object} base - first object
     * @param {Object} current - second object
     *
     * @returns {Boolean} result - is current object equal to base object
     */
    function compareObjects(base, current) {
        var prop;

        /*eslint-disable max-depth, eqeqeq*/
        for (prop in base) {
            if (_.isArray(base[prop]) && _.isArray(current[prop])) {
                if (!compareArrays(base[prop], current[prop])) {
                    return false;
                }
            } else if (typeof base[prop] === 'object' && typeof current[prop] === 'object') {
                if (!compareObjects(base[prop], current[prop])) {
                    return false;
                }
            } else if (castValue(base[prop]) != castValue(current[prop])) {
                return false;
            }
        }/*eslint-enable max-depth, eqeqeq */

        return true;
    }

    return uiCollection.extend({
        defaults: {
            defaultRecord: false,
            columnsHeader: true,
            columnsHeaderAfterRender: false,
            columnsHeaderClasses: '',
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
            maxPosition: 0,
            deleteProperty: 'delete',
            identificationProperty: 'record_id',
            deleteValue: true,
            showSpinner: true,
            isDifferedFromDefault: false,
            defaultState: [],
            defaultPagesState: {},
            pagesChanged: {},
            hasInitialPagesState: {},
            changed: false,
            fallbackResetTpl: 'ui/form/element/helper/fallback-reset-link',
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
                recordTemplate: 'onUpdateRecordTemplate',
                recordData: 'setDifferedFromDefault parsePagesData setRecordDataToCache',
                currentPage: 'changePage',
                elems: 'checkSpinner',
                changed: 'updateTrigger'
            },
            modules: {
                dnd: '${ $.dndConfig.name }'
            },
            pages: 1,
            pageSize: 20,
            relatedData: [],
            currentPage: 1,
            recordDataCache: [],
            startIndex: 0
        },

        /**
         * Sets record data to cache
         */
        setRecordDataToCache: function (data) {
            this.recordDataCache = data;
        },

        /**
         * Extends instance with default config, calls initialize of parent
         * class, calls initChildren method, set observe variable.
         * Use parent "track" method - wrapper observe array
         *
         * @returns {Object} Chainable.
         */
        initialize: function () {
            _.bindAll(this,
                'processingDeleteRecord',
                'onChildrenUpdate',
                'checkDefaultState',
                'renderColumnsHeader',
                'deleteHandler',
                'setDefaultState'
            );

            this._super()
                .initChildren()
                .initDnd()
                .initDefaultRecord()
                .setInitialProperty()
                .setColumnsHeaderListener()
                .checkSpinner();

            this.on('recordData', this.checkDefaultState);

            return this;
        },

        /**
         * @inheritdoc
         */
        bubble: function (event) {
            if (event === 'deleteRecord' || event === 'update') {
                return false;
            }

            return this._super();
        },

        /**
         * Inits DND module
         *
         * @returns {Object} Chainable.
         */
        initDnd: function () {
            if (this.dndConfig.enabled) {
                layout([this.dndConfig]);
            }

            return this;
        },

        /** @inheritdoc */
        destroy: function () {
            if (this.dnd()) {
                this.dnd().destroy();
            }
            this._super();
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
                    'pages',
                    'currentPage',
                    'recordData',
                    'columnsHeader',
                    'visible',
                    'disabled',
                    'labels',
                    'showSpinner',
                    'isDifferedFromDefault',
                    'changed'
                ]);

            return this;
        },

        /**
         * @inheritdoc
         */
        initElement: function (elem) {
            this._super();
            elem.on({
                'deleteRecord': this.deleteHandler,
                'update': this.onChildrenUpdate,
                'addChild': this.setDefaultState
            });

            return this;
        },

        /**
         * Handler for deleteRecord event
         *
         * @param {Number|String} index - element index
         * @param {Number|String} id
         */
        deleteHandler: function (index, id) {
            var defaultState;

            this.setDefaultState();
            defaultState = this.defaultPagesState[this.currentPage()];
            this.processingDeleteRecord(index, id);
            this.pagesChanged[this.currentPage()] =
                !compareArrays(defaultState, this.arrayFilter(this.getChildItems()));
            this.changed(_.some(this.pagesChanged));
        },

        /**
         * Set initial property to records data
         *
         * @returns {Object} Chainable.
         */
        setInitialProperty: function () {
            if (_.isArray(this.recordData())) {
                this.recordData.each(function (data, index) {
                    this.source.set(this.dataScope + '.' + this.index + '.' + index + '.initialize', true);
                }, this);
            }

            return this;
        },

        /**
         * Handler for update event
         *
         * @param {Boolean} state
         */
        onChildrenUpdate: function (state) {
            var changed,
                dataScope,
                changedElemDataScope;

            if (state && !this.hasInitialPagesState[this.currentPage()]) {
                this.setDefaultState();
                changed = this.getChangedElems(this.elems());
                dataScope = this.elems()[0].dataScope.split('.');
                dataScope.splice(dataScope.length - 1, 1);
                changed.forEach(function (elem) {
                    changedElemDataScope = elem.dataScope.split('.');
                    changedElemDataScope.splice(0, dataScope.length);
                    changedElemDataScope[0] =
                        (parseInt(changedElemDataScope[0], 10) - this.pageSize * (this.currentPage() - 1)).toString();
                    this.setValueByPath(
                        this.defaultPagesState[this.currentPage()],
                        changedElemDataScope, elem.initialValue
                    );
                }, this);
            }

            if (this.defaultPagesState[this.currentPage()]) {
                this.setChangedForCurrentPage();
            }
        },

        /**
         * Set default dynamic-rows state or state before changing data
         *
         * @param {Array} data - defaultState data
         */
        setDefaultState: function (data) {
            var componentData,
                childItems;

            if (!this.hasInitialPagesState[this.currentPage()]) {
                childItems = this.getChildItems();
                componentData = childItems.length ?
                    utils.copy(childItems) :
                    utils.copy(this.getChildItems(this.recordDataCache));
                componentData.forEach(function (dataObj) {
                    if (dataObj.hasOwnProperty('initialize')) {
                        delete dataObj.initialize;
                    }
                });

                this.hasInitialPagesState[this.currentPage()] = true;
                this.defaultPagesState[this.currentPage()] = data ? data : this.arrayFilter(componentData);
            }
        },

        /**
         * Sets value to object by string path
         *
         * @param {Object} obj
         * @param {Array|String} path
         * @param {*} value
         */
        setValueByPath: function (obj, path, value) {
            var prop;

            if (_.isString(path)) {
                path = path.split('.');
            }

            if (path.length - 1) {
                prop = obj[path[0]];
                path.splice(0, 1);
                this.setValueByPath(prop, path, value);
            } else if (path.length && obj) {
                obj[path[0]] = value;
            }
        },

        /**
         * Returns elements which changed self state
         *
         * @param {Array} array - data array
         * @param {Array} changed - array with changed elements
         * @returns {Array} changed - array with changed elements
         */
        getChangedElems: function (array, changed) {
            changed = changed || [];

            array.forEach(function (elem) {
                if (_.isFunction(elem.elems)) {
                    this.getChangedElems(elem.elems(), changed);
                } else if (_.isFunction(elem.hasChanged) && elem.hasChanged()) {
                    changed.push(elem);
                }
            }, this);

            return changed;
        },

        /**
         * Checks columnsHeaderAfterRender property,
         * and set listener on elems if needed
         *
         * @returns {Object} Chainable.
         */
        setColumnsHeaderListener: function () {
            if (this.columnsHeaderAfterRender) {
                this.on('recordData', this.renderColumnsHeader);

                if (_.isArray(this.recordData()) && this.recordData().length) {
                    this.renderColumnsHeader();
                }
            }

            return this;
        },

        /**
         * Checks whether component's state is default or not
         */
        checkDefaultState: function () {
            var isRecordDataArray = _.isArray(this.recordData()),
                initialize,
                hasNotDefaultRecords = isRecordDataArray ? !!this.recordData().filter(function (data) {
                    return !data.initialize;
                }).length : false;

            if (!this.hasInitialPagesState[this.currentPage()] && isRecordDataArray && hasNotDefaultRecords) {
                this.hasInitialPagesState[this.currentPage()] = true;
                this.defaultPagesState[this.currentPage()] = utils.copy(this.getChildItems().filter(function (data) {
                    initialize = data.initialize;
                    delete data.initialize;

                    return initialize;
                }));

                this.setChangedForCurrentPage();
            } else if (this.hasInitialPagesState[this.currentPage()]) {
                this.setChangedForCurrentPage();
            }
        },

        /**
         * Filters out deleted items from array
         *
         * @param {Array} data
         *
         * @returns {Array} filtered array
         */
        arrayFilter: function (data) {
            var prop;

            /*eslint-disable no-loop-func*/
            data.forEach(function (elem) {
                for (prop in elem) {
                    if (_.isArray(elem[prop])) {
                        elem[prop] = _.filter(elem[prop], function (elemProp) {
                            return elemProp[this.deleteProperty] !== this.deleteValue;
                        }, this);

                        elem[prop].forEach(function (elemProp) {
                            if (_.isArray(elemProp)) {
                                elem[prop] = this.arrayFilter(elemProp);
                            }
                        }, this);
                    }
                }
            }, this);

            /*eslint-enable no-loop-func*/

            return data;
        },

        /**
         * Triggers update event
         *
         * @param {Boolean} val
         */
        updateTrigger: function (val) {
            this.trigger('update', val);
        },

        /**
         * Returns component state
         */
        hasChanged: function () {
            return this.changed();
        },

        /**
         * Render column header
         */
        renderColumnsHeader: function () {
            this.recordData().length ? this.columnsHeader(true) : this.columnsHeader(false);
        },

        /**
         * Init default record
         *
         * @returns Chainable.
         */
        initDefaultRecord: function () {
            if (this.defaultRecord && !this.recordData().length) {
                this.addChild();
            }

            return this;
        },

        /**
         * Create header template
         *
         * @param {Object} prop - instance obj
         *
         * @returns {Object} Chainable.
         */
        createHeaderTemplate: function (prop) {
            var visible = prop.visible !== false,
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
            var labels = [],
                data;

            if (!this.labels().length) {
                _.each(this.childTemplate.children, function (cell) {
                    data = this.createHeaderTemplate(cell.config);
                    cell.config.labelVisible = false;
                    _.extend(data, {
                        defaultLabelVisible: data.visible(),
                        label: cell.config.label,
                        name: cell.name,
                        required: !!cell.config.validation,
                        columnsHeaderClasses: cell.config.columnsHeaderClasses,
                        sortOrder: cell.config.sortOrder
                    });
                    labels.push(data);
                }, this);
                this.labels(_.sortBy(labels, 'sortOrder'));
            }
        },

        /**
         * Set max element position
         *
         * @param {Number} position - element position
         * @param {Object} elem - instance
         */
        setMaxPosition: function (position, elem) {
            if (position || position === 0) {
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

            if (this.elems().filter(function (el) {
                    return el.position || el.position === 0;
                }).length !== this.getChildItems().length) {

                return false;
            }

            if (!elem.containers.length) {
                registry.get(elem.name, function () {
                    that.sort(position, elem);
                });

                return false;
            }

            sorted = this.elems().sort(function (propOne, propTwo) {
                return ~~propOne.position - ~~propTwo.position;
            });

            updatedCollection = this.updatePosition(sorted, position, elem.name);
            this.elems(updatedCollection);
        },

        /**
         * Checking loader visibility
         *
         * @param {Array} elems
         */
        checkSpinner: function (elems) {
            this.showSpinner(!(!this.recordData().length || elems && elems.length === this.getChildItems().length));
        },

        /**
         * Filtering data and calculates the quantity of pages
         *
         * @param {Array} data
         */
        parsePagesData: function (data) {
            this.relatedData = this.deleteProperty ?
                _.filter(data, function (elem) {
                    return elem && elem[this.deleteProperty] !== this.deleteValue;
                }, this) : data;

            this._updatePagesQuantity();
        },

        /**
         * Reinit record data in order to remove deleted values
         *
         * @return void
         */
        reinitRecordData: function () {
            this.recordData(
                _.filter(this.recordData(), function (elem) {
                    return elem && elem[this.deleteProperty] !== this.deleteValue;
                }, this)
            );
        },

        /**
         * Get items to rendering on current page
         *
         * @returns {Array} data
         */
        getChildItems: function (data, page) {
            var dataRecord = data || this.relatedData,
                startIndex;

            this.startIndex = (~~this.currentPage() - 1) * this.pageSize;

            startIndex = page || this.startIndex;

            return dataRecord.slice(startIndex, this.startIndex + parseInt(this.pageSize, 10));
        },

        /**
         * Get record count with filtered delete property.
         *
         * @returns {Number} count
         */
        getRecordCount: function () {
            return _.filter(this.recordData(), function (record) {
                return record && record[this.deleteProperty] !== this.deleteValue;
            }, this).length;
        },

        /**
         * Get number of columns
         *
         * @returns {Number} columns
         */
        getColumnsCount: function () {
            return this.labels().length + (this.dndConfig.enabled ? 1 : 0);
        },

        /**
         * Processing pages before addChild
         *
         * @param {Object} ctx - element context
         * @param {Number|String} index - element index
         * @param {Number|String} prop - additional property to element
         */
        processingAddChild: function (ctx, index, prop) {
            this.bubble('addChild', false);

            if (this.relatedData.length && this.relatedData.length % this.pageSize === 0) {
                this.pages(this.pages() + 1);
                this.nextPage();
            } else if (~~this.currentPage() !== this.pages()) {
                this.currentPage(this.pages());
            }

            this.addChild(ctx, index, prop);
        },

        /**
         * Processing pages before deleteRecord
         *
         * @param {Number|String} index - element index
         * @param {Number|String} recordId
         */
        processingDeleteRecord: function (index, recordId) {
            this.deleteRecord(index, recordId);
        },

        /**
         * Change page
         *
         * @param {Number} page - current page
         */
        changePage: function (page) {
            this.clear();

            if (page === 1 && !this.recordData().length) {
                return false;
            }

            if (~~page > this.pages()) {
                this.currentPage(this.pages());

                return false;
            } else if (~~page < 1) {
                this.currentPage(1);

                return false;
            }

            this.initChildren();

            return true;
        },

        /**
         * Check page
         *
         * @returns {Boolean} is page first or not
         */
        isFirst: function () {
            return this.currentPage() === 1;
        },

        /**
         * Check page
         *
         * @returns {Boolean} is page last or not
         */
        isLast: function () {
            return this.currentPage() === this.pages();
        },

        /**
         * Change page to next
         */
        nextPage: function () {
            this.currentPage(this.currentPage() + 1);
        },

        /**
         * Change page to previous
         */
        previousPage: function () {
            this.currentPage(this.currentPage() - 1);
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
                parsePosition = ~~position,
                result = _.filter(collection, function (record) {
                    return ~~record.position === parsePosition;
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

            this.recordData.each(function (record) {
                pos = ~~record.position;
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
                this.maxPosition < record.position ? this.maxPosition = ~~record.position : false;
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
                recordsData,
                lastRecordIndex;

            if (this.deleteProperty) {
                recordsData = this.recordData();
                recordInstance = _.find(this.elems(), function (elem) {
                    return elem.index === index;
                });
                recordInstance.destroy();
                this.elems([]);
                this._updateCollection();
                this.removeMaxPosition();
                recordsData[recordInstance.index][this.deleteProperty] = this.deleteValue;
                this.recordData(recordsData);
                this.reinitRecordData();
                this.reload();
            } else {
                this.update = true;

                if (~~this.currentPage() === this.pages()) {
                    lastRecordIndex = this.startIndex + this.getChildItems().length - 1;
                    lastRecord =
                        _.findWhere(this.elems(), {
                            index: lastRecordIndex
                        }) ||
                        _.findWhere(this.elems(), {
                            index: lastRecordIndex.toString()
                        });

                    lastRecord.destroy();
                }

                this.removeMaxPosition();
                recordsData = this._getDataByProp(recordId);
                this._updateData(recordsData);
                this.update = false;
            }

            this._reducePages();
            this._sort();
        },

        /**
         * Update number of pages.
         *
         * @private
         * @return void
         */
        _updatePagesQuantity: function () {
            var pages = Math.ceil(this.relatedData.length / this.pageSize) || 1;

            this.pages(pages);
        },

        /**
         * Reduce the number of pages
         *
         * @private
         * @return void
         */
        _reducePages: function () {
            if (this.pages() < ~~this.currentPage()) {
                this.currentPage(this.pages());
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

            return _.reject(this.getChildItems(), function (recordData) {
                return recordData[prop].toString() === id.toString();
            }, this);
        },

        /**
         * Sort elems by position property
         */
        _sort: function () {
            this.elems(this.elems().sort(function (propOne, propTwo) {
                return ~~propOne.position - ~~propTwo.position;
            }));
        },

        /**
         * Set new data to dataSource,
         * delete element
         *
         * @param {Array} data - record data
         */
        _updateData: function (data) {
            var elems = _.clone(this.elems()),
                path,
                dataArr;

            dataArr = this.recordData.splice(this.startIndex, this.recordData().length - this.startIndex);
            dataArr.splice(0, this.pageSize);
            elems = _.sortBy(this.elems(), function (elem) {
                return ~~elem.index;
            });

            data.concat(dataArr).forEach(function (rec, idx) {
                if (elems[idx]) {
                    elems[idx].recordId = rec[this.identificationProperty];
                }

                if (!rec.position) {
                    rec.position = this.maxPosition;
                    this.setMaxPosition();
                }

                path = this.dataScope + '.' + this.index + '.' + (this.startIndex + idx);
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
            this._updatePagesQuantity();

            /* After change page size need to check existing current page */
            this._reducePages();
        },

        /**
         * Update page size based on select change event.
         * The value needs to be retrieved from select as ko value handler is executed after the event handler.
         *
         * @param {Object} component
         * @param {jQuery.Event} event
         */
        updatePageSize: function (component, event) {
            this.pageSize = $(event.target).val();
            this.reload();
        },

        /**
         * Destroy all dynamic-rows elems
         *
         * @returns {Object} Chainable.
         */
        clear: function () {
            this.destroyChildren();

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
                '_error': data.error,
                '_empty': !this.elems().length,
                '_no-header': this.columnsHeaderAfterRender || this.collapsibleHeader
            });

            return data.additionalClasses;
        },

        /**
         * Initialize children
         *
         * @returns {Object} Chainable.
         */
        initChildren: function () {
            this.showSpinner(true);
            this.getChildItems().forEach(function (data, index) {
                this.addChild(data, this.startIndex + index);
            }, this);

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
         * @param {Number|String} prop - custom identify property
         *
         * @returns {Object} Chainable.
         */
        addChild: function (data, index, prop) {
            var template = this.templates.record,
                child;

            index = index || _.isNumber(index) ? index : this.recordData().length;
            prop = prop || _.isNumber(prop) ? prop : index;

            _.extend(this.templates.record, {
                recordId: prop
            });

            child = utils.template(template, {
                collection: this,
                index: index
            });

            layout([child]);

            return this;
        },

        /**
         * Restore value to default
         */
        restoreToDefault: function () {
            this.recordData(utils.copy(this.default));
            this.reload();
        },

        /**
         * Update whether value differs from default value
         */
        setDifferedFromDefault: function () {
            var recordData;

            if (this.default) {
                recordData = utils.copy(this.recordData());

                Array.isArray(recordData) && recordData.forEach(function (item) {
                    delete item['record_id'];
                });

                this.isDifferedFromDefault(!_.isEqual(recordData, this.default));
            }
        },

        /**
         * Set the changed property if the current page is different
         * than the default state
         *
         * @return void
         */
        setChangedForCurrentPage: function () {
            this.pagesChanged[this.currentPage()] =
                !compareArrays(this.defaultPagesState[this.currentPage()], this.arrayFilter(this.getChildItems()));
            this.changed(_.some(this.pagesChanged));
        }
    });
});
