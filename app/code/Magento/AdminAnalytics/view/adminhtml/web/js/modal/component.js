/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define(
    [
    'jquery',
    'Magento_Ui/js/modal/modal-component'
    ],
    function ($, Modal) {
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
                            escapeKey: function () {
                                return; }
                        }
                    }
                },
                initModal: function () {
                    this.options.opened = this.onOpened.bind(this);
                    this._super();
                },
                onOpened: function () {
                    $('.modal-header button.action-close').hide();
                },
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
                    this.closeModal();
                },
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
                    this.closeModal();
                }
            }
        )
    }
);
