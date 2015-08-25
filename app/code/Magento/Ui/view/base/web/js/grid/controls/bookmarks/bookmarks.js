/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'mageUtils',
    'uiRegistry',
    'uiLayout',
    'Magento_Ui/js/lib/collapsible'
], function (_, utils, registry, layout, Collapsible) {
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

    return Collapsible.extend({
        defaults: {
            template: 'ui/grid/controls/bookmarks/bookmarks',
            defaultIndex: 'default',
            activeIndex: 'default',
            hasChanges: false,
            templates: {
                view: {
                    parent: '${ $.$data.name }',
                    name: '${ $.$data.index }',
                    label: '${ $.$data.label }',
                    provider: '${ $.$data.provider }',
                    component: 'Magento_Ui/js/grid/controls/bookmarks/view'
                },
                newView: {
                    label: 'New View',
                    index: '_${ Date.now() }',
                    editing: true,
                    isNew: true
                }
            },
            storageConfig: {
                provider: '${ $.storageConfig.name }',
                name: '${ $.name }_storage',
                component: 'Magento_Ui/js/grid/controls/bookmarks/storage'
            },
            views: {
                default: {
                    label: 'Default View',
                    index: 'default',
                    editable: false
                }
            },
            listens: {
                activeIndex: 'onActiveIndexChange',
                activeView: 'checkChanges',
                current: 'onStateChange'
            }
        },

        /**
         * Initializes bookmarks component.
         *
         * @returns {Bookmarks} Chainable.
         */
        initialize: function () {
            utils.limit(this, 'saveSate', 2000);
            utils.limit(this, '_defaultPolyfill', 1000);
            utils.limit(this, 'checkChanges', 50);

            this._super()
                .restore()
                .initStorage()
                .initViews();

            return this;
        },

        /**
         * Initializes observable properties.
         *
         * @returns {Bookmarks} Chainable.
         */
        initObservable: function () {
            this._super()
                .observe('activeView hasChanges');

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
         * Called when another element was added to the current component.
         *
         * @param {Object} elem - Instance of an element that was added.
         * @returns {Bookmarks} Chainable.
         */
        initElement: function (elem) {
            var index = elem.index;

            if (index === this.defaultIndex) {
                this.defaultView = elem;
            }

            if (index === this.activeIndex) {
                this.activeView(elem);
            }

            elem.on({
                editing: this.onEditingChange.bind(this, elem)
            });

            return this._super();
        },

        /**
         * Creates instances of a previously saved views.
         *
         * @returns {Bookmarks} Chainable.
         */
        initViews: function () {
            var views = this.views,
                active = _.findWhere(views, {index: this.activeIndex});

            if (!active) {
                this.activeIndex = this.defaultIndex;
            }

            _.each(views, this.createView, this);

            return this;
        },

        /**
         * Creates view with a provided data.
         *
         * @param {Object} item - Data object that will be passed to a view instance.
         * @returns {Bookmarks} Chainable.
         */
        createView: function (item) {
            var data = _.extend({}, this, item),
                child = utils.template(this.templates.view, data);

            _.extend(child, item);

            if (this.activeIndex === item.index) {
                child.active = true;
            }

            layout([child]);

            return this;
        },

        /**
         * Creates new view instance.
         *
         * @returns {Bookmarks} Chainable.
         */
        createNewView: function () {
            var view = this.templates.newView;

            view.data = this.current;

            this.createView(view);

            return this;
        },

        /**
         * Deletes specfied view.
         *
         * @param {View} view - View to be deleted.
         * @returns {Bookmarks} Chainable.
         */
        removeView: function (view) {
            if (view.active()) {
                this.applyView(this.defaultIndex);
            }

            if (!view.isNew) {
                this.removeStored('views.' + view.index);
            }

            view.destroy();

            return this;
        },

        /**
         * Saves data of a specified view.
         *
         * @param {View} view - View to be saved.
         * @returns {Bookmarks} Chainable.
         */
        saveView: function (view) {
            if (view.isNew || view.active()) {
                view.setData(this.current);

                this.hasChanges(false);
            }

            this.store('views.' + view.index, view.exportView());

            if (view.isNew) {
                view.isNew = false;

                view.active(true);
            }

            return this;
        },

        /**
         * Activates specified view and applies its' data.
         *
         * @param {(View|String)} view - View to be applied.
         * @returns {Bookmarks} Chainable.
         */
        applyView: function (view) {
            if (typeof view === 'string') {
                view = this.elems.findWhere({
                    index: view
                });
            }

            view.active(true);

            this.activeView(view);
            this.applyState('saved');

            return this;
        },

        /**
         * Applies specified views' data on a current data object.
         *
         * @param {String} state - Defines what state shultd be used: default or saved.
         * @param {String} [path] - Path to the property whose value
         *      will be inserted to a current data object.
         * @returns {Bookmarks} Chainable.
         */
        applyState: function (state, path) {
            var view,
                value;

            view = state === 'default' ?
                this.defaultView :
                this.activeView();

            path  = removeStateNs(path);
            value = view.getData(path);

            if (!_.isUndefined(value)) {
                path = path ? 'current.' + path : 'current';

                this.set(path, value);
            }

            return this;
        },

        /**
         * Saves current data state.
         *
         * @returns {Bookmarks} Chainable.
         */
        saveSate: function () {
            this.store('current');

            return this;
        },

        /**
         * Defines whether current state is different
         * from a saved state of an active view.
         *
         * @returns {Bookmarks} Chainable.
         */
        checkChanges: function () {
            var view = this.activeView(),
                diff = utils.compare(view.getData(), this.current);

            this.hasChanges(!diff.equal);

            return this;
        },

        /**
         * Resets current state to a saved state of an active view.
         *
         * @returns {Bookmarks} Chainable.
         */
        discardChanges: function () {
            this.applyState('saved');

            return this;
        },

        /**
         * Defines default data if it wasn't gathered previously.
         * Assumes that if theres is no views available,
         * then current data object is the default configuration.
         *
         * @private
         * @returns {Bookmarks} Chainbale.
         */
        _defaultPolyfill: function () {
            var view = this.defaultView,
                data = view.data;

            if (!_.size(data.items)) {
                data.items = utils.copy(this.current);

                this.store('views.' + view.index, view.exportView());
            }

            this.defaultDefined = true;

            this.checkChanges();

            return this;
        },

        /**
         * Listener of the activeIndex property.
         *
         * @param {String} index - Index of the active view.
         */
        onActiveIndexChange: function (index) {
            this.store('activeIndex')
                .applyView(index);
        },

        /**
         * Listens changes of a current data object.
         */
        onStateChange: function () {
            this.saveSate();

            if (this.activeView()) {
                this.checkChanges();
            }

            if (!this.defaultDefined) {
                this._defaultPolyfill();
            }
        },

        /**
         * Lsitens changes of the views' 'editing' property.
         */
        onEditingChange: function (view, isEditing) {
            if (!isEditing && view.isNew) {
                this.removeView(view);
            }
        }
    });
});
