/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'mageUtils',
    'Magento_Ui/js/form/components/button'
], function (utils, Button) {
    'use strict';

    return Button.extend({

        /**
         * Creates and submits form for Guarantee create/cancel
         */
        sendRequest: function () {
            utils.submit({
                url: this.requestURL,
                data: this.data
            });
        }
    });
});
