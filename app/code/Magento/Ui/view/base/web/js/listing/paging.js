/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    '../lib/ko/scope',
    '../lib/component'
], function(_, Scope, Component) {
    'use strict';

    var defaults = {
        sizes: [5, 10, 20, 30, 50, 100, 200],
        params: {
            dir: 'paging',
            items: ['pageSize', 'current']
        }
    };

    var Paging = Scope.extend({

        /**
         * Extends instance with defaults and config, initializes observable properties.
         * Updates storage with current state of instance. 
         * @param  {Object} config
         */
        initialize: function(config) {
            _.extend(this, defaults, config);

            this.initObservable(config)
                .initProvider()
                .pushParams();
        },

        /**
         * Initializes observable properties of instance.
         * @return {Object} - reference to instance
         */
        initObservable: function(config) {
            var data = this.provider.data.get();

            this.observe({
                'pages':        data.pages || 1,
                'totalCount':   data.totalCount,
                'current':      this.current,
                'pageSize':     this.pageSize,
                'selected':     0
            });

            return this;
        },

        /**
         * Subscribes on provider's events
         * @returns {Paging} Chainable.
         */
        initProvider: function(){
            var provider    = this.provider,
                params      = provider.params,
                meta        = provider.meta;

            _.bindAll(this, 'drop', 'onRefresh', 'pullParams', 'updateSelected');

            provider.on('refresh', this.onRefresh);
            
            meta.on('update:selected', this.updateSelected);

            params.on({
                'update:filter':    this.drop,
                'update:sorting':   this.drop,
                'update:paging':    this.pullParams
            });

            return this;
        },

        /**
         * Increments current observable prop by val and call reload method 
         * @param {String} val
         */
        go: function(val) {
            var current = this.current;

            current(current() + val);

            this.reload();
        },

        /**
         * Calls go method with 1 as agrument
         */
        next: function() {
            this.go(1);
        },

        /**
         * Calls go method with -1 as agrument
         */
        prev: function() {
            this.go(-1);
        },

        /**
         * Compares current and pages observables and returns boolean result
         * @return {Boolean} is current equal to pages property
         */
        isLast: function() {
            return this.current() === this.pages();
        },

        /**
         * Compares current observable to 1
         * @return {Boolean} is current page first
         */
        isFirst: function() {
            return this.current() === 1;
        },

        /**
         * Returns closest existing page number to page argument
         * @param  {Number} page
         * @return {Number} closest existing page number
         */
        getInRange: function(page) {
            return Math.min(Math.max(1, page), this.pages());
        },
        
        /**
         * Sets current observable to 1 and calls pushParams method
         */
        drop: function() {
            this.current(1);

            this.pushParams();
        },

        /**
         * Updates number of selected items.
         * @param {Number} count - New number of selected items.
         */
        updateSelected: function(count){
            this.selected(count);
        },

        /**
         * Is being called on provider's refresh event.
         * Updates totalCount and pages observables
         */
        onRefresh: function() {
            var data = this.provider.data.get();

            this.totalCount(data.totalCount);
            this.pages(data.pages || 1);
        },

        /**
         * Is being triggered on user interaction with page size select.
         * Resets current page to first if needed.
         */
        onSizeChange: function() {
            var size = this.pageSize();

            if (size * this.current() > this.totalCount()) {
                this.current(1);
            }

            this.reload();
        },

        /**
         * Validates page change according to user's input.
         * Sets current observable to result of validation.
         * Calls reload method then.
         */
        onPageChange: function() {
            var current,
                valid;

            current = +this.current();
            valid = !isNaN(current) ? this.getInRange(current) : 1;

            this.current(valid);

            this.reload();
        }
    });

    return Component({
        constr: Paging
    });
});