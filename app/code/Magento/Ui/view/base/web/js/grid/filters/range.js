/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'uiLayout',
    'mageUtils',
    'Magento_Ui/js/form/components/group',
    'mage/translate'
], function (_, layout, utils, Group, $t) {
    'use strict';

    return Group.extend({
        defaults: {
            template: 'ui/grid/filters/elements/group',
            isRange: true,
            templates: {
                base: {
                    parent: '${ $.$data.group.name }',
                    provider: '${ $.$data.group.provider }',
                    template: 'ui/grid/filters/field'
                },
                date: {
                    component: 'Magento_Ui/js/form/element/date',
                    dateFormat: 'MM/dd/YYYY'
                },
                text: {
                    component: 'Magento_Ui/js/form/element/abstract'
                },
                ranges: {
                    from: {
                        label: $t('from'),
                        dataScope: 'from'
                    },
                    to: {
                        label: $t('to'),
                        dataScope: 'to'
                    }
                }
            }
        },

        /**
         * Initializes range component.
         *
         * @returns {Range} Chainable.
         */
        initialize: function () {
            this._super()
                .initChildren();

            return this;
        },

        /**
         * Creates instances of child components.
         *
         * @returns {Range} Chainable.
         */
        initChildren: function () {
            var children = this.buildChildren();

            layout(children);

            return this;
        },

        /**
         * Creates configuration for the child components.
         *
         * @returns {Object}
         */
        buildChildren: function () {
            var templates   = this.templates,
                typeTmpl    = templates[this.rangeType],
                tmpl        = utils.extend({}, templates.base, typeTmpl),
                children    = {};

            _.each(templates.ranges, function (range, key) {
                children[key] = utils.extend({}, tmpl, range);
            });

            return utils.template(children, {
                group: this
            }, true, true);
        },

        /**
         * Clears childrens data.
         *
         * @returns {Range} Chainable.
         */
        clear: function () {
            this.elems.each('clear');

            return this;
        },

        /**
         * Checks if some children has data.
         *
         * @returns {Boolean}
         */
        hasData: function () {
            return this.elems.some('hasData');
        }
    });
});
