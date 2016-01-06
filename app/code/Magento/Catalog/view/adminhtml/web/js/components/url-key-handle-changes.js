/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/abstract'
], function (Abstract) {
    'use strict';

    return Abstract.extend({

        /**
         * Disable checkbox field, when 'url_key' field without changes
         */
        handleChanges: function (newValue) {
            if (newValue !== this.getInitialValue()) {
                this.disabled(false);
            } else {
                this.disabled(true);
            }
        },

        /**
         * Set real 'url_key' to 'url_key_create_redirect' when field is checked
         */
        onUpdate: function () {
            this._super();

            if (this.value()) {
                this.value(this.initialValue);
            } else {
                this.value(0);
            }
            this._super();
        }
    });
});
