/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global wysiwygSetup */
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
    var wysiwygSetup = Class.create();
    wysiwygSetup.prototype = {
        wysiwygInstance: null,
        /**
         * @param {*} htmlId
         * @param {Object} config
         */
        initialize: function (htmlId, config) {
            var WysiwygInstancePrototype = new wysiwygAdapter.getAdapterPrototype();

            _.bindAll(this, 'openFileBrowser');
            wysiwygInstance = new WysiwygInstancePrototype(htmlId, config);
        },

        /**
         * @param {*} mode
         */
        setup: function (mode) {
            wysiwygInstance.setup(mode);
        },

        /**
         * @param {Object} o
         */
        openFileBrowser: function (o) {
            wysiwygInstance.openFileBrowser(o);
        },

        /**
         * @return {Boolean}
         */
        toggle: function () {
            return wysiwygInstance.toggle();
        },

        /**
         * On form validation.
         */
        onFormValidation: function () {
            wysiwygInstance.onFormValidate();
        },

        updateContent: function(content) {
            return wysiwygInstance.encodeContent(content);
        }
    };
    window.wysiwygSetup = wysiwygSetup;
});
