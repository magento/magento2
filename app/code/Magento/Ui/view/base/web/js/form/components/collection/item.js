/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'mageUtils',
    '../tab'
], function (_, utils, Tab) {
    'use strict';

    var previewConfig = {
        separator: ' ',
        prefix: ''
    };

    /**
     * Parses incoming data and returnes result merged with default preview config
     *
     * @param  {Object|String} data
     * @return {Object}
     */
    function parsePreview(data) {
        if (typeof data == 'string') {
            data = {
                items: data
            };
        }

        data.items = utils.stringToArray(data.items);

        return _.defaults(data, previewConfig);
    }

    return Tab.extend({
        defaults: {
            label: '',
            uniqueNs: 'activeCollectionItem',
            previewTpl: 'ui/form/components/collection/preview'
        },

        /**
         * Extends instance with default config, calls initializes of parent class
         */
        initialize: function () {
            _.bindAll(this, 'buildPreview', 'hasPreview');

            return this._super();
        },

        /**
         * Calls initProperties of parent class, initializes properties
         * of instance.
         *
         * @return {Object} - reference to instance
         */
        initConfig: function () {
            this._super();

            this.displayed = [];

            return this;
        },

        /**
         * Calls initObservable of parent class, initializes observable
         * properties of instance.
         *
         * @return {Object} - reference to instance
         */
        initObservable: function () {
            this._super()
                .observe({
                    noPreview: true,
                    indexed: {}
                });

            return this;
        },

        /**
         * Is being called when child element has been initialized,
         *     calls initElement of parent class, binds to element's update event,
         *     calls insertToArea and insertToIndexed methods passing element to it
         *
         * @param  {Object} elem
         */
        initElement: function (elem) {
            this._super()
                .insertToIndexed(elem);

            return this;
        },

        /**
         * Adds element to observable indexed object of instance
         *
         * @param  {Object} elem
         * @return {Object} - reference to instance
         */
        insertToIndexed: function (elem) {
            var indexed = this.indexed();

            indexed[elem.index] = elem;

            this.indexed(indexed);

            return this;
        },

        /**
         * Destroys current instance along with all of its' children.
         * Overrides base method to clear data when this method is called.
         */
        destroy: function () {
            this._super();
            this._clearData();
        },

        /**
         * Clears all data associated with component.
         * @private
         *
         * @returns {Item} Chainable.
         */
        _clearData: function () {
            this.source.remove(this.dataScope);

            return this;
        },

        /**
         * Formats incoming previews array via parsePreview function.
         *
         * @param  {Array} previews
         * @return {Array} - formatted previews
         */
        formatPreviews: function (previews) {
            return previews.map(parsePreview);
        },

        /**
         * Creates string view of previews
         *
         * @param  {Object} data
         * @return {Strict} - formatted preview string
         */
        buildPreview: function (data) {
            var preview = this.getPreview(data.items),
                prefix = data.prefix;

            return prefix + preview.join(data.separator);
        },

        /**
         * Defines if instance has preview for incoming data
         *
         * @param  {Object}  data
         * @return {Boolean}
         */
        hasPreview: function (data) {
            return !!this.getPreview(data.items).length;
        },

        /**
         * Creates an array of previews for elements specified in incoming
         * items array, calls updatePreview afterwards.
         *
         * @param  {Array} items - An array of element's indexes.
         * @returns {Array} An array of previews.
         */
        getPreview: function (items) {
            var elems = this.indexed(),
                displayed = this.displayed,
                preview;

            items = items.map(function (index) {
                var elem = elems[index];

                preview = elem && elem.visible() ? elem.getPreview() : '';

                preview = Array.isArray(preview) ?
                    _.compact(preview).join(', ') :
                    preview;

                utils.toggle(displayed, index, !!preview);

                return preview;
            });

            this.noPreview(!displayed.length);

            return _.compact(items);
        }
    });
});
