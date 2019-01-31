/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_AuthorizenetAcceptjs/js/authorizenet',
    'jquery',
    'domReady!'
], function (AuthorizenetAcceptjs, $) {
    'use strict';

    return function (data) {
        var $form = $(data.formSelector),
            config = data.config;

        config.active = $form.length > 0 && !$form.is(':hidden');
        new AuthorizenetAcceptjs(config);
    };
});
