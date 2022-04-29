/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'mage/utils/wrapper'
], function ($, wrapper) {
    'use strict';

    var mixin = {

        /**
         * Check if persistent section is expired due to lifetime.
         *
         * @param {Function} originFn - Original method.
         * @return {Array}
         */
        getExpiredSectionNames: function (originFn) {
            var expiredSections = originFn(),
                storage = $.initNamespaceStorage('mage-cache-storage').localStorage,
                currentTimestamp = Math.floor(Date.now() / 1000),
                persistentIndex = expiredSections.indexOf('persistent'),
                persistentLifeTime = 0,
                sectionData;

            if (window.persistent !== undefined && window.persistent.expirationLifetime !== undefined) {
                persistentLifeTime = window.persistent.expirationLifetime;
            }

            if (persistentIndex !== -1) {
                sectionData = storage.get('persistent');

                if (typeof sectionData === 'object' &&
                    sectionData['data_id'] + persistentLifeTime >= currentTimestamp
                ) {
                    expiredSections.splice(persistentIndex, 1);
                }
            }

            return expiredSections;
        },

        /**
         * @param {Object} settings
         * @constructor
         */
        'Magento_Customer/js/customer-data': function (originFn,invalidateOptions) {
            let date;
            let storage = $.initNamespaceStorage('mage-cache-storage').localStorage;
            if (new Date($.localStorage.get('mage-cache-timeout')) < new Date()) {
                storage.removeAll();
                this.reload(['persistent','cart'],true);
            }
            date = new Date(Date.now() + parseInt(invalidateOptions.cookieLifeTime, 10) * 1000);
            $.localStorage.set('mage-cache-timeout', date);

            if (!$.cookieStorage.isSet('mage-cache-sessid')) {
                $.cookieStorage.set('mage-cache-sessid', true);
                storage.removeAll();
                this.reload(['persistent','cart'],true);
            }
            originFn();

        }
    };

    /**
     * Override default customer-data.getExpiredSectionNames().
     */
    return function (target) {
        return wrapper.extend(target, mixin);
    };
});
