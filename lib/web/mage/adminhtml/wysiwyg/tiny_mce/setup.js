/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global tinyMceWysiwygSetup */
/* eslint-disable strict */
define([
    'jquery',
    'underscore',
    'wysiwygAdapter',
    'mage/translate',
    'prototype',
    'mage/adminhtml/events',
    'mage/adminhtml/browser'
], function (jQuery, _, wysiwygAdapter) {
    window.tinyMceWysiwygSetup = Class.create();

    tinyMceWysiwygSetup.prototype = {
        /**
         * @param {*} htmlId
         * @param {Object} config
         */
        initialize: function (htmlId, config) {
            var WysiwygInstancePrototype = new wysiwygAdapter.getAdapterPrototype();

            _.bindAll(this, 'openFileBrowser');
            this.wysiwygInstance = new WysiwygInstancePrototype(htmlId, config);
        },

        /**
         * @param {*} mode
         */
        setup: function (mode) {
            this.wysiwygInstance.setup(mode);
        },

        /**
         * @param {Object} o
         */
        openFileBrowser: function (o) {
            this.wysiwygInstance.openFileBrowser(o);
        },

        /**
         * @return {Boolean}
         */
        toggle: function () {
            return this.wysiwygInstance.toggle();
        },

        /**
         * On form validation.
         */
        onFormValidation: function () {
            this.wysiwygInstance.onFormValidate();
        }
    };
});
