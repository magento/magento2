/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    mage
 * @package     mage
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint jquery:true*/
(function($) {
    $.widget("vde.themeSelector", {
        options: {
            assignEvent:      'assign',
            assignSaveEvent:  'assign-save',
            previewEvent:     'preview',
            deleteEvent:      'delete',
            loadEvent:        'loaded',
            storeView: {
                windowSelector: '#store-view-window'
            },
            assignSaveUrl: null,
            afterAssignSaveUrl: null,
            storesByThemes: {},
            isMultipleStoreViewMode: null
        },

        /**
         * Identifier of a theme currently processed
         *
         * It is set in showStoreViews(), used and then cleared in _onAssignSave()
         */
        themeId: null,

        /**
         * Form creation
         * @protected
         */
        _create: function() {
            this._bind();
        },

        /**
         * Bind handlers
         * @protected
         */
        _bind: function() {
            //this.element is <body>
            this.element.on(this.options.assignEvent, $.proxy(this._onAssign, this));
            this.element.on(this.options.assignSaveEvent, $.proxy(this._onAssignSave, this));
            this.element.on(this.options.previewEvent, $.proxy(this._onPreview, this));
            this.element.on(this.options.deleteEvent, $.proxy(this._onDelete, this));
            this.element.on('keyup', $.proxy(function(e) {
                //ESC button
                if (e.keyCode == 27) {
                    var popUp = $(this.options.storeView.windowSelector);
                    popUp.hide();
                    this.themeId = null;
                }
            }, this));

            $('body').on(this.options.loadEvent, function() {
                $('*[data-widget-button]').button();
            });
        },

        /**
         * Preview action
         * @protected
         */
        _onPreview: function(event, data) {
            document.location = data.preview_url;
        },

        /**
         * Delete action
         * @protected
         */
        _onDelete: function(event, data) {
            deleteConfirm($.mage.__('Are you sure you want to do this?'), data['url']);
        },

        /**
         * Assign event handler
         * @protected
         */
        _onAssign: function(event, data) {
            if (this.options.isMultipleStoreViewMode) {
                this.showStoreViews(data.theme_id);
            } else {
                if (!this._confirm($.mage.__('You are about to change this theme for your live store, are you sure want to do this?'))) {
                    return;
                }
                this.assignSaveTheme(data.theme_id, null);
            }
        },

        /**
         * "Assign Save" button click handler
         * @protected
         */
        _onAssignSave: function(event, data) {
            var stores = [];
            var checkedValue = 1;
            $(this.options.storeView.windowSelector).find('form').serializeArray().each(function(object, index) {
                if (object.value == checkedValue) {
                    stores.push(object.name.match('storeviews\\[(\\d)\\]')[1] * 1);
                }
            });

            if (!this._isStoreChanged(this.themeId, stores)) {
                alert($.mage.__('No stores were reassigned.'));
                return;
            }

            var popUp = $(this.options.storeView.windowSelector);
            popUp.hide();

            this.assignSaveTheme(this.themeId, stores);
            this.themeId = null;
        },

        /**
         * Check if the stores changed
         * @protected
         */
        _isStoreChanged: function(themeId, storesToAssign) {
            var assignedStores = this.options.storesByThemes[themeId] || [] ;
            var isChanged = !(
                storesToAssign.length == assignedStores.length
                && $(storesToAssign).not(assignedStores).length == 0
            );

            return isChanged;
        },

        /**
         * Assign event handlers
         * @protected
         */
        _confirm: function(message) {
            return confirm(message);
        },

        /**
         * Show store-view selector window
         * @public
         */
        showStoreViews: function(themeId) {
            var popUp = $(this.options.storeView.windowSelector);
            var storesByThemes = this.options.storesByThemes;
            popUp.find('input[type=checkbox]').each(function(index, element) {
                element = $(element);
                var storeViewId = element.attr('id').replace('storeview_', '') * 1;
                var checked = true;
                if (!storesByThemes[themeId] || storesByThemes[themeId].indexOf(storeViewId) == -1) {
                    checked = false;
                }
                element.attr('checked', checked);
            });
            this.themeId = themeId;
            popUp.show();
        },

        /**
         * Send AJAX request to assign theme to store-views
         * @public
         */
        assignSaveTheme: function(themeId, stores) {
            if (!this.options.assignSaveUrl) {
                throw Error($.mage.__('Url to assign themes to store is not defined'));
            }

            var data = {
                theme_id: themeId,
                stores:   stores
            };
            //TODO since we can't convert data to JSON string we use magic numbers
            var DEFAULT_STORE = '-1';
            var EMPTY_STORES = '-2';
            if (data.stores === null) {
                data.stores = DEFAULT_STORE;
            } else if (data.stores.length == 0) {
                data.stores = EMPTY_STORES;
            }
            $.post(this.options.assignSaveUrl, data, $.proxy(function(response) {
                if (response.error) {
                    alert($.mage.__('Error') + ': "' + response.error + '".');
                } else {
                    var defaultStore = 0;
                    var url = [
                        this.options.afterAssignSaveUrl + 'store_id',
                        stores ? stores[0] : defaultStore,
                        'theme_id',
                        themeId
                    ].join('/');
                    this.options.storesByThemes[themeId] = stores;

                    setTimeout(function() {$('body').loader('show');}, 500);
                    document.location = url;
                }
            }, this)).error(function() {
                alert($.mage.__('Error: unknown error.'));
            });
        }
    });
})(jQuery);
