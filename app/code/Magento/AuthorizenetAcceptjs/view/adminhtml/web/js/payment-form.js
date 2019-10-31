/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_AuthorizenetAcceptjs/js/authorizenet',
    'jquery'
], function (AuthorizenetAcceptjs, $) {
    'use strict';

    return function (config, element) {
        var $form = $(element);

        config.active = $form.length > 0 && !$form.is(':hidden');
        new AuthorizenetAcceptjs(config);
    };
});
