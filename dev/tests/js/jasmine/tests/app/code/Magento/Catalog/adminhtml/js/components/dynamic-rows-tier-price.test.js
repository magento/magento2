/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

define([
    'jquery',
    'ko',
    'uiElement',
    'uiRegistry',
    'Magento_Catalog/js/components/dynamic-rows-tier-price',
    'Magento_Ui/js/dynamic-rows/record',
    'Magento_Ui/js/lib/knockout/template/renderer',
    'text!ui/template/form/element/helper/service.html',
    'text!ui/template/dynamic-rows/templates/default.html'
], function ($, ko, Element, registry, DynamicRows, Record, renderer, serviceTemplate, rowsTemplate) {
    'use strict';

    describe('Magento_Catalog/js/components/dynamic-rows-tier-price', function () {
        var model, provider, container;

        function createModel(config) {
            model = new DynamicRows($.extend({
                name: 'tier_price',
                index: 'tier_price',
                provider: 'tier_price_provider',
                dataScope: 'data.product',
                disabled: true,
                dndConfig: {
                    enabled: false
                },
                service: {
                    template: 'ui/form/element/helper/service'
                }
            }, config));

            return model;
        }

        beforeEach(function () {
            provider = new Element({
                name: 'tier_price_provider',
                data: {
                    product: {
                        'tier_price': []
                    }
                }
            });
            registry.set(provider.name, provider);
            container = document.createElement('div');
            document.body.appendChild(container);
        });

        afterEach(function () {
            ko.cleanNode(container);
            container.remove();
            model.destroy();
            provider.destroy();
        });

        it('initializes inherited tier prices as using the default value', function () {
            createModel();

            expect(model.isUseDefault()).toBe(true);
            expect(model.disabled()).toBe(true);
            expect(provider.get('data.use_default.tier_price')).toBe(1);
        });

        it('initializes overridden tier prices as editable', function () {
            createModel({
                disabled: false
            });

            expect(model.isUseDefault()).toBe(false);
            expect(model.disabled()).toBe(false);
            expect(provider.get('data.use_default.tier_price')).toBe(0);
        });

        it('enables and disables Add through the default-value checkbox template', function () {
            var checkbox, button, context;

            createModel();
            $(container).append(serviceTemplate);
            container.appendChild($(rowsTemplate).find('tfoot button').first()[0]);
            renderer.normalize(container);
            context = new ko.bindingContext(model).extend({
                element: model
            });
            ko.applyBindings(context, container);
            checkbox = container.querySelector('input[type="checkbox"]');
            button = container.querySelector('button');

            expect(checkbox.checked).toBe(true);
            expect(button.disabled).toBe(true);

            checkbox.click();

            expect(model.isUseDefault()).toBe(false);
            expect(model.disabled()).toBe(false);
            expect(button.disabled).toBe(false);
            expect(provider.get('data.use_default.tier_price')).toBe(0);

            checkbox.click();

            expect(model.isUseDefault()).toBe(true);
            expect(model.disabled()).toBe(true);
            expect(button.disabled).toBe(true);
            expect(provider.get('data.use_default.tier_price')).toBe(1);
        });

        it('toggles existing rows without enabling the locked website selector', function () {
            var record,
                website = new Element({
                    index: 'website_id',
                    disabled: true,
                    visible: true
                }).observe('disabled visible'),
                price = new Element({
                    index: 'price',
                    disabled: true,
                    visible: true
                }).observe('disabled visible');

            createModel();
            model.childTemplate = {
                children: [
                    {
                        name: 'website_id',
                        config: {
                            disabled: true
                        }
                    },
                    {
                        name: 'price',
                        config: {}
                    }
                ]
            };
            record = new Record({
                name: 'tier_price.0'
            });
            record.elems([website, price]);
            model.elems([record]);

            model.isUseDefault(false);

            expect(price.disabled()).toBe(false);
            expect(website.disabled()).toBe(true);

            model.isUseDefault(true);

            expect(price.disabled()).toBe(true);
            expect(website.disabled()).toBe(true);

            model.isUseDefault(false);

            expect(price.disabled()).toBe(false);
            expect(website.disabled()).toBe(true);
        });

        it('keeps tier prices disabled when the default-value control is locked', function () {
            createModel({
                serviceDisabled: true
            });
            model.isUseDefault(false);

            expect(model.disabled()).toBe(true);
            expect(provider.get('data.use_default.tier_price')).toBeUndefined();
        });

        it('does not unlock tier prices without a default-value control', function () {
            createModel({
                service: null
            });
            model.isUseDefault(false);

            expect(model.disabled()).toBe(true);
            expect(provider.get('data.use_default.tier_price')).toBeUndefined();
        });

        it('does not submit default-value flags at the default scope', function () {
            createModel({
                disabled: false,
                service: null
            });

            expect(model.disabled()).toBe(false);
            expect(provider.get('data.use_default.tier_price')).toBeUndefined();
        });
    });
});
