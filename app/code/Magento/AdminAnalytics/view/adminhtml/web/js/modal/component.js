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

        console.log('Hello two');

        return Modal.extend(
            {
                defaults: {
                    imports: {
                        logAction:  '${ $.provider }:data.logAction'
                    }
                },
                keyEventHandlers: {
                    escapeKey: function (){}
                },
                opened: function ($Event) {
                    $('.modal-header button.action-close', $Event.srcElement).hide();
                },

                dontAllow: function(){
                    console.log("Clicked")
                },
                actionDone: function() {
                    console.log("Clicked")
                },
                buttons: [
                {
                    text: $.mage.__(`Don't Allow`),
                    class: 'action',
                    click: function () {
                        var data = {
                            'form_key': window.FORM_KEY
                        };
                        $.ajax(
                            {
                                type: 'POST',
                                url: '/magento2ce/admin_michell/adminAnalytics/config/disableAdminUsage',
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
                },
                {
                    text: $.mage.__('Ok'),
                    class: 'action',
                    click: function () {
                        var data = {
                            'form_key': window.FORM_KEY
                        };
                        $.ajax(
                            {
                                type: 'POST',
                                url: '/magento2ce/admin_michell/adminAnalytics/config/enableAdminUsage',
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
                }
                ],
            }
        );
    }
);
