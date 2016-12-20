/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    $.widget('mage.addToWishlist', {
        options: {
            bundleInfo: 'div.control [name^=bundle_option]',
            configurableInfo: '.super-attribute-select',
            groupedInfo: '#super-product-table input',
            downloadableInfo: '#downloadable-links-list input',
            customOptionsInfo: '.product-custom-option',
            qtyInfo: '#qty'
        },

        /** @inheritdoc */
        _create: function () {
            this._bind();
        },

        /**
         * @private
         */
        _bind: function () {
            var options = this.options,
                dataUpdateFunc = '_updateWishlistData',
                changeCustomOption = 'change ' + options.customOptionsInfo,
                changeQty = 'change ' + options.qtyInfo,
                events = {},
                key;

            if ('productType' in options) {
                if (typeof options.productType === 'string') {
                    options.productType = [options.productType];
                }
            } else {
                options.productType = [];
            }

            events[changeCustomOption] = dataUpdateFunc;
            events[changeQty] = dataUpdateFunc;

            for (key in options.productType) {
                if (options.productType.hasOwnProperty(key) && options.productType[key] + 'Info' in options) {
                    events['change ' + options[options.productType[key] + 'Info']] = dataUpdateFunc;
                }
            }
            this._on(events);
        },

        /**
         * @param {jQuery.Event} event
         * @private
         */
        _updateWishlistData: function (event) {
            var dataToAdd = {},
                isFileUploaded = false,
                self = this;

            if (event.handleObj.selector == this.options.qtyInfo) { //eslint-disable-line eqeqeq
                this._updateAddToWishlistButton({});
                event.stopPropagation();

                return;
            }
            $(event.handleObj.selector).each(function (index, element) {
                if ($(element).is('input[type=text]') ||
                    $(element).is('input[type=email]') ||
                    $(element).is('input[type=number]') ||
                    $(element).is('input[type=hidden]') ||
                    $(element).is('input[type=checkbox]:checked') ||
                    $(element).is('input[type=radio]:checked') ||
                    $(element).is('textarea') ||
                    $('#' + element.id + ' option:selected').length
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

        /**
         * @param {Object} dataToAdd
         * @private
         */
        _updateAddToWishlistButton: function (dataToAdd) {
            var self = this;

            $('[data-action="add-to-wishlist"]').each(function (index, element) {
                var params = $(element).data('post');

                if (!params) {
                    params = {
                        'data': {}
                    };
                }

                if (!$.isEmptyObject(dataToAdd)) {
                    self._removeExcessiveData(params, dataToAdd);
                }

                params.data = $.extend({}, params.data, dataToAdd, {
                    'qty': $(self.options.qtyInfo).val()
                });
                $(element).data('post', params);
            });
        },

        /**
         * @param {Object} array1
         * @param {Object} array2
         * @return {Object}
         * @private
         */
        _arrayDiffByKeys: function (array1, array2) {
            var result = {};

            $.each(array1, function (key, value) {
                if (key.indexOf('option') === -1) {
                    return;
                }

                if (!array2[key]) {
                    result[key] = value;
                }
            });

            return result;
        },

        /**
         * @param {HTMLElement} element
         * @return {Object}
         * @private
         */
        _getElementData: function (element) {
            var data, elementName, elementValue;

            element = $(element);
            data = {};
            elementName = element.data('selector') ? element.data('selector') : element.attr('name');
            elementValue = element.val();

            if (element.is('select[multiple]') && elementValue !== null) {
                if (elementName.substr(elementName.length - 2) == '[]') { //eslint-disable-line eqeqeq
                    elementName = elementName.substring(0, elementName.length - 2);
                }
                $.each(elementValue, function (key, option) {
                    data[elementName + '[' + option + ']'] = option;
                });
            } else {
                if (elementValue) { //eslint-disable-line no-lonely-if
                    if (elementName.substr(elementName.length - 2) == '[]') { //eslint-disable-line eqeqeq, max-depth
                        elementName = elementName.substring(0, elementName.length - 2);

                        if (elementValue) { //eslint-disable-line max-depth
                            data[elementName + '[' + elementValue + ']'] = elementValue;
                        }
                    } else {
                        data[elementName] = elementValue;
                    }
                }
            }

            return data;
        },

        /**
         * @param {Object} params
         * @param {Object} dataToAdd
         * @private
         */
        _removeExcessiveData: function (params, dataToAdd) {
            var dataToRemove = this._arrayDiffByKeys(params.data, dataToAdd);

            $.each(dataToRemove, function (key) {
                delete params.data[key];
            });
        },

        /**
         * Bind form submit.
         */
        bindFormSubmit: function () {
            var self = this;

            $('[data-action="add-to-wishlist"]').on('click', function (event) {
                var element, params, form, action;

                event.stopPropagation();
                event.preventDefault();

                element = $('input[type=file]' + self.options.customOptionsInfo);
                params = $(event.currentTarget).data('post');
                form = $(element).closest('form');
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
