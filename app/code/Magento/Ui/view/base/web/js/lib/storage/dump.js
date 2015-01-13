/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    './storage',
    'Magento_Ui/js/lib/deferred_events'
], function (Storage, DeferredEvents) {
    return Storage.extend({}, DeferredEvents);
});