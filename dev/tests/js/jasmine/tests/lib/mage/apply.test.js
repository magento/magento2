/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable max-nested-callbacks */
define([
    'underscore',
    'tests/assets/tools',
    'tests/assets/apply/index',
    'mage/apply/main'
], function (_, tools, config, mage) {
    'use strict';

    describe('mage/apply/main', function () {
        var body = document.body;

        afterEach(function () {
            tools.removeContainer(config.base.containerId);
        });

        it('exports object', function () {
            expect(_.isFunction(mage) || _.isObject(mage)).toBe(true);
        });

        it('removes data-mage-init attribute affter processing', function () {
            var preset = config.fn,
                elem;

            /**
             * Checks if element has data attribute.
             *
             * @returns {Boolean}
             */
            function hasAttr() {
                return elem.hasAttribute(preset.dataAttr);
            }

            body.insertAdjacentHTML('beforeend', preset.tmpl);

            elem = document.getElementById(preset.nodeId);

            expect(hasAttr()).toBe(true);

            mage.apply();

            expect(hasAttr()).toBe(false);
        });

        it('calls function returned from module', function (done) {
            var preset = config.fn,
                node;

            spyOn(preset.component, 'testCallback').and.callThrough();

            body.insertAdjacentHTML('beforeend', preset.tmpl);

            node = document.getElementById(preset.nodeId);

            mage.apply();

            setTimeout(function () {
                expect(preset.component.testCallback)
                    .toHaveBeenCalledWith(jasmine.any(Object), node);

                done();
            }, preset.timeout);
        });
    });
});
