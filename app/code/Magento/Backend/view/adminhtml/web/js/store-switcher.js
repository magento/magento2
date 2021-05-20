/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery'
], function ($) {
    'use strict';

    /**
     * @param {Object} storeSwitchConfig
     */
    return function (storeSwitchConfig) {
        var scopeSwitcherHandler;

        (function () {
            var storesList = $('[data-role=stores-list]');

            storesList.on('click', '[data-value]', function (event) {
                var val = $(event.target).data('value'),
                    role = $(event.target).data('role'),
                    switcher = $('[data-role=' + role + ']');

                event.preventDefault();

                if (!switcher.val() || val !== switcher.val()) {

                    /* Set the value & trigger event */
                    switcher.val(val).trigger('change');
                }
            });
        })($);

        /**
         * Switch store scope
         *
         * @param {Object} obj
         * @return void
         */
        function switchScope(obj) {
            var switcher = $(obj),
                scopeId = switcher.val(),
                scopeParams = '',
                switcherParams = {};

            if (scopeId) {
                scopeParams = switcher.data('param') + '/' + scopeId + '/';
            }

            if (obj.switchParams) {
                scopeParams += obj.switchParams;
            }

            /**
             * Reload function for switcher
             */
            function reload() {
                var url;

                if (!storeSwitchConfig.isUsingIframe) {

                    if (storeSwitchConfig.switchUrl && storeSwitchConfig.switchUrl.length > 0) {
                        url = storeSwitchConfig.switchUrl + scopeParams;

                        /* eslint-disable no-undef */
                        setLocation(url);
                    }

                } else {
                    $('#preview_selected_store').val(scopeId);
                    $('#preview_form').trigger('submit');

                    $('.store-switcher .dropdown-menu li a').each(function () {
                        var $this = $(this);

                        if ($this.data('role') === 'store-view-id' && $this.data('value') === scopeId) {
                            $('#store-change-button').html($this.text());
                        }
                    });

                    $('#store-change-button').click();
                }
            }

            if (typeof scopeSwitcherHandler !== 'undefined') {
                switcherParams = {
                    scopeId: scopeId,
                    scopeParams: scopeParams,
                    useConfirm: storeSwitchConfig.useConfirm
                };

                scopeSwitcherHandler(switcherParams);
            } else if (storeSwitchConfig.useConfirm) {
                require([
                    'Magento_Ui/js/modal/confirm',
                    'mage/translate'
                ], function (confirm, $t) {
                    confirm({
                        content: $t('Please confirm scope switching. All data that hasn\'t been saved will be lost.'),
                        actions: {

                            /**
                             * Confirm action
                             */
                            confirm: function () {
                                reload();
                            },

                            /**
                             * Cancel action
                             */
                            cancel: function () {
                                obj.value = storeSwitchConfig.storeId ? storeSwitchConfig.storeId : '';
                            }
                        }
                    });
                });
            } else {
                reload();
            }
        }

        window.scopeSwitcherHandler = scopeSwitcherHandler;
        window.switchScope = switchScope;
    };
});
