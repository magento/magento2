/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define(['squire'], function (Squire) {
    'use strict';

    var injector = new Squire(),
        Component,
        params = {
            name: 'qty-element',
            dataScope: '',
            value: 1000,
            setListeners: jasmine.createSpy().and.callFake(function () {
                return this;
            }),
            setLinks: jasmine.createSpy().and.callFake(function () {
                this.isConfigurable = false;

                return this;
            })
        };

    beforeEach(function (done) {
        injector.require(
            ['Magento_ConfigurableProduct/js/components/qty-configurable'], function (QtyConfigurable) {
                Component = QtyConfigurable;
                done();
            });
    });

    afterEach(function () {
        try {
            injector.remove();
            injector.clean();
        } catch (e) {
        }
    });

    describe('Magento_ConfigurableProduct/js/components/qty-configurable', function () {
        it('Product is not configurable by default', function () {
            var component = new Component(params);

            expect(component.disabled()).toBeFalsy();
            expect(component.value()).toEqual(1000);
        });

        it('State of component does not changed', function () {
            var component = new Component(params);

            expect(component.disabled()).toBeFalsy();

            component.value(99);
            component.handleQtyValue(false);

            expect(component.disabled()).toBeFalsy();
            expect(component.value()).toEqual(99);
        });

        it('Product changed to configurable', function () {
            var component = new Component(params);

            expect(component.disabled()).toBeFalsy();
            expect(component.value()).toEqual(1000);

            component.handleQtyValue(true);

            expect(component.disabled()).toBeTruthy();
            expect(component.value()).toEqual('');
        });

        it('Product is configurable by default', function () {
            var component = new Component($.extend({}, params, {
                // eslint-disable-next-line max-nested-callbacks
                setLinks: jasmine.createSpy().and.callFake(function () {
                    this.isConfigurable = true;

                    return this;
                })
            }));

            expect(component.disabled()).toBeTruthy();
            expect(component.value()).toEqual('');
        });

        it('Product changed from configurable to another one', function () {
            var component = new Component($.extend({}, params, {
                // eslint-disable-next-line max-nested-callbacks
                setLinks: jasmine.createSpy().and.callFake(function () {
                    this.isConfigurable = true;

                    return this;
                })
            }));

            expect(component.disabled()).toBeTruthy();
            expect(component.value()).toEqual('');

            component.value(100);
            component.handleQtyValue(false);

            expect(component.disabled()).toBeFalsy();
            expect(component.value()).toEqual(100);
        });
    });
});
