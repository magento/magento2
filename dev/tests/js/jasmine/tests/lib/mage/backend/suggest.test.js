/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable max-nested-callbacks */
define([
    'jquery',
    'mage/backend/suggest'
], function ($) {
    'use strict';

    describe('mage/backend/suggest', function () {
        var suggestSelector = '#suggest';

        beforeEach(function () {
            var $suggest = $('<input name="test-suggest" id="suggest" />');

            $('body').append($suggest);
            $('body').append('<script type="text/template" id="test-template">' +
                '<div><%= data.test %></div>' +
                '</script>');
        });

        afterEach(function () {
            $(suggestSelector).remove();
            $('#test-template').remove();
            $(suggestSelector).suggest('destroy');
        });

        it('Check that suggest inited', function () {
            var $suggest = $(suggestSelector).suggest({
                template: '#test-template',
                choiceTemplate: '<li/>'
            });

            expect($suggest.is(':mage-suggest')).toBe(true);
        });

        it('Check suggest create', function () {
            var options = {
                    template: '#test-template',
                    choiceTemplate: '<li/>',
                    controls: {
                        selector: '.test',
                        eventsMap: {
                            focus: ['testfocus'],
                            blur: ['testblur'],
                            select: ['testselect']
                        }
                    },
                    showRecent: true,
                    storageKey: 'test-suggest-recent',
                    multiselect: true
                },
                recentItems = [{
                        id: '1',
                        label: 'TestLabel1'
                    },
                    {
                        id: '2',
                        label: 'TestLabel2'
                    }
                ],
                nonSelectedItem = {
                    id: '',
                    label: ''
                },
                suggestInstance;

            if (window.localStorage) {
                localStorage.setItem(options.storageKey, JSON.stringify(recentItems));
            }

            suggestInstance = $(suggestSelector).suggest(options).data('mage-suggest');

            expect(suggestInstance._term).toBe(null);
            expect(suggestInstance._nonSelectedItem).toEqual(nonSelectedItem);
            expect(suggestInstance._renderedContext).toBe(null);
            expect(suggestInstance._selectedItem).toEqual(nonSelectedItem);
            expect(suggestInstance._control).toEqual(suggestInstance.options.controls);
            expect(suggestInstance._recentItems).toEqual(window.localStorage ? recentItems : []);
            expect(suggestInstance.valueField.is(':hidden')).toBe(true);

            if (window.localStorage) {
                localStorage.removeItem(options.storageKey);
            }
        });

        it('Check suggest render', function () {
            var options = {
                    template: '#test-template',
                    choiceTemplate: '<li/>',
                    dropdownWrapper: '<div class="wrapper-test"></div>',
                    className: 'test-suggest',
                    inputWrapper: '<div class="test-input-wrapper"></div>'
                },
                suggestInstance = $(suggestSelector).suggest(options).data('mage-suggest');

            suggestInstance._render();

            expect(suggestInstance.dropdown.hasClass('wrapper-test')).toBe(true);
            expect(suggestInstance.dropdown.is(':hidden')).toBe(true);
            expect(suggestInstance.element.closest('.test-input-wrapper').length).toBeGreaterThan(0);
            expect(suggestInstance.element.closest('.' + options.className).length).toBeGreaterThan(0);
            expect(suggestInstance.element.attr('autocomplete')).toBe('off');

            options.appendMethod = 'before';
            $(suggestSelector).suggest('destroy');
            suggestInstance = $(suggestSelector).suggest(options).data('mage-suggest');
            suggestInstance._render();
            expect(suggestInstance.element.prev().is(suggestInstance.dropdown)).toBe(true);

            options.appendMethod = 'after';
            $(suggestSelector).suggest('destroy');
            suggestInstance = $(suggestSelector).suggest(options).data('mage-suggest');
            suggestInstance._render();
            expect(suggestInstance.element.next().is(suggestInstance.dropdown)).toBe(true);
        });

        it('Check suggest createValueField', function () {
            var suggestInstance = $(suggestSelector).suggest({
                    template: '#test-template',
                    choiceTemplate: '<li/>'
                }).data('mage-suggest'),
                valueField = suggestInstance._createValueField();

            expect(valueField.is('input')).toBe(true);
            expect(valueField.is(':hidden')).toBe(true);

            $(suggestSelector).suggest('destroy');
            suggestInstance = $(suggestSelector).suggest({
                multiselect: true,
                template: '#test-template',
                choiceTemplate: '<li/>'
            }).data('mage-suggest');
            valueField = suggestInstance._createValueField();

            expect(valueField.is('select')).toBe(true);
            expect(valueField.is(':hidden')).toBe(true);
            expect(valueField.attr('multiple')).toBe('multiple');
        });

        it('Check suggest prepareValueField', function () {
            var $suggest = $(suggestSelector).suggest({
                    template: '#test-template',
                    choiceTemplate: '<li/>'
                }),
                suggestInstance = $suggest.data('mage-suggest'),
                suggestName = $suggest.attr('name');

            suggestInstance._prepareValueField();

            expect(suggestInstance.valueField).not.toBe(true);
            expect(suggestInstance.element.prev().is(suggestInstance.valueField)).toBe(true);
            expect(suggestInstance.element.attr('name')).toBe(undefined);
            expect(suggestInstance.valueField.attr('name')).toBe(suggestName);
        });

        it('Check suggest destroy', function () {
            var options = {
                    template: '#test-template',
                    choiceTemplate: '<li/>',
                    inputWrapper: '<div class="test-input-wrapper"></div>',
                    valueField: null
                },
                $suggest = $(suggestSelector).suggest(options),
                suggestInstance = $suggest.data('mage-suggest'),
                suggestName = $suggest.attr('name');

            expect(suggestInstance.dropdown).not.toBe(undefined);
            expect(suggestInstance.valueField).not.toBe(undefined);
            expect(suggestName).toBe(undefined);

            $suggest.suggest('destroy');

            expect($suggest.closest('.test-input-wrapper').length).toBe(0);
            expect($suggest.attr('autocomplete')).toBe(undefined);
            expect($suggest.attr('name')).toBe(suggestInstance.valueField.attr('name'));
            expect(suggestInstance.valueField.parents('html').length).not.toBeGreaterThan(0);
            expect(suggestInstance.dropdown.parents('html').length).not.toBeGreaterThan(0);
        });

        it('Check suggest value', function () {
            var value = 'test-value',
                suggestInstance, suggestDivInstance;

            $(suggestSelector).val(value);
            $('body').append('<div id="suggest-div">' + value + '</div>');

            suggestInstance = $(suggestSelector).suggest({
                template: '#test-template',
                choiceTemplate: '<li/>'
            }).data('mage-suggest');
            suggestDivInstance = $('#suggest-div').suggest({
                template: '#test-template',
                choiceTemplate: '<li/>'
            }).data('mage-suggest');

            expect(suggestInstance._value()).toBe(value);
            expect(suggestDivInstance._value()).toBe(value);
            $('#suggest-div').remove();
        });

        it('Check suggest bind', function () {
            var eventIsBinded = false,
                options = {
                    template: '#test-template',
                    choiceTemplate: '<li/>',
                    events: {
                        /** Stub function */
                        click: function () {
                            eventIsBinded = true;
                        }
                    }
                },
                $suggest = $(suggestSelector).suggest(options);

            $suggest.trigger('click');
            expect(eventIsBinded).toBe(true);
        });

        it('Check suggest focus/blur', function () {
            var suggestInstance = $(suggestSelector).suggest({
                    template: '#test-template',
                    choiceTemplate: '<li/>'
                }).data('mage-suggest'),
                uiHash = {
                    item: {
                        id: 1,
                        label: 'Test Label'
                    }
                };

            expect(suggestInstance._focused).toBe(undefined);
            expect(suggestInstance.element.val()).toBe('');

            suggestInstance._focusItem($.Event('focus'), uiHash);

            expect(suggestInstance._focused).toEqual(uiHash.item);
            expect(suggestInstance.element.val()).toBe(uiHash.item.label);

            suggestInstance._blurItem();

            expect(suggestInstance._focused).toBe(null);
            expect(suggestInstance.element.val()).toBe('');
        });

        it('Check suggest select', function () {
            var suggestInstance = $(suggestSelector).suggest({
                    template: '#test-template',
                    choiceTemplate: '<li/>'
                }).data('mage-suggest'),
                uiHash = {
                    item: {
                        id: 1,
                        label: 'Test Label'
                    }
                };

            suggestInstance._focused = suggestInstance._term = suggestInstance._selectedItem = null;
            suggestInstance.valueField.val('');
            suggestInstance._selectItem($.Event('select'));

            expect(suggestInstance._selectedItem).toBe(null);
            expect(suggestInstance._term).toBe(null);
            expect(suggestInstance.valueField.val()).toBe('');
            expect(suggestInstance.dropdown.is(':hidden')).toBe(true);

            suggestInstance._focused = uiHash.item;
            suggestInstance._selectItem($.Event('select'));

            expect(suggestInstance._selectedItem).toEqual(suggestInstance._focused);
            expect(suggestInstance._term).toBe(suggestInstance._focused.label);
            expect(suggestInstance.valueField.val()).toBe(suggestInstance._focused.id.toString());
            expect(suggestInstance.dropdown.is(':hidden')).toBe(true);
        });

        it('Check suggest multiselect', function () {
            var suggestInstance = $(suggestSelector).suggest({
                    template: '#test-template',
                    choiceTemplate: '<li/>',
                    multiselect: true
                }).data('mage-suggest'),
                uiHash = {
                    item: {
                        id: 1,
                        label: 'Test Label'
                    }
                },
                event = $.Event('select'),
                selectedElement = $('<div/>');

            event.target = selectedElement[0];
            suggestInstance._focused = suggestInstance._term = suggestInstance._selectedItem = null;
            suggestInstance.valueField.val('');
            suggestInstance._selectItem(event);

            expect(suggestInstance._selectedItem).toBe(null);
            expect(suggestInstance._term).toBe(null);
            expect(suggestInstance.valueField.find('option').length).not.toBeGreaterThan(0);
            expect(suggestInstance.dropdown.is(':hidden')).toBe(true);

            suggestInstance._focused = uiHash.item;
            suggestInstance._selectItem(event);

            expect(suggestInstance._selectedItem).toEqual(suggestInstance._focused);
            expect(suggestInstance._term).toBe('');
            expect(suggestInstance._getOption(suggestInstance._focused).length).toBeGreaterThan(0);
            expect(selectedElement.hasClass(suggestInstance.options.selectedClass)).toBe(true);
            expect(suggestInstance.dropdown.is(':hidden')).toBe(true);

            suggestInstance._selectItem(event);
            expect(suggestInstance._selectedItem).toEqual(suggestInstance._nonSelectedItem);
            expect(suggestInstance._term).toBe('');
            expect(suggestInstance._getOption(suggestInstance._focused).length).not.toBeGreaterThan(0);
            expect(selectedElement.hasClass(suggestInstance.options.selectedClass)).toBe(false);
            expect(suggestInstance.dropdown.is(':hidden')).toBe(true);
        });

        it('Check suggest reset value', function () {
            var suggestInstance = $(suggestSelector).suggest({
                template: '#test-template',
                choiceTemplate: '<li/>'
            }).data('mage-suggest');

            suggestInstance.valueField.val('test');
            expect(suggestInstance.valueField.val()).toBe('test');
            suggestInstance._resetSuggestValue();
            expect(suggestInstance.valueField.val()).toBe(suggestInstance._nonSelectedItem.id);
        });

        it('Check suggest reset multiselect value', function () {
            var suggestInstance = $(suggestSelector).suggest({
                    template: '#test-template',
                    choiceTemplate: '<li/>',
                    multiselect: true
                }).data('mage-suggest'),
                uiHash = {
                    item: {
                        id: 1,
                        label: 'Test Label'
                    }
                },
                event = $.Event('select');

            event.target = $('<div/>')[0];
            suggestInstance._focused = uiHash.item;

            suggestInstance._selectItem(event);
            suggestInstance._resetSuggestValue();

            expect(suggestInstance.valueField.val() instanceof Array).toBe(true);
            expect(suggestInstance.valueField.val()[0]).not.toBe(undefined);
            expect(suggestInstance.valueField.val()[0]).toBe(uiHash.item.id.toString());
        });

        it('Check suggest read item data', function () {
            var suggestInstance = $(suggestSelector).suggest({
                    template: '#test-template',
                    choiceTemplate: '<li/>'
                }).data('mage-suggest'),
                testElement = $('<div/>');

            expect(suggestInstance._readItemData(testElement)).toEqual(suggestInstance._nonSelectedItem);
            testElement.data('suggestOption', 'test');
            expect(suggestInstance._readItemData(testElement)).toEqual('test');
        });

        it('Check suggest template', function () {
            var suggestInstance = $(suggestSelector).suggest({
                    template: '<div><%= data.test %></div>',
                    choiceTemplate: '<li/>'
                }).data('mage-suggest'),
                tmpl = suggestInstance.templates[suggestInstance.templateName],
                html = $('<div/>').append(tmpl({
                    data: {
                        test: 'test'
                    }
                })).html();

            expect(html).toEqual('<div>test</div>');
            suggestInstance.destroy();
            $('body').append('<script type="text/template" id="test-template">' +
                '<div><%= data.test %></div>' +
                '</script>');

            suggestInstance = $(suggestSelector).suggest({
                template: '#test-template',
                choiceTemplate: '<li/>'
            }).data('mage-suggest');
            tmpl = suggestInstance.templates[suggestInstance.templateName];
            html = $('<div />').append(tmpl({
                data: {
                    test: 'test'
                }
            })).html();

            expect(html).toEqual('<div>test</div>');
            $('#test-template').remove();
        });

        it('Check suggest dropdown visibility', function () {
            var suggestInstance = $(suggestSelector).suggest({
                template: '#test-template',
                choiceTemplate: '<li/>'
            }).data('mage-suggest');

            suggestInstance.dropdown.hide();
            expect(suggestInstance.isDropdownShown()).toBe(false);
            expect(suggestInstance.dropdown.is(':hidden')).toBe(true);

            suggestInstance.dropdown.show();
            expect(suggestInstance.isDropdownShown()).toBe(true);
            expect(suggestInstance.dropdown.is(':visible')).toBe(true);
        });

        it('Check suggest create option', function () {
            var suggestInstance = $(suggestSelector).suggest({
                    template: '#test-template',
                    choiceTemplate: '<li/>'
                }).data('mage-suggest'),
                uiHash = {
                    item: {
                        id: 1,
                        label: 'Test Label'
                    }
                },
                option = suggestInstance._createOption(uiHash.item);

            expect(option.val()).toBe('1');
            expect(option.prop('selected')).toBe(true);
            expect(option.text()).toBe('Test Label');
            expect(option.data('renderedOption')).not.toBe(undefined);
        });

        it('Check suggest add option', function () {
            var suggestInstance = $(suggestSelector).suggest({
                    template: '#test-template',
                    choiceTemplate: '<li/>'
                }).data('mage-suggest'),
                uiHash = {
                    item: {
                        id: 1,
                        label: 'Test Label'
                    }
                },
                selectTarget = $('<div/>'),
                event = $.Event('add'),
                option;

            event.target = selectTarget[0];
            suggestInstance._addOption(event, uiHash.item);
            option = suggestInstance.valueField.find('option[value=' + uiHash.item.id + ']');

            expect(option.length).toBeGreaterThan(0);
            expect(option.data('selectTarget').is(selectTarget)).toBe(true);
        });

        it('Check suggest get option', function () {
            var suggestInstance = $(suggestSelector).suggest({
                    template: '#test-template',
                    choiceTemplate: '<li/>'
                }).data('mage-suggest'),
                uiHash = {
                    item: {
                        id: 1,
                        label: 'Test Label'
                    }
                },
                option = $('<option value="1">Test Label</option>');

            expect(suggestInstance._getOption(uiHash.item).length).not.toBeGreaterThan(0);

            suggestInstance.valueField.append(option);
            expect(suggestInstance._getOption(uiHash.item).length).toBeGreaterThan(0);
            expect(suggestInstance._getOption(option).length).toBeGreaterThan(0);
        });

        it('Check suggest last added', function () {
            var suggestInstance = $(suggestSelector).suggest({
                    template: '#test-template',
                    choiceTemplate: '<li/>',
                    multiselect: true
                }).data('mage-suggest'),
                uiHash = {
                    item: {
                        id: 1,
                        label: 'Test Label'
                    }
                };

            suggestInstance._addOption({}, uiHash.item);
            expect(suggestInstance.valueField.find('option').length).toBeGreaterThan(0);
            suggestInstance._removeLastAdded();
            expect(suggestInstance.valueField.find('option').length).not.toBeGreaterThan(0);
        });

        it('Check suggest remove option', function () {
            var suggestInstance = $(suggestSelector).suggest({
                    template: '#test-template',
                    choiceTemplate: '<li/>',
                    multiselect: true
                }).data('mage-suggest'),
                uiHash = {
                    item: {
                        id: 1,
                        label: 'Test Label'
                    }
                },
                selectTarget = $('<div/>'),
                event = $.Event('select');

            selectTarget.addClass(suggestInstance.options.selectedClass);
            event.target = selectTarget[0];

            suggestInstance._addOption(event, uiHash.item);
            expect(suggestInstance.valueField.find('option').length).toBeGreaterThan(0);
            suggestInstance.removeOption(event, uiHash.item);
            expect(suggestInstance.valueField.find('option').length).not.toBeGreaterThan(0);
            expect(selectTarget.hasClass(suggestInstance.options.selectedClass)).toBe(false);
        });
    });
});
