/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
define([
    "jquery",
    "jquery/ui"
], function($){
    "use strict";
    
    $.widget('mage.addToWishlist', {
        options: {
            bundleInfo: 'div.control [name^=bundle_option]:not([name*=qty])',
            configurableInfo: '.super-attribute-select',
            groupedInfo: '#super-product-table input',
            downloadableInfo: '#downloadable-links-list input',
            customOptionsInfo: '.product-custom-option',
            qtyInfo: '#qty'
        },
        _create: function () {
            this._bind();
        },
        _bind: function() {
            var changeCustomOption = 'change ' + this.options.customOptionsInfo,
                changeQty = 'change ' + this.options.qtyInfo,
                changeProductInfo = 'change ' + this.options[this.options.productType + 'Info'],
                events = {};
            events[changeCustomOption] = '_updateWishlistData';
            events[changeProductInfo] = '_updateWishlistData';
            events[changeQty] = '_updateWishlistData';
            this._on(events);
        },
        _updateWishlistData: function(event) {
            var dataToAdd = {},
                dataOrigin = {};
            var self = this;
            $(event.handleObj.selector).each(function(index, element){
                dataOrigin = $.extend({}, dataOrigin, self._getElementData(element, 1));
                if ($(element).is(':checked') || $(element).find(':checked').length) {
                    dataToAdd = $.extend({}, dataToAdd, self._getElementData(element));
                }
            });
            $('[data-action="add-to-wishlist"]').each(function(index, element) {
                var params = $(element).data('post');
                if (!params)
                    params = {};
                self._removeExcessiveData(params, dataOrigin, dataToAdd);
                params.data = $.extend({}, params.data, dataToAdd, {'qty': $(self.options.qtyInfo).val()});
                $(element).data('post', params);
            });
            event.stopPropagation();
        },
        _arrayDiffByKeys: function(array1, array2) {
            var result = {};
            $.each(array1, function(key, value) {
                if (!array2[key])
                    result[key] = value;
            });
            return result;
        },
        _getElementData: function(element, origin) {
            var data = {},
                elementName = $(element).attr('name'),
                elementValue = $(element).val();
            if (origin && $(element).is('select')) {
                elementValue = $(element).find('option');
            }
            if ($(element).is('select[multiple]')) {
                $.each(elementValue, function(key, option) {
                    var value = origin ? option.value : option;
                    data[elementName + '[' + value + ']'] = value;
                });
            } else {
                data[elementName] = elementValue;
            }
            return data;
        },
        _removeExcessiveData: function(params, dataOrigin, dataToAdd) {
            var dataToRemove = this._arrayDiffByKeys(dataOrigin, dataToAdd);
            $.each(dataToRemove, function(key, value) {
                delete params.data[key];
            });
        }
    });
    
    return $.mage.addToWishlist;
});