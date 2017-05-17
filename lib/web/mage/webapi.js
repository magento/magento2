/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

(function (factory) {
    'use strict';

    if (typeof define === 'function' && define.amd) {
        define([
            'jquery',
            'mage/mage'
        ], factory);
    } else {
        factory(jQuery);
    }
}(function ($) {
    'use strict';

    /**
     * Webapi object constructor
     *
     * @param {String} baseUrl - Base URL
     * @param {Object|undefined} ajaxArgs - Arguments for AJAX API call
     * @see http://api.jquery.com/jQuery.ajax/
     * @returns {{method: Object, call: Function}}
     */
    $.mage.Webapi = function (baseUrl, ajaxArgs) {
        var validMethods;

        /**
         * Resource-related parameters. Further extended by other domain objects like Product, etc.
         *
         * @const
         * @type {Object}
         */
        this.resource = {
            uri: {
                base: '', // Initialized below
                api: '/webapi/rest'
            }
        };

        /**
         * @const
         * @type {Object}
         */
        this.method = {
            'create': 'POST',
            'update': 'PUT',
            'get': 'GET',
            'delete': 'DELETE'
        };

        validMethods = [this.method.create, this.method.update, this.method.get, this.method['delete']];

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
         * @param {String} resourceUri - Resource URI request to be sent to, e.g. '/v1/products/'
         * @param {String} method - Request method, e.g. GET, POST, etc.
         * @param {*} data - Payload to be sent to the server
         * @param {String|undefined} version - Optional: API version, e.g. 'v1' (if not specifieds using URI)
         * @returns {jqXHR}
         */
        this.call = function (resourceUri, method, data, version) {
            var that = this,
                ajaxOptions;

            /**
             * Helper function to validate request method
             *
             * @param {String} methodName
             * @returns {String}
             */
            function validateMethod(methodName) {
                if (validMethods.indexOf(methodName) === -1) {
                    throw 'Method name is not valid: ' + methodName;
                }

                return methodName;
            }

            /**
             * Helper function to construct URIs
             *
             * @param {String} resUri - Resource URI request to be sent to, e.g. '/v1/products/'
             * @param {String} m - Request method, e.g. GET, POST, etc.
             * @param {*} payload - Payload to be sent to the server
             * @param {String|undefined} v - Optional: API version, e.g. 'v1'
             * @returns {String}
             */
            function getUrl(resUri, m, payload, v) {
                /**
                 * @param {String} str
                 * @return {String}
                 */
                function ensureForwardSlash(str) {
                    return str[0] === '/' ? str : '/' + str;
                }

                if (v) {
                    resUri = v + ensureForwardSlash(resUri);
                }

                if (payload && [that.method.get, that.method['delete']].indexOf(m) !== -1) {
                    // Append data for GET and DELETE request methods as it's simple ID (usually int)
                    resUri += payload;
                }

                return that.resource.uri.base + that.resource.uri.api + ensureForwardSlash(resUri);
            }

            ajaxOptions = {
                url: getUrl(resourceUri, method, data, version),
                type: validateMethod(method),
                data: data,
                dataType: 'text',
                timeout: 5000,
                processData: false, // Otherwise jQuery will try to append 'data' to query URL
                cache: false, // Disable browser cache for GET requests

                /**
                 * @param {Object} request
                 */
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
     * @param {String} version - API version (e.g. 'v1')
     * @returns {{get: Function, create: Function}}
     */
    $.mage.Webapi.prototype.Product = function (version) {
        var that = this; // Points to $.mage.webapi

        if (!(typeof version === 'string' && /v\d+/i.test(version))) {
            throw 'Incorrect version format: ' + version;
        }

        version = version.toLowerCase();
        that.resource.uri.products = '/products/';

        return {
            /**
             * Retrieves information about specific product
             *
             * @param {Object} idObj - Object which helps to identify the product, e.g. {id: 1}
             * @returns {jqXHR}
             */
            get: function (idObj) {
                if (!idObj.hasOwnProperty('id')) {
                    throw '"id" property expected in the object';
                }

                return that.call(that.resource.uri.products, that.method.get, idObj.id, version);
            },

            /**
             * Create a new product
             *
             * @param {Object} productData - Example product data:
             *  productData = {
             *      "type_id": "simple",
             *      "attribute_set_id": 4,
             *      "sku": "1234567890",
             *      "weight": 1,
             *      "status": 1,
             *      "visibility": 4,
             *      "name": "Simple Product",
             *      "description": "Simple Description",
             *      "short_description": "Simple Short Description",
             *      "price": 99.95,
             *      "tax_class_id": 0
             *  };
             * @returns {jqXHR}
             */
            create: function (productData) {
                return that.call(that.resource.uri.products, that.method.create, productData, version);
            }
        };
    };
}));
