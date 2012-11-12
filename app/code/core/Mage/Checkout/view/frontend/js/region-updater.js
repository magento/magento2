/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    frontend Checkout region-updater
 * @package     mage
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint browser:true jquery:true expr:true*/
(function($) {
    $.widget('mage.regionUpdater', {
        options: {
        },
        _create: function() {
            this._updateRegion(this.element.find('option:selected').val());
            this.element.on('change', $.proxy(function(e) {
                this._updateRegion($(e.target).val());
            }, this));
            // Form validation
            this.element.addClass('required');
            $(this.options.formId).mage().validate();
            $(this.options.formId + ' button').on('click', $.proxy(function() {
                $(this.options.formId).submit();
            }, this));
        },
        _updateRegion: function(country) {
            // Clear validation error messages
            var form = $(this.options.formId),
                regionList = $(this.options.regionListId),
                regionInput = $(this.options.regionInputId),
                postcode = $(this.options.postcodeId);
            form.find('div.mage-error').remove();
            form.find('.mage-error').removeClass('mage-error');
            // Populate state/province dropdown list if available or use input box
            if (this.options.regionJson[country]) {
                regionList.find('option').each(function (index){
                    index && $(this).remove();
                });
                $.each(this.options.regionJson[country], $.proxy(function(key, value) {
                    regionList.append($.proxy(function() {
                        var option = '<option value="%v" title="%t">%t</option>';
                        var optionWithSelect = '<option value="%v" title="%t" selected="selected">%t</option>';
                        if (this.options.defaultRegion === key) {
                            return optionWithSelect.replace(/%v/g, key).replace(/%t/g, value.name);
                        } else {
                            return option.replace(/%v/g, key).replace(/%t/g, value.name);
                        }
                    }, this));
                }, this));
                regionList.addClass('required').show();
                regionInput.removeClass('required').hide();
            } else {
                regionList.removeClass('required').hide();
                regionInput.addClass('required').show();
            }
            // If country is in optionalzip list, make postcode input not required
            $.inArray(country, this.options.countriesWithOptionalZip) >= 0 ?
                regionList.add(regionInput).add(postcode).removeClass('required') :
                regionList.add(regionInput).add(postcode).addClass('required');
            // Add defaultvalue attribute to state/province select element
            regionList.attr('defaultvalue', this.options.defaultRegion);
        }
    });
}(jQuery));