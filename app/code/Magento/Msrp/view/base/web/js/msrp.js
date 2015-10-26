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
            singleOpenDropDown: true,
            dialog: {}, // Options for mage/dropdown
            dialogDelay: 500, // Delay in ms after resize dropdown shown again

            // Selectors
            cartForm: '.form.map.checkout',
            msrpLabelId: '#map-popup-msrp',
            priceLabelId: '#map-popup-price',
            popUpAttr: '[data-role=msrp-popup-template]',
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
            var tierOptions,
                popupClone;

            this.popupDOM = $(this.options.popUpAttr)[0];
            this.infoPopupDOM = $('[data-role=msrp-info-template]')[0];

            if (this.options.popupId) {
                popupClone  = $($(this.popupDOM).html()).clone();
                $('body').append(popupClone);
                this.$popup = popupClone;
                popupClone.trigger('contentUpdated');

                this.$popup.find('button').on('click', function () {
                    if (this.options.addToCartButton) {
                        $(this.options.addToCartButton).click();
                        this.closePopup(this.$popup);
                    }
                }.bind(this));

                $(this.options.popupId).on('click', function (e) {
                    this.popUpOptions.position.of = $(e.target);
                    this.$popup.find(this.options.msrpLabelId).html(this.options.msrpPrice);
                    this.$popup.find(this.options.priceLabelId).html(this.options.realPrice);
                    this.$popup.dropdownDialog(this.popUpOptions).dropdownDialog('open');
                    this._toggle(this.$popup);
                }.bind(this));
            }

            if (this.options.helpLinkId) {
                this.$infoPopup = $(this.infoPopupDOM.innerText).appendTo('body');
                $(this.options.helpLinkId).on('click', function (e) {
                    this.popUpOptions.position.of = $(e.target);
                    this.$infoPopup.dropdownDialog(this.popUpOptions).dropdownDialog('open');
                    this._toggle(this.$infoPopup);
                }.bind(this));
            }

            if (this.options.attr) {
                this.popupDOM = $(this.options.popUpAttr)[0];
                this.$popup = $(this.popupDOM.innerText).appendTo('body');
                this.popUpOptions.position.of = $(this.options.helpLinkId);

                this.$popup.find('button').on('click', function (ev) {
                    ev.preventDefault();
                    this.$popup.find('form').attr('action', tierOptions.addToCartUrl).submit();
                }.bind(this));

                $(this.options.attr).on('click', function (e) {
                    this.popUpOptions.position.of = $(e.target);
                    tierOptions = JSON.parse($(e.target).attr('data-tier-price'));
                    this.$popup.find(this.options.msrpLabelId).html(tierOptions.msrp);
                    this.$popup.find(this.options.priceLabelId).html(tierOptions.price);
                    this.$popup.dropdownDialog(this.popUpOptions).dropdownDialog('open');
                    this._toggle(this.$popup);
                }.bind(this));
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
        }
    });

    return $.mage.addToCart;
});
