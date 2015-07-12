/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'underscore',
    'jquery/ui',
    'mage/dropdown'
], function ($, _) {
    'use strict';

    var openDropDown = null;
    $.widget('mage.addToCart', {
        options: {
            showAddToCart: true,
            submitUrl: '',
            singleOpenDropDown: true,
            dialog: {}, // Options for mage/dropdown
            dialogDelay: 500, // Delay in ms after resize dropdown shown again

            // Selectors
            cartForm: '.form.map.checkout',
            cartButtonId: '', // better to be cartButton
            popupId: '', // better to be popup
            realPrice: '',
            msrpPrice: '',
            helpLinkId: '', // better to be helpLink
            addToCartButton: '',

            // Text options
            productName: '',
            addToCartUrl: ''
        },

        openDropDown: null,

        /**
         * Creates widget instance
         * @private
         */
        _create: function () {
            $(this.options.cartButtonId).on('click', this._addToCartSubmit.bind(this));

            $(this.options.popupId).on('click', function (event) {
                var dialog;
                event.preventDefault();

                if (this.options.submitUrl) {
                    location.href = this.options.submitUrl;
                } else {
                    $(this.options.popupCartButtonId)
                        .off('click')
                        .on('click', this._addToCartSubmit.bind(this));
                    $('#map-popup-heading-price').text(this.options.productName);
                    $('#map-popup-price').html($(this.options.realPrice).html().trim());
                    $('#map-popup-msrp > span.price').html(this.options.msrpPrice);

                    this.element.trigger('reloadPrice');

                    dialog = $('#map-popup-click-for-price');
                    this._popupDialog(dialog, this.options.popupId);

                    if (this.options.addToCartUrl) {
                        $(this.options.cartForm).attr('action', this.options.addToCartUrl);
                    }

                    if (!this.options.showAddToCart) {
                        $('#product_addtocart_form_from_popup').hide();
                    }

                    return false;
                }
            }.bind(this));

            $(this.options.helpLinkId).on('click', function () {
                $('#map-popup-heading-what-this').text(this.options.productName);
                this._popupDialog($('#map-popup-what-this'), this.options.helpLinkId);

                return false;
            }.bind(this));
        },

        /**
         * Handler for dialog popup
         * @param {jQuery} elementTarget
         * @param {jQuery} elementTrigger
         * @private
         */
        _popupDialog: function (elementTarget, elementTrigger) {
            var target = $(elementTarget),
                trigger = $(elementTrigger),
                counter = 0,
                triggerClass = 'dropdown-active',
                options;

            options = {
                appendTo: 'body',
                dialogContentClass: 'active',
                closeOnMouseLeave: false,
                autoPosition: true,
                'dialogClass': 'popup map-popup-wrapper',
                position: {
                    my: 'left top',
                    collision: 'fit none',
                    at: 'left bottom',
                    within: 'body',
                    of: trigger
                },
                shadowHinter: 'popup popup-pointer'
            };
            options = _.extend(options, this.options.dialog);

            if (openDropDown && openDropDown.is(':data(mage-dropdownDialog)')) {
                openDropDown.dropdownDialog('close');
            }

            if (this.options.singleOpenDropDown) {
                this.openDropDown = openDropDown;
            }
            openDropDown = target
                .dropdownDialog(options)
                .off('dropdowndialogclose')
                .on('dropdowndialogclose', function () {
                    if (!counter) {
                        openDropDown = null;
                        $(window).off('resize');
                    }
                    trigger.removeClass(triggerClass);
                })
                .on('dropdowndialogopen', function () {
                    trigger.addClass(triggerClass);
                })
                .dropdownDialog('open');

            $(window)
                .resize(_.debounce(function () {
                    if (openDropDown) {
                        counter--;
                        openDropDown.dropdownDialog('open');
                    }
                }, this.options.dialogDelay))
                .resize(_.debounce(function () {
                    if (openDropDown) {
                        counter++;
                        openDropDown.dropdownDialog('close');
                    }
                }, this.options.dialogDelay, true));
        },

        /**
         * Handler for addToCart action
         * @private
         */
        _addToCartSubmit: function () {
            this.element.trigger('addToCart', this.element);
            if (this.element.data('stop-processing')) {
                return false;
            }

            if (this.options.addToCartButton) {
                $(this.options.addToCartButton).click();

                return;
            }

            if (this.options.addToCartUrl) {
                $('.mage-dropdown-dialog > .ui-dialog-content').dropdownDialog('close');
            }
            $(this.options.cartForm).submit();
        }
    });

    return $.mage.addToCart;
});
