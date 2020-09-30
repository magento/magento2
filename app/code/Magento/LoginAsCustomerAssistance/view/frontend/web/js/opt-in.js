/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery'
], function ($) {
    'use strict';

    return function (config, element) {
        $(element).on('submit', function () {
            this.elements['assistance_allowed'].value =
                this.elements['assistance_allowed_checkbox'].checked ?
                    config.allowAccess : config.denyAccess;
        });
    };
});
