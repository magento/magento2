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
            newChild: {
                label: 'New View',
                active: true,
                changed: true,
                editing: true
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

        initViews: function () {
            var views = this.views,
                active = _.findWhere(views, {index: this.activeIndex});

            if (!active) {
                this.activeIndex = this.defaultIndex;
            }

            _.each(views, this.createView, this);

            this.activeIndex = '';
        },

        createView: function (item) {
            var data = _.extend({}, this, item),
                child = utils.template(itemTmpl, data);

            _.extend(child, item);

            if (this.activeIndex === item.index) {
                child.active = true;
            }

            layout([child]);
        },

        createNewView: function () {
            var newChild = this.newChild;

            newChild.index = Date.now();
            newChild.data = this.current;

            this.createView(newChild);
        },

        removeView: function (view) {
            if (view.active()) {
                this.defaultView.active(true);
            }

            this.removeStored('views.' + view.index);

            view.destroy();
        },

        saveView: function (view) {
            var data = view.save();

            this.store('views.' + view.index, {
                index: view.index,
                label: view.label(),
                data: data
            });

            return this;
        },

        getSaved: function () {
            return this.activeView().getSaved();
        },

        getDefault: function () {
            return this.defaultView.getSaved();
        },

        defaultPolyfill: function () {
            var active = this.activeView();

            if (active && active.index === this.defaultIndex) {
                active.setData(this.current);
                this.saveView(active);
            }
        },

        onActiveChange: function (index) {
            var views = this.elems,
                active = views.findWhere({index: index}),
                data = active.getData();

            this.store('activeIndex')
                .activeView(active);

            if (_.size(data)) {
                this.set('current', data);
            }
        },

        onDataChange: function () {
            var active = this.activeView();

            if (active && active.editable) {
                active.setData(this.current);
            }
        }
    });
});
