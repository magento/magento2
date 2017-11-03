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
         * @param {String} id
         * @return {Object}
         */
        get: function (id) {
            return this.wysiwygInstance.get(id);
        },

        /**
         * @param {String} string
         * @return {String}
         */
        translate: function (string) {
            return this.wysiwygInstance.translate(string);
        },

        /**
         * @return {null}
         */
        getMediaBrowserOpener: function () {
            return this.wysiwygInstance.getMediaBrowserOpenener();
        },

        /**
         * @return {null}
         */
        getMediaBrowserTargetElementId: function () {
            return this.wysiwygInstance.getMediaBrowserTargetElementId.getMediaBrowserTargetElementId();
        },

        /**
         * @return {jQuery|*|HTMLElement}
         */
        getToggleButton: function () {
            return this.wysiwygInstance.getToggleButton();
        },

        /**
         * Get plugins button.
         */
        getPluginButtons: function () {
            return this.wysiwygInstance.getPluginButtons();
        },

        /**
         * @param {*} mode
         * @return {tinyMceWysiwygSetup}
         */
        turnOn: function (mode) {
            return this.wysiwygInstance.turnOn(mode);
        },

        /**
         * @param {String} name
         */
        closeEditorPopup: function (name) {
            this.wysiwygInstance.closeEditorPopup(name);
        },

        /**
         * @return {tinyMceWysiwygSetup}
         */
        turnOff: function () {
            return this.wysiwygInstance.turnOff();
        },

        /**
         * Close popups.
         */
        closePopups: function () {
            this.wysiwygInstance.closePopups();
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
        },

        /**
         * On change content.
         */
        onChangeContent: function () {
            this.wysiwygInstance.onChangeContent();
        },

        /**
         * Retrieve directives URL with substituted directive value.
         *
         * @param {String} directive
         */
        makeDirectiveUrl: function (directive) {
            return this.wysiwygInstance.makeDirectiveUrl(directive);
        },

        /**
         * @param {Object} content
         * @return {*}
         */
        encodeDirectives: function (content) {
            // collect all HTML tags with attributes that contain directives
            return this.wysiwygInstance.encodeDirectives(content);
        },

        /**
         * @param {Object} content
         * @return {*}
         */
        encodeWidgets: function (content) {
            return this.wysiwygInstance.encodeWidgets(content);
        },

        /**
         * @param {Object} content
         * @return {*}
         */
        decodeDirectives: function (content) {
            return this.wysiwygInstance.decodeDirectives(content);
        },

        /**
         * @param {Object} content
         * @return {*}
         */
        decodeWidgets: function (content) {
            return this.wysiwygInstance.decodeWidgets(content);
        },

        /**
         * @param {Object} attributes
         * @return {Object}
         */
        parseAttributesString: function (attributes) {
            return this.wysiwygInstance.parseAttributesString(attributes);
        },

        /**
         * Update text area.
         */
        updateTextArea: function () {
            this.wysiwygInstance.updateTextArea();
        },

        /**
         * @param {Object} content
         * @return {*}
         */
        decodeContent: function (content) {
            return this.wysiwygInstance.decodeContent(content);
        },

        /**
         * @param {Object} content
         * @return {*}
         */
        encodeContent: function (content) {
            return this.wysiwygInstance.encodeContent(content);
        },

        /**
         * @param {Object} o
         */
        beforeSetContent: function (o) {
            this.wysiwygInstance.beforeSetContent(o);
        },

        /**
         * @param {Object} o
         */
        saveContent: function (o) {
            this.wysiwygInstance.saveContent(o);
        }
    };
});
