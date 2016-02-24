/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiRegistry',
    'Magento_Ui/js/form/element/abstract'
], function (registry, Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            parentComponentScope: '',
            canEditField: 'canEdit',
            listens: {
                'parentComponentScope': 'setVisibility'
            }
        },

        setVisibility: function (parentScope) {
            var canEdit = registry.get(this.provider + ':' + parentScope + '.' + this.canEditField);
            //this.visible(canEdit);
        }
    });
});
