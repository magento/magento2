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
                    options: {
                        keyEventHandlers: {
                            /**
                             * Prevents escape key from exiting out of modal
                             */
                            escapeKey: function () {
                                return;
                            }
                        }
                    },
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
                 * Once the modal is opened it hides the X
                 */
                onOpened: function () {
                    $('.modal-header button.action-close').hide();
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
                    var notifiModal = registry.get('release_notification.release_notification.notification_modal_1');

                    if (analyticsPopupConfig.releaseVisible) {
                        notifiModal.initializeContentAfterAnalytics();
                    }
                }
            }
        );
    }
);
