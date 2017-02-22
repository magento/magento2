/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint jquery:true*/
/*global FORM_KEY*/
/*global integration*/
define([
    "jquery",
    "Magento_Ui/js/modal/alert",
    "jquery/ui",
    "mage/translate",
    "Magento_Ui/js/modal/modal"
], function($, alert){
    "use strict";

    $.widget('mage.integration', {
        /**
         * Options common to all instances of this widget.
         * @type {Object}
         */
        options: {
            /**
             * URL of the integration grid.
             * @type {String}
             */
            gridUrl: ''
        },

        /**
         * Bind event handler for the action when admin clicks "Save & Activate" button.
         * @private
         */
        _create: function () {
            if ($('#save-split-button-activate').length) {
                // We're on the "New integration" page - bind related handler
                this._form = $('#edit_form');
                this._form.on('saveAndActivate', $.proxy(this._saveAndActivate, this));
            }
        },

        /**
         * Save new integration, then kick off the activate dialog.
         * @private
         */
        _saveAndActivate: function () {
            if (this._form.validation && !this._form.validation('isValid')) {
                return false;
            }

            $.ajax({
                url: this._form.prop('action'),
                type: 'post',
                data: this._form.serialize(),
                dataType: 'json',
                context: this,
                beforeSend: function () {
                    $('body').trigger('processStart');
                },
                success: function (data) {
                    if (data['_redirect']) {
                        window.location.href = data['_redirect'];
                    } else if (data['integrationId']) {
                        var integrationName = $('#integration_properties_name').val();
                        window.integration.popup.show($('<span>').attr({
                            'data-row-dialog': 'permissions',
                            'data-row-id': data['integrationId'],
                            // We do escaping here instead of the place of actual output because _showPopup()
                            // actually receives dialog window title from couple of places: from here and from the grid.
                            // The issue is we always should escape values in the grid, so that value is already
                            // escaped. To avoid double escaping we do it here instead of the output.
                            'data-row-name': $('<div>').text(integrationName).html(),
                            'data-row-is-reauthorize': '0',
                            'data-row-is-token-exchange': data['isTokenExchange']
                        }));
                        var that = this;
                        $('#integration-popup-container').on('dialogclose', function () {
                            $('body').trigger('processStart');
                            window.location.href = that.options.gridUrl;
                            return false;
                        });
                    }
                },
                error: function (jqXHR, status, error) {
                    alert({
                        content: $.mage.__('Sorry, something went wrong. Please try again later.')
                    });
                    window.console && console.log(status + ': ' + error + "\nResponse text:\n" + jqXHR.responseText);
                },
                complete: function () {
                    $('body').trigger('processStop');
                }
            });

            return true;
        }
    });

    window.Integration = function (
        permissionsDialogUrl,
        tokensDialogUrl,
        tokensExchangeUrl,
        gridUrl,
        successCallbackUrl
    ) {
        var url = {
            permissions: permissionsDialogUrl,
            tokens: tokensDialogUrl,
            tokensExchange: tokensExchangeUrl,
            grid: gridUrl
        };

        var IdentityLogin = {
            win: null,
            strLocation: null,
            checker: null,
            isCalledBack: false,
            //Info popup dialog. Should be hidden when login window is closed
            jqInfoDialog: $('#integration-popup-container'),
            successCallbackUrl: successCallbackUrl,
            Constants: {
                /*
                 This interval is set such that it adjusts to the child window closing timeout of 1000 ms. This will
                 give the checker function enough time to detect if the successCallback has been invoked
                 */
                CHECKER_INTERVAL: 500,
                //Login screen size plus some buffer
                WIDTH: 680,
                HEIGHT: 510,
                // subtract pixels(30) and width(680) from screen width to move popup from extreme left
                LEFT: screen.width - 680 - 30,
                // subtract pixels(300) and height(300) from screen height to move from top
                TOP: screen.height - 510 - 300
            },

            invokePopup: function (identityCallbackUrl, consumerId, jqInfoDialog) {
                // Callback should be invoked only once. Reset callback flag on subsequent invocations.
                IdentityLogin.isCalledBack = false;
                IdentityLogin.jqInfoDialog = jqInfoDialog;
                var param = $.param({"consumer_id": consumerId, "success_call_back": IdentityLogin.successCallbackUrl});
                IdentityLogin.win = window.open(identityCallbackUrl + '?' + param, '',
                    'top=' + IdentityLogin.Constants.TOP +
                        ', left=' + IdentityLogin.Constants.LEFT +
                        ', width=' + IdentityLogin.Constants.WIDTH +
                        ', height=' + IdentityLogin.Constants.HEIGHT + ',scrollbars=no');

                if (IdentityLogin.checker != null) {
                    //Clear any previous check
                    clearInterval(IdentityLogin.checker);
                }
                //Polling to detect url of the child window.
                IdentityLogin.checker = setInterval(
                    IdentityLogin.fnCheckLocation, IdentityLogin.Constants.CHECKER_INTERVAL
                );
            },

            /**
             * Function to check the location of the child popoup window.
             * Once detected if the callback is successful, parent window will be reloaded
             */
            fnCheckLocation: function () {
                if (IdentityLogin.win == null) {
                    return;
                }
                // Check to see if the location has changed.
                try {
                    //Is the success callback invoked
                    if (IdentityLogin.win.closed ||
                        (IdentityLogin.win.location.href == IdentityLogin.successCallbackUrl)) {
                        //Stop the the polling
                        clearInterval(IdentityLogin.checker);
                        $('body').trigger('processStart');
                        //Check for window closed
                        window.location.reload();
                        IdentityLogin.jqInfoDialog.modal('closeModal');
                    }
                } catch (e) {
                    //squash. In case Window closed without success callback, clear polling
                    if (IdentityLogin.win.closed) {
                        IdentityLogin.jqInfoDialog.modal('closeModal');
                        clearInterval(IdentityLogin.checker);
                    }
                    return;
                }
            }
        };

        var _showPopup = function (dialog, title, okButton, url) {
            $.ajax({
                url: url,
                cache: false,
                data: {form_key: window.FORM_KEY},
                method: 'GET',
                beforeSend: function () {
                    // Show the spinner
                    $('body').trigger('processStart');
                },

                success: function (result) {
                    var redirect = result._redirect;

                    if (redirect) {
                        window.location.href = redirect;
                        return;
                    }

                    var identityLinkUrl = null,
                        consumerId = null,
                        popupHtml = null,
                        popup = $('#integration-popup-container');

                    try {
                        var resultObj = typeof result === 'string' ?
                            JSON.parse(result) :
                            result;

                        identityLinkUrl = resultObj['identity_link_url'];
                        consumerId      = resultObj['consumer_id'];
                        popupHtml       = resultObj['popup_content'];
                        
                    } catch (e) {
                        //This is expected if result is not json. Do nothing.
                    }

                    if (identityLinkUrl && consumerId && popupHtml) {
                        IdentityLogin.invokePopup(identityLinkUrl, consumerId, popup);
                    } else {
                        popupHtml = result;
                    }

                    if (popup.length === 0){
                        popup = $('<div/>');
                    }
                    popup.html(popupHtml);

                    var buttons = [],
                        dialogProperties = {
                            title: title,
                            type: 'slide',
                            dialogClass: dialog == 'permissions' ? 'integration-dialog' : 'integration-dialog no-close',
                        };

                    // Add confirmation button to the list of dialog buttons. okButton not set for tokenExchange dialog
                    if (okButton) {
                        buttons.push(okButton);
                    }
                    // Add button only if its not empty
                    if (buttons.length > 0) {
                        dialogProperties['buttons'] = buttons
                    }
                    popup.modal(dialogProperties);
                    popup.modal('openModal');
                },
                error: function (jqXHR, status, error) {
                    alert({
                        content: $.mage.__('Sorry, something went wrong. Please try again later.')
                    });
                    window.console && console.log(status + ': ' + error + "\nResponse text:\n" + jqXHR.responseText);
                },
                complete: function () {
                    // Hide the spinner
                    $('body').trigger('processStop');
                }
            })
        };

        return {
            popup: {
                show: function (ctx) {
                    var dialog = $(ctx).attr('data-row-dialog');
                    var isReauthorize = $(ctx).attr('data-row-is-reauthorize');
                    var isTokenExchange = $(ctx).attr('data-row-is-token-exchange');

                    if (!url.hasOwnProperty(dialog)) {
                        throw 'Invalid dialog type';
                    }

                    var integrationId = $(ctx).attr('data-row-id');

                    if (!integrationId) {
                        throw 'Unable to find integration ID';
                    }

                    // Replace placeholders in URL
                    var ajaxUrl = url[dialog].replace(':id', integrationId).replace(':isReauthorize', isReauthorize);

                    try {
                        // Get integration name either from current element or from neighbor column
                        var integrationName = $(ctx).attr('data-row-name')
                            || $(ctx).parents('tr').find('.col-name').html().trim();
                        if (integrationName.indexOf('<span') > -1) {
                            // Remove unsecure URL warning from popup window title if it is present
                            integrationName = integrationName.substring(0, integrationName.indexOf('<span'));
                        }
                    } catch (e) {
                        throw 'Unable to find integration name';
                    }

                    var okButton = {
                        permissions: {
                            text: (isReauthorize == '1') ? $.mage.__('Reauthorize') : $.mage.__('Allow'),
                            'class': 'action-primary',
                            attr: {
                                'data-row-id': integrationId,
                                'data-row-name': integrationName,
                                'data-row-dialog': (isTokenExchange == '1') ? 'tokensExchange' : 'tokens',
                                'data-row-is-reauthorize': isReauthorize,
                                'data-row-is-token-exchange': isTokenExchange
                            },
                            click: function () {
                                // Find the 'Allow' button and clone - it has all necessary data, but is going to be
                                // destroyed along with the current dialog
                                var ctx = this.modal.find('button.action-primary').clone(true);

                                this.closeModal();
                                this.modal.remove();
                                // Make popup out of data we saved from 'Allow' button
                                window.integration.popup.show(ctx);
                            }
                        },
                        tokens: {
                            text: $.mage.__('Done'),
                            'class': 'action-primary',
                            click: function () {
                                // Integration has been activated at the point of generating tokens
                                window.location.href = url.grid;
                            }
                        }
                    };

                    _showPopup(dialog, integrationName, okButton[dialog], ajaxUrl);
                }
            }
        };
    };

    return $.mage.integration;
});
