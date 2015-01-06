/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
define([
    './storage',
    'Magento_Ui/js/lib/deferred_events'
], function (Storage, DeferredEvents) {
    return Storage.extend({}, DeferredEvents);
});