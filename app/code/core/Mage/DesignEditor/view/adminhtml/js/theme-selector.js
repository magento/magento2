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
    $.widget('vde.themeSelector', {
        options: {
            refreshIframeEvent: 'refreshIframe',
            assignEvent:        'assign',
            assignSaveEvent:    'assign-save',
            previewEvent:       'preview',
            editEvent:          'edit',
            deleteEvent:        'delete',
            loadEvent:          'loaded',
            storeView: {
                windowSelector: '#store-view-window'
            },
            closePopupBtn: '[class^="action-close"]',
            assignSaveUrl: null,
            afterAssignSaveUrl: null,
            storesByThemes: {},
            isMultipleStoreViewMode: null,
            frameSelector: 'iframe#vde_container_frame'
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
            $('body').on(this.options.refreshIframeEvent, $.proxy(this._onIframeRefresh, this));

            //this.element is <body>
            this.element.on(this.options.assignEvent, $.proxy(this._onAssign, this));
            this.element.on(this.options.assignSaveEvent, $.proxy(this._onAssignSave, this));
            this.element.on(this.options.previewEvent, $.proxy(this._onPreview, this));
            this.element.on(this.options.editEvent, $.proxy(this._onEdit, this));
            this.element.on(this.options.deleteEvent, $.proxy(this._onDelete, this));
            this.element.on('click.closePopup', this.options.closePopupBtn, $.proxy(this._closePopup, this));
            this.element.on('keyup', $.proxy(function(e) {
                //ESC button
                if (e.keyCode === 27) {
                    this._closePopup();
                }
            }, this));
            this.element.on(this.options.loadEvent, $.proxy(function() {
                this.element.trigger('contentUpdated');
            }, this));
        },

        /**
         * Iframe refresh
         * @protected
         */
        _onIframeRefresh: function() {
            $(this.options.frameSelector)[0].contentWindow.location.reload(true);
        },

        /**
         * Preview action
         * @protected
         */
        _onPreview: function(event, data) {
            document.location = data.preview_url;
        },

        /**
         * Edit action
         * @protected
         */
        _onEdit: function(event, data) {
            document.location = data.edit_url;
        },

        /**
         * Delete action
         * @protected
         */
        _onDelete: function(event, data) {
            deleteConfirm($.mage.__('Are you sure you want to do this?'), data.url);
        },

        _closePopup: function(event, data) {
            $(this.options.storeView.windowSelector).hide();
            this.themeId = null;
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
        _onAssignSave: function() {
            var stores = [];
            var checkedValue = 1;
            $(this.options.storeView.windowSelector).find('form').serializeArray().each(function(object, index) {
                if (parseInt(object.value, 10) === checkedValue) {
                    stores.push(parseInt(object.name.match('storeviews\\[(\\d+)\\]')[1], 10));
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
            return !(storesToAssign.length === assignedStores.length &&
                $(storesToAssign).not(assignedStores).length === 0);
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

                var storeViewId = parseInt(element.attr('id').replace('storeview_', ''), 10);
                element.attr('checked',
                    !(!storesByThemes[themeId] || storesByThemes[themeId].indexOf(storeViewId) === -1));

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
            } else if (data.stores.length === 0) {
                data.stores = EMPTY_STORES;
            }

            if ($(this.options.frameSelector).get(0)) {
                var historyObject = $(this.options.frameSelector).get(0).contentWindow.vdeHistoryObject;
                if (historyObject && historyObject.getItems().length != 0) {
                    data.layoutUpdate = this._preparePostItems(historyObject.getItems());
                }
                var frameUrl = $(this.options.frameSelector).attr('src');
                var urlParts = frameUrl.split('handle');
                if (urlParts.length > 1) {
                    data.handle = frameUrl.split('handle')[1].replace(/\//g, '');
                }
            }

            $('#messages').html('');
            $.ajax({
                type: 'POST',
                url:  this.options.assignSaveUrl,
                data: data,
                dataType: 'json',
                success: $.proxy(function(response) {
                    if (response.error) {
                        alert($.mage.__('Error') + ': "' + response.message + '".');
                    } else {
                        var defaultStore = 0;
                        var url = [
                            this.options.afterAssignSaveUrl + 'store_id',
                            stores ? stores[0] : defaultStore,
                            'theme_id',
                            response.themeId
                        ].join('/');
                        this.options.storesByThemes[themeId] = stores;

                        document.location = url;
                    }
                }, this),
                error: function() {
                    alert($.mage.__('Error: unknown error.'));
                }
            });
        },

        /**
         * Prepare items for post request
         *
         * @param items
         * @return {Object}
         * @private
         */
        _preparePostItems: function(items) {
            var postData = {};
            $.each(items, function(index, item){
                postData[index] = item.getPostData();
            });
            return postData;
        }
    });

    /**
     * Theme quick edit controls
     */
    $.widget('vde.themeControl', {
        options: {
            themeData: null,
            saveEventName: 'quickEditSave',
            isActive: false
        },

        /**
         * Bind widget events
         * @protected
         */
        _init: function() {
            this.options._textControl.on('click.editThemeTitle', $.proxy(this._onEdit, this));
            this.options._saveTitleBtn.on('click.submitForm', $.proxy(function() {
                this.options._formControl.trigger('submit');
                return false;
            }, this));
            this.options._formControl.on('submit.saveThemeTitle', $.proxy(function() {
                this._onSave();
                return false;
            }, this));
            this.document
                .on('click.cancelEditThemeTitle', $.proxy(this._onCancel, this))
                .on('keyup', $.proxy(function(e) {
                    //ESC button
                    if (e.keyCode === 27) {
                        this._cancelEdit();
                    }
                }, this));
        },

        /**
         * Widget initialization
         * @protected
         */
        _create: function() {
            this.options._textControl = this.widget().find('.theme-title');
            this.options._inputControl = this.widget().find('.edit-theme-title-form');
            this.options._formControl = this.widget().find('.edit-theme-title-form');
            this.options._saveTitleBtn = this.widget().find('.action-save');
            this.options._control = this.widget().find('.theme-control-title');

            this.options.themeData = this.widget().data('widget-options');
        },

        /**
         * Edit event
         * @protected
         */
        _onEdit: function() {
            if (this.options.isActive) {
                return;
            }
            this.options.isActive = true;
            this.options._textControl.fadeOut();
            this.options._inputControl.fadeIn().focus();
            this._setThemeTitle(this.options.themeData.theme_title);
        },

        /**
         * Save changed theme data
         * @protected
         */
        _onSave: function() {
            if(!this.options.isActive) {
                return;
            }
            var params = {
                theme_id: this.options.themeData.theme_id,
                theme_title: this._getThemeTitle()
            };
            $('#messages').html('');
            $.ajax({
                url: this.options.url,
                type: 'POST',
                dataType: 'json',
                data: params,
                showLoader: true,
                success: $.proxy(function(response) {
                    if (response.success) {
                        this.options.themeData.theme_title = this._getThemeTitle();
                        this._setThemeTitle(this.options.themeData.theme_title);
                    }
                    this._cancelEdit();
                }, this),
                error: $.proxy(function() {
                    this._cancelEdit();
                    alert($.mage.__('Error: unknown error.'));
                }, this)
            });
        },

        /**
         * Get the entered value
         * @return {string}
         * @protected
         */
        _getThemeTitle: function() {
            return this.options._inputControl.find('input').val();
        },

        /**
         * Set the saved value
         * @param title {string}
         * @return {*}
         * @protected
         */
        _setThemeTitle: function(title) {
            this.options._textControl
                .text(title)
                .attr('title', title);
            this.options._inputControl.find('input').val(title);
            return this;
        },

        /**
         * Cancel saving theme title
         * @param event {*}
         * @protected
         */
        _onCancel: function(event) {
            if (this.options.isActive && this.widget().has($(event.target)).length === 0) {
                this._cancelEdit();
            }
        },

        /**
         * Cancel editing mode
         * @protected
         */
        _cancelEdit: function() {
            this.options.isActive = false;
            this.options._textControl.fadeIn();
            this.options._inputControl.fadeOut();
        }
    });

})(jQuery);
