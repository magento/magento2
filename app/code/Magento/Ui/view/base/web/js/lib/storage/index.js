/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/**
 * Assembles storages returning storage mapping
 */
define([
    './storage',
    './meta',
    './dump'
], function(Storage, MetaStorage, DumpStorage){
    'use strict';

    return {
        meta:   MetaStorage,
        params: Storage,
        config: Storage,
        data:   Storage,
        dump:   DumpStorage
    }
});