/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'ko',
    'underscore',
    'Magento_Ui/js/grid/massactions'
], function (ko, _, Massactions) {
    'use strict';

    return Massactions.extend({
        defaults: {
            template: 'ui/grid/tree-massactions',
            submenuTemplate: 'ui/grid/submenu',
            listens: {
                opened: 'hideSubmenus'
            }
        },

        /**
         * Initializes observable properties.
         *
         * @returns {Massactions} Chainable.
         */
        initObservable: function () {
            this._super()
                .recursiveObserveActions(this.actions());

            return this;
        },

        /**
         * Recursive initializes observable actions.
         *
         * @param {Array} actions - Action objects.
         * @returns {Massactions} Chainable.
         */
        recursiveObserveActions: function (actions) {
            _.each(actions, function (action) {
                if (action.actions) {
                    action.visible = ko.observable(false);
                    action.parent = actions;
                    this.recursiveObserveActions(action.actions);
                }
            }, this);

            return this;
        },

        /**
         * Applies specified action.
         *
         * @param {String} actionIndex - Actions' identifier.
         * @returns {Massactions} Chainable.
         */
        applyAction: function (actionIndex) {
            var action = this.getAction(actionIndex),
                visibility;

            if (action.visible) {
                visibility = action.visible();

                this.hideSubmenus(action.parent);
                action.visible(!visibility);

                return this;
            }

            return this._super(actionIndex);
        },

        /**
         * Retrieves action object associated with a specified index.
         *
         * @param {String} actionIndex - Actions' identifier.
         * @param {Array} actions - Action objects.
         * @returns {Object} Action object.
         */
        getAction: function (actionIndex, actions) {
            var currentActions = actions || this.actions(),
                result = false;

            _.find(currentActions, function (action) {
                if (action.type === actionIndex) {
                    result = action;

                    return true;
                }

                if (action.actions) {
                    result = this.getAction(actionIndex, action.actions);

                    return result;
                }
            }, this);

            return result;
        },

        /**
         * Recursive hide all sub folders in given array.
         *
         * @param {Array} actions - Action objects.
         * @returns {Massactions} Chainable.
         */
        hideSubmenus: function (actions) {
            var currentActions = actions || this.actions();

            _.each(currentActions, function (action) {
                if (action.visible && action.visible()) {
                    action.visible(false);
                }

                if (action.actions) {
                    this.hideSubmenus(action.actions);
                }
            }, this);

            return this;
        }
    });
});
