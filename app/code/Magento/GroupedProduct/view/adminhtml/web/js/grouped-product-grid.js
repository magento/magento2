/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/dynamic-rows/dynamic-rows-grid'
], function (_, registry, dynamicRowsGrid) {
    'use strict';

    return dynamicRowsGrid.extend({

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

                if (~~position === this.maxPosition && ~~position > this.getDefaultPageBoundary() + 1) {
                    this.shiftNextPagesPositions(position);
                }
            } else {
                this.maxPosition += 1;
            }
        },

        /**
         * Shift positions for next page elements
         *
         * @param {Number} position
         */
        shiftNextPagesPositions: function (position) {

            var recordData = this.recordData(),
                startIndex = ~~this.currentPage() * this.pageSize,
                offset = position - startIndex + 1,
                index = startIndex;

            if (~~this.currentPage() === this.pages()) {
                return false;
            }

            for (index; index < recordData.length; index++) {
                recordData[index].position = index + offset;
            }
            this.recordData(recordData);
        },

        /**
         * Update position for element after position from another page is entered
         *
         * @param {Object} data
         * @param {Object} event
         */
        updateGridPosition: function (data, event) {
            var inputValue = parseInt(event.target.value, 10),
                recordData = this.recordData(),
                record,
                previousValue,
                updatedRecord;

            record = this.elems().find(function (obj) {
                return obj.dataScope === data.parentScope;
            });

            previousValue = this.getCalculatedPosition(record);

            if (isNaN(inputValue) || inputValue < 0 || inputValue === previousValue) {
                return false;
            }

            this.elems([]);

            updatedRecord = this.getUpdatedRecordIndex(recordData, record.data().id);

            if (inputValue >= this.recordData().size() - 1) {
                recordData[updatedRecord].position = this.getGlobalMaxPosition() + 1;
            } else {
                recordData.forEach(function (value, index) {
                    if (~~value.id === ~~record.data().id) {
                        recordData[index].position = inputValue;
                    } else if (inputValue > previousValue && index <= inputValue) {
                        recordData[index].position = index - 1;
                    } else if (inputValue < previousValue && index >= inputValue) {
                        recordData[index].position = index + 1;
                    }
                });
            }

            this.reloadGridData(recordData);

        },

        /**
         * Get updated record index
         *
         * @param  {Array} recordData
         * @param {Number} recordId
         * @return {Number}
         */
        getUpdatedRecordIndex: function (recordData, recordId) {
            return recordData.map(function (o) {
                return ~~o.id;
            }).indexOf(~~recordId);
        },

        /**
         *
         * @param {Array} recordData - to reprocess
         */
        reloadGridData: function (recordData) {
            this.recordData(recordData.sort(function (a, b) {
                return ~~a.position - ~~b.position;
            }));
            this._updateCollection();
            this.reload();
        },

        /**
         * Event handler for "Send to bottom" button
         *
         * @param {Object} positionObj
         * @return {Boolean}
         */
        sendToBottom: function (positionObj) {

            var objectToUpdate = this.getObjectToUpdate(positionObj),
                recordData = this.recordData(),
                updatedRecord;

            if (~~this.currentPage() === this.pages) {
                objectToUpdate.position = this.maxPosition;
            } else {
                this.elems([]);
                updatedRecord = this.getUpdatedRecordIndex(recordData, objectToUpdate.data().id);
                recordData[updatedRecord].position = this.getGlobalMaxPosition() + 1;
                this.reloadGridData(recordData);
            }

            return false;
        },

        /**
         * Event handler for "Send to top" button
         *
         * @param {Object} positionObj
         * @return {Boolean}
         */
        sendToTop: function (positionObj) {
            var objectToUpdate = this.getObjectToUpdate(positionObj),
                recordData = this.recordData(),
                updatedRecord;

            //isFirst
            if (~~this.currentPage() === 1) {
                objectToUpdate.position = 0;
            } else {
                this.elems([]);
                updatedRecord = this.getUpdatedRecordIndex(recordData, objectToUpdate.data().id);
                recordData.forEach(function (value, index) {
                    recordData[index].position = index === updatedRecord ? 0 : value.position + 1;
                });
                this.reloadGridData(recordData);
            }

            return false;
        },

        /**
         * Get element from grid for update
         *
         * @param {Object} object
         * @return {*}
         */
        getObjectToUpdate: function (object) {
            return this.elems().filter(function (item) {
                return item.name === object.parentName;
            })[0];
        },

        /**
         * Value function for position input
         *
         * @param {Object} data
         * @return {Number}
         */
        getCalculatedPosition: function (data) {
            return (~~this.currentPage() - 1) * this.pageSize + this.elems().pluck('name').indexOf(data.name);
        },

        /**
         * Return Page Boundary
         *
         * @return {Number}
         */
        getDefaultPageBoundary: function () {
            return ~~this.currentPage() * this.pageSize - 1;
        },

        /**
         * Returns position for last element to be moved after
         *
         * @return {Number}
         */
        getGlobalMaxPosition: function () {
            return _.max(this.recordData().map(function (r) {
                return ~~r.position;
            }));
        }
    });
});
