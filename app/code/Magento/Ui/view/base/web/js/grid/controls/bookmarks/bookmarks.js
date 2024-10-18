/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'underscore',
    'mageUtils',
    'mage/translate',
    'rjsResolver',
    'uiLayout',
    'uiCollection'
], function (_, utils, $t, resolver, layout, Collection) {
    'use strict';

    /**
     * Removes 'current' namespace from a 'path' string.
     *
     * @param {String} path
     * @returns {String} Path without namespace.
     */
    function removeStateNs(path) {
        path = typeof path == 'string' ? path.split('.') : [];

        if (path[0] === 'current') {
            path.shift();
        }

        return path.join('.');
    }

    return Collection.extend({
        defaults: {
            template: 'ui/grid/controls/bookmarks/bookmarks',
            viewTmpl: 'ui/grid/controls/bookmarks/view',
            newViewLabel: $t('New View'),
            defaultIndex: 'default',
            activeIndex: 'default',
            viewsArray: [],
            storageConfig: {
                provider: '${ $.storageConfig.name }',
                name: '${ $.name }_storage',
                component: 'Magento_Ui/js/grid/controls/bookmarks/storage'
            },
            views: {
                default: {
                    label: $t('Default View'),
                    index: 'default',
                    editable: false
                }
            },
            tracks: {
                editing: true,
                viewsArray: true,
                activeView: true,
                hasChanges: true,
                customLabel: true,
                customVisible: true,
                isActiveIndexChanged: false
            },
            listens: {
                activeIndex: 'onActiveIndexChange',
                activeView: 'checkState',
                current: 'onStateChange'
            }
        },

        /**
         * Initializes bookmarks component.
         *
         * @returns {Bookmarks} Chainable.
         */
        initialize: function () {
            utils.limit(this, 'checkState', 5);
            utils.limit(this, 'saveState', 2000);

            this._super()
                .restore()
                .initStorage()
                .initViews();

            return this;
        },

        /**
         * Creates custom storage instance.
         *
         * @returns {Bookmarks} Chainable.
         */
        initStorage: function () {
            layout([this.storageConfig]);

            return this;
        },

        /**
         * Defines default data if it wasn't gathered previously.
         *
         * @private
         * @returns {Bookmarks} Chainbale.
         */
        initDefaultView: function () {
            var data = this.getViewData(this.defaultIndex);

            if (!_.size(data) && (this.current.columns && this.current.positions)) {
                this.setViewData(this.defaultIndex, this.current)
                    .saveView(this.defaultIndex);
                this.defaultDefined = true;
            }

            return this;
        },

        /**
         * Creates instances of a previously saved views.
         *
         * @returns {Bookmarks} Chainable.
         */
        initViews: function () {
            _.each(this.views, function (config) {
                this.addView(config);
            }, this);

            this.activeView = this.getActiveView();

            return this;
        },

        /**
         * Creates complete configuration for a view.
         *
         * @param {Object} [config] - Additional configuration object.
         * @returns {Object}
         */
        buildView: function (config) {
            var view = {
                label: this.newViewLabel,
                index: '_' + Date.now(),
                editable: true
            };

            utils.extend(view, config || {});

            view.data   = view.data || utils.copy(this.current);
            view.value  = view.label;

            this.observe.call(view, true, 'label value');

            return view;
        },

        /**
         * Creates instance of a view with a provided configuration.
         *
         * @param {Object} [config] - View configuration.
         * @param {Boolean} [saveView=false] - Whether to save created view automatically or not.
         * @param {Boolean} [applyView=false] - Whether to apply created view automatically or not.
         * @returns {View} Created view.
         */
        addView: function (config, saveView, applyView) {
            var view    = this.buildView(config),
                index   = view.index;

            this.views[index] = view;

            if (saveView) {
                this.saveView(index);
            }

            if (applyView) {
                this.applyView(index);
            }

            this.updateArray();

            return view;
        },

        /**
         * Removes specified view.
         *
         * @param {String} index - Index of a view to be removed.
         * @returns {Bookmarks} Chainable.
         */
        removeView: function (index) {
            var viewPath = this.getViewPath(index);

            if (this.isViewActive(index)) {
                this.applyView(this.defaultIndex);
            }

            this.endEdit(index)
                .remove(viewPath)
                .removeStored(viewPath)
                .updateArray();
            this.isActiveIndexChanged = false;

            return this;
        },

        /**
         * Saves data of a specified view.
         *
         * @param {String} index - Index of a view to be saved.
         * @returns {Bookmarks} Chainable.
         */
        saveView: function (index) {
            var viewPath = this.getViewPath(index);

            this.updateViewLabel(index)
                .endEdit(index)
                .store(viewPath)
                .checkState();

            return this;
        },

        /**
         * Sets specified view as active
         * and applies its' state.
         *
         * @param {String} index - Index of a view to be applied.
         * @returns {Bookmarks} Chainable.
         */
        applyView: function (index) {
            this.applyStateOf(index)
                .set('activeIndex', index);

            return this;
        },

        /**
         * Updates data of a specified view if it's
         * currently active and saves its' data.
         *
         * @param {String} index - Index of a view.
         * @returns {Bookmarks} Chainable.
         */
        updateAndSave: function (index) {
            if (this.isViewActive(index)) {
                this.updateActiveView(index);
            }

            this.saveView(index);

            return this;
        },

        /**
         * Returns instance of a specified view.
         *
         * @param {String} index - Index of a view to be retrieved.
         * @returns {View}
         */
        getView: function (index) {
            return this.views[index];
        },

        /**
         * Returns instance of an active view.
         *
         * @returns {View}
         */
        getActiveView: function () {
            return this.views[this.activeIndex];
        },

        /**
         * Checks if specified view is active.
         *
         * @param {String} index - Index of a view to be checked.
         * @returns {Boolean}
         */
        isViewActive: function (index) {
            return this.activeView === this.getView(index);
        },

        /**
         * Sets current state as a data of an active view.
         *
         * @returns {Bookmarks} Chainable.
         */
        updateActiveView: function () {
            this.setViewData(this.activeIndex, this.current);

            return this;
        },

        /**
         * Replaces label a view with a provided one.
         * If new label is not specified, then views'
         * 'value' property will be taken.
         *
         * @param {String} index - Index of a view.
         * @param {String} [label=view.value] - New labels' value.
         * @returns {Bookmarks} Chainable.
         */
        updateViewLabel: function (index, label) {
            var view    = this.getView(index),
                current = view.label;

            label = (label || view.value).trim() || current;
            label = this.uniqueLabel(label, current);

            view.label = view.value = label;

            return this;
        },

        /**
         * Retrieves data of a specified view.
         *
         * @param {String} index - Index of a view whose data should be retrieved.
         * @param {String} [property] - If not specified then whole views' data will be retrieved.
         * @returns {Object} Views' data.
         */
        getViewData: function (index, property) {
            var view = this.getView(index),
                data = view.data;

            if (property) {
                data = utils.nested(data, property);
            }

            return utils.copy(data);
        },

        /**
         * Sets data to the specified view.
         *
         * @param {String} index - Index of a view whose data will be replaced.
         * @param {Object} data - New view data.
         * @returns {Bookmarks} Chainable.
         */
        setViewData: function (index, data) {
            var path = this.getViewPath(index) + '.data';

            this.set(path, utils.copy(data));

            return this;
        },

        /**
         * Starts editing of a specified view.
         *
         * @param {String} index - Index of a view.
         * @returns {Bookmarks} Chainable.
         */
        editView: function (index) {
            this.editing = index;

            return this;
        },

        /**
         * Ends editing of specified view
         * and restores its' label.
         *
         * @param {String} index - Index of a view.
         * @returns {Bookmarks} Chainable.
         */
        endEdit: function (index) {
            var view;

            if (!this.isEditing(index)) {
                return this;
            }

            index   = index || this.editing;
            view    = this.getView(index);

            view.value = view.label;

            this.editing = false;

            return this;
        },

        /**
         * Checks if specified view is in editing state.
         *
         * @param {String} index - Index of a view to be checked.
         * @returns {Boolean}
         */
        isEditing: function (index) {
            return this.editing === index;
        },

        /**
         * Generates label unique among present views, based
         * on the incoming label pattern.
         *
         * @param {String} [label=this.newViewLabel] - Label pattern.
         * @param {String} [exclude]
         * @returns {String}
         */
        uniqueLabel: function (label, exclude) {
            var labels      = _.pluck(this.views, 'label'),
                hasParenth  = _.last(label) === ')',
                index       = 2,
                result,
                suffix;

            labels = _.without(labels, exclude);
            result = label = label || this.newViewLabel;

            for (index = 2; _.contains(labels, result); index++) {
                suffix = '(' + index + ')';

                if (!hasParenth) {
                    suffix = ' ' + suffix;
                }

                result = label + suffix;
            }

            return result;
        },

        /**
         * Applies state of a specified view, without
         * making it active.
         *
         * @param {String} [state=this.activeIndex]
         * @param {String} [property]
         * @returns {Bookmarks} Chainable.
         */
        applyStateOf: function (state, property) {
            var index    = state || this.activeIndex,
                dataPath = removeStateNs(property),
                viewData = this.getViewData(index, dataPath);

            dataPath = dataPath ?
                'current.' + dataPath :
                'current';

            this.set(dataPath, viewData);

            return this;
        },

        /**
         * Saves current state.
         *
         * @returns {Bookmarks} Chainable.
         */
        saveState: function () {
            if (!this.isActiveIndexChanged) {
                this.store('current');
            }
            this.isActiveIndexChanged = false;
            return this;
        },

        /**
         * Applies state of an active view.
         *
         * @returns {Bookmarks} Chainable.
         */
        resetState: function () {
            this.applyStateOf(this.activeIndex);

            return this;
        },

        /**
         * Checks if current state is different
         * from the state of an active view.
         *
         * @returns {Bookmarks} Chainable.
         */
        checkState: function () {
            var viewData = this.getViewData(this.activeIndex),
                diff     = utils.compare(viewData, this.current);

            this.hasChanges = !diff.equal;

            return this;
        },

        /**
         * Returns path to the view instance,
         * based on a provided index.
         *
         * @param {String} index - Index of a view.
         * @returns {String}
         */
        getViewPath: function (index) {
            return 'views.' + index;
        },

        /**
         * Updates the array of views.
         *
         * @returns {Bookmarks} Chainable
         */
        updateArray: function () {
            this.viewsArray = _.values(this.views);

            return this;
        },

        /**
         * Shows custom view field and creates unique label for it.
         *
         * @returns {Bookmarks} Chainable.
         */
        showCustom: function () {
            this.customLabel    = this.uniqueLabel();
            this.customVisible  = true;

            return this;
        },

        /**
         * Hides custom view field.
         *
         * @returns {Bookmarks} Chainable.
         */
        hideCustom: function () {
            this.customVisible = false;

            return this;
        },

        /**
         * Checks if custom view field is visible.
         *
         * @returns {Boolean}
         */
        isCustomVisible: function () {
            return this.customVisible;
        },

        /**
         * Creates new view instance with a label specified
         * in a custom view field.
         *
         * @returns {Bookmarks} Chainable.
         */
        applyCustom: function () {
            var label = this.customLabel.trim();

            this.hideCustom()
                .addView({
                    label: this.uniqueLabel(label)
                }, true, true);

            return this;
        },

        /**
         * Listener of the activeIndex property.
         */
        onActiveIndexChange: function () {
            this.activeView = this.getActiveView();
            this.updateActiveView();
            this.store('activeIndex');
            this.isActiveIndexChanged = true;
        },

        /**
         * Listener of the activeIndex property.
         */
        onStateChange: function () {
            this.checkState();
            this.saveState();

            if (!this.defaultDefined) {
                resolver(this.initDefaultView, this);
            }

            if (!_.isUndefined(this.activeView)
                && !_.isUndefined(this.activeView.data)
                && !_.isUndefined(this.current)) {
                if (JSON.stringify(this.activeView.data.filters) === JSON.stringify(this.current.filters)
                    && JSON.stringify(this.activeView.data.positions) !== JSON.stringify(this.current.positions)) {
                    this.updateActiveView();
                }
            }
        }
    });
});
