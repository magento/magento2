/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/components/button'
], function (Button) {
    'use strict';

    return Button.extend({

        /**
         * Extends 'buttonClasses' object.
         *
         * @returns {Object} Chainable
         */
        _setButtonClasses: function () {
            this._super();

            _.extend(this.buttonClasses, {
                'action-basic': false
            });

            return this;
        }
    });
});
