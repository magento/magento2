/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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