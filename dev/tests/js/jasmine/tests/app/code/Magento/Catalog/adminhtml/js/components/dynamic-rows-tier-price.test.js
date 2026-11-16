/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

/*eslint max-nested-callbacks: 0*/

define([
    'jquery',
    'ko',
    'uiElement',
    'uiRegistry',
    'Magento_Catalog/js/components/dynamic-rows-tier-price',
    'Magento_Ui/js/dynamic-rows/record',
    'Magento_Ui/js/lib/knockout/template/engine',
    'Magento_Ui/js/lib/knockout/bootstrap'
], function ($, ko, Element, registry, DynamicRows, Record, templateEngine) {
    'use strict';

    describe('Magento_Catalog/js/components/dynamic-rows-tier-price', function () {
        var model, provider, container;

        function createModel(config) {
            model = new DynamicRows($.extend({
                name: 'tier_price',
                index: 'tier_price',
                provider: 'tier_price_provider',
                dataScope: 'data.product',
                template: 'ui/dynamic-rows/templates/default',
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

        function renderModel() {
            var rendered = templateEngine.waitForFinishRender(),
                context = new ko.bindingContext(model).extend({
                    element: model,
                    $t: $.mage.__,
                    ko: ko
                });

            container.setAttribute('data-bind', 'template: template');
            ko.applyBindings(context, container);

            return rendered;
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

        [0, ''].forEach(function (disabled) {
            it('renders Add enabled for an overridden grid with disabled=' + JSON.stringify(disabled), function (done) {
                createModel({
                    disabled: disabled
                });
                renderModel().then(function () {
                    expect(container.querySelector('input[type="checkbox"]').checked).toBe(false);
                    expect(container.querySelector('[data-action="add_new_row"]').disabled).toBe(false);
                }).then(done, done.fail);
            });
        });

        it('enables and disables Add through the grid default-value checkbox', function (done) {
            createModel();
            renderModel().then(function () {
                var checkbox = container.querySelector('input[type="checkbox"]'),
                    button = container.querySelector('[data-action="add_new_row"]'),
                    label = container.querySelector('.admin__field-service label');

                expect(checkbox).not.toBeNull();
                expect(button).not.toBeNull();
                expect(checkbox.checked).toBe(true);
                expect(button.disabled).toBe(true);
                expect(label.htmlFor).toBe(checkbox.id);
                expect(checkbox.id).toBe(model.name + '_default');

                label.click();

                expect(model.isUseDefault()).toBe(false);
                expect(model.disabled()).toBe(false);
                expect(button.disabled).toBe(false);
                expect(provider.get('data.use_default.tier_price')).toBe(0);

                checkbox.click();

                expect(model.isUseDefault()).toBe(true);
                expect(model.disabled()).toBe(true);
                expect(button.disabled).toBe(true);
                expect(provider.get('data.use_default.tier_price')).toBe(1);
            }).then(done, done.fail);
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

        it('keeps tier prices disabled when the default-value control is locked', function (done) {
            createModel({
                serviceDisabled: true
            });
            renderModel().then(function () {
                var checkbox = container.querySelector('input[type="checkbox"]');

                expect(checkbox.disabled).toBe(true);
                checkbox.click();
                expect(container.querySelector('[data-action="add_new_row"]').disabled).toBe(true);

                model.isUseDefault(false);

                expect(model.disabled()).toBe(true);
                expect(provider.get('data.use_default.tier_price')).toBeUndefined();
            }).then(done, done.fail);
        });

        it('does not unlock tier prices without a default-value control', function () {
            createModel({
                service: null
            });
            model.isUseDefault(false);

            expect(model.disabled()).toBe(true);
            expect(provider.get('data.use_default.tier_price')).toBeUndefined();
        });

        it('does not render a default-value checkbox or submit its flag at the default scope', function (done) {
            createModel({
                disabled: false,
                service: null
            });
            renderModel().then(function () {
                expect(model.disabled()).toBe(false);
                expect(container.querySelector('input[type="checkbox"]')).toBeNull();
                expect(container.querySelector('[data-action="add_new_row"]').disabled).toBe(false);
                expect(provider.get('data.use_default.tier_price')).toBeUndefined();
            }).then(done, done.fail);
        });
    });
});
