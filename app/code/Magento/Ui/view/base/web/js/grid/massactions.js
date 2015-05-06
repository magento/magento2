/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'mageUtils',
    'Magento_Ui/js/lib/collapsible'
], function (_, utils, Collapsible) {
    'use strict';

    return Collapsible.extend({
        defaults: {
            template: 'ui/grid/actions',
            noItems:  'You haven\'t selected any items!'
        },

        applyAction: function (action) {
            var proceed = true,
                selections = this.source.get('config.multiselect');

            if (!selections || !selections.total) {
                proceed = false;

                alert(this.noItems);
            }

            if (proceed && action.confirm) {
                proceed = window.confirm(action.confirm);
            }

            if (proceed) {
                utils.submit({
                    url: action.url,
                    data: selections
                });
            }
        }
    });
});
