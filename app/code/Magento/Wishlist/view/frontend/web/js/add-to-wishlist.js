/**
 * Copyright © 2016 Magento. All rights reserved.
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
            bundleInfo: 'div.control [name^=bundle_option]',
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
            var options = this.options,
                dataUpdateFunc = '_updateWishlistData',
                changeCustomOption = 'change ' + options.customOptionsInfo,
                changeQty = 'change ' + options.qtyInfo,
                events = {};

            if ('productType' in options) {
                if (typeof options.productType === 'string') {
                    options.productType = [options.productType];
                }
            } else {
                options.productType = [];
            }

            events[changeCustomOption] = dataUpdateFunc;
            events[changeQty] = dataUpdateFunc;

            for (var key in options.productType) {
                if (options.productType.hasOwnProperty(key) && options.productType[key] + 'Info' in options) {
                    events['change ' + options[options.productType[key] + 'Info']] = dataUpdateFunc;
                }
            }
            this._on(events);
        },
        _updateWishlistData: function(event) {
            var dataToAdd = {},
                isFileUploaded = false;
            if (event.handleObj.selector == this.options.qtyInfo) {
                this._updateAddToWishlistButton({});
                event.stopPropagation();
                return;
            }
            var self = this;
            $(event.handleObj.selector).each(function(index, element){
                if ($(element).is('input[type=text]')
                    || $(element).is('input[type=email]')
                    || $(element).is('input[type=number]')
                    || $(element).is('input[type=hidden]')
                    || $(element).is('input[type=checkbox]:checked')
                    || $(element).is('input[type=radio]:checked')
                    || $(element).is('textarea')
                    || $('#' + element.id + ' option:selected').length
                ) {
                    dataToAdd = $.extend({}, dataToAdd, self._getElementData(element));
                    return;
                }
                if ($(element).is('input[type=file]') && $(element).val()) {
                    isFileUploaded = true;
                }
            });
            if (isFileUploaded) {
                this.bindFormSubmit();
            }
            this._updateAddToWishlistButton(dataToAdd);
            event.stopPropagation();
        },
        _updateAddToWishlistButton: function(dataToAdd) {
            var self = this;
            $('[data-action="add-to-wishlist"]').each(function(index, element) {
                var params = $(element).data('post');
                if (!params)
                    params = {'data': {}};

                if (!$.isEmptyObject(dataToAdd)) {
                    self._removeExcessiveData(params, dataToAdd);
                }

                params.data = $.extend({}, params.data, dataToAdd, {'qty': $(self.options.qtyInfo).val()});
                $(element).data('post', params);
            });
        },
        _arrayDiffByKeys: function(array1, array2) {
            var result = {};
            $.each(array1, function(key, value) {
                if (key.indexOf('option') === -1) {
                    return;
                }
                if (!array2[key])
                    result[key] = value;
            });
            return result;
        },
        _getElementData: function(element) {
            element = $(element);
            var data = {},
                elementName = element.data('selector') ? element.data('selector') : element.attr('name'),
                elementValue = element.val();
            if (element.is('select[multiple]') && elementValue !== null) {
                if (elementName.substr(elementName.length - 2) == '[]') {
                    elementName = elementName.substring(0, elementName.length - 2);
                }
                $.each(elementValue, function (key, option) {
                    data[elementName + '[' + option + ']'] = option;
                });
            } else {
                if (elementValue) {
                    if (elementName.substr(elementName.length - 2) == '[]') {
                        elementName = elementName.substring(0, elementName.length - 2);
                        if (elementValue) {
                            data[elementName + '[' + elementValue + ']'] = elementValue;
                        }
                    } else {
                        data[elementName] = elementValue;
                    }
                }
            }
            return data;
        },
        _removeExcessiveData: function(params, dataToAdd) {
            var dataToRemove = this._arrayDiffByKeys(params.data, dataToAdd);
            $.each(dataToRemove, function(key, value) {
                delete params.data[key];
            });
        },
        bindFormSubmit: function() {
            var self = this;
            $('[data-action="add-to-wishlist"]').on('click', function(event) {
                event.stopPropagation();
                event.preventDefault();

                var element = $('input[type=file]' + self.options.customOptionsInfo),
                    params = $(event.currentTarget).data('post'),
                    form = $(element).closest('form'),
                    action = params.action;
                if (params.data.id) {
                    $('<input>', {
                        type: 'hidden',
                        name: 'id',
                        value: params.data.id
                    }).appendTo(form);
                }
                if (params.data.uenc) {
                    action += 'uenc/' + params.data.uenc;
                }

                $(form).attr('action', action).submit();
            });
        }
    });
    
    return $.mage.addToWishlist;
});
