/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
<<<<<<< HEAD
    'mage/mage'
=======
    'mage/mage',
    'validation'
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
], function ($) {
    'use strict';

    return function (config, element) {
<<<<<<< HEAD

        $(element).mage('form').mage('validation', {
=======
        $(element).mage('form').validation({
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            validationUrl: config.validationUrl
        });
    };
});
