/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
define([
    'jquery',
    'Magento_Backend/js/validate-store'
], function ($, validateStore) {
    'use strict';

    $.widget('mage.deleteWithConfirm', validateStore, {});

    return $.mage.deleteWithConfirm;
});
