/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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