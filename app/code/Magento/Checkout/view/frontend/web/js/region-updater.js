/**
 * @category    frontend Checkout region-updater
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true expr:true*/
define([
    "jquery",
    "jquery/ui",
    "jquery/template",
    "mage/validation"
], function($){
    "use strict";
    
    $.widget('mage.regionUpdater', {
        options: {
            regionTemplate: '<option value="${value}" title="${title}" {{if isSelected}}selected="selected"{{/if}}>${title}</option>',
            isRegionRequired: true,
            isZipRequired: true,
            isCountryRequired: true,
            currentRegion: null
        },

        _create: function() {
            this.currentRegionOption = this.options.currentRegion;
            this._updateRegion(this.element.find('option:selected').val());
            this.element.on('change', $.proxy(function(e) {
                this._updateRegion($(e.target).val());
            }, this));
            if (this.isCountryRequired) {
                this.element.addClass('required-entry');
            }
            $(this.options.regionListId).on('change', $.proxy(function(e) {
                this.setOption = false;
                this.currentRegionOption = $(e.target).val();
            }, this));
            $(this.options.regionInputId).on('focusout', $.proxy(function() {
                this.setOption = true;
            }, this));
        },

        /**
         * Remove options from dropdown list
         * @param {object} selectElement - jQuery object for dropdown list
         * @private
         */
        _removeSelectOptions: function(selectElement) {
            selectElement.find('option').each(function(index) {
                if (index) {
                    $(this).remove();
                }
            });
        },

        /**
         * Render dropdown list
         * @param {object} selectElement - jQuery object for dropdown list
         * @param {string} key - region code
         * @param {object} value - region object
         * @private
         */
        _renderSelectOption: function(selectElement, key, value) {
            selectElement.append($.proxy(function() {
                var name = value.name.replace(/[!"#$%&'()*+,.\/:;<=>?@[\\\]^`{|}~]/g, "\\$&");
                if (value.code &&  $(name).is('span')) {
                    key = value.code;
                    value.name = $(name).text();
                }
                $.template('regionTemplate', this.options.regionTemplate);
                if (this.options.defaultRegion === key) {
                    return $.tmpl('regionTemplate', {value: key, title: value.name, isSelected: true});
                } else {
                    return $.tmpl('regionTemplate', {value: key, title: value.name});
                }
            }, this));
        },

        /**
         * Takes clearError callback function as first option
         * If no form is passed as option, look up the closest form and call clearError method.
         * @private
         */
        _clearError: function() {
            if (this.options.clearError && typeof(this.options.clearError) === "function") {
                this.options.clearError.call(this);
            } else {
                if (!this.options.form) {
                    this.options.form = this.element.closest('form').length ? $(this.element.closest('form')[0]) : null;
                }
                this.options.form && this.options.form.data('validation') && this.options.form.validation('clearError',
                    this.options.regionListId, this.options.regionInputId, this.options.postcodeId);
            }
        },
        /**
         * Update dropdown list based on the country selected
         * @param {string} country - 2 uppercase letter for country code
         * @private
         */
        _updateRegion: function(country) {
            // Clear validation error messages
            var regionList = $(this.options.regionListId),
                regionInput = $(this.options.regionInputId),
                postcode = $(this.options.postcodeId),
                label = regionList.parent().siblings('label'),
                requiredLabel = regionList.parents('div.field');
            this._clearError();
            this._checkRegionRequired(country);
            // Populate state/province dropdown list if available or use input box
            if (this.options.regionJson[country]) {
                this._removeSelectOptions(regionList);
                $.each(this.options.regionJson[country], $.proxy(function(key, value) {
                    this._renderSelectOption(regionList, key, value);
                }, this));
                if (this.currentRegionOption) {
                    regionList.val(this.currentRegionOption);
                }
                if (this.setOption) {
                    regionList.find("option").filter(function() {
                        return this.text === regionInput.val();
                    }).attr('selected', true);
                }
                if (this.options.isRegionRequired) {
                    regionList.addClass('required-entry').removeAttr('disabled');
                    requiredLabel.addClass('required');
                } else {
                    regionList.removeClass('required-entry validate-select').removeAttr('data-validate');
                    requiredLabel.removeClass('required');
                    if (!this.options.optionalRegionAllowed) {
                        regionList.attr('disabled', 'disabled');
                    }
                }
                regionList.show();
                regionInput.hide();
                label.attr('for', regionList.attr('id'));
            } else {
                if (this.options.isRegionRequired) {
                    regionInput.addClass('required-entry').removeAttr('disabled');
                    requiredLabel.addClass('required');
                } else {
                    if (!this.options.optionalRegionAllowed) {
                        regionInput.attr('disabled', 'disabled');
                    }
                }
                regionList.removeClass('required-entry').hide();
                regionInput.show();
                requiredLabel.removeClass('required');
                label.attr('for', regionInput.attr('id'));
            }
            // If country is in optionalzip list, make postcode input not required
            if (this.options.isZipRequired) {
                $.inArray(country, this.options.countriesWithOptionalZip) >= 0 ?
                    postcode.removeClass('required-entry').closest('.field').removeClass('required') :
                    postcode.removeClass('required-entry').closest('.field').addClass('required');
            }
            // Add defaultvalue attribute to state/province select element
            regionList.attr('defaultvalue', this.options.defaultRegion);
        },

        /**
         * Check if the selected country has a mandatory region selection
         *
         * @param {string} country Code of the country - 2 uppercase letter for country code
         * @private
         */
        _checkRegionRequired: function(country) {
            this.options.isRegionRequired = false;
            var self = this;
            $.each(this.options.regionJson.config.regions_required, function(index, elem){
                if (elem == country) {
                    self.options.isRegionRequired = true;
                }
            });
        }
    });
    
    return $.mage.regionUpdater;
});
