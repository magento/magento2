/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'Magento_Ui/js/form/components/group'
], function (_, Group) {
    'use strict';

    return Group.extend({
        defaults: {
            template: 'ui/grid/filters/elements/group'
        },

        hasData: function () {
            return this.elems.some(function (elem) {
                return elem.delegate('hasData');
            });
        },

        getPreview: function () {
            var previews = this.elems.map(function (elem) {
                if (elem.hasData()) {
                    return elem.label + ': ' + elem.getPreview();
                }
            });

            return _.compact(previews).join(' ');
        }
    });
});
