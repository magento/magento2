/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
define([
    'jquery',
    'Magento_Review/js/rating',
    'prototype',
    'Magento_Ui/js/modal/alert',
    'mage/translate'
], function (jQuery, rating, prototype, alert) {
    'use strict';

    window.review = {
        reviewFormEditSelector: '#edit_form',
        ratingSelector: '[data-widget=ratingControl]',
        productInfoUrl: null,
        formHidden: true,
        ratingItemsUrl: '',
        productEditUrl: '',

        /**
         * @param {Object} config
         */
        configure: function (config) {
            this.ratingItemsUrl = config.ratingItemsUrl;
            this.productEditUrl = config.productEditUrl;
            this.bindStoreChange();
        },

        /**
         * @return {void}
         */
        bindStoreChange: function () {
            var selectStores = $('select_stores');

            if (selectStores) {
                Event.observe(selectStores, 'change', this.updateRating.bind(this));
            }
        },

        /**
         * @param {Object} data
         * @param {Object} click
         */
        gridRowClick: function (data, click) {
            var row = Event.findElement(click, 'TR'),
                reviewModel = window.review;

            if (row && row.title) {
                reviewModel.productInfoUrl = row.title;
                reviewModel.loadProductData();
                reviewModel.showForm();
                reviewModel.formHidden = false;
            }
        },

        /**
         * @return {void}
         */
        loadProductData: function () {
            jQuery.ajax({
                type: 'GET',
                url: this.productInfoUrl,
                data: {
                    form_key: window.FORM_KEY
                },
                showLoader: true,
                success: this.reqSuccess.bind(this),
                error: this.reqFailure.bind(this)
            });
        },

        /**
         * @return {void}
         */
        showForm: function () {
            window.toggleParentVis('add_review_form');
            window.toggleVis('productGrid');
            window.toggleVis('save_button');
            window.toggleVis('reset_button');
        },

        /**
         * @return {void}
         */
        formReset: function () {
            jQuery(this.reviewFormEditSelector).trigger('reset');
            jQuery(this.ratingSelector).ratingControl('removeRating');
        },

        /**
         * @return {void}
         */
        updateRating: function () {
            var elements = [$('select_stores'),
                    $('rating_detail').getElementsBySelector('input[type=\'radio\']')].flatten(),
                params;

            $('save_button').disabled = true;

            // Serialize to a Hash (not a query string) so params.isAjax = 'true' can be assigned below;
            // 'use strict' throws when setting a property on a primitive string.
            params = Form.serializeElements(elements, true);

            if (!params.isAjax) {
                params.isAjax = 'true';
            }

            if (!params.form_key) {
                params.form_key = window.FORM_KEY;
            }

            new Ajax.Updater('rating_detail', this.ratingItemsUrl, {
                parameters: params,
                evalScripts: true,
                onComplete: function () {
                    $('save_button').disabled = false;
                },
                onFailure: function () {
                    $('save_button').disabled = false;
                }
            });
        },

        /**
         * @param {Object} response
         */
        reqSuccess: function (response) {
            var productName;

            if (response.error) {
                alert(response.message);
            } else if (response.id) {
                productName = response.name;
                $('product_id').value = response.id;
                $('product_name').innerHTML = '<a href="' + this.productEditUrl +
                    'id/' + response.id + '" target="_blank">' + productName.escapeHTML() + '</a>';
            } else if (response.message) {
                alert(response.message);
            }
        },

        /**
         * @return {void}
         */
        reqFailure: function () {
            alert({
                content: jQuery.mage.__('We can\'t retrieve the product ID.')
            });
        }
    };

    return window.review;
});
