/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/single-checkbox'
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
        }
    });
});
