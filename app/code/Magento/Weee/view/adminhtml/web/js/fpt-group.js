/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/components/group',
    'uiRegistry',
], function (Group, uiRegistry) {
    'use strict';

    return Group.extend({
        defaults: {
            visible: true,
            label: '',
            showLabel: true,
            required: false,
            template: 'ui/group/group',
            fieldTemplate: 'ui/form/field',
            breakLine: true,
            validateWholeGroup: false,
            additionalClasses: {}
        },
        initElement: function (elem) {
            elem.initContainer(this);
            //alter the validator here
            return this;
        },
        insertChild: function (elems, position) {
            //or alter the validator here
            this._super();
        }
    });
});
