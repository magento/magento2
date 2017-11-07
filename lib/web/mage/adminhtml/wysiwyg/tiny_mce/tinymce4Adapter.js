/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global varienGlobalEvents, popups, tinyMceEditors, MediabrowserUtility, Base64 */
define([
    'jquery',
    'underscore',
    'tinymce',
    'mage/translate',
    'prototype',
    'mage/adminhtml/events',
    'mage/adminhtml/browser'
], function (jQuery, _, tinyMCE4) {
    'use strict';

    var tinyMce4Wysiwyg = Class.create();

    tinyMce4Wysiwyg.prototype = {
        mediaBrowserOpener: null,
        mediaBrowserTargetElementId: null,

        /**
         * @param {*} htmlId
         * @param {Object} config
         */
        initialize: function (htmlId, config) {

            this.id = htmlId;
            this.config = config;

            _.bindAll(this, 'beforeSetContent', 'saveContent', 'onChangeContent', 'openFileBrowser', 'updateTextArea');

            varienGlobalEvents.attachEventHandler('tinymceChange', this.onChangeContent);
            varienGlobalEvents.attachEventHandler('tinymceBeforeSetContent', this.beforeSetContent);
            varienGlobalEvents.attachEventHandler('tinymceSetContent', this.updateTextArea);
            varienGlobalEvents.attachEventHandler('tinymceSaveContent', this.saveContent);

            if (typeof tinyMceEditors === 'undefined') {
                window.tinyMceEditors = $H({});
            }

            tinyMceEditors.set(this.id, this);
        },

        /**
         * Setup TinyMCE4 editor
         */
        setup: function () {
            // if (this.config['widget_plugin_src']) {
            //     tinyMCE4.PluginManager.load('magentowidget', this.config['widget_plugin_src']);
            // }

            // if (this.config.plugins) {
            //     this.config.plugins.each(function (plugin) {
            //         tinyMCE4.PluginManager.load(plugin.name, plugin.src);
            //     });
            // }

            if (jQuery.isReady) {
                tinyMCE4.dom.Event.domLoaded = true;
            }

            tinyMCE4.init(this.getSettings());

            // window.tinyMCE_GZ = window.tinyMCE_GZ || {};
            // window.tinyMCE_GZ.loaded = true;
        },

        /**
         * @return {Object}
         */
        getSettings: function () {
            var settings;

            settings = {
                selector: 'textarea#' + this.id,
                theme: 'modern',
                'entity_encoding': 'raw',
                'convert_urls': false,
                'relative_urls': true,
                menubar: false,
                plugins: this.config.tinymce4.plugins,
                toolbar: this.config.tinymce4.toolbar,

                /**
                 * @param {Object} editor
                 */
                setup: function (editor) {
                    var onChange;

                    /*
                     * These two events handle encoding and decoding Magento Widgets, Variables, and Directives.
                     * They will not be needed until we add support for them, and are currently causing errors
                     * to be thrown
                     *
                    editor.on('BeforeSetContent', function(evt) {
                        varienGlobalEvents.fireEvent('tinymceBeforeSetContent', evt);
                    });

                    editor.on('SetContent', function(evt) {
                        varienGlobalEvents.fireEvent('tinymceSetContent', evt);
                    });
                    */

                    editor.on('paste', function (evt) {
                        varienGlobalEvents.fireEvent('tinymcePaste', evt);
                    });

                    /**
                     * @param {*} evt
                     */
                    onChange = function (evt) {
                        varienGlobalEvents.fireEvent('tinymceChange', evt);
                    };

                    editor.on('Change', onChange);
                    editor.on('keyup', onChange);

                    editor.on('ExecCommand', function (cmd) {
                        varienGlobalEvents.fireEvent('tinymceExecCommand', cmd);
                    });
                }
            };

            if (this.config.baseStaticUrl && this.config.baseStaticDefaultUrl) {
                settings['document_base_url'] = this.config.baseStaticUrl;
            }
            // Set the document base URL
            if (this.config['document_base_url']) {
                settings['document_base_url'] = this.config['document_base_url'];
            }

            if (this.config['files_browser_window_url']) {
                /**
                 * @param {*} fieldName
                 * @param {*} url
                 * @param {*} objectType
                 * @param {*} w
                 */
                settings['file_browser_callback'] = function (fieldName, url, objectType, w) {
                    varienGlobalEvents.fireEvent('open_browser_callback', {
                        win: w,
                        type: objectType,
                        field: fieldName
                    });
                };
            }

            if (this.config.width) {
                settings.width = this.config.width;
            }

            if (this.config.height) {
                settings.height = this.config.height;
            }

            if (this.config.settings) {
                Object.extend(settings, this.config.settings);
            }

            return settings;
        },

        /**
         * @param {String} id
         */
        get: function (id) {
            return tinyMCE4.get(id);
        },

        /**
         * @return {Object}
         */
        activeEditor: function () {
            return tinyMCE4.activeEditor;
        },

        /**
         * @param {Object} o
         */
        openFileBrowser: function (o) {
            var typeTitle,
                storeId = this.config['store_id'] !== null ? this.config['store_id'] : 0,
                frameDialog = jQuery(o.win.frameElement).parents('[role="dialog"]'),
                wUrl = this.config['files_browser_window_url'] +
                    'target_element_id/' + this.id + '/' +
                    'store/' + storeId + '/';

            this.mediaBrowserOpener = o.win;
            this.mediaBrowserTargetElementId = o.field;

            if (typeof o.type !== 'undefined' && o.type !== '') { //eslint-disable-line eqeqeq
                typeTitle =  o.type === 'image' ? this.translate('Insert Image...') : this.translate('Insert Media...'); //eslint-disable-line
                wUrl = wUrl + 'type/' + o.type + '/';
            } else {
                typeTitle = this.translate('Insert File...');
            }

            frameDialog.hide();
            jQuery('#mceModalBlocker').hide();

            MediabrowserUtility.openDialog(wUrl, false, false, typeTitle, {
                /**
                 * Closed.
                 */
                closed: function () {
                    frameDialog.show();
                    jQuery('#mceModalBlocker').show();
                }
            });
        },

        /**
         * @param {String} string
         * @return {String}
         */
        translate: function (string) {
            return jQuery.mage.__ ? jQuery.mage.__(string) : string;
        },

        /**
         * @return {null}
         */
        getMediaBrowserOpener: function () {
            return this.mediaBrowserOpener;
        },

        /**
         * @return {null}
         */
        getMediaBrowserTargetElementId: function () {
            return this.mediaBrowserTargetElementId;
        },

        /**
         * @return {jQuery|*|HTMLElement}
         */
        getToggleButton: function () {
            return $('toggle' + this.id);
        },

        /**
         * Get plugins button.
         */
        getPluginButtons: function () {
            return $$('#buttons' + this.id + ' > button.plugin');
        },

        /**
         * @param {*} mode
         * @return {tinyMceWysiwygSetup}
         */
        turnOn: function (mode) {
            this.closePopups();

            this.setup(mode);

            tinyMCE4.execCommand('mceAddControl', false, this.id);

            this.getPluginButtons().each(function (e) {
                e.hide();
            });

            return this;
        },

        /**
         * @param {String} name
         */
        closeEditorPopup: function (name) {
            if (typeof popups !== 'undefined' && popups[name] !== undefined && !popups[name].closed) {
                popups[name].close();
            }
        },

        /**
         * @return {tinyMceWysiwygSetup}
         */
        turnOff: function () {
            this.closePopups();

            tinyMCE4.execCommand('mceRemoveEditor', false, this.id);

            this.getPluginButtons().each(function (e) {
                e.show();
            });

            return this;
        },

        /**
         * Close popups.
         */
        closePopups: function () {
            // close all popups to avoid problems with updating parent content area
            this.closeEditorPopup('widget_window' + this.id);
            this.closeEditorPopup('browser_window' + this.id);
        },

        /**
         * @return {Boolean}
         */
        toggle: function () {
            if (!tinyMCE4.get(this.id)) {
                this.turnOn();

                return true;
            }
            this.turnOff();

            return false;
        },

        /**
         * On form validation.
         */
        onFormValidation: function () {
            if (tinyMCE4.get(this.id)) {
                $(this.id).value = tinyMCE4.get(this.id).getContent();
            }
        },

        /**
         * On change content.
         */
        onChangeContent: function () {
            // Add "changed" to tab class if it exists
            var tab;

            this.updateTextArea();

            if (this.config['tab_id']) {
                tab = $$('a[id$=' + this.config['tab_id'] + ']')[0];

                if ($(tab) != undefined && $(tab).hasClassName('tab-item-link')) { //eslint-disable-line eqeqeq
                    $(tab).addClassName('changed');
                }
            }
        },

        /**
         * Retrieve directives URL with substituted directive value.
         *
         * @param {String} directive
         */
        makeDirectiveUrl: function (directive) {
            return this.config['directives_url'].replace('directive', 'directive/___directive/' + directive);
        },

        /**
         * @param {Object} content
         * @return {*}
         */
        encodeDirectives: function (content) {
            // collect all HTML tags with attributes that contain directives
            return content.gsub(/<([a-z0-9\-\_]+.+?)([a-z0-9\-\_]+=".*?\{\{.+?\}\}.*?".*?)>/i, function (match) {
                var attributesString = match[2];

                // process tag attributes string
                attributesString = attributesString.gsub(/([a-z0-9\-\_]+)="(.*?)(\{\{.+?\}\})(.*?)"/i, function (m) {
                    return m[1] + '="' + m[2] + this.makeDirectiveUrl(Base64.mageEncode(m[3])) + m[4] + '"';
                }.bind(this));

                return '<' + match[1] + attributesString + '>';

            }.bind(this));
        },

        /**
         * @param {Object} content
         * @return {*}
         */
        encodeWidgets: function (content) {
            return content.gsub(/\{\{widget(.*?)\}\}/i, function (match) {
                var attributes = this.parseAttributesString(match[1]),
                    imageSrc, imageHtml;

                if (attributes.type) {
                    attributes.type = attributes.type.replace(/\\\\/g, '\\');
                    imageSrc = this.config['widget_placeholders'][attributes.type];
                    imageHtml = '<img';
                    imageHtml += ' id="' + Base64.idEncode(match[0]) + '"';
                    imageHtml += ' src="' + imageSrc + '"';
                    imageHtml += ' title="' +
                        match[0].replace(/\{\{/g, '{').replace(/\}\}/g, '}').replace(/\"/g, '&quot;') + '"';
                    imageHtml += '>';

                    return imageHtml;
                }
            }.bind(this));
        },

        /**
         * @param {Object} content
         * @return {*}
         */
        decodeDirectives: function (content) {
            // escape special chars in directives url to use it in regular expression
            var url = this.makeDirectiveUrl('%directive%').replace(/([$^.?*!+:=()\[\]{}|\\])/g, '\\$1'),
                reg = new RegExp(url.replace('%directive%', '([a-zA-Z0-9,_-]+)'));

            return content.gsub(reg, function (match) { //eslint-disable-line no-extra-bind
                return Base64.mageDecode(match[1]);
            });
        },

        /**
         * @param {Object} content
         * @return {*}
         */
        decodeWidgets: function (content) {
            return content.gsub(/<img([^>]+id=\"[^>]+)>/i, function (match) {
                var attributes = this.parseAttributesString(match[1]),
                    widgetCode;

                if (attributes.id) {
                    widgetCode = Base64.idDecode(attributes.id);

                    if (widgetCode.indexOf('{{widget') !== -1) {
                        return widgetCode;
                    }

                    return match[0];
                }

                return match[0];
            }.bind(this));
        },

        /**
         * @param {Object} attributes
         * @return {Object}
         */
        parseAttributesString: function (attributes) {
            var result = {};

            attributes.gsub(
                /(\w+)(?:\s*=\s*(?:(?:"((?:\\.|[^"])*)")|(?:'((?:\\.|[^'])*)')|([^>\s]+)))?/,
                function (match) {
                    result[match[1]] = match[2];
                }
            );

            return result;
        },

        /**
         * Update text area.
         */
        updateTextArea: function () {
            var editor = tinyMCE4.get(this.id),
                content;

            if (!editor) {
                return;
            }

            content = editor.getContent();
            content = this.decodeContent(content);

            jQuery('#' + this.id).val(content).trigger('change');
        },

        /**
         * @param {Object} content
         * @return {*}
         */
        decodeContent: function (content) {
            var result = content;

            if (this.config['add_widgets']) {
                result = this.decodeWidgets(result);
                result = this.decodeDirectives(result);
            } else if (this.config['add_directives']) {
                result = this.decodeDirectives(result);
            }

            return result;
        },

        /**
         * @param {Object} content
         * @return {*}
         */
        encodeContent: function (content) {
            var result = content;

            if (this.config['add_widgets']) {
                result = this.encodeWidgets(result);
                result = this.encodeDirectives(result);
            } else if (this.config['add_directives']) {
                result = this.encodeDirectives(result);
            }

            return result;
        },

        /**
         * @param {Object} o
         */
        beforeSetContent: function (o) {
            o.content = this.encodeContent(o.content);
        },

        /**
         * @param {Object} o
         */
        saveContent: function (o) {
            o.content = this.decodeContent(o.content);
        },

        /**
         * @returns {Object}
         */
        getAdapterPrototype: function () {
            return tinyMce4Wysiwyg;
        }
    };

    return tinyMce4Wysiwyg.prototype;
});
