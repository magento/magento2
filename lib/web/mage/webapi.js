/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/* jshint define: true */
(function (factory) {
    if (typeof define === 'function' && define.amd) {
        define([
            "jquery",
            "mage/mage"
        ], factory);
    } else {
        factory(jQuery);
    }
}(function ($) {
    "use strict";

    /**
     * Webapi object constructor
     *
     * @param {string}           baseUrl  Base URL
     * @param {Object|undefined} ajaxArgs Arguments for AJAX API call
     * @see http://api.jquery.com/jQuery.ajax/
     * @returns {{method: Object, call: Function}}
     */
    $.mage.Webapi = function(baseUrl, ajaxArgs) {
        /**
         * Resource-related parameters. Further extended by other domain objects like Product, etc.
         *
         * @const
         * @type {{uri: {base: string}}}
         */
        this.resource = {
            uri: {
                base: '', // Initialized below
                api: '/webapi/rest'
            }
        };

        /**
         *
         *
         * @const
         * @type {{create: string, update: string, get: string, delete: string}}
         */
        this.method = {
            'create': 'POST',
            'update': 'PUT',
            'get': 'GET',
            'delete': 'DELETE'
        };

        var validMethods = [this.method.create, this.method.update, this.method.get, this.method['delete']];

        // Check whether passed options comply with what we allow
        if (ajaxArgs && typeof ajaxArgs !== 'object') {
            throw 'ajaxArgs expected to be object';
        }

        if (!(baseUrl && typeof baseUrl === 'string')) {
            throw 'String baseUrl parameter required';
        }

        // Ensure that baseUrl doesn't have ending forward slash
        this.resource.uri.base = baseUrl[baseUrl.length - 1] === '/' ? baseUrl.substr(0, baseUrl.length - 1) : baseUrl;

        /**
         * Makes an API request
         *
         * @param {string}           resourceUri Resource URI request to be sent to, e.g. '/v1/products/'
         * @param {string}           method      Request method, e.g. GET, POST, etc.
         * @param {*}                data        Payload to be sent to the server
         * @param {string|undefined} version     Optional: API version, e.g. 'v1' (if not specified
         *                                       using URI)
         * @returns {jqXHR}
         */
        this.call = function(resourceUri, method, data, version) {
            /**
             * Helper function to validate request method
             *
             * @param {string} method
             * @returns {string}
             */
            function validateMethod(method) {
                if (validMethods.indexOf(method) === -1) {
                    throw 'Method name is not valid: ' + method;
                }

                return method;
            }

            var that = this;

            /**
             * Helper function to construct URIs
             *
             * @param {string}           resourceUri Resource URI request to be sent to, e.g. '/v1/products/'
             * @param {string}           method      Request method, e.g. GET, POST, etc.
             * @param {*}                data        Payload to be sent to the server
             * @param {string|undefined} version     Optional: API version, e.g. 'v1'
             *
             * @returns {string}
             */
            function getUrl(resourceUri, method, data, version) {
                function ensureForwardSlash(str) {
                    return str[0] === '/' ? str : '/' + str;
                }

                if (version) {
                    resourceUri = version + ensureForwardSlash(resourceUri);
                }

                if (data && [that.method.get, that.method['delete']].indexOf(method) !== -1) {
                    // Append data for GET and DELETE request methods as it's simple ID (usually int)
                    resourceUri += data;
                }

                return that.resource.uri.base + that.resource.uri.api + ensureForwardSlash(resourceUri);
            }

            var ajaxOptions = {
                url: getUrl(resourceUri, method, data, version),
                type: validateMethod(method),
                data: data,
                dataType: 'text',
                timeout: 5000,
                processData: false, // Otherwise jQuery will try to append 'data' to query URL
                cache: false, // Disable browser cache for GET requests

                beforeSend: function (request) {
                    request.setRequestHeader('Accept', 'application/json');
                }
            };

            $.extend(ajaxOptions, ajaxArgs);

            return $.ajax(ajaxOptions);
        };

        return this;
    };

    $.mage.Webapi.prototype.constructor = $.mage.Webapi;

    /**
     * Syntax sugar over call(). Example usage: $.mage.webapi.Product('v1').get({...})
     *
     * @param {string} version API version (e.g. 'v1')
     * @returns {{get: Function, create: Function}}
     */
    $.mage.Webapi.prototype.Product = function(version) {
        if (!(typeof version === 'string' && /v\d+/i.test(version))) {
            throw 'Incorrect version format: ' + version;
        }

        version = version.toLowerCase();
        var that = this; // Points to $.mage.webapi
        that.resource.uri.products = '/products/';

        return {
            /**
             * Retrieves information about specific product
             *
             * @param idObj Object which helps to identify the product, e.g. {id: 1}
             * @returns {jqXHR}
             */
            get: function(idObj) {
                if (!idObj.hasOwnProperty('id')) {
                    throw '"id" property expected in the object';
                }

                return that.call(that.resource.uri.products, that.method.get, idObj.id, version);
            },

            /**
             * Create a new product
             *
             * @param productData Example product data:
             *                    productData = {
             *                        "type_id": "simple",
             *                        "attribute_set_id": 4,
             *                        "sku": "1234567890",
             *                        "weight": 1,
             *                        "status": 1,
             *                        "visibility": 4,
             *                        "name": "Simple Product",
             *                        "description": "Simple Description",
             *                        "short_description": "Simple Short Description",
             *                        "price": 99.95,
             *                        "tax_class_id": 0
             *                    };
             * @returns {jqXHR}
             */
            create: function(productData) {
                return that.call(that.resource.uri.products, that.method.create, productData, version);
            }
        };
    };
}));
