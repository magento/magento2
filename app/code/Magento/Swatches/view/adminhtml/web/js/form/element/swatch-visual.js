/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global $break $ FORM_KEY */

define([
    'underscore',
    'Magento_Ui/js/lib/view/utils/async',
    'mage/template',
    'uiRegistry',
    'prototype',
    'Magento_Ui/js/form/element/abstract',
    'jquery/colorpicker/js/colorpicker',
    'jquery/ui'
], function (_, jQuery, mageTemplate, rg, prototype, Abstract) {
    'use strict';

    /**
     * Former implementation.
     *
     * @param {*} value
     * @param {Object} container
     * @param {String} uploadUrl
     * @param {String} elementName
     */
    function oldCode(value, container, uploadUrl, elementName) {
        var swatchVisualOption = {
            itemCount: 0,
            totalItems: 0,
            rendered: 0,
            isReadOnly: false,

            /**
             * Initialize.
             */
            initialize: function () {
                if (_.isEmpty(value)) {
                    container.addClassName('unavailable');
                }

                jQuery(container).on(
                    'click',
                    '.colorpicker_handler',
                    this.initColorPicker
                );
            },

            /**
             * ColorPicker initialization process
             */
            initColorPicker: function () {
                var element = this,
                    hiddenColorPicker = !jQuery(element).data('colorpickerId');

                jQuery(this).ColorPicker({

                    /**
                     * ColorPicker onShow action
                     */
                    onShow: function () {
                        var color = jQuery(element).parent().parent().prev().prev('input').val(),
                            menu = jQuery(this).parents('.swatch_sub-menu_container');

                        menu.hide();
                        jQuery(element).ColorPickerSetColor(color);
                    },

                    /**
                     * ColorPicker onSubmit action
                     *
                     * @param {String} hsb
                     * @param {String} hex
                     * @param {String} rgb
                     * @param {String} el
                     */
                    onSubmit: function (hsb, hex, rgb, el) {
                        var localContainer = jQuery(el).parent().parent().prev();

                        jQuery(el).ColorPickerHide();
                        localContainer.parent().removeClass('unavailable');
                        localContainer.prev('input').val('#' + hex).trigger('change');
                        localContainer.css('background', '#' + hex);
                    }
                });

                if (hiddenColorPicker) {
                    jQuery(this).ColorPickerShow();
                }
            },

            /**
             * Remove action
             *
             * @param {Object} event
             */
            remove: function (event) {
                var element = $(Event.findElement(event, 'tr')),
                    elementFlags; // !!! Button already have table parent in safari

                // Safari workaround
                element.ancestors().each(function (parentItem) {
                    if (parentItem.hasClassName('option-row')) {
                        element = parentItem;
                        throw $break;
                    } else if (parentItem.hasClassName('box')) {
                        throw $break;
                    }
                });

                if (element) {
                    elementFlags = element.getElementsByClassName('delete-flag');

                    if (elementFlags[0]) {
                        elementFlags[0].value = 1;
                    }

                    element.addClassName('no-display');
                    element.addClassName('template');
                    element.hide();
                    this.totalItems--;
                    this.updateItemsCountField();
                }
            },

            /**
             * Update items count field
             */
            updateItemsCountField: function () {
                $('swatch-visual-option-count-check').value = this.totalItems > 0 ? '1' : '';
            }
        };

        //swatchVisualOption.initColorPicker();

        jQuery('body').on('click', function (event) {
            var element = jQuery(event.target);

            if (
                element.parents('.swatch_sub-menu_container').length === 1 ||
                element.next('div.swatch_sub-menu_container').length === 1
            ) {
                return true;
            }
            jQuery('.swatch_sub-menu_container').hide();
        });

        jQuery(function ($) {

            var swatchComponents = {

                /**
                 * div wrapper for to hide all evement
                 */
                wrapper: null,

                /**
                 * iframe component to perform file upload without page reload
                 */
                iframe: null,

                /**
                 * form component for upload image
                 */
                form: null,

                /**
                 * Input file component for upload image
                 */
                inputFile: null,

                /**
                 * Create swatch component for upload files
                 *
                 * @this {swatchComponents}
                 * @public
                 */
                create: function () {
                    this.wrapper = $('<div>').css({
                        display: 'none'
                    }).appendTo($('body'));

                    this.iframe = $('<iframe />', {
                        name: 'upload_iframe_' + elementName
                    }).appendTo(this.wrapper);

                    this.form = $('<form />', {
                        name: 'swatch_form_image_upload_' + elementName,
                        target: 'upload_iframe_' + elementName,
                        method: 'post',
                        enctype: 'multipart/form-data',
                        class: 'ignore-validate',
                        action: uploadUrl
                    }).appendTo(this.wrapper);

                    this.inputFile = $('<input />', {
                        type: 'file',
                        name: 'datafile',
                        class: 'swatch_option_file'
                    }).appendTo(this.form);

                    $('<input />', {
                        type: 'hidden',
                        name: 'form_key',
                        value: FORM_KEY
                    }).appendTo(this.form);
                }
            };

            swatchVisualOption.initialize();

            /**
             * Create swatch components
             */
            swatchComponents.create();

            /**
             * Register event for swatch input[type=file] change
             */
            swatchComponents.inputFile.change(function () {
                var localContainer = $('.' + $(this).attr('data-called-by')).parents().eq(2).children('.swatch_window'),

                    /**
                     * @this {iframe}
                     */
                    iframeHandler = function () {
                        var imageParams = $.parseJSON($(this).contents().find('body').html()),
                            fullMediaUrl = imageParams['swatch_path'] + imageParams['file_path'];

                        localContainer.prev('input').val(imageParams['file_path']).trigger('change');
                        localContainer.css({
                            'background-image': 'url(' + fullMediaUrl + ')',
                            'background-size': 'cover'
                        });
                        localContainer.parent().removeClass('unavailable');
                    };

                swatchComponents.iframe.off('load');
                swatchComponents.iframe.load(iframeHandler);
                swatchComponents.form.submit();
                $(this).val('');
            });

            /**
             * Register event for choose "upload image" option
             */
            $(container).on('click', '.btn_choose_file_upload', function () {
                swatchComponents.inputFile.attr('data-called-by', $(this).data('class'));
                swatchComponents.inputFile.click();
            });

            /**
             * Register event for remove option
             */
            $(container).on('click', '.btn_remove_swatch', function () {
                var optionPanel = $(this).parents().eq(2);

                optionPanel.children('input').val('').trigger('change');
                optionPanel.children('.swatch_window').css('background', '');
                optionPanel.addClass('unavailable');
                jQuery('.swatch_sub-menu_container').hide();
            });

            /**
             * Toggle color upload chooser
             */
            $(container).on('click', '.swatch_window', function () {
                jQuery('.swatch_sub-menu_container').hide();
                $(this).next('div').toggle();
            });
        });
    }

    return Abstract.extend({
        defaults: {
            elementId: 0,
            prefixName: '',
            prefixElementName: '',
            elementName: '',
            value: '',
            uploadUrl: ''
        },

        /**
         * Parses options and merges the result with instance
         *
         * @returns {Object} Chainable.
         */
        initConfig: function () {
            this._super();

            this.configureDataScope();

            return this;
        },

        /**
         * Initialize.
         *
         * @returns {Object} Chainable.
         */
        initialize: function () {
            this._super()
                .initOldCode();

            return this;
        },

        /**
         * Initialize wrapped former implementation.
         *
         * @returns {Object} Chainable.
         */
        initOldCode: function () {
            jQuery.async('.' + this.elementName, function (elem) {
                oldCode(this.value(), elem.parentElement, this.uploadUrl, this.elementName);
            }.bind(this));

            return this;
        },

        /**
         * Configure data scope.
         */
        configureDataScope: function () {
            var recordId, prefixName;

            // Get recordId
            recordId = this.parentName.split('.').last();

            prefixName = this.dataScopeToHtmlArray(this.prefixName);
            this.elementName = this.prefixElementName + recordId;

            this.inputName = prefixName + '[' + this.elementName + ']';
            this.dataScope = 'data.' + this.prefixName + '.' + this.elementName;

            this.links.value = this.provider + ':' + this.dataScope;
        },

        /**
         * Get HTML array from data scope.
         *
         * @param {String} dataScopeString
         * @returns {String}
         */
        dataScopeToHtmlArray: function (dataScopeString) {
            var dataScopeArray, dataScope, reduceFunction;

            /**
             * Add new level of nesting.
             *
             * @param {String} prev
             * @param {String} curr
             * @returns {String}
             */
            reduceFunction = function (prev, curr) {
                return prev + '[' + curr + ']';
            };

            dataScopeArray = dataScopeString.split('.');

            dataScope = dataScopeArray.shift();
            dataScope += dataScopeArray.reduce(reduceFunction, '');

            return dataScope;
        }
    });
});
