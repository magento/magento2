/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'underscore',
    'ko',
    'mageUtils',
    'jquery/jquery-storageapi'
], function ($, _, ko, utils) {
    'use strict';

    return {

        /**
         * Class name
         */
        name: 'IdsStorage',

        /**
         * Initializes class
         *
         * @return Chainable.
         */
        initialize: function () {
            if (!this.data) {
                this.data = ko.observable({});
            }

            this.initCustomerDataReloadListener()
                .initLocalStorage()
                .cachesDataFromLocalStorage()
                .initDataListener();

            return this;
        },

        /**
         * Gets data from local storage by current namespace
         *
         * @return {Object}.
         */
        getDataFromLocalStorage: function () {
            return this.localStorage.get();
        },

        /**
         * Caches data from local storage to local scope
         *
         * @return Chainable.
         */
        cachesDataFromLocalStorage: function () {
            this.data(this.getDataFromLocalStorage());

            return this;
        },

        /**
         * Initialize localStorage
         *
         * @return Chainable.
         */
        initLocalStorage: function () {
            this.localStorage = $.initNamespaceStorage(this.namespace).localStorage;

            return this;
        },

        /**
         * Initializes listener to "data" property
         */
        initDataListener: function () {
            this.data.subscribe(this.internalDataHandler.bind(this));
        },

        /**
         * Initialize listener to customer data reload
         *
         * @return Chainable.
         */
        initCustomerDataReloadListener: function () {
            $(document).on('customer-data-reload', function (event, sections) {
                if ((_.isEmpty(sections) || _.contains(sections, this.namespace)) && ~~this.allowToSendRequest) {
                    localStorage.removeItem(this.namespace);
                    this.data();
                }
            }.bind(this));

            return this;
        },

        /**
         * Initializes handler to "data" property update
         */
        internalDataHandler: function (data) {
            var localStorage = this.localStorage.get();

            if (!utils.compare(data, localStorage).equal) {
                this.localStorage.set(data);
            }
        },

        /**
         * Initializes handler to storage update
         */
        externalDataHandler: function (data) {
            data = data.items ? data.items : data;

            this.set(_.extend(utils.copy(this.data()), data));
        }
    };
});

