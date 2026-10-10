/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

define([
    'jquery',
    'knockout',
    'Magento_Ui/js/grid/filters/filters',
    'Magento_Ui/js/lib/knockout/template/renderer',
    'text!Magento_Ui/templates/grid/filters/filters.html'
], function ($, ko, Filter, renderer, filtersTemplate) {
    'use strict';

    describe('Magento_Ui/js/grid/filters/filters', function () {
        var filterObj,
            temp;

        beforeEach(function () {
            filterObj = new Filter({
                name: 'filter'
            });
        });
        it('has been initialized', function () {
            expect(filterObj).toBeDefined();
        });
        it('has initObservable', function () {
            temp = filterObj.initObservable();
            expect(temp).toBeDefined();
        });
        it('has initElement', function () {
            spyOn(filterObj, 'initElement');
            filterObj.initElement();
            expect(filterObj.initElement).toHaveBeenCalled();
        });
        it('has clear', function () {
            temp = filterObj.clear();
            expect(temp).toBeDefined();
        });
        it('has apply', function () {
            temp = filterObj.apply();
            expect(temp).toBeDefined();
        });
        it('has cancel', function () {
            temp = filterObj.cancel();
            expect(temp).toBeDefined();
        });
        it('has isFilterVisible method', function () {
            temp = {
                /** Stub */
                visible: function () {
                    return false;
                }
            };
            spyOn(filterObj, 'isFilterActive');
            filterObj.isFilterVisible(temp);
            expect(filterObj.isFilterActive).toHaveBeenCalled();
        });
        it('has isFilterActive method', function () {
            spyOn(filterObj, 'isFilterActive');
            filterObj.isFilterActive();
            expect(filterObj.isFilterActive).toHaveBeenCalled();
        });
        it('has hasVisible method', function () {
            spyOn(filterObj, 'hasVisible');
            filterObj.hasVisible();
            expect(filterObj.hasVisible).toHaveBeenCalled();
        });

        describe('filters template', function () {
            var stubTemplate = 'stub',
                originalTemplateBinding,
                container;

            beforeEach(function () {
                originalTemplateBinding = ko.bindingHandlers.template;
                ko.bindingHandlers.template = {
                    /** Renders the filter name for a filter template, delegates collection iteration to knockout */
                    init: function (element, valueAccessor) {
                        var value = ko.unwrap(valueAccessor());

                        if (value === stubTemplate) {
                            ko.virtualElements.prepend(element, document.createTextNode(ko.dataFor(element).name));

                            return {
                                controlsDescendantBindings: true
                            };
                        }

                        return originalTemplateBinding.init.apply(this, arguments);
                    },

                    /** Skips the stubbed filter templates */
                    update: function (element, valueAccessor) {
                        if (ko.unwrap(valueAccessor()) !== stubTemplate) {
                            return originalTemplateBinding.update.apply(this, arguments);
                        }
                    }
                };
                container = $('<div></div>');
            });

            afterEach(function () {
                ko.bindingHandlers.template = originalTemplateBinding;
                ko.cleanNode(container[0]);
                container.remove();
            });

            /**
             * Renders the filters fieldset for the given filters and returns rendered filter names in DOM order.
             *
             * @param {Array} elems
             * @returns {Array}
             */
            function renderFilters(elems) {
                var nodes = renderer.parseTemplate(filtersTemplate),
                    fieldset;

                elems.forEach(function (elem) {
                    elem.getTemplate = function () {
                        return stubTemplate;
                    };
                });

                container.append(nodes);
                fieldset = container.find('.admin__data-grid-filters');
                fieldset.children('legend').remove();
                ko.applyBindings({
                    elems: elems,
                    getRanges: Filter.prototype.getRanges,
                    getPlain: Filter.prototype.getPlain,

                    /** Stub */
                    isFilterVisible: function () {
                        return true;
                    }
                }, fieldset[0]);

                return fieldset.children('.admin__form-field').map(function () {
                    return $(this).text();
                }).get();
            }

            it('renders range and plain filters in the order of the elements', function () {
                var names = renderFilters([
                    {
                        name: 'name',
                        isRange: false
                    },
                    {
                        name: 'price',
                        isRange: true
                    },
                    {
                        name: 'email',
                        isRange: false
                    },
                    {
                        name: 'created_at',
                        isRange: true
                    }
                ]);

                expect(names).toEqual(['name', 'price', 'email', 'created_at']);
            });

            it('wraps range filters in a fieldset and plain filters in a div', function () {
                renderFilters([
                    {
                        name: 'price',
                        isRange: true
                    },
                    {
                        name: 'name',
                        isRange: false
                    }
                ]);

                expect(container.find('fieldset.admin__form-field').length).toBe(1);
                expect(container.find('div.admin__form-field').length).toBe(1);
            });
        });
    });
});
