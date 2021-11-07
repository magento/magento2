/*
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*eslint-disable max-nested-callbacks*/
/*jscs:disable jsDoc*/
define([
    'jquery',
    'Magento_Directory/js/region-updater'
], function ($) {
    'use strict';

    var regionJson = {
            'config': {
                'show_all_regions': true,
                'regions_required': [
                    'US'
                ]
            },
            'US':
                [
                    {
                        'id': 1,
                        'code': 'AL',
                        'name': 'Alabama'
                    },
                    {
                        'id': 2,
                        'code': 'AK',
                        'name': 'Alaska'
                    },
                    {
                        'id': 3,
                        'code': 'AS',
                        'name': 'American Samoa'
                    }
                ],
            'DE':
                [
                    {
                        'id': 81,
                        'code': 'BAY',
                        'name': 'Bayern'
                    },
                    {
                        'id': 82,
                        'code': 'BER',
                        'name': 'Berlin'
                    },
                    {
                        'id': 83,
                        'code': 'BRG',
                        'name': 'Brandenburg'
                    }
                ]
        },
        defaultCountry = 'GB',
        countries = {
            '': '',
            'US': 'United States',
            'GB': 'United Kingdom',
            'DE': 'Germany',
            'IT': 'Italy'
        },
        regions = {
            '': 'Please select a region, state or province.'
        },
        countryEl,
        regionSelectEl,
        regionInputEl,
        postalCodeEl,
        formEl,
        containerEl;

    function createFormField() {
        var fieldWrapperEl = document.createElement('div'),
            labelEl = document.createElement('label'),
            inputControlEl = document.createElement('div'),
            i;

        fieldWrapperEl.appendChild(labelEl);
        fieldWrapperEl.appendChild(inputControlEl);

        for (i = 0; i < arguments.length; i++) {
            inputControlEl.appendChild(arguments[i]);
        }
        labelEl.setAttribute('class', 'label');
        fieldWrapperEl.setAttribute('class', 'field required');
        inputControlEl.setAttribute('class', 'control');

        return fieldWrapperEl;
    }

    function buildSelectOptions(select, options, defaultOption) {
        var optionValue,
            optionEl;

        defaultOption = typeof defaultOption === 'undefined' ? '' : defaultOption;

        // eslint-disable-next-line guard-for-in
        for (optionValue in options) {
            if (options.hasOwnProperty(optionValue)) {
                optionEl = document.createElement('option');
                optionEl.setAttribute('value', optionValue);
                optionEl.textContent = countries[optionValue];
                // eslint-disable-next-line max-depth
                if (defaultOption === optionValue) {
                    optionEl.setAttribute('selected', 'selected');
                }
                select.add(optionEl);
            }
        }
    }

    function init(config) {
        var defaultConfig = {
            'optionalRegionAllowed': true,
            'regionListId': '#' + regionSelectEl.id,
            'regionInputId': '#' + regionInputEl.id,
            'postcodeId': '#' + postalCodeEl.id,
            'form': '#' + formEl.id,
            'regionJson': regionJson,
            'defaultRegion': 0,
            'countriesWithOptionalZip': ['GB']
        };

        $(countryEl).directoryRegionUpdater($.extend({}, defaultConfig, config || {}));
    }

    beforeEach(function () {
        containerEl = document.createElement('div');
        formEl = document.createElement('form');
        regionSelectEl = document.createElement('select');
        regionInputEl = document.createElement('input');
        postalCodeEl = document.createElement('input');
        countryEl = document.createElement('select');
        regionSelectEl.setAttribute('id', 'dir_region_id');
        regionSelectEl.setAttribute('style', 'display:none;');
        regionInputEl.setAttribute('id', 'dir_region');
        regionInputEl.setAttribute('style', 'display:none;');
        countryEl.setAttribute('id', 'dir_country');
        postalCodeEl.setAttribute('id', 'zip');
        formEl.setAttribute('id', 'dir_test_form');
        formEl.appendChild(createFormField(countryEl));
        formEl.appendChild(createFormField(regionSelectEl, regionInputEl));
        formEl.appendChild(createFormField(postalCodeEl));
        containerEl.appendChild(formEl);
        buildSelectOptions(regionSelectEl, regions);
        buildSelectOptions(countryEl, countries, defaultCountry);
        document.body.appendChild(containerEl);
    });

    afterEach(function () {
        $(containerEl).remove();
        formEl = undefined;
        containerEl = undefined;
        regionSelectEl = undefined;
        regionInputEl = undefined;
        postalCodeEl = undefined;
        countryEl = undefined;
    });

    describe('Magento_Directory/js/region-updater', function () {
        it('Check that default country is selected', function () {
            init();
            expect($(countryEl).val()).toBe(defaultCountry);
        });
        it('Check that region list is not displayed when selected country has no predefined regions', function () {
            init();
            $(countryEl).val('GB').trigger('change');
            expect($(regionInputEl).is(':visible')).toBe(true);
            expect($(regionInputEl).is(':disabled')).toBe(false);
            expect($(regionSelectEl).is(':visible')).toBe(false);
            expect($(regionSelectEl).is(':disabled')).toBe(true);
        });
        it('Check country that has predefined and optional regions', function () {
            init();
            $(countryEl).val('DE').trigger('change');
            expect($(regionSelectEl).is(':visible')).toBe(true);
            expect($(regionSelectEl).is(':disabled')).toBe(false);
            expect($(regionSelectEl).hasClass('required-entry')).toBe(false);
            expect($(regionInputEl).is(':visible')).toBe(false);
            expect(
                $(regionSelectEl).find('option')
                    .map(function () {
                        return this.textContent;
                    })
                    .get()
            ).toContain('Berlin');
        });
        it('Check country that has predefined and required regions', function () {
            init();
            $(countryEl).val('US').trigger('change');
            expect($(regionSelectEl).is(':visible')).toBe(true);
            expect($(regionSelectEl).is(':disabled')).toBe(false);
            expect($(regionSelectEl).hasClass('required-entry')).toBe(true);
            expect($(regionInputEl).is(':visible')).toBe(false);
            expect(
                $(regionSelectEl).find('option')
                    .map(function () {
                        return this.textContent;
                    })
                    .get()
            ).toContain('Alaska');
        });
        it('Check that region fields are not displayed for country with optional regions if configured', function () {
            init({
                optionalRegionAllowed: false
            });
            $(countryEl).val('DE').trigger('change');
            expect($(regionSelectEl).is(':visible')).toBe(false);
            expect($(regionInputEl).is(':visible')).toBe(false);
        });
        it('Check that initial values are not overwritten - region input', function () {
            $(countryEl).val('GB');
            $(regionInputEl).val('Liverpool');
            $(postalCodeEl).val('L13 0AL');
            init();
            expect($(countryEl).val()).toBe('GB');
            expect($(regionInputEl).val()).toBe('Liverpool');
            expect($(postalCodeEl).val()).toBe('L13 0AL');
        });
        it('Check that initial values are not overwritten - region select', function () {
            $(countryEl).val('US');
            $(postalCodeEl).val('99501');
            init({
                defaultRegion: '2'
            });
            expect($(countryEl).val()).toBe('US');
            expect($(regionSelectEl).find('option:selected').text()).toBe('Alaska');
            expect($(postalCodeEl).val()).toBe('99501');
        });
        it('Check that region values are cleared out on country change - region input', function () {
            $(countryEl).val('GB');
            $(regionInputEl).val('Liverpool');
            init();
            $(countryEl).val('IT').trigger('change');
            expect($(countryEl).val()).toBe('IT');
            expect($(regionInputEl).val()).toBe('');
        });
        it('Check that region values are cleared out on country change - region select', function () {
            $(countryEl).val('US');
            init({
                defaultRegion: '2'
            });
            $(countryEl).val('DE').trigger('change');
            expect($(countryEl).val()).toBe('DE');
            expect($(regionSelectEl).val()).toBe('');
        });
    });
});
