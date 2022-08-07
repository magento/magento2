/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/translate',
    'Magento_Ui/js/modal/confirm',
    'domReady!'
], function ($, $t, confirm) {
    'use strict';

    return function (config, inputEl) {
        var $inputEl = $(inputEl);

        $inputEl.on('blur', function () {
            var inputVal = parseInt($inputEl.val(), 10);

            if (inputVal < 256000) {
                confirm({
                    title: $t(config.modalTitleText),
                    content: $t(config.modalContentBody),
                    buttons: [{
                        text: $t('No'),
                        class: 'action-secondary action-dismiss',

                        /**
                         * Close modal and trigger 'cancel' action on click
                         */
                        click: function (event) {
                            this.closeModal(event);
                        }
                    }, {
                        text: $t('Yes'),
                        class: 'action-primary action-accept',

                        /**
                         * Close modal and trigger 'confirm' action on click
                         */
                        click: function (event) {
                            this.closeModal(event, true);
                        }
                    }],
                    actions: {

                        /**
                         * Revert back to original value
                         */
                        cancel: function () {
                            $inputEl.val(256000);
                        }
                    }
                });
            }
        });
    };
});
