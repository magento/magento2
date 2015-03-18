define([
    'underscore',
    './text'
], function (_, Text) {
    'use strict';

    return Text.extend({
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
