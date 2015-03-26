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
            preview: elem.delegate('getPreview'),
            elem: elem
        };
    }

    function hasData(elem) {
        return elem.delegate('hasData');
    }

    function resetValue(elem) {
        return elem.delegate('reset');
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
            this.source.reload();
        },

        reset: function (filter) {
            filter ?
                resetValue(filter) :
                this.active.each(resetValue);

            this.apply();
        },

        extractActive: function () {
            var active = this.elems.filter(hasData);

            this.active(active);

            return this;
        },

        extractPreviews: function (elems) {
            var previews = elems.map(extractPreview);

            this.previews(_.compact(previews));

            return this;
        }
    });
});
