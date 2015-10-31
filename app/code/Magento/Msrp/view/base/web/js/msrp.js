/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'underscore',
    'jquery/ui',
    'mage/dropdown',
    'mage/template'
], function ($) {
    'use strict';

    $.widget('mage.addToCart', {
        options: {
            showAddToCart: true,
            submitUrl: '',
            cartButtonId: '',
            singleOpenDropDown: true,
            dialog: {}, // Options for mage/dropdown
            dialogDelay: 500, // Delay in ms after resize dropdown shown again
            origin: '', //Required, type of popup: 'msrp', 'tier' or 'info' popup

            // Selectors
            cartForm: '.form.map.checkout',
            msrpLabelId: '#map-popup-msrp',
            priceLabelId: '#map-popup-price',
            popUpAttr: '[data-role=msrp-popup-template]',
            popupCartButtonId: '#map-popup-button',
            paypalCheckoutButons: '[data-action=checkout-form-submit]',
            popupId: '',
            realPrice: '',
            isSaleable: '',
            msrpPrice: '',
            helpLinkId: '',
            addToCartButton: '',

            // Text options
            productName: '',
            addToCartUrl: ''
        },

        openDropDown: null,
        triggerClass: 'dropdown-active',

        popUpOptions: {
            appendTo: 'body',
            dialogContentClass: 'active',
            closeOnMouseLeave: false,
            autoPosition: true,
            closeOnClickOutside: false,
            'dialogClass': 'popup map-popup-wrapper',
            position: {
                my: 'left top',
                collision: 'fit none',
                at: 'left bottom',
                within: 'body'
            },
            shadowHinter: 'popup popup-pointer'
        },
        popupOpened: false,

        /**
         * Creates widget instance
         * @private
         */
        _create: function () {
            if (this.options.origin === 'msrp') {
                this.initMsrpPopup();
            } else if (this.options.origin === 'info') {
                this.initInfoPopup();
            } else if (this.options.origin === 'tier') {
                this.initTierPopup();
            }
            $(this.options.cartButtonId).on('click', this._addToCartSubmit.bind(this));
        },

        /**
         * Init msrp popup
         * @private
         */
        initMsrpPopup: function () {
            var popupDOM = $(this.options.popUpAttr)[0],
                $msrpPopup = $($(popupDOM).html()).clone();

            $msrpPopup.find(this.options.productIdInput).val(this.options.productId);
            $('body').append($msrpPopup);
            $msrpPopup.trigger('contentUpdated');

            $msrpPopup.find('button').on('click', function (ev) {
                ev.preventDefault();
                this.handleMsrpAddToCart();
            }.bind(this));

            $msrpPopup.find(this.options.paypalCheckoutButons).on('click',
                this.handleMsrpPaypalCheckout.bind(this));

            $(this.options.popupId).on('click', this.openPopup.bind(this));

            this.$popup = $msrpPopup;
        },

        /**
         * Init info popup
         * @private
         */
        initInfoPopup: function () {
            var infoPopupDOM = $('[data-role=msrp-info-template]')[0],
                $infoPopup = $(infoPopupDOM.innerText).appendTo('body');

            $(this.options.helpLinkId).on('click', function (e) {
                this.popUpOptions.position.of = $(e.target);
                $infoPopup.dropdownDialog(this.popUpOptions).dropdownDialog('open');
                this._toggle($infoPopup);
            }.bind(this));

            this.$popup = $infoPopup;
        },

        /**
         * Init tier price popup
         * @private
         */
        initTierPopup: function () {
            var tierOptions = JSON.parse($(this.options.attr).attr('data-tier-price')),
                popupDOM = $(this.options.popUpAttr)[0],
                $tierPopup = $(popupDOM.innerText).appendTo('body');

            $tierPopup.find(this.options.productIdInput).val(this.options.productId);
            this.popUpOptions.position.of = $(this.options.helpLinkId);

            $tierPopup.find('button').on('click', function (ev) {
                ev.preventDefault();
                this.handleTierAddToCart(tierOptions);
            }.bind(this));

            $tierPopup.find(this.options.paypalCheckoutButons).on('click', function () {
                this.handleTierPaypalCheckout(tierOptions);
            }.bind(this));

            $(this.options.attr).on('click', function (e) {
                this.popUpOptions.position.of = $(e.target);
                $tierPopup.find(this.options.msrpLabelId).html(tierOptions.msrp);
                $tierPopup.find(this.options.priceLabelId).html(tierOptions.price);
                $tierPopup.dropdownDialog(this.popUpOptions).dropdownDialog('open');
                this._toggle($tierPopup);
            }.bind(this));

            this.$popup = $tierPopup;
        },

        /**
         * handle 'AddToCart' click on Msrp popup
         *
         * @private
         */
        handleMsrpAddToCart: function () {
            if (this.options.addToCartButton) {
                $(this.options.addToCartButton).click();
                this.closePopup(this.$popup);
            }
        },

        /**
         * handle 'paypal checkout buttons' click on Msrp popup
         *
         * @private
         */
        handleMsrpPaypalCheckout: function () {
            this.closePopup(this.$popup);
        },

        /**
         * handle 'AddToCart' click on Tier popup
         *
         * @param {Object} tierOptions
         * @private
         */
        handleTierAddToCart: function (tierOptions) {
            if (this.options.addToCartButton &&
                this.options.inputQty && !isNaN(tierOptions.qty)
            ) {
                $(this.options.inputQty).val(tierOptions.qty);
                $(this.options.addToCartButton).click();
                this.closePopup(this.$popup);
            }
        },

        /**
         * handle 'paypal checkout buttons' click on Tier popup
         *
         * @param {Object} tierOptions
         * @private
         */
        handleTierPaypalCheckout: function (tierOptions) {
            if (this.options.inputQty && !isNaN(tierOptions.qty)
            ) {
                $(this.options.inputQty).val(tierOptions.qty);
                this.closePopup(this.$popup);
            }
        },

        /**
         * Open and set up popup
         *
         * @param {Object} event
         */
        openPopup: function (event) {
            this.popUpOptions.position.of = $(event.target);
            this.$popup.find(this.options.msrpLabelId).html(this.options.msrpPrice);
            this.$popup.find(this.options.priceLabelId).html(this.options.realPrice);
            this.$popup.dropdownDialog(this.popUpOptions).dropdownDialog('open');
            this.$popup.find('button').on('click', function () {
                if (this.options.addToCartButton) {
                    $(this.options.addToCartButton).click();
                }
            }.bind(this));
            this._toggle(this.$popup);

            if (!this.options.isSaleable) {
                this.$popup.find('form').hide();
            }
        },

        /**
         *
         * @param {HTMLElement} $elem
         * @private
         */
        _toggle: function ($elem) {
            $(document).on('mouseup', function (e) {
                if (!$elem.is(e.target) && $elem.has(e.target).length === 0) {
                    this.closePopup($elem);
                }
            }.bind(this));
            $(window).on('resize', function () {
                this.closePopup($elem);
            }.bind(this));
        },

        /**
         *
         * @param {HTMLElement} $elem
         */
        closePopup: function ($elem) {
            $elem.dropdownDialog('close');
            $(document).off('mouseup');
        },

        /**
         * Handler for addToCart action
         */
        _addToCartSubmit: function () {
            this.element.trigger('addToCart', this.element);

            if (this.element.data('stop-processing')) {
                return false;
            }

            if (this.options.addToCartButton) {
                $(this.options.addToCartButton).click();

                return false;
            }

            if (this.options.addToCartUrl) {
                $('.mage-dropdown-dialog > .ui-dialog-content').dropdownDialog('close');
            }
            $(this.options.cartForm).submit();

        }
    });

    return $.mage.addToCart;
});
