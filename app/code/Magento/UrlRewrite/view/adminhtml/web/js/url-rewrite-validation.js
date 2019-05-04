/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/backend/form',
    'mage/backend/validation'
], function ($) {
    'use strict';

    return function (data, element) {

        $(element).form().validation({
            validationUrl: data.url
        });
    };
});
