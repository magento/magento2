/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'mageUtils',
    'mage/translate',
    'Magento_Ui/js/lib/collapsible'
], function (_, utils, $t, Collapsible) {
    'use strict';

    return Collapsible.extend({
        defaults: {
            template: 'ui/grid/actions',
            noItemsMsg:  $t('You haven\'t selected any items!')
        },

        applyAction: function (action) {
            var proceed = true,
                selections = this.source.get('config.multiselect');

            if (!selections || !selections.total) {
                proceed = false;

                alert(this.noItemsMsg);
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
