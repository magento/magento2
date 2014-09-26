/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
define([
    'underscore',
    'jquery',
    'Magento_Ui/js/lib/utils',
    'Magento_Ui/js/lib/class',
    'Magento_Ui/js/lib/events',
    './request_builder'
], function(_, $, utils, Class, EventsBus, requestBuilder) {
    'use strict';

    var defaults = {
        ajax: {
            dataType: 'json'
        }
    };

    return Class.extend({
        initialize: function(config) {
            $.extend(true, this.config = {}, defaults, config);
        },

        /**
         * Sends ajax request using params and config passed to it and calls this.config.onRead when done.
         * @param {Object} params - request body params
         * @param {Object} config - config to build url from
         */
        read: function(params, config) {
            config = this.createConfig(params, config);

            $.ajax(config)
                .done(this.onRead.bind(this));
        },

        /**
         * Creates config for ajax call.
         * @param {Object} params - request body params
         * @param {Object} config - config to build url from
         * @returns {Object} - merged config for ajax call
         */
        createConfig: function(params, config) {
            var baseConf;

            config = config || {};
            params = params || {};

            baseConf = {
                url: requestBuilder(this.config.root, params),
                data: params
            };

            return $.extend(true, baseConf, this.config.ajax, config);
        },

        /**
         * Callback of ajax call.
         * Parses results and triggers read event;
         * @param  {Object|*} result - Result of ajax call.
         */
        onRead: function(result){
            result = typeof result === 'string' ?
                JSON.parse(result) :
                result;

            this.trigger('read', result);
        },

        /**
         * Submits data using utils.submitAsForm
         * @param {Object} config - object containing ajax options
         */
        submit: function(config){
            var ajax = this.config.ajax,
                data = ajax.data || {};

            _.extend(config.data, data);

            utils.submitAsForm(config);
        }
    }, EventsBus);

});