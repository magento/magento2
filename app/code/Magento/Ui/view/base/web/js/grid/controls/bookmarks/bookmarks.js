/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'mageUtils',
    'Magento_Ui/js/lib/collapsible',
    'Magento_Ui/js/core/renderer/layout'
], function (_, utils, Collapsible, layout) {
    'use strict';

    var itemTmpl = {
        parent: '<%= $data.name %>',
        name: '<%= $data.index %>',
        label: '<%= $data.label %>',
        provider: '<%= $data.provider %>',
        component: 'Magento_Ui/js/grid/controls/bookmarks/view'
    };

    return Collapsible.extend({
        defaults: {
            template: 'ui/grid/controls/bookmarks/bookmarks',
            defaultIndex: 'default',
            activeIndex: 'default',
            hasChanges: false,
            initialSet: true,
            listens: {
                activeIndex: 'onActiveChange',
                current: 'onDataChange'
            },
            newViewTmpl: {
                label: 'New View',
                editing: true,
                isNew: true
            },
            views: {
                default: {
                    label: 'Default View',
                    index: 'default',
                    editable: false
                }
            }
        },

        initialize: function () {
            utils.limit(this, 'onDataChange', 600);

            this._super()
                .restore();

            this.initViews();

            setTimeout(this.defaultPolyfill.bind(this), 1000);

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
         * Called when another element was added to current component.
         *
         * @param {Object} elem - Instance of an element that was added.
         * @returns {Bookmarks} Chainable.
         */
        initElement: function (elem) {
            if (elem.index === this.defaultIndex) {
                this.defaultView = elem;
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

            this.activeIndex = '';

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
                child = utils.template(itemTmpl, data);

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
            var view = this.newViewTmpl;

            view.index = Date.now();
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
                this.defaultView.active(true);
            }

            view.destroy();

            this.removeStored('views.' + view.index);

            return this;
        },

        /**
         * Saves data of a specified view;
         * only if view has unsaved changes.
         *
         * @param {View} view - View to be saved.
         * @returns {Bookmarks} Chainable.
         */
        saveView: function (view) {
            var data;

            this.hasChanges(false);

            view.setData(this.current);

            if (view.isNew) {
                view.active(true);
            }

            data = view.save();

            this.store('views.' + view.index, {
                index: view.index,
                label: view.label(),
                data: data
            });

            return this;
        },

        /**
         * Saves current data state.
         *
         * @returns {Bookmarks} Chainable.
         */
        saveCurrent: function () {
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
                diff = utils.compare(view.getSaved(), this.current);

            this.hasChanges(!diff.equal);

            return this;
        },

        /**
         * Retrieves last saved data of a current view.
         *
         * @returns {Object}
         */
        getSaved: function () {
            return this.activeView().getSaved();
        },

        /**
         * Retrieves default data.
         *
         * @returns {Object}
         */
        getDefault: function () {
            return this.defaultView.getSaved();
        },

        defaultPolyfill: function () {
            var view = this.activeView();

            if (view && view.index === this.defaultIndex && !view.restored) {
                view.data.items = utils.copy(this.current);

                view.save();

                this.store('views.' + view.index, {
                    index: view.index,
                    label: view.label(),
                    restored: true,
                    data: this.current
                });
            }

            this.checkChanges();
        },

        /**
         * Listener of the activeIndex property.
         *
         * @param {String} index - Index of the active view.
         */
        onActiveChange: function (index) {
            var view = this.elems.findWhere({index: index});

            this.store('activeIndex')
                .activeView(view);

            if (!this.initialSet) {
                this.set('current', view.getData());
            }

            this.initialSet = false;
        },

        /**
         * Listens changes of current data object.
         */
        onDataChange: function () {
            this.saveCurrent();

            if (this.activeView()) {
                this.checkChanges();
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
