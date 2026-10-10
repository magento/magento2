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

        /**
         * FieldArray __empty sentinel must participate in dependence enable/disable
         * when marked with class "disableable".
         */
        describe('FormElementDependenceController FieldArray disableable hidden', function () {
            var originalObserve,
                originalDollar,
                master,
                rowInput,
                emptySentinel,
                otherHidden,
                fakeTarget,
                wrapperSelectResult,
                controller;

            /**
             * Build a Prototype-like enumerable list with each/include/push.
             *
             * @param {Array} items
             * @returns {Array}
             */
            function protoList(items) {
                var list = items.slice();

                list.each = function (callback) {
                    var i;

                    for (i = 0; i < this.length; i++) {
                        callback(this[i], i);
                    }
                };
                list.include = function (item) {
                    return this.indexOf(item) !== -1;
                };

                return list;
            }

            beforeEach(function () {
                originalObserve = window.Event && window.Event.observe;
                originalDollar = window.$;

                master = document.createElement('select');
                master.id = 'field_array_master';
                master.appendChild(new Option('No', '0'));
                master.appendChild(new Option('Yes', '1'));
                master.value = '0';
                document.body.appendChild(master);

                rowInput = document.createElement('input');
                rowInput.type = 'text';
                rowInput.name = 'groups[g][fields][items][value][_row][col]';
                rowInput.disabled = false;

                emptySentinel = document.createElement('input');
                emptySentinel.type = 'hidden';
                emptySentinel.className = 'disableable';
                emptySentinel.name = 'groups[g][fields][items][value][__empty]';
                emptySentinel.value = '';
                emptySentinel.disabled = false;

                otherHidden = document.createElement('input');
                otherHidden.type = 'hidden';
                otherHidden.name = 'groups[g][fields][items][token]';
                otherHidden.value = 'keep';
                otherHidden.disabled = false;

                // Elements found inside levels_up container (table wrapper) — not __empty
                wrapperSelectResult = protoList([rowInput]);

                fakeTarget = {
                    id: 'field_array_dependent',
                    type: undefined,
                    tagName: 'TABLE',
                    getAttribute: function () {
                        return null;
                    },
                    show: function () {},
                    hide: function () {},
                    up: function (arg) {
                        if (arg === 'td.value') {
                            return {
                                select: function (selector) {
                                    if (selector === 'input.disableable') {
                                        return protoList([emptySentinel]);
                                    }

                                    return protoList([]);
                                }
                            };
                        }

                        // levels_up container (table wrapper)
                        return {
                            show: function () {},
                            hide: function () {},
                            select: function () {
                                return wrapperSelectResult;
                            },
                            up: function () {
                                return {
                                    select: function (selector) {
                                        if (selector === 'input.disableable') {
                                            return protoList([emptySentinel]);
                                        }

                                        return protoList([]);
                                    }
                                };
                            }
                        };
                    }
                };

                if (window.Event) {
                    window.Event.observe = function () {};
                }

                window.$ = function (elemId) {
                    if (elemId === 'field_array_dependent') {
                        return fakeTarget;
                    }
                    if (elemId === 'row_field_array_dependent') {
                        return {
                            show: function () {},
                            hide: function () {}
                        };
                    }

                    return document.getElementById(elemId);
                };
            });

            afterEach(function () {
                if (window.Event && originalObserve) {
                    window.Event.observe = originalObserve;
                }
                if (originalDollar) {
                    window.$ = originalDollar;
                }
                if (master && master.parentNode) {
                    master.parentNode.removeChild(master);
                }
            });

            it('disables FieldArray .disableable hidden when dependence hides the field', function () {
                master.value = '0';

                /* eslint-disable no-new */
                controller = new window.FormElementDependenceController({
                    'field_array_dependent': {
                        'field_array_master': {
                            values: ['1'],
                            negative: false
                        }
                    }
                }, {
                    levels_up: 1
                });
                /* eslint-enable no-new */

                expect(rowInput.disabled).toBe(true);
                expect(emptySentinel.disabled).toBe(true);
                expect(otherHidden.disabled).toBe(false);
            });

            it('re-enables FieldArray .disableable hidden when dependence shows the field', function () {
                master.value = '0';

                /* eslint-disable no-new */
                controller = new window.FormElementDependenceController({
                    'field_array_dependent': {
                        'field_array_master': {
                            values: ['1'],
                            negative: false
                        }
                    }
                }, {
                    levels_up: 1
                });
                /* eslint-enable no-new */

                expect(emptySentinel.disabled).toBe(true);

                master.value = '1';
                controller.trackChange(null, 'field_array_dependent', {
                    'field_array_master': {
                        values: ['1'],
                        negative: false
                    }
                });

                expect(rowInput.disabled).toBe(false);
                expect(emptySentinel.disabled).toBe(false);
                expect(otherHidden.disabled).toBe(false);
            });

            it('does not disable plain hidden inputs without .disableable class', function () {
                var plainHiddenInWrapper = document.createElement('input');

                plainHiddenInWrapper.type = 'hidden';
                plainHiddenInWrapper.name = 'plain';
                plainHiddenInWrapper.disabled = false;
                wrapperSelectResult = protoList([rowInput, plainHiddenInWrapper]);

                master.value = '0';

                /* eslint-disable no-new */
                new window.FormElementDependenceController({
                    'field_array_dependent': {
                        'field_array_master': {
                            values: ['1'],
                            negative: false
                        }
                    }
                }, {
                    levels_up: 1
                });
                /* eslint-enable no-new */

                expect(plainHiddenInWrapper.disabled).toBe(false);
                expect(emptySentinel.disabled).toBe(true);
            });

            it('_isToggleableOnDependence allows disableable hiddens and skips plain hiddens', function () {
                /* eslint-disable no-new */
                controller = new window.FormElementDependenceController({}, {
                    levels_up: 1
                });
                /* eslint-enable no-new */

                expect(controller._isToggleableOnDependence(null)).toBe(false);
                expect(controller._isToggleableOnDependence(rowInput)).toBe(true);
                expect(controller._isToggleableOnDependence(emptySentinel)).toBe(true);
                expect(controller._isToggleableOnDependence(otherHidden)).toBe(false);
            });

            it('_isToggleableOnDependence skips controls with checked inherit checkbox', function () {
                var inherit = document.createElement('input'),
                    field = document.createElement('input');

                field.id = 'scoped_field';
                field.type = 'text';
                inherit.id = 'scoped_field_inherit';
                inherit.type = 'checkbox';
                inherit.checked = true;
                document.body.appendChild(field);
                document.body.appendChild(inherit);

                /* eslint-disable no-new */
                controller = new window.FormElementDependenceController({}, {
                    levels_up: 1
                });
                /* eslint-enable no-new */

                expect(controller._isToggleableOnDependence(field)).toBe(false);

                inherit.checked = false;
                expect(controller._isToggleableOnDependence(field)).toBe(true);

                document.body.removeChild(field);
                document.body.removeChild(inherit);
            });

            it('_getToggleableElements includes disableable hiddens outside levels_up container', function () {
                /* eslint-disable no-new */
                controller = new window.FormElementDependenceController({}, {
                    levels_up: 1
                });
                /* eslint-enable no-new */

                var elements = controller._getToggleableElements(fakeTarget);

                expect(elements).toBeTruthy();
                expect(elements.indexOf(rowInput)).not.toBe(-1);
                expect(elements.indexOf(emptySentinel)).not.toBe(-1);
                // otherHidden is never in the DOM walk — not disableable, not in wrapper list
                expect(elements.indexOf(otherHidden)).toBe(-1);
            });

            it('_getToggleableElements returns false when target is missing', function () {
                /* eslint-disable no-new */
                controller = new window.FormElementDependenceController({}, {
                    levels_up: 1
                });
                /* eslint-enable no-new */

                expect(controller._getToggleableElements(null)).toBe(false);
            });

            it('adds ignore-validate when hiding and removes it when showing', function () {
                master.value = '0';

                /* eslint-disable no-new */
                controller = new window.FormElementDependenceController({
                    'field_array_dependent': {
                        'field_array_master': {
                            values: ['1'],
                            negative: false
                        }
                    }
                }, {
                    levels_up: 1
                });
                /* eslint-enable no-new */

                expect($(rowInput).hasClassName ?
                    rowInput.hasClassName('ignore-validate') :
                    jQuery(rowInput).hasClass('ignore-validate')).toBeTruthy();
                expect(jQuery(emptySentinel).hasClass('ignore-validate')).toBe(true);

                master.value = '1';
                controller.trackChange(null, 'field_array_dependent', {
                    'field_array_master': {
                        values: ['1'],
                        negative: false
                    }
                });

                expect(jQuery(rowInput).hasClass('ignore-validate')).toBe(false);
                expect(jQuery(emptySentinel).hasClass('ignore-validate')).toBe(false);
            });
        });
    });
});
