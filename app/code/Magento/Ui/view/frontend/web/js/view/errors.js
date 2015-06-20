/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(['uiComponent', '../model/errorlist'], function (Component, errors) {
    'use strict';

    return Component.extend({
        errorList: errors.getAll(),
        defaults: {
            template: 'Magento_Ui/errors'
        },

        /**
         * Remove all errors
         */
        removeAll: function () {
            this.errorList.removeAll();
        }
    });
});
