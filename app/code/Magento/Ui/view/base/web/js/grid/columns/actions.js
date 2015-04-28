/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    './column'
], function (_, Column) {
    'use strict';

    return Column.extend({
        defaults: {
            headerTmpl: 'ui/grid/columns/actions',
            bodyTmpl: 'ui/grid/cells/actions'
        },

        getDisplayed: function (actions) {
            actions = _.filter(actions, function (action) {
                return !('hidden' in action) || !action.hidden;
            });

            this.displayed = actions;

            return actions;
        }
    });
});
