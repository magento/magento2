/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    /**
     * ProductListToolbarForm Widget - this widget is setting cookie and submitting form according to toolbar controls
     */
    $.widget('mage.productListToolbarForm', {

        options: {
            modeControl: '[data-role="mode-switcher"]',
            directionControl: '[data-role="direction-switcher"]',
            orderControl: '[data-role="sorter"]',
            limitControl: '[data-role="limiter"]',
            mode: 'product_list_mode',
            direction: 'product_list_dir',
            order: 'product_list_order',
            limit: 'product_list_limit',
            modeDefault: 'grid',
            directionDefault: 'asc',
            orderDefault: 'position',
            limitDefault: '9',
            url: '',
            formKey: '',
            post: false
        },

        /** @inheritdoc */
        _create: function () {
            this._bind($(this.options.modeControl), this.options.mode, this.options.modeDefault);
            this._bind($(this.options.directionControl), this.options.direction, this.options.directionDefault);
            this._bind($(this.options.orderControl), this.options.order, this.options.orderDefault);
            this._bind($(this.options.limitControl), this.options.limit, this.options.limitDefault);
        },

        /** @inheritdoc */
        _bind: function (element, paramName, defaultValue) {
            if (element.is('select')) {
                element.on('change', {
                    paramName: paramName,
                    'default': defaultValue
                }, $.proxy(this._processSelect, this));
            } else {
                element.on('click', {
                    paramName: paramName,
                    'default': defaultValue
                }, $.proxy(this._processLink, this));
            }
        },

        /**
         * @param {jQuery.Event} event
         * @private
         */
        _processLink: function (event) {
            event.preventDefault();
            this.changeUrl(
                event.data.paramName,
                $(event.currentTarget).data('value'),
                event.data.default
            );
        },

        /**
         * @param {jQuery.Event} event
         * @private
         */
        _processSelect: function (event) {
            this.changeUrl(
                event.data.paramName,
                event.currentTarget.options[event.currentTarget.selectedIndex].value,
                event.data.default
            );
        },

        /**
         * @param {String} paramName
         * @param {*} paramValue
         * @param {*} defaultValue
         */
        changeUrl: function (paramName, paramValue, defaultValue) {
            var decode = window.decodeURIComponent,
                urlPaths = this.options.url.split('?'),
                baseUrl = urlPaths[0],
                urlParams = urlPaths[1] ? urlPaths[1].split('&') : [],
                paramData = {},
                parameters, i, form, params, key, input, formKey;

            for (i = 0; i < urlParams.length; i++) {
                parameters = urlParams[i].split('=');
                paramData[decode(parameters[0])] = parameters[1] !== undefined ?
                    decode(parameters[1].replace(/\+/g, '%20')) :
                    '';
            }
            paramData[paramName] = paramValue;

            if (this.options.post) {
                form = document.createElement('form');
                params = [this.options.mode, this.options.direction, this.options.order, this.options.limit];

                for (key in paramData) {
                    if (params.indexOf(key) !== -1) { //eslint-disable-line max-depth
                        input = document.createElement('input');
                        input.name = key;
                        input.value = paramData[key];
                        form.appendChild(input);
                        delete paramData[key];
                    }
                }
                formKey = document.createElement('input');
                formKey.name = 'form_key';
                formKey.value = this.options.formKey;
                form.appendChild(formKey);

                paramData = $.param(paramData);
                baseUrl += paramData.length ? '?' + paramData : '';

                form.action = baseUrl;
                form.method = 'POST';
                document.body.appendChild(form);
                form.submit();
            } else {
                if (paramValue == defaultValue) { //eslint-disable-line eqeqeq
                    delete paramData[paramName];
                }
                paramData = $.param(paramData);
                location.href = baseUrl + (paramData.length ? '?' + paramData : '');
            }
        }
    });

    return $.mage.productListToolbarForm;
});
