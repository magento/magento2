/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'uiComponent',
    'mageUtils'
], function (Component, utils) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'ui/grid/controls/bookmarks/view',
            active: false,
            editable: true,
            editing: false,
            changed: false,
            data: {},
            saved: {},
            exports: {
                active: 'onActivate'
            },
            listens: {
                label: 'setLabel',
                '<%= parentName %>:activeIndex': 'onActiveChange'
            },
            modules: {
                states: '<%= parentName %>'
            }
        },

        initialize: function () {
            this._super();

            this.data = {
                label: this.label(),
                items: this.data
            };

            if (!this.changed()){
                this.save();
            }
        },

        initObservable: function () {
            this._super()
                .observe('active label editing changed');

            return this;
        },

        getSaved: function () {
            return utils.extend({}, this.saved.items);
        },

        getData: function () {
            return utils.extend({}, this.data.items);
        },

        setData: function (data) {
            this.data.items = utils.extend({}, data);

            this.checkChanges();

            return this;
        },

        setLabel: function (label) {       
            label = label.trim() || this.data.label;

            this.data.label = label;
            this.label(label);

            this.checkChanges();
        },

        closeEdit: function () {
            this.editing(false);

            return this;
        },

        openEdit: function () {
            this.editing(true);

            return this;
        },

        checkChanges: function () {
            var diff = utils.compare(this.saved, this.data),
                changed = !diff.equal;

            this.changed(changed);

            return changed;
        },

        save: function () {
            var data = this.getData(),
                saved = this.saved;

            saved.items = data;
            saved.label = this.label();

            this.changed(false);

            return data;
        },

        onActivate: function (active) {
            if (active) {
                this.states('set', 'activeIndex', this.index);
            }
        },

        onActiveChange: function (index) {
            if (index !== this.index){
                this.active(false);
            } 
        }
    });
});
