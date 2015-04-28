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
            listens: {
                activeIndex: 'onActiveChange',
                current: 'onDataChange'
            },
            newViewTmpl: {
                label: 'New View',
                active: true,
                editing: true,
                restored: false
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
            this._super()
                .restore();

            this.initViews();

            setTimeout(this.defaultPolyfill.bind(this), 1000);

            return this;
        },

        initObservable: function () {
            this._super()
                .observe('activeView');

            return this;
        },

        initElement: function (elem) {
            if (elem.index === this.defaultIndex) {
                this.defaultView = elem;
            }

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

            this.removeStored('views.' + view.index);

            view.destroy();

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

            if (view.changed()) {
                data = view.save();

                this.store('views.' + view.index, {
                    index: view.index,
                    label: view.label(),
                    data: data
                });
            }

            return this;
        },

        /**
         * Retrives last saved data of current view.
         *
         * @returns {Object}
         */
        getSaved: function () {
            return this.activeView().getSaved();
        },

        /**
         * Retrives default data.
         *
         * @returns {Object}
         */
        getDefault: function () {
            return this.defaultView.getSaved();
        },

        defaultPolyfill: function () {
            var active = this.activeView();

            if (active && active.index === this.defaultIndex) {
                active.data.items = utils.copy(this.current);
                active.changed(true);
                this.saveView(active);
            }
        },

        /**
         * Listener of the activeIndex property.
         *
         * @param {String} index - Index of the active view.
         */
        onActiveChange: function (index) {
            var active = this.elems.findWhere({index: index}),
                data = active.getData();

            this.store('activeIndex')
                .activeView(active);

            if (_.size(data)) {
                this.set('current', data);
            }
        },

        /**
         * Listens changes of current data object.
         */
        onDataChange: function () {
            var active = this.activeView();

            if (active) {
                active.setData(this.current);
            }
        }
    });
});
