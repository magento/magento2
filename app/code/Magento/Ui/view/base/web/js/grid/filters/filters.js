/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'Magento_Ui/js/lib/collapsible'
], function (_, Collapsible) {
    'use strict';

    function extractPreview(elem) {
        return {
            label: elem.label,
            preview: elem.getPreview(),
            elem: elem
        };
    }

    return Collapsible.extend({
        defaults: {
            template: 'ui/grid/filters/filters',
            listens: {
                active: 'extractPreviews'
            }
        },

        initObservable: function () {
            this._super()
                .observe({
                    active: [],
                    previews: []
                });

            return this;
        },

        apply: function () {
            this.extractActive();

            this.source.trigger('params.applyFilters');
            this.source.reload();
        },

        clear: function (filter) {
            filter ?
                filter.reset() :
                this.active.each('reset');

            this.apply();
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
            var active = this.elems.filter('hasData');

            this.active(active);

            return this;
        },

        extractPreviews: function (elems) {
            var previews = elems.map(extractPreview);

            this.previews(_.compact(previews));

            return this;
        },

        onApply: function () {
            this.close()
                .apply();
        }
    });
});
