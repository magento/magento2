/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/**
 * Assembles storages for form provider
 */
define([
    'Magento_Ui/js/lib/storage/storage'
], function(Storage){
    'use strict';

    return {
        meta:   Storage,
        params: Storage,
        data:   Storage,
        dump:   Storage
    }
});