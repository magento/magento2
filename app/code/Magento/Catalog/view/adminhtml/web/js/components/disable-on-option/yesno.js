/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/form/element/single-checkbox',
    'Magento_Catalog/js/components/disable-on-option/strategy'
], function (Element, strategy) {
    'use strict';

    var comp = Element.extend(strategy).extend({

        defaults: {
            listens: {
                disabled: 'updateValueForDisabledField',
                visible: 'updateValueForDisabledField'
            }
        },

        /**
         * {@inheritdoc}
         */
        initialize: function () {
            this._super();
            this.updateValueForDisabledField();

            return this;
        },

        /**
         * Set element value to O(No) if element is invisible and disabled
         * Set element value to initialValue if element becomes visible and enable
         */
        updateValueForDisabledField: function () {
            if (!this.disabled() && this.visible()) {
                this.set('value', this.initialValue);
            } else {
                this.set('value', 0);
            }
        }
    });

    return comp.extend(strategy);
});
