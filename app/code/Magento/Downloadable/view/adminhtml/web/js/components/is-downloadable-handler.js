/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/form/element/single-checkbox'
], function (Element) {
    'use strict';

    return Element.extend({
        defaults: {
            listens: {
                disabled: 'changeVisibility',
                checked: 'changeVisibility'
            },
            modules: {
                samplesFieldset: '${ $.samplesFieldset }',
                linksFieldset: '${ $.linksFieldset}'
            }
        },

        /**
         * Change visibility for samplesFieldset & linksFieldset based on current statuses of checkbox.
         */
        changeVisibility: function () {
            if (this.samplesFieldset && this.linksFieldset) {
                if (this.checked() && !this.disabled()) {
                    this.samplesFieldset().visible(true);
                    this.linksFieldset().visible(true);
                } else {
                    this.samplesFieldset().visible(false);
                    this.linksFieldset().visible(false);
                }
            }
        }
    });
});
