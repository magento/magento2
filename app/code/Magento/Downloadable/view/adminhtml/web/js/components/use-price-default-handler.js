/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/form/element/single-checkbox'
], function (Element) {
    'use strict';

    return Element.extend({
        defaults: {
            linksPurchasedSeparately: '0',
            listens: {
                linksPurchasedSeparately: 'changeVisibleStatus'
            }
        },

        /**
         * Change visibility of checkbox
         */
        changeVisibleStatus: function () {
            if (this.linksPurchasedSeparately === '1') {
                this.visible(true);
            } else {
                this.visible(false);
            }
        }
    });
});
