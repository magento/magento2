/**
 * Copyright © 2016 Magento. All rights reserved.
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
                recordData: 'setDifferedFromDefault parsePagesData',
                currentPage: 'changePage',
                elems: 'checkSpinner'
            },
            modules: {
                dnd: '${ $.dndConfig.name }'
            },
            pages: 1,
            pageSize: 20,
            relatedData: [],
            currentPage: 1,
            startIndex: 0
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
                .setColumnsHeaderListener()
                .initDefaultRecord()
                .checkSpinner();

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
                    'pages',
                    'currentPage',
                    'recordData',
                    'columnsHeader',
                    'visible',
                    'disabled',
                    'labels',
                    'showSpinner',
                    'isDifferedFromDefault'
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
        setColumnsHeaderListener: function () {
            if (this.columnsHeaderAfterRender) {
                this.on('recordData', this.renderColumnsHeader.bind(this));
            }

            return this;
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
            var data;

            if (!this.labels().length) {
                _.each(this.childTemplate.children, function (cell) {
                    data = this.createHeaderTemplate(cell.config);

                    cell.config.labelVisible = false;
                    _.extend(data, {
                        label: cell.config.label,
                        name: cell.name,
                        columnsHeaderClasses: cell.config.columnsHeaderClasses
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

            if (this.elems().filter(function (el) {
                    return el.position;
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
            var pages;

            this.relatedData = this.deleteProperty ?
                _.filter(data, function (elem) {
                    return elem[this.deleteProperty] !== this.deleteValue;
                }, this) : data;

            pages = Math.ceil(this.relatedData.length / this.pageSize) || 1;
            this.pages(pages);
        },

        /**
         * Get items to rendering on current page
         *
         * @returns {Array} data
         */
        getChildItems: function () {
            this.startIndex = (~~this.currentPage() - 1) * this.pageSize;

            return this.relatedData.slice(this.startIndex, this.startIndex + this.pageSize);
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
            if (this.relatedData.length && this.relatedData.length % this.pageSize === 0) {
                this.clear();
                this.pages(this.pages() + 1);
                this.currentPage(this.pages());
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

            if (this.getChildItems().length <= 0 && this.pages() !== 1) {
                this.pages(this.pages() - 1);
                this.currentPage(this.pages());
            }
        },

        /**
         * Change page
         *
         * @param {Number} page - current page
         */
        changePage: function (page) {
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

            this.clear();
            this.initChildren();
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
         * Change page to previos
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

            this.elems.each(function (record) {
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
                childs;

            if (this.deleteProperty) {
                recordInstance = _.find(this.elems(), function (elem) {
                    return elem.index === index;
                });
                recordInstance.destroy();
                this.elems([]);
                this._updateCollection();
                this.removeMaxPosition();
                this.recordData()[recordInstance.index][this.deleteProperty] = this.deleteValue;
                this.recordData.valueHasMutated();
                childs = this.getChildItems();

                if (childs.length > this.elems().length) {
                    this.addChild(false, childs[childs.length - 1][this.identificationProperty], false);
                }
            } else {
                this.update = true;

                if (~~this.currentPage() === this.pages()) {
                    lastRecord =
                        _.findWhere(this.elems(), {
                            index: this.startIndex + this.getChildItems().length - 1
                        }) ||
                        _.findWhere(this.elems(), {
                            index: (this.startIndex + this.getChildItems().length - 1).toString()
                        });

                    lastRecord.destroy();
                }

                this.removeMaxPosition();
                recordsData = this._getDataByProp(recordId);
                this._updateData(recordsData);
                this.update = false;
            }

            if (this.pages() < ~~this.currentPage()) {
                this.currentPage(this.pages());
            }

            this._sort();
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
                return ~~recordData[prop] === ~~id;
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
            var recordData = utils.copy(this.recordData());

            Array.isArray(recordData) && recordData.forEach(function (item) {
                delete item['record_id'];
            });

            this.isDifferedFromDefault(!_.isEqual(recordData, this.default));
        }
    });
});
