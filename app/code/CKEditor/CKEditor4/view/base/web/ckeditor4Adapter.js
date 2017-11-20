/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global varienGlobalEvents, tinyMceEditors, MediabrowserUtility, closeEditorPopup, Base64 */
/* eslint-disable strict */
define([
    'jquery',
    'underscore',
    'CKEditor_CKEditor4/js/ckeditor4/ckeditor',
    'mage/translate',
    'prototype'
], function (jQuery, _, ckeditor4) {

    var ckeditorWysiwyg = Class.create();

    ckeditorWysiwyg.prototype = {
        mediaBrowserOpener: null,
        mediaBrowserTargetElementId: null,

        /**
         * @param {*} htmlId
         * @param {Object} config
         */
        initialize: function (htmlId, config) {

        },

        /**
         * @param {*} mode
         */
        setup: function (mode) {
            ckeditor4.replaceAll();
        },


        /**
         * @param {Object} editor
         */
        applySchema: function (editor) {

        },

        /**
         * @param {String} id
         */
        get: function (id) {
            //return tinyMCE3.get(id);
        },

        /**
         * @return {Object}
         */
        activeEditor: function () {
            //return tinyMCE3.activeEditor;
        },

        /**
         * @param {Object} o
         */
        openFileBrowser: function (o) {

        },

        /**
         * @param {String} string
         * @return {String}
         */
        translate: function (string) {
            //return jQuery.mage.__ ? jQuery.mage.__(string) : string;
        },

        /**
         * @return {null}
         */
        getMediaBrowserOpener: function () {
            //return this.mediaBrowserOpener;
        },

        /**
         * @return {null}
         */
        getMediaBrowserTargetElementId: function () {
            //return this.mediaBrowserTargetElementId;
        },

        /**
         * @return {jQuery|*|HTMLElement}
         */
        getToggleButton: function () {
            //return $('toggle' + this.id);
        },

        /**
         * Get plugins button.
         */
        getPluginButtons: function () {
            //return $$('#buttons' + this.id + ' > button.plugin');
        },

        /**
         * @param {*} mode
         * @return {tinyMceWysiwygSetup}
         */
        turnOn: function (mode) {
            // this.closePopups();
            //
            // this.setup(mode);
            //
            // tinyMCE3.execCommand('mceAddControl', false, this.id);
            //
            // this.getPluginButtons().each(function (e) {
            //     e.hide();
            // });

            return this;
        },

        /**
         * @return {tinyMceWysiwygSetup}
         */
        turnOff: function () {
            // this.closePopups();
            //
            // tinyMCE3.execCommand('mceRemoveControl', false, this.id);
            //
            // this.getPluginButtons().each(function (e) {
            //     e.show();
            // });

            return this;
        },

        /**
         * Close popups.
         */
        closePopups: function () {

        },

        /**
         * @return {Boolean}
         */
        toggle: function () {
            if (!ckeditor4.get(this.id)) {
                this.turnOn();

                return true;
            }
            this.turnOff();

            return false;
        },

        /**
         * Editor pre-initialise event handler.
         */
        onEditorPreInit: function (editor) {

        },

        /**
         * @deprecated
         */
        onEditorInit: function () {},

        /**
         * On form validation.
         */
        onFormValidation: function () {

        },

        /**
         * On change content.
         */
        onChangeContent: function () {

        },

        /**
         * Retrieve directives URL with substituted directive value.
         *
         * @param {String} directive
         */
        makeDirectiveUrl: function (directive) {

        },

        /**
         * @param {Object} content
         * @return {*}
         */
        encodeDirectives: function (content) {

        },

        /**
         * @param {Object} content
         * @return {*}
         */
        encodeWidgets: function (content) {

        },

        /**
         * @param {Object} content
         * @return {*}
         */
        decodeDirectives: function (content) {

        },

        /**
         * @param {Object} content
         * @return {*}
         */
        decodeWidgets: function (content) {

        },

        /**
         * @param {Object} attributes
         * @return {Object}
         */
        parseAttributesString: function (attributes) {

        },

        /**
         * Update text area.
         */
        updateTextArea: function () {

        },

        /**
         * @param {Object} content
         * @return {*}
         */
        decodeContent: function (content) {

        },

        /**
         * @param {Object} content
         * @return {*}
         */
        encodeContent: function (content) {

        },

        /**
         * @param {Object} o
         */
        beforeSetContent: function (o) {

        },

        /**
         * @param {Object} o
         */
        saveContent: function (o) {

        },

        /**
         * @returns {Object}
         */
        getAdapterPrototype: function () {
            return ckeditorWysiwyg;
        }
    };

    return ckeditorWysiwyg.prototype;
});
