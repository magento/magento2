/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
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
