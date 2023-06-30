/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'mageUtils',
    'Magento_Ui/js/grid/tree-massactions'
], function (_, utils, Massactions) {
    'use strict';

    return Massactions.extend({
        /**
         * Overwrite Default action callback.
         * Sends selections data with ids
         * via POST request.
         *
         * @param {Object} action - Action data.
         * @param {Object} data - Selections data.
         */
        defaultCallback: function (action, data) {
            var itemsType = 'selected',
                selections = {};

            selections[itemsType] = data[itemsType];
            _.extend(selections, data.params || {});
            utils.submit({
                url: action.url,
                data: selections
            });
        }
    });
});
