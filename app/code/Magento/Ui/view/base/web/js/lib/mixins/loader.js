/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/lib/spinner'
], function (spinner) {
    'use strict';

    return {
        /**
         * Activates spinner
         * @return {Object} reference to instance
         */
        lock: function() {
            spinner.show();

            return this;
        },

        /**
         * Deactivates spinner
         * @return {Object} reference to instance
         */
        unlock: function() {
            spinner.hide();

            return this;
        }
    }
});