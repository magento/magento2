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
            return value === '';
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
                active: 'extractPreviews'
            },
            imports: {
                onStateChange: '<%= states.provider %>:<%= states.namespace %>'
            },
            modules: {
                source: '<%= provider %>',
                statesProvider: '<%= states.provider %>'
            }
        },

        initialize: function () {
            _.bindAll(this, 'exportStates', 'exportParams');

            this._super()
                .apply();

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

        apply: function () {
            this.extractActive();

            this.applied = removeEmpty(this.filters);

            this.statesProvider(this.exportStates);
            this.source(this.exportParams);
        },

        clear: function (filter) {
            filter ?
                filter.clear() :
                this.active.each('clear');

            this.apply();

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
        },

        exportStates: function (states) {
            states.set(this.states.namespace, this.applied);
        },

        exportParams: function (source) {
            source.set('params.filters', this.applied);
        },

        onStateChange: function () {
            var data = this.statesProvider().get(this.states.namespace);

            this.set('filters', utils.copy(data))
                .apply();
        }
    });
});
