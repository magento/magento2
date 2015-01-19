/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint jquery:true*/
define([
    "jquery",
    "jquery/ui",
    "mage/translate",
    "Magento_DesignEditor/js/dialog"
], function($){

    /**
     * Widget theme save
     */
    $.widget('vde.themeSave', {
        options: {
            loadEvent: 'loaded',
            saveEvent: 'save',
            saveConfirmEvent: 'save-confirm',
            saveAndAssignEvent: 'save-and-assign',
            saveAndAssignConfirmEvent: 'save-and-assign-confirm',
            dialogSelector: '#dialog-message-confirm'
        },
        editorFrame: null,

        _create: function() {
            this._bind();
        },

        /**
         * Bind handlers
         * @protected
         */
        _bind: function() {
            var $body = $('body');
            $body.on(this.options.loadEvent, function() {$body.trigger('contentUpdated');});
            $body.on(this.options.saveEvent, $.proxy(this._onSave, this));
            $body.on(this.options.saveConfirmEvent, $.proxy(this._onSaveConfirm, this));
            $body.on(this.options.saveAndAssignEvent, $.proxy(this._onSaveAndAssign, this));
            $body.on(this.options.saveAndAssignConfirmEvent, $.proxy(this._onSaveAndAssignConfirm, this));
        },

        /**
         * Handler for 'save' event
         *
         * @param event
         * @param eventData
         * @private
         */
        _onSave: function(event, eventData) {
            var saveConfirmEvent = this.options.saveConfirmEvent;
            if (eventData.confirm && eventData.confirm.message) {
                var dialog = eventData.dialog = this._getDialog();
                dialog.messages.clear();
                dialog.set(
                    eventData.confirm.title,
                    eventData.confirm.message,
                    [
                        {
                            text: 'No',
                            click: $.proxy(function() {
                                this.close();
                            }, dialog),
                            'class': 'action-close'
                        },
                        {
                            text: 'Yes',
                            click: function() {
                                $('body').trigger(saveConfirmEvent, eventData);
                            },
                            'class': 'primary'
                        }
                    ]
                );
                dialog.open();
            } else {
                $('body').trigger(saveConfirmEvent, eventData);
            }
        },

        /**
         * Handler for 'save-confirm' event
         *
         * @param event
         * @param eventData
         * @private
         */
        _onSaveConfirm: function(event, eventData) {
            if (!eventData.save_url) {
                throw Error('Save url is not defined');
            }

            var data = {
                themeId: eventData.theme_id
            };

            $.ajax({
                type: 'POST',
                url:  eventData.save_url,
                data: data,
                dataType: 'json',
                success: eventData.saveAfter || $.proxy(function(response) {
                    var dialog, title, type;

                    if (eventData.dialog) {
                        dialog = eventData.dialog;
                    } else {
                        dialog = this._getDialog();
                    }
                    if (response.error) {
                        title = 'Error';
                        type = 'error';
                    } else {
                        title = 'Success';
                        type = 'success';
                    }
                    dialog.text.clear();
                    dialog.title.set($.mage.__(title));
                    dialog.messages.set(response.message, type);
                    dialog.setButtons([], false);
                    dialog.open();
                }, this),
                error: function() {
                    alert($.mage.__('Error: unknown error.'));
                }
            });
        },

        /**
         * Handler for 'save-and-assign' event
         *
         * @param event
         * @param eventData
         * @private
         */
        _onSaveAndAssign: function(event, eventData) {
            var saveAndAssignConfirmEvent = this.options.saveAndAssignConfirmEvent;
            eventData.confirm.buttons = [
                {
                    text: 'No',
                    click: function() {
                        eventData.dialog.close();
                    },
                    'class': 'action-close'
                },
                {
                    text: 'Yes',
                    click: function() {
                        $('body').trigger(saveAndAssignConfirmEvent, eventData);
                        eventData.dialog.assignAfter = $.proxy(function(response) {
                            var messageType = response.error ? 'error' : 'success';
                            this.text.clear();
                            this.setButtons([], false);
                            this.messages.add(response.message, messageType);
                        }, eventData.dialog);
                    },
                    'class': 'primary'
                }
            ];
            $(event.target).trigger('assign', eventData);
        },

        /**
         * Handler for 'save-and-assign-confirm' event
         *
         * @param event
         * @param eventData
         * @private
         */
        _onSaveAndAssignConfirm: function(event, eventData) {
            if (eventData.dialog) {
                eventData.dialog.messages.clear();
            }

            //NOTE: Line below makes copy of eventData to have an ability to unset 'confirm.message' later
            // and to not miss this 'confirm.message' for next calls of _onSaveAndAssign
            var tempData = $.extend({}, eventData);
            tempData.saveAfter = function() {};
            tempData.reportToSession = 0;
            $('body').trigger(this.options.saveConfirmEvent, tempData);
            $('body').trigger('assign-confirm', tempData);
        },

        /**
         * Get dialog element
         *
         * @returns {Object}
         * @private
         */
        _getDialog: function() {
            return $(this.options.dialogSelector).data('dialog');
        },

        /**
         * Prepare post data
         *
         * @param {Object} items
         * @returns {Object}
         * @private
         */
        _preparePostItems: function(items) {
            var postData = {};
            $.each(items, function(index, item) {
                postData[index] = item.getPostData();
            });
            return postData;
        },

        _post: function(action, data) {
            var url = action;
            var postResult;
            $.ajax({
                url: url,
                type: 'POST',
                dataType: 'JSON',
                data: {historyData: data},
                async: false,
                success: function(data) {
                    if (data.error) {
                        /** @todo add error validator */
                        throw Error($.mage.__('Some problem with save action'));
                        return;
                    }
                    postResult = data.success;
                },
                error: function(data) {
                    throw Error($.mage.__('Some problem with save action'));
                }
            });
            return postResult;
        },

        _destroy: function() {
            $('body').off(this.options.saveEvent + ' ' + this.options.saveAndAssignEvent);
            this._super();
        }
    });

});