/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'Magento_Backend/js/validate-store'
], function ($, validateStore) {
    'use strict';

    $.widget('mage.deleteWithConfirm', validateStore, {});

    return $.mage.deleteWithConfirm;
});
