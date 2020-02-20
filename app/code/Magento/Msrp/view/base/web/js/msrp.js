/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'Magento_Catalog/js/price-utils',
    'underscore',
    'jquery-ui-modules/widget',
    'mage/dropdown',
    'mage/template'
], function ($, priceUtils, _) {
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
            msrpPriceElement: '#map-popup-msrp .price-wrapper',
            priceLabelId: '#map-popup-price',
            priceElement: '#map-popup-price .price',
            mapInfoLinks: '.map-show-info',
            displayPriceElement: '.old-price.map-old-price .price-wrapper',
            fallbackPriceElement: '.normal-price.map-fallback-price .price-wrapper',
            displayPriceContainer: '.old-price.map-old-price',
            fallbackPriceContainer: '.normal-price.map-fallback-price',
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
        wasOpened: false,

        /**
         * Creates widget instance
         *
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
            $(document).on('updateMsrpPriceBlock', this.onUpdateMsrpPrice.bind(this));
            $(this.options.cartForm).on('submit', this._onSubmitForm.bind(this));
        },

        /**
         * Init msrp popup
         *
         * @private
         */
        initMsrpPopup: function () {
            var popupDOM = $(this.options.popUpAttr)[0],
                $msrpPopup = $(popupDOM.innerHTML.trim());

            $msrpPopup.find(this.options.productIdInput).val(this.options.productId);
            $('body').append($msrpPopup);
            $msrpPopup.trigger('contentUpdated');

            $msrpPopup.find('button')
                .on('click',
                    this.handleMsrpAddToCart.bind(this))
                .filter(this.options.popupCartButtonId)
                .text($(this.options.addToCartButton).text());

            $msrpPopup.find(this.options.paypalCheckoutButons).on('click',
                this.handleMsrpPaypalCheckout.bind(this));

            $(this.options.popupId).on('click',
                this.openPopup.bind(this));

            this.$popup = $msrpPopup;
        },

        /**
         * Init info popup
         *
         * @private
         */
        initInfoPopup: function () {
            var infoPopupDOM = $('[data-role=msrp-info-template]')[0],
                $infoPopup = $(infoPopupDOM.innerHTML.trim());

            $('body').append($infoPopup);

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
            var popupDOM = $(this.options.popUpAttr)[0],
                $tierPopup = $(popupDOM.innerHTML.trim());

            $('body').append($tierPopup);
            $tierPopup.find(this.options.productIdInput).val(this.options.productId);
            this.popUpOptions.position.of = $(this.options.helpLinkId);

            $tierPopup.find('button').on('click',
                this.handleTierAddToCart.bind(this))
                .filter(this.options.popupCartButtonId)
                .text($(this.options.addToCartButton).text());

            $tierPopup.find(this.options.paypalCheckoutButons).on('click',
                this.handleTierPaypalCheckout.bind(this));

            $(this.options.attr).on('click', function (e) {
                this.$popup = $tierPopup;
                this.tierOptions = $(e.target).data('tier-price');
                this.openPopup(e);
            }.bind(this));
        },

        /**
         * handle 'AddToCart' click on Msrp popup
         * @param {Object} ev
         *
         * @private
         */
        handleMsrpAddToCart: function (ev) {
            ev.preventDefault();

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
         * @param {Object} ev
         * @private
         */
        handleTierAddToCart: function (ev) {
            ev.preventDefault();

            if (this.options.addToCartButton &&
                this.options.inputQty && !isNaN(this.tierOptions.qty)
            ) {
                $(this.options.inputQty).val(this.tierOptions.qty);
                $(this.options.addToCartButton).click();
                this.closePopup(this.$popup);
            }
        },

        /**
         * handle 'paypal checkout buttons' click on Tier popup
         *
         * @private
         */
        handleTierPaypalCheckout: function () {
            if (this.options.inputQty && !isNaN(this.tierOptions.qty)
            ) {
                $(this.options.inputQty).val(this.tierOptions.qty);
                this.closePopup(this.$popup);
            }
        },

        /**
         * Open and set up popup
         *
         * @param {Object} event
         */
        openPopup: function (event) {
            var options = this.tierOptions || this.options;

            this.popUpOptions.position.of = $(event.target);

            if (!this.wasOpened) {
                this.$popup.find(this.options.msrpLabelId).html(options.msrpPrice);
                this.$popup.find(this.options.priceLabelId).html(options.realPrice);
                this.wasOpened = true;
            }
            this.$popup.dropdownDialog(this.popUpOptions).dropdownDialog('open');
            this._toggle(this.$popup);

            if (!this.options.isSaleable) {
                this.$popup.find('form').hide();
            }
        },

        /**
         * Toggle MAP popup visibility
         *
         * @param {HTMLElement} $elem
         * @private
         */
        _toggle: function ($elem) {
            $(document).on('mouseup.msrp touchend.msrp', function (e) {
                if (!$elem.is(e.target) && $elem.has(e.target).length === 0) {
                    this.closePopup($elem);
                }
            }.bind(this));
            $(window).on('resize', function () {
                this.closePopup($elem);
            }.bind(this));
        },

        /**
         * Close MAP information popup
         *
         * @param {HTMLElement} $elem
         */
        closePopup: function ($elem) {
            $elem.dropdownDialog('close');
            $(document).off('mouseup.msrp touchend.msrp');
        },

        /**
         * Handler for addToCart action
         *
         * @param {Object} e
         */
        _addToCartSubmit: function (e) {
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

            e.preventDefault();
            $(this.options.cartForm).submit();
        },

        /**
         * Call on event updatePrice. Proxy to updateMsrpPrice method.
         *
         * @param {Event} event
         * @param {mixed} priceIndex
         * @param {Object} prices
         * @param {Object|undefined} $priceBox
         */
        onUpdateMsrpPrice: function onUpdateMsrpPrice(event, priceIndex, prices, $priceBox) {

            var defaultMsrp,
                defaultPrice,
                msrpPrice,
                finalPrice;

            defaultMsrp = _.chain(prices).map(function (price) {
                return price.msrpPrice.amount;
            }).reject(function (p) {
                return p === null;
            }).max().value();

            defaultPrice = _.chain(prices).map(function (p) {
                return p.finalPrice.amount;
            }).min().value();

            if (typeof priceIndex !== 'undefined') {
                msrpPrice = prices[priceIndex].msrpPrice.amount;
                finalPrice = prices[priceIndex].finalPrice.amount;

                if (msrpPrice === null || msrpPrice <= finalPrice) {
                    this.updateNonMsrpPrice(priceUtils.formatPrice(finalPrice), $priceBox);
                } else {
                    this.updateMsrpPrice(
                        priceUtils.formatPrice(finalPrice),
                        priceUtils.formatPrice(msrpPrice),
                        false,
                        $priceBox);
                }
            } else {
                this.updateMsrpPrice(
                    priceUtils.formatPrice(defaultPrice),
                    priceUtils.formatPrice(defaultMsrp),
                    true,
                    $priceBox);
            }
        },

        /**
         * Update prices for configurable product with MSRP enabled
         *
         * @param {String} finalPrice
         * @param {String} msrpPrice
         * @param {Boolean} useDefaultPrice
         * @param {Object|undefined} $priceBox
         */
        updateMsrpPrice: function (finalPrice, msrpPrice, useDefaultPrice, $priceBox) {
            var options = this.tierOptions || this.options;

            $(this.options.fallbackPriceContainer, $priceBox).hide();
            $(this.options.displayPriceContainer, $priceBox).show();
            $(this.options.mapInfoLinks, $priceBox).show();

            if (useDefaultPrice || !this.wasOpened) {
                if (this.$popup) {
                    this.$popup.find(this.options.msrpLabelId).html(options.msrpPrice);
                    this.$popup.find(this.options.priceLabelId).html(options.realPrice);
                }

                $(this.options.displayPriceElement, $priceBox).html(msrpPrice);
                this.wasOpened = true;
            }

            if (!useDefaultPrice) {
                this.$popup.find(this.options.msrpPriceElement).html(msrpPrice);
                this.$popup.find(this.options.priceElement).html(finalPrice);
                $(this.options.displayPriceElement, $priceBox).html(msrpPrice);
            }
        },

        /**
         * Display non MAP price for irrelevant products
         *
         * @param {String} price
         * @param {Object|undefined} $priceBox
         */
        updateNonMsrpPrice: function (price, $priceBox) {
            $(this.options.fallbackPriceElement, $priceBox).html(price);
            $(this.options.displayPriceContainer, $priceBox).hide();
            $(this.options.mapInfoLinks, $priceBox).hide();
            $(this.options.fallbackPriceContainer, $priceBox).show();
        },

        /**
         * Handler for submit form
         *
         * @private
         */
        _onSubmitForm: function () {
            if ($(this.options.cartForm).valid()) {
                $(this.options.cartButtonId).prop('disabled', true);
            }
        }

    });

    return $.mage.addToCart;
});
