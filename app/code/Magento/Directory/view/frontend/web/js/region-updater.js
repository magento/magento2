/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/template',
    'underscore',
    'jquery-ui-modules/widget',
    'mage/validation'
], function ($, mageTemplate, _) {
    'use strict';

    $.widget('mage.directoryRegionUpdater', {
        options: {
            regionTemplate:
                '<option value="<%- data.value %>" <% if (data.isSelected) { %>selected="selected"<% } %>>' +
                '<%- data.title %>' +
                '</option>',
            isRegionRequired: true,
            isZipRequired: true,
            isCountryRequired: true,
            currentRegion: null,
            isMultipleCountriesAllowed: true
        },

        /**
         *
         * @private
         */
        _create: function () {
            this._initCountryElement();

            this.currentRegionOption = this.options.currentRegion;
            this.regionTmpl = mageTemplate(this.options.regionTemplate);

            this._updateRegion(this.element.find('option:selected').val());

            $(this.options.regionListId).on('change', $.proxy(function (e) {
                this.setOption = false;
                this.currentRegionOption = $(e.target).val();

                if (!this.currentRegionOption) {
                    $(this.options.regionListId).add(this.options.regionInputId).val('');
                }
            }, this));

            $(this.options.regionInputId).on('focusout', $.proxy(function () {
                this.setOption = true;
            }, this));
        },

        /**
         *
         * @private
         */
        _initCountryElement: function () {

            if (this.options.isMultipleCountriesAllowed) {
                this.element.parents('div.field').show();
                this.element.on('change', $.proxy(function (e) {
                    // clear region inputs on country change
                    $(this.options.regionListId).val('');
                    $(this.options.regionInputId).val('');
                    this._updateRegion($(e.target).val());
                }, this));

                if (this.options.isCountryRequired) {
                    this.element.addClass('required-entry');
                    this.element.parents('div.field').addClass('required');
                }
            } else {
                this.element.parents('div.field').hide();
            }
        },

        /**
         * Remove options from dropdown list
         *
         * @param {Object} selectElement - jQuery object for dropdown list
         * @private
         */
        _removeSelectOptions: function (selectElement) {
            selectElement.find('option').each(function (index) {
                if (index) {
                    $(this).remove();
                }
            });
        },

        /**
         * Render dropdown list
         * @param {Object} selectElement - jQuery object for dropdown list
         * @param {String} key - region code
         * @param {Object} value - region object
         * @private
         */
        _renderSelectOption: function (selectElement, key, value) {
            selectElement.append($.proxy(function () {
                var name = value.name.replace(/[!"#$%&'()*+,.\/:;<=>?@[\\\]^`{|}~]/g, '\\$&'),
                    tmplData,
                    tmpl;

                if (value.code && $(name).is('span')) {
                    key = value.code;
                    value.name = $(name).text();
                }

                tmplData = {
                    value: key,
                    title: value.name,
                    isSelected: false
                };

                if (this.options.defaultRegion === key) {
                    tmplData.isSelected = true;
                }

                tmpl = this.regionTmpl({
                    data: tmplData
                });

                return $(tmpl);
            }, this));
        },

        /**
         * Takes clearError callback function as first option
         * If no form is passed as option, look up the closest form and call clearError method.
         * @private
         */
        _clearError: function () {
            var args = ['clearError', this.options.regionListId, this.options.regionInputId, this.options.postcodeId];

            if (this.options.clearError && typeof this.options.clearError === 'function') {
                this.options.clearError.call(this);
            } else {
                if (!this.options.form) {
                    this.options.form = this.element.closest('form').length ? $(this.element.closest('form')[0]) : null;
                }

                this.options.form = $(this.options.form);

                this.options.form && this.options.form.data('validator') &&
                this.options.form.validation.apply(this.options.form, _.compact(args));

                // Clean up errors on region & zip fix
                $(this.options.regionInputId).removeClass('mage-error').parent().find('[generated]').remove();
                $(this.options.regionListId).removeClass('mage-error').parent().find('[generated]').remove();
                $(this.options.postcodeId).removeClass('mage-error').parent().find('[generated]').remove();
            }
        },

        /**
         * Update dropdown list based on the country selected
         *
         * @param {String} country - 2 uppercase letter for country code
         * @private
         */
        _updateRegion: function (country) {
            // Clear validation error messages
            var regionList = $(this.options.regionListId),
                regionInput = $(this.options.regionInputId),
                postcode = $(this.options.postcodeId),
                label = regionList.parent().siblings('label'),
                container = regionList.parents('div.field'),
                regionsEntries,
                regionId,
                regionData;

            this._clearError();
            this._checkRegionRequired(country);

            // Populate state/province dropdown list if available or use input box
            if (this.options.regionJson[country]) {
                this._removeSelectOptions(regionList);
                regionsEntries = _.pairs(this.options.regionJson[country]);
                $.each(regionsEntries, $.proxy(function (key, value) {
                    regionData = value[1];
                    regionId = regionData.id;
                    this._renderSelectOption(regionList, regionId.toString(), regionData);
                }, this));

                if (this.currentRegionOption) {
                    regionList.val(this.currentRegionOption);
                }

                if (this.setOption) {
                    regionList.find('option').filter(function () {
                        return this.text === regionInput.val();
                    }).attr('selected', true);
                }

                if (this.options.isRegionRequired) {
                    regionList.addClass('required-entry').removeAttr('disabled');
                    container.addClass('required').show();
                } else {
                    regionList.removeClass('required-entry validate-select').removeAttr('data-validate');
                    container.removeClass('required');

                    if (!this.options.optionalRegionAllowed) { //eslint-disable-line max-depth
                        regionList.hide();
                        container.hide();
                    } else {
                        regionList.removeAttr('disabled').show();
                    }
                }

                regionList.show();
                regionInput.hide();
                label.attr('for', regionList.attr('id'));
            } else {
                this._removeSelectOptions(regionList);

                if (this.options.isRegionRequired) {
                    regionInput.addClass('required-entry').removeAttr('disabled');
                    container.addClass('required').show();
                } else {
                    if (!this.options.optionalRegionAllowed) { //eslint-disable-line max-depth
                        regionInput.attr('disabled', 'disabled');
                        container.hide();
                    }
                    container.removeClass('required');
                    regionInput.removeClass('required-entry');
                }

                regionList.removeClass('required-entry').prop('disabled', 'disabled').hide();
                regionInput.show();
                label.attr('for', regionInput.attr('id'));
            }

            // If country is in optionalzip list, make postcode input not required
            if (this.options.isZipRequired) {
                $.inArray(country, this.options.countriesWithOptionalZip) >= 0 ?
                    postcode.removeClass('required-entry').closest('.field').removeClass('required') :
                    postcode.addClass('required-entry').closest('.field').addClass('required');
            }

            // Add defaultvalue attribute to state/province select element
            regionList.attr('defaultvalue', this.options.defaultRegion);
            this.options.form.find('[type="submit"]').removeAttr('disabled').show();
        },

        /**
         * Check if the selected country has a mandatory region selection
         *
         * @param {String} country - Code of the country - 2 uppercase letter for country code
         * @private
         */
        _checkRegionRequired: function (country) {
            var self = this;

            this.options.isRegionRequired = false;
            $.each(this.options.regionJson.config['regions_required'], function (index, elem) {
                if (elem === country) {
                    self.options.isRegionRequired = true;
                }
            });
        }
    });

    return $.mage.directoryRegionUpdater;
});
