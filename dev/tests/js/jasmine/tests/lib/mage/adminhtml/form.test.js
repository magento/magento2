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

    describe('FormElementDependenceController multiselect dependencies', function () {
        var fixture, source, target, row, dependency;

        beforeEach(function () {
            fixture = $('<div id="dependence_fixture">' +
                '<select id="dependence_source" multiple>' +
                '<option value="pending">Pending</option>' +
                '<option value="processing">Processing</option>' +
                '<option value="complete">Complete</option>' +
                '</select>' +
                '<div id="row_dependence_target">' +
                '<div class="value">' +
                '<input id="dependence_target" type="text" disabled>' +
                '</div></div></div>').appendTo('body');
            source = fixture.find('#dependence_source');
            target = fixture.find('#dependence_target');
            row = fixture.find('#row_dependence_target');
            dependency = {
                dependence_source: {values: ['processing'], negative: false}
            };
        });

        afterEach(function () {
            window.Event.stopObserving(source[0]);
            fixture.remove();
        });

        /**
         * @returns {Object}
         */
        function createController() {
            return new window.FormElementDependenceController({
                dependence_target: dependency
            });
        }

        /**
         * @param {Boolean} shown
         */
        function expectTarget(shown) {
            expect(row[0].style.display).toBe(shown ? '' : 'none');
            expect(target[0].style.display).toBe(shown ? '' : 'none');
            expect(target.prop('disabled')).toBe(!shown);
            expect(target.hasClass('ignore-validate')).toBe(!shown);
            expect(source.prop('disabled')).toBe(false);
        }

        it('shows and enables the target when only a later selected option matches', function () {
            source.val(['pending', 'processing', 'complete']);
            createController();
            expectTarget(true);
        });

        it('shows and enables the target when the first selected option matches', function () {
            source.val(['processing', 'complete']);
            createController();
            expectTarget(true);
        });

        it('hides and disables the target when no selected option matches', function () {
            source.val(['pending', 'complete']);
            createController();
            expectTarget(false);
        });

        it('hides and disables the target when nothing is selected', function () {
            source.val([]);
            createController();
            expectTarget(false);
        });

        it('hides a negative dependency when a later selected option matches', function () {
            dependency.dependence_source.negative = true;
            source.val(['pending', 'processing']);
            createController();
            expectTarget(false);
        });

        it('shows a negative dependency when no selected option matches', function () {
            dependency.dependence_source.negative = true;
            source.val(['pending', 'complete']);
            createController();
            expectTarget(true);
        });

        it('shows a negative dependency when nothing is selected', function () {
            dependency.dependence_source.negative = true;
            source.val([]);
            createController();
            expectTarget(true);
        });

        it('preserves strict comparisons for selected option values', function () {
            dependency.dependence_source.values = [1];
            source.append('<option value="1">One</option>').val(['1']);
            createController();
            expectTarget(false);
        });

        it('preserves positive empty-value dependencies when nothing is selected', function () {
            dependency.dependence_source.values = [''];
            source.val([]);
            createController();
            expectTarget(true);
        });

        it('preserves negative empty-value dependencies when nothing is selected', function () {
            dependency.dependence_source.values = [''];
            dependency.dependence_source.negative = true;
            source.val([]);
            createController();
            expectTarget(false);
        });

        it('matches any configured dependency value', function () {
            dependency.dependence_source.values = ['complete', 'processing'];
            source.val(['pending', 'processing']);
            createController();
            expectTarget(true);
        });

        it('updates the target after selection changes', function () {
            var event = document.createEvent('HTMLEvents');

            source.val(['pending']);
            createController();
            expectTarget(false);
            source.val(['pending', 'processing']);
            event.initEvent('change', true, true);
            source[0].dispatchEvent(event);
            expectTarget(true);
            source.val(['pending']);
            source[0].dispatchEvent(event);
            expectTarget(false);
        });

        it('preserves single-select matching and non-matching behavior', function () {
            var controller;

            source.prop('multiple', false).val('processing');
            controller = createController();
            expectTarget(true);
            source.val('pending');
            controller.trackChange(null, 'dependence_target', dependency);
            expectTarget(false);
        });

        it('preserves text-input matching and non-matching behavior', function () {
            var controller;

            source.replaceWith('<input id="dependence_source" type="text" value="processing">');
            source = fixture.find('#dependence_source');
            controller = createController();
            expectTarget(true);
            source.val('pending');
            controller.trackChange(null, 'dependence_target', dependency);
            expectTarget(false);
        });

        it('keeps inherited and CSS-disabled inputs disabled when a later option matches', function () {
            row.append('<input id="dependence_target_inherit" type="checkbox" checked>' +
                '<input id="dependence_disabled" class="disabled" disabled>');
            source.val(['pending', 'processing']);
            createController();
            expect(row[0].style.display).toBe('');
            expect(target[0].style.display).toBe('');
            expect(target.prop('disabled')).toBe(true);
            expect(fixture.find('#dependence_target_inherit').prop('checked')).toBe(true);
            expect(fixture.find('#dependence_disabled').prop('disabled')).toBe(true);
        });
    });
});
