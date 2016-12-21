/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'tests/assets/tools',
    'tests/assets/script/index',
    'mage/apply/scripts'
], function (tools, config, processScripts) {
    'use strict';

    describe('mage/apply/scripts', function () {
        var body = document.body;

        afterEach(function () {
            tools.removeContainer(config.base.containerId);
        });

        it('exports function', function () {
            expect(typeof processScripts).toBe('function');
        });

        it('removes script node after processing it', function () {
            var preset = config.virtual;

            /**
             * Checks if script node exists.
             *
             * @returns {Boolean}
             */
            function hasNode() {
                return !!document.getElementById(preset.scriptId);
            }

            body.insertAdjacentHTML('beforeend', preset.tmpl);

            expect(hasNode()).toBe(true);

            processScripts();

            expect(hasNode()).toBe(false);
        });

        it('parses custom script nodes without element selector', function () {
            var virtuals,
                preset = config.virtual;

            body.insertAdjacentHTML('beforeend', preset.tmpl);

            virtuals = processScripts();

            expect(virtuals).toBeDefined();
            expect(virtuals.length).toBeGreaterThan(0);
        });

        it('extends data-mage-init attribute of a found node', function () {
            var preset = config.bySelector,
                elem,
                result;

            body.insertAdjacentHTML('beforeend', preset.tmpl);

            processScripts();

            elem = document.getElementById(preset.nodeId);
            result = elem.getAttribute(preset.dataAttr);

            expect(result).toEqual(JSON.stringify(preset.merged));
        });
    });
});
