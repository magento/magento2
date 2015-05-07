/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'mageUtils',
    'Magento_Ui/js/lib/collapsible'
], function (_, utils, Collapsible) {
    'use strict';

    function extractPreview(elem) {
        return {
            label: elem.label,
            preview: elem.getPreview(),
            elem: elem
        };
    }

    function removeEmpty(data) {
        data = utils.flatten(data);

        data = _.omit(data, function (value, key) {
            return value === '' || typeof value === 'undefined';
        });

        return utils.unflatten(data);
    }

    return Collapsible.extend({
        defaults: {
            template: 'ui/grid/filters/filters',
            applied: {},
            states: {
                namespace: 'current.filters'
            },
            listens: {
                active: 'extractPreviews',
                applied: 'cancel extractActive'
            },
            links: {
                applied: '<%= states.provider %>:<%= states.namespace %>'
            },
            exports: {
                applied: '<%= provider %>:params.filters'
            },
            modules: {
                source: '<%= provider %>',
                statesProvider: '<%= states.provider %>'
            }
        },

        initialize: function () {
            this._super()
                .cancel()
                .extractActive();

            return;
        },

        initObservable: function () {
            this._super()
                .observe({
                    active: [],
                    previews: []
                });

            return this;
        },

        initElement: function () {
            this._super()
                .extractActive();

            return this;
        },

        clear: function (filter) {
            filter ?
                filter.clear() :
                this.active.each('clear');

            this.apply();

            return this;
        },

        apply: function () {
            this.set('applied', removeEmpty(this.filters));

            return this;
        },

        cancel: function () {
            this.set('filters', utils.copy(this.applied));

            return this;
        },

        isOpened: function () {
            return this.opened() && this.hasVisible();
        },

        isFilterVisible: function (filter) {
            return filter.visible() || this.isFilterActive(filter);
        },

        isFilterActive: function (filter) {
            return this.active.contains(filter);
        },

        hasVisible: function () {
            var visible = this.elems.filter(this.isFilterVisible, this);

            return !!visible.length;
        },

        extractActive: function () {
            this.active(this.elems.filter('hasData'));

            return this;
        },

        extractPreviews: function (elems) {
            var previews = elems.map(extractPreview);

            this.previews(_.compact(previews));

            return this;
        }
    });
});
