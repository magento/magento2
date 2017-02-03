/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'uiCollection',
    'Magento_Ui/js/lib/spinner',
    './adapter'
], function (_, Collection, loader, adapter) {
    'use strict';

    function collectData(selector) {
        var items = document.querySelectorAll(selector),
            result = {};

        items = Array.prototype.slice.call(items);

        items.forEach(function (item) {
            result[item.name] = item.value;
        });

        return result;
    }

    return Collection.extend({
        initialize: function () {
            this._super()
                .initAdapter()
                .hideLoader();

            return this;
        },

        initAdapter: function () {
            adapter.on({
                'reset': this.reset.bind(this),
                'save': this.save.bind(this, true),
                'saveAndContinue': this.save.bind(this, false)
            });

            return this;
        },

        initConfig: function () {
            this._super();

            this.selector = '[data-form-part=' + this.namespace + ']';

            return this;
        },

        hideLoader: function () {
            loader.get(this.name).hide();

            return this;
        },

        save: function (redirect) {
            this.validate();

            if (!this.source.get('params.invalid')) {
                this.submit(redirect);
            }
        },

        /**
         * Submits form
         */
        submit: function (redirect) {
            var additional = collectData(this.selector),
                source = this.source;

            _.each(additional, function (value, name) {
                source.set('data.' + name, value);
            });

            source.save({
                redirect: redirect,
                attributes: {
                    id: this.namespace
                }
            });
        },

        /**
         * Validates each element and returns true, if all elements are valid.
         */
        validate: function () {
            this.source.set('params.invalid', false);
            this.source.trigger('data.validate');
        },

        reset: function () {
            this.source.trigger('data.reset');
        }
    });
});
