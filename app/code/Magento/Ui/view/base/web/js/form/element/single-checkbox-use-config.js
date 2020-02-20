/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'Magento_Ui/js/form/element/single-checkbox'
], function (Component) {
    'use strict';

    return Component.extend({
        defaults: {
            isUseDefault: false,
            isUseConfig: false,
            listens: {
                'isUseConfig': 'toggleElement',
                'isUseDefault': 'toggleElement'
            }
        },

        /**
         * @inheritdoc
         */
        initObservable: function () {

            return this
                ._super()
                .observe('isUseConfig');
        },

        /**
         * Toggle element
         */
        toggleElement: function () {
            this.disabled(this.isUseDefault() || this.isUseConfig());

            if (this.source) {
                this.source.set('data.use_default.' + this.index, Number(this.isUseDefault()));
            }
        }
    });
});
