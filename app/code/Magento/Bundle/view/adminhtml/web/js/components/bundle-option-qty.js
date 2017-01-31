/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/abstract'
], function (Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            valueUpdate: 'input',
            isInteger: true
        },

        /**
         * update event
         */
        onUpdate: function () {
            this.validation['validate-number'] = true;
            this.validation['validate-digits'] = this.isInteger;
            this.validate();
        }
    });
});
