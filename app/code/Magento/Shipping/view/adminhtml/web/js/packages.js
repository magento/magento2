/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/translate'
], function ($, modal, $t) {
    'use strict';

    return function (config, element) {
        config.buttons = [
            {
                text: $t('Print'),
                'class': 'action action-primary',

                /**
                 * Click handler
                 */
                click: function () {
                    window.location.href = this.options.url;
                }
            }, {
                text: $t('Cancel'),
                'class': 'action action-secondary',

                /**
                 * Click handler
                 */
                click: function () {
                    this.closeModal();
                }
            }
        ];
        modal(config, element);
    };
});
