/**
 * Copyright � 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiElement',
    'uiRegistry',
    'uiLayout',
    'mageUtils'
], function (Element, registry, layout, utils) {
    'use strict';

    return Element.extend({
        defaults:{
            template: 'ui/form/components/component-trigger'
        },

        /**
         * Initializes component.
         *
         * @returns {Object} Chainable.
         */
        initialize: function () {
            return this._super();
        },

        /**
         * Perform configured action on target component,
         * but previously create this component from template if it is not existed
         */
        action: function () {
            var target = this.target,
                targetName = this.targetName,
                parentName = targetName.split('.'),
                child,
                index;

            if (!target) {
                if (registry.has(targetName)) {
                    target = registry.async(targetName);
                } else {
                    index = parentName.pop();
                    parentName = parentName.join('.');

                    child = utils.template({
                        parent: parentName,
                        name: index,
                        nodeTemplate: targetName
                    });
                    layout([child]);
                    target = registry.async(this.targetName);
                }
            }

            if (target && typeof target === 'function' && this.actionName) {
                target(this.actionName);
            }
        }
    });
});
