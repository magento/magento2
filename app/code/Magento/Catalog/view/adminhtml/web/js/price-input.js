/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/form/element/abstract'
], function (Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            priceValue: '',
            showFallbackReset: false,
            valueUpdate: 'afterkeydown'
        }
    });
});
