/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([], function () {
        'use strict';

        var mixin = {

            /**
             * Parsed data
             *
             * @param {Array} data - array with data
             * about selected records
             */
            processingInsertDataFromGrid: function (data) {
                var changes,
                    tmpArray;

                if (!data.length) {
                    return;
                }

                tmpArray = this.getUnionInsertData();

                changes = this._checkGridData(data);
                this.cacheGridData = data;

                changes.each(function (changedObject) {
                    var mappedData = this.mappingValue(changedObject),
                        sources = [];

                    mappedData[this.canEditField] = 0;
                    mappedData[this.newProductField] = 0;
                    mappedData.variationKey = this._getVariationKey(changedObject);
                    mappedData['configurable_attribute'] = this._getConfigurableAttribute(changedObject);

                    if ('quantity_per_source' in changedObject) {
                        changedObject['quantity_per_source'].each(function (source) {
                            sources.push({
                                'quantity_per_source': source.qty,
                                'source': source['source_name'],
                                'source_code': source['source_code']
                            });
                        });
                        mappedData['quantity_per_source'] = sources;
                    }

                    tmpArray.push(mappedData);
                }, this);

                // Attributes cannot be changed before regeneration thought wizard
                if (!this.source.get('data.attributes').length) {
                    this.source.set('data.attributes', this.attributesTmp);
                }
                this.unionInsertData(tmpArray);
            }
        };

        return function (target) {
            return target.extend(mixin);
        };
    }
);
