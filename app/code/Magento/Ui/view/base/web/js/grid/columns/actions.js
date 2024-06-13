/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

/**
 * @api
 */
define([
    'underscore',
    'mageUtils',
    'uiRegistry',
    './column',
    'Magento_Ui/js/modal/confirm',
    'mage/dataPost'
], function (_, utils, registry, Column, confirm, dataPost) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'ui/grid/cells/actions',
            sortable: false,
            draggable: false,
            actions: [],
            rows: [],
            rowsProvider: '${ $.parentName }',
            fieldClass: {
                'data-grid-actions-cell data-action-position': true
            },
            templates: {
                actions: {}
            },
            imports: {
                rows: '${ $.rowsProvider }:rows'
            },
            listens: {
                rows: 'updateActions'
            }
        },

        /**
         * Initializes observable properties.
         *
         * @returns {ActionsColumn} Chainable.
         */
        initObservable: function () {
            this._super()
                .track('actions');

            return this;
        },

        /**
         * Returns specific action of a specified row
         * or all action objects associated with it.
         *
         * @param {Number} rowIndex - Index of a row.
         * @param {String} [actionIndex] - Action identifier.
         * @returns {Array|Object}
         */
        getAction: function (rowIndex, actionIndex) {
            var rowActions = this.actions[rowIndex];

            return rowActions && actionIndex ?
                rowActions[actionIndex] :
                rowActions;
        },

        /**
         * Returns visible actions for a specified row.
         *
         * @param {Number} rowIndex - Index of a row.
         * @returns {Array} Visible actions.
         */
        getVisibleActions: function (rowIndex) {
            var rowActions = this.getAction(rowIndex);

            return _.filter(rowActions, this.isActionVisible, this);
        },

        /**
         * Adds new action. If an action with the specified identifier
         * already exists, then the original will be overridden.
         *
         * @param {String} index - Actions' identifier.
         * @param {Object} action - Actions' data.
         * @returns {ActionsColumn} Chainable.
         */
        addAction: function (index, action) {
            var actionTmpls = this.templates.actions;

            actionTmpls[index] = action;

            this.updateActions();

            return this;
        },

        /**
         * Recreates actions for each row.
         *
         * @returns {ActionsColumn} Chainable.
         */
        updateActions: function () {
            this.actions = this.rows.map(this._formatActions, this);

            return this;
        },

        /**
         * Processes actions, setting additional information to them and
         * evaluating their properties as string templates.
         *
         * @private
         * @param {Object} row - Row object.
         * @param {Number} rowIndex - Index of a row.
         * @returns {Array}
         */
        _formatActions: function (row, rowIndex) {
            var rowActions      = row[this.index] || {},
                recordId        = row[this.indexField],
                customActions   = this.templates.actions;

            /**
             * Actions iterator.
             */
            function iterate(action, index) {
                action = utils.extend({
                    index: index,
                    rowIndex: rowIndex,
                    recordId: recordId
                }, action);

                return utils.template(action, row, true);
            }

            rowActions      = _.mapObject(rowActions, iterate);
            customActions   = _.map(customActions, iterate);

            customActions.forEach(function (action) {
                rowActions[action.index] = action;
            });

            return rowActions;
        },

        /**
         * Applies specified action.
         *
         * @param {String} actionIndex - Actions' identifier.
         * @param {Number} rowIndex - Index of a row.
         * @returns {ActionsColumn} Chainable.
         */
        applyAction: function (actionIndex, rowIndex) {
            var action = this.getAction(rowIndex, actionIndex),
                callback = this._getCallback(action);

            action.confirm ?
                this._confirm(action, callback) :
                callback();

            return this;
        },

        /**
         * Creates handler for the provided action if it's required.
         *
         * @param {Object} action - Action object.
         * @returns {Function|Undefined}
         */
        getActionHandler: function (action) {
            var index = action.index,
                rowIndex = action.rowIndex;

            if (this.isHandlerRequired(index, rowIndex)) {
                return this.applyAction.bind(this, index, rowIndex);
            }
        },

        /**
         * Returns target of action if it's been set.
         *
         * @param {Object} action - Action object.
         * @returns {String}
         */
        getTarget: function (action) {
            if (action.target) {
                return action.target;
            }

            return '_self';
        },

        /**
         * Checks if specified action requires a handler function.
         *
         * @param {String} actionIndex - Actions' identifier.
         * @param {Number} rowIndex - Index of a row.
         * @returns {Boolean}
         */
        isHandlerRequired: function (actionIndex, rowIndex) {
            var action = this.getAction(rowIndex, actionIndex);

            return _.isObject(action.callback) || action.confirm || !action.href;
        },

        /**
         * Creates action callback based on it's data. If the action doesn't specify
         * a callback function than the default one will be used.
         *
         * @private
         * @param {Object} action - Action's object.
         * @returns {Function} Callback function.
         */
        _getCallback: function (action) {
            var args = [action.index, action.recordId, action],
                callback = action.callback;

            if (utils.isObject(callback)) {
                args.unshift(callback.target);

                callback = registry.async(callback.provider);
            } else if (_.isArray(callback)) {
                return this._getCallbacks(action);
            } else if (!_.isFunction(callback)) {
                callback = this.defaultCallback.bind(this);
            }

            return function () {
                callback.apply(callback, args);
            };
        },

        /**
         * Creates action callback for multiple actions.
         *
         * @private
         * @param {Object} action - Action's object.
         * @returns {Function} Callback function.
         */
        _getCallbacks: function (action) {
            var callback = action.callback,
                callbacks = [],
                tmpCallback;

            _.each(callback, function (cb) {
                tmpCallback = {
                    action: registry.async(cb.provider),
                    args: _.compact([cb.target, cb.params])
                };
                callbacks.push(tmpCallback);
            });

            return function () {
                _.each(callbacks, function (cb) {
                    cb.action.apply(cb.action, cb.args);
                });
            };
        },

        /**
         * Default action callback. Redirects to
         * the specified in action's data url.
         *
         * @param {String} actionIndex - Action's identifier.
         * @param {(Number|String)} recordId - Id of the record associated
         *      with a specified action.
         * @param {Object} action - Action's data.
         */
        defaultCallback: function (actionIndex, recordId, action) {
            if (action.post) {
                dataPost().postData({
                    action: action.href,
                    data: {}
                });
            } else {
                window.location.href = action.href;
            }
        },

        /**
         * Shows actions' confirmation window.
         *
         * @param {Object} action - Action's data.
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
        },

        /**
         * Checks if row has only one visible action.
         *
         * @param {Number} rowIndex - Row index.
         * @returns {Boolean}
         */
        isSingle: function (rowIndex) {
            return this.getVisibleActions(rowIndex).length === 1;
        },

        /**
         * Checks if row has more than one visible action.
         *
         * @param {Number} rowIndex - Row index.
         * @returns {Boolean}
         */
        isMultiple: function (rowIndex) {
            return this.getVisibleActions(rowIndex).length > 1;
        },

        /**
         * Checks if action should be displayed.
         *
         * @param {Object} action - Action object.
         * @returns {Boolean}
         */
        isActionVisible: function (action) {
            return action.hidden !== true;
        },

        /**
         * Overrides base method, because this component
         * can't have global field action.
         *
         * @returns {Boolean} False.
         */
        hasFieldAction: function () {
            return false;
        }
    });
});
