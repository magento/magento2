define([
    'underscore',
    'uiComponent'
], function (_, Component) {
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

    return Component.extend({
        defaults: {
            template: 'ui/grid/filters/filters',
            isVisible: false,

            listens: {
                active: 'extractPreviews'
            }
        },

        initObservable: function () {
            this._super()
                .observe('isVisible')
                .observe({
                    active: [],
                    previews: []
                });

            return this;
        },

        toggleVisible: function () {
            this.isVisible(!this.isVisible());
        },

        close: function () {
            this.isVisible(false);
        },

        apply: function () {
            this.extractActive();
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
