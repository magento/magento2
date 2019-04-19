define(
    [
        'jquery',
        'underscore',
        'ko',
        'uiComponent',
        'uiRegistry',
    ],
    function (
        $,
        _,
        ko,
        Component,
        registry,
    ) {
        'use strict';

        var mixin = {
            processingInsertDataFromGrid: function(data) {
                var changes,
                    tmpArray;

                if (!data.length) {
                    return;
                }

                tmpArray = this.getUnionInsertData();

                changes = this._checkGridData(data);
                this.cacheGridData = data;

                changes.each(function (changedObject) {
                    var mappedData = this.mappingValue(changedObject);

                    mappedData[this.canEditField] = 0;
                    mappedData[this.newProductField] = 0;
                    mappedData.variationKey = this._getVariationKey(changedObject);
                    mappedData['configurable_attribute'] = this._getConfigurableAttribute(changedObject);

                    if ('quantity_per_source' in changedObject) {
                        var sources = [];
                        changedObject['quantity_per_source'].each(function (source) {
                            sources.push({
                                quantity_per_source: source['qty'],
                                source: source['source_name'],
                                source_code: source['source_code'],
                            })
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
            },
        };

        return function (target) {
            return target.extend(mixin);
        };

    }
);