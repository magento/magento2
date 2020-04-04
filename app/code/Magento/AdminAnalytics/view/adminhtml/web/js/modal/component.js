/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'jquery',
    'Magento_Ui/js/modal/modal-component',
    'uiRegistry',
    'analyticsPopupConfig'
],
    function (_, $, Modal, registry, analyticsPopupConfig) {
        'use strict';

        return Modal.extend(
            {
                defaults: {
                    imports: {
                        enableLogAction: '${ $.provider }:data.enableLogAction',
                        disableLogAction: '${ $.provider }:data.disableLogAction'
                    },
                    options: {},
                    notificationWindow: null
                },

                /**
                 * Initializes modal on opened function
                 */
                initModal: function () {
                    this.options.opened = this.onOpened.bind(this);
                    this._super();
                },

                /**
                 * Configure ESC and TAB so user can't leave modal
                 * without selecting an option
                 *
                 * @returns {Object} Chainable.
                 */
                initModalEvents: function () {
                    this._super();
                    //Don't allow ESC key to close modal
                    this.options.keyEventHandlers.escapeKey = this.handleEscKey.bind(this);
                    //Restrict tab action to the modal
                    this.options.keyEventHandlers.tabKey = this.handleTabKey.bind(this);

                    return this;
                },

                /**
                 * Once the modal is opened it hides the X
                 */
                onOpened: function () {
                    $('.modal-header button.action-close').attr('disabled', true).hide();

                    this.focusableElements = $(this.rootSelector).find('a[href], button:enabled');
                    this.firstFocusableElement = this.focusableElements[0];
                    this.lastFocusableElement = this.focusableElements[this.focusableElements.length - 1];
                    this.firstFocusableElement.focus();
                },

                /**
                 * Changes admin usage setting to yes
                 */
                enableAdminUsage: function () {
                    var data = {
                        'form_key': window.FORM_KEY
                    };

                    $.ajax(
                        {
                            type: 'POST',
                            url: this.enableLogAction,
                            data: data,
                            showLoader: true
                        }
                    ).done(
                        function (xhr) {
                            if (xhr.error) {
                                self.onError(xhr);
                            }
                        }
                    ).fail(this.onError);
                    this.openReleasePopup();
                    this.closeModal();
                },

                /**
                 * Changes admin usage setting to no
                 */
                disableAdminUsage: function () {
                    var data = {
                        'form_key': window.FORM_KEY
                    };

                    $.ajax(
                        {
                            type: 'POST',
                            url: this.disableLogAction,
                            data: data,
                            showLoader: true
                        }
                    ).done(
                        function (xhr) {
                            if (xhr.error) {
                                self.onError(xhr);
                            }
                        }
                    ).fail(this.onError);
                    this.openReleasePopup();
                    this.closeModal();
                },

                /**
                 * Allows admin usage popup to be shown first and then new release notification
                 */
                openReleasePopup: function () {
                    var notificationModalSelector = 'release_notification.release_notification.notification_modal_1';

                    if (analyticsPopupConfig.releaseVisible) {
                        registry.get(notificationModalSelector).initializeContentAfterAnalytics();
                    }
                },

                /**
                 * Handle Tab and Shift+Tab key event
                 *
                 * Keep the tab actions restricted to the popup modal
                 * so the user must select an option to dismiss the modal
                 */
                handleTabKey: function (event) {
                    var modal = this,
                        KEY_TAB = 9;

                    /**
                     * Handle Shift+Tab to tab backwards
                     */
                    function handleBackwardTab() {
                        if (document.activeElement === modal.firstFocusableElement ||
                            document.activeElement === $(modal.rootSelector)[0]
                        ) {
                            event.preventDefault();
                            modal.lastFocusableElement.focus();
                        }
                    }

                    /**
                     * Handle Tab forward
                     */
                    function handleForwardTab() {
                        if (document.activeElement === modal.lastFocusableElement) {
                            event.preventDefault();
                            modal.firstFocusableElement.focus();
                        }
                    }

                    switch (event.keyCode) {
                        case KEY_TAB:
                            if (modal.focusableElements.length === 1) {
                                event.preventDefault();
                                break;
                            }

                            if (event.shiftKey) {
                                handleBackwardTab();
                                break;
                            }
                            handleForwardTab();
                            break;
                        default:
                            break;
                    }
                },

                /**
                 * Handle Esc key
                 *
                 * Esc key should not close modal
                 */
                handleEscKey: function (event) {
                    event.preventDefault();
                }
            }
        );
    }
);
