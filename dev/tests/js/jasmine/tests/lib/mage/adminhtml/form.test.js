/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

define([
    'jquery',
    'mage/adminhtml/form'
], function ($) {
    'use strict';

    describe('mage/adminhtml/form', function () {
        var id = 'edit_form',
            elementId = '#' + id;

        beforeEach(function () {
            var element = $('<form id="' + id + '" action="action/url" method="GET" target="_self" ></form>');

            element.appendTo('body');
        });
        afterEach(function () {
            $(elementId).remove();
        });

        it('should not enable inputs that have the disabled CSS class when dependencies are satisfied', function () {
            var container = document.createElement('div'),
                target = document.createElement('input'),
                cssDisabled = document.createElement('input'),
                normalInput = document.createElement('input'),
                source = document.createElement('input'),
                originalObserve = window.Event && window.Event.observe,
                originalDollar = window.$,
                fakeTarget = {
                    id: 'test_input_dependent',
                    type: 'input',
                    tagName: 'INPUT',
                    getAttribute: function () {
                        return null;
                    },
                    show: function () {},
                    hide: function () {},
                    up: function () {
                        return {
                            show: function () {},
                            hide: function () {},
                            select: function () {
                                return [cssDisabled, normalInput];
                            }
                        };
                    }
                };

            document.body.appendChild(container);
            target.id = 'test_input_dependent';
            container.appendChild(target);
            cssDisabled.id = 'css_disabled_input';
            cssDisabled.className = 'disabled';
            cssDisabled.disabled = true;
            container.appendChild(cssDisabled);
            normalInput.id = 'normal_input';
            normalInput.disabled = true;
            container.appendChild(normalInput);
            source.id = 'dep_source';
            source.value = '1';
            document.body.appendChild(source);

            if (window.Event) {
                window.Event.observe = function () {};
            }

            window.$ = function (elemId) {
                if (elemId === 'test_input_dependent') {
                    return fakeTarget;
                }
                return document.getElementById(elemId);
            };
            /* eslint-disable no-new */
            new window.FormElementDependenceController({
                'test_input_dependent': {
                    'dep_source': { values: ['1'] }
                }
            }, {
                levels_up: 1
            });
            /* eslint-enable no-new */

            // The element with CSS class "disabled" must remain disabled
            expect(cssDisabled.disabled).toBe(true);
            // The normal input should be enabled because dependencies are satisfied
            expect(normalInput.disabled).toBe(false);

            // Cleanup
            if (window.Event && originalObserve) {
                window.Event.observe = originalObserve;
            }
            if (originalDollar) {
                window.$ = originalDollar;
            }
            document.body.removeChild(container);
            document.body.removeChild(source);
        });
    });
});
