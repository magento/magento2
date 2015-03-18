define([
    'underscore',
    './column'
], function (_, Column) {
    'use strict';

    return Column.extend({
        defaults: {
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
