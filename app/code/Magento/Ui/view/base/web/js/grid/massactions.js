/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'uiRegistry',
    'mageUtils',
    'Magento_Ui/js/lib/collapsible',
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/modal/alert',
    'mage/translate'
], function (_, registry, utils, Collapsible, confirm, alert, $t) {
    'use strict';

    return Collapsible.extend({
        defaults: {
            template: 'ui/grid/actions',
            stickyTmpl: 'ui/grid/sticky/actions',
            selectProvider: 'ns = ${ $.ns }, index = ids',
            actions: [],
            noItemsMsg: $t('You haven\'t selected any items!'),
            modules: {
                selections: '${ $.selectProvider }'
            }
        },

        /**
         * Initializes observable properties.
         *
         * @returns {Massactions} Chainable.
         */
        initObservable: function () {
            this._super()
                .observe('actions');

            return this;
        },

        /**
         * Applies specified action.
         *
         * @param {String} actionIndex - Actions' identifier.
         * @returns {Massactions} Chainable.
         */
        applyAction: function (actionIndex) {
            var data = this.getSelections(),
                action,
                callback;

            if (!data.total) {
                alert({
                    content: this.noItemsMsg
                });

                return this;
            }

            action   = this.getAction(actionIndex);
            callback = this._getCallback(action, data);

            action.confirm ?
                this._confirm(action, callback) :
                callback();

            return this;
        },

        /**
         * Retrieves selections data from the selections provider.
         *
         * @returns {Object|Undefined}
         */
        getSelections: function () {
            var provider = this.selections(),
                selections = provider && provider.getSelections();

            return selections;
        },

        /**
         * Retrieves action object associated with a specified index.
         *
         * @param {String} actionIndex - Actions' identifier.
         * @returns {Object} Action object.
         */
        getAction: function (actionIndex) {
            return _.findWhere(this.actions(), {
                type: actionIndex
            });
        },

        /**
         * Adds new action. If action with a specfied identifier
         * already exists, than the original one will be overrided.
         *
         * @param {Object} action - Action object.
         * @returns {Massactions} Chainable.
         */
        addAction: function (action) {
            var actions = this.actions(),
                index = _.findIndex(actions, {
                    type: action.type
                });

            ~index ?
                actions[index] = action :
                actions.push(action);

            this.actions(actions);

            return this;
        },

        /**
         * Creates action callback based on its' data. If action doesn't spicify
         * a callback function than the default one will be used.
         *
         * @private
         * @param {Object} action - Actions' object.
         * @param {Object} selections - Selections data.
         * @returns {Function} Callback function.
         */
        _getCallback: function (action, selections) {
            var callback = action.callback,
                args     = [action, selections];

            if (utils.isObject(callback)) {
                args.unshift(callback.target);

                callback = registry.async(callback.provider);
            } else if (typeof callback != 'function') {
                callback = this.defaultCallback.bind(this);
            }

            return function () {
                callback.apply(null, args);
            };
        },

        /**
         * Default action callback. Sends selections data
         * via POST request.
         *
         * @param {Object} action - Action data.
         * @param {Object} data - Selections data.
         */
        defaultCallback: function (action, data) {
            var itemsType = data.excludeMode ? 'excluded' : 'selected',
                selections = {};

            selections[itemsType] = data[itemsType];

            if (!selections[itemsType].length) {
                selections[itemsType] = false;
            }

            _.extend(selections, data.params || {});

            utils.submit({
                url: action.url,
                data: selections
            });
        },

        /**
         * Shows actions' confirmation window.
         *
         * @param {Object} action - Actions' data.
         * @param {Function} callback - Callback that will be
         *      invoked if action is confirmed.
         */
        _confirm: function (action, callback) {
            var confirmData = action.confirm;

            confirm({
                title: confirmData.title,
                content: confirmData.message,
                actions: {
                    confirm: callback
                }
            });
        }
    });
});
