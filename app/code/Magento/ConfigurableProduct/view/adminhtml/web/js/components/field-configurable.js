/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/abstract'
], function (Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            parentComponentScope: '',
            canEditField: 'canEdit',
            visibleIfCanEdit: true,
            listens: {
                'parentComponentScope': 'setVisibility'
            }
        },

        /**
         * Set visibility for current component
         *
         * @param {String} parentScope
         */
        setVisibility: function (parentScope) {
            var canEdit = this.source.get(parentScope + '.' + this.canEditField);
            var visible = this.visibleIfCanEdit ? canEdit : !canEdit;

            this.visible(visible);
        }
    });
});
