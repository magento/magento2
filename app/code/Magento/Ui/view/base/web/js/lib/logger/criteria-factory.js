/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'mage/utils/compare'
], function (compare) {
    /**
     *
     */
    function LogCriteriaFactory() {}

    /**
     *
     * @param {Object | RegExp} criteria
     * @returns {LogCriteria}
     */
    LogCriteriaFactory.prototype.create = function () {
        if (criteria instanceof RegExp) {
            return function (entry) {
                return criteria.test(entry.message);
            };
        }

        return function (entry) {
            var diff = utils.compare(entry, criteria);

            return diff.every(result => {
                var type = result.type;

                return type !== 'add' && type !== 'update';
            });
        };
    };

    return LogCriteriaFactory;
});
