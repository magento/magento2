/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

(function (factory) {
    if (typeof define === 'function' && define.amd) {
        define([
            'jquery',
            'mage/backend/validation',
            'prototype'
        ], factory);
    } else {
        factory(jQuery);
    }
}(function (jQuery) {

    window.directPost = Class.create();
    directPost.prototype = {
        initialize: function (methodCode, iframeId, controller, orderSaveUrl, cgiUrl, nativeAction) {
            var prepare = function (event, method) {
                if (method === 'authorizenet_directpost') {
                    this.preparePayment();
                } else {
                    jQuery('#edit_form')
                        .off('submitOrder.authorizenet');
                }
            };

            this.iframeId = iframeId;
            this.controller = controller;
            this.orderSaveUrl = orderSaveUrl;
            this.nativeAction = nativeAction;
            this.cgiUrl = cgiUrl;
            this.code = methodCode;
            this.inputs = ['cc_type', 'cc_number', 'expiration', 'expiration_yr', 'cc_cid'];
            this.headers = [];
            this.isValid = true;
            this.paymentRequestSent = false;
            this.orderIncrementId = false;
            this.successUrl = false;
            this.hasError = false;
            this.tmpForm = false;

            this.onLoadIframe = this.loadIframe.bindAsEventListener(this);
            this.onLoadOrderIframe = this.loadOrderIframe.bindAsEventListener(this);
            this.onSubmitAdminOrder = this.submitAdminOrder.bindAsEventListener(this);

            jQuery('#edit_form').on('changePaymentMethod', prepare.bind(this));

            jQuery('#edit_form').trigger(
                'changePaymentMethod',
                [
                    jQuery('#edit_form').find(':radio[name="payment[method]"]:checked').val()
                ]
            );
        },

        validate: function () {
            this.isValid = true;
            this.inputs.each(function (elemIndex) {
                if ($(this.code + '_' + elemIndex)) {
                    if (!jQuery.validator.validateElement($(this.code + '_' + elemIndex))) {
                        this.isValid = false;
                    }
                }
            }, this);

            return this.isValid;
        },

        changeInputOptions: function (param, value) {
            this.inputs.each(function (elemIndex) {
                if ($(this.code + '_' + elemIndex)) {
                    $(this.code + '_' + elemIndex).writeAttribute(param, value);
                }
            }, this);
        },

        preparePayment: function () {
            this.changeInputOptions('autocomplete', 'off');
            jQuery('#edit_form')
                .off('submitOrder')
                .on('submitOrder.authorizenet', this.submitAdminOrder.bind(this));

            if ($(this.iframeId)) {
                // Temporary solution will be removed after refactoring Authorize.Net (sales) functionality
                jQuery('.scalable.save:not(disabled)').removeAttr('onclick');
                jQuery(document).off('click.directPost');
                jQuery(document).on(
                    'click.directPost',
                    '.scalable.save:not(disabled)',
                    jQuery.proxy(this.onSubmitAdminOrder, this)
                );
                $('order-' + this.iframeId).observe('load', this.onLoadOrderIframe);
                $(this.iframeId).observe('load', this.onLoadIframe);
            }
        },

        loadIframe: function () {
            if (this.paymentRequestSent) {
                if (!this.orderRequestSent) {
                    this.paymentRequestSent = false;

                    if (!this.hasError) {
                        this.returnQuote();
                    } else {
                        this.changeInputOptions('disabled', false);
                        jQuery('body').trigger('processStop');
                        enableElements('save');
                    }
                }

                if (this.tmpForm) {
                    document.body.removeChild(this.tmpForm);
                }
            }
        },

        loadOrderIframe: function () {
            if (this.orderRequestSent) {
                $(this.iframeId).hide();
                var data = $('order-' + this.iframeId).contentWindow.document.body.getElementsByTagName('pre')[0].innerHTML;

                this.saveAdminOrderSuccess(data);
                this.orderRequestSent = false;
            }
        },

        showError: function (msg) {
            this.hasError = true;

            if (this.controller == 'onepage') {
                $(this.iframeId).hide();
                this.resetLoadWaiting();
            }
            alert(msg);
        },

        returnQuote: function () {
            var url = this.orderSaveUrl.replace('place', 'returnQuote');

            new Ajax.Request(url, {
                onSuccess: function (transport) {
                    try {
                        response = transport.responseText.evalJSON(true);
                    } catch (e) {
                        response = {};
                    }

                    if (response.error_message) {
                        alert(response.error_message);
                    }
                    $(this.iframeId).show();
                    this.changeInputOptions('disabled', false);
                    jQuery('body').trigger('processStop');
                    enableElements('save');
                }.bind(this)
            });
        },

        setLoadWaiting: function () {
            this.headers.each(function (header) {
                header.removeClassName('allow');
            });
            checkout.setLoadWaiting('review');
        },

        resetLoadWaiting: function () {
            this.headers.each(function (header) {
                header.addClassName('allow');
            });
            checkout.setLoadWaiting(false);
        },

        submitAdminOrder: function () {
            // Temporary solution will be removed after refactoring Authorize.Net (sales) functionality
            var editForm = jQuery('#edit_form');

            if (editForm.valid()) {
                // Temporary solution will be removed after refactoring Authorize.Net (sales) functionality
                paymentMethodEl = editForm.find(':radio[name="payment[method]"]:checked');
                this.hasError = false;

                if (paymentMethodEl.val() == this.code) {
                    jQuery('body').trigger('processStart');
                    setLoaderPosition();
                    this.changeInputOptions('disabled', 'disabled');
                    this.paymentRequestSent = true;
                    this.orderRequestSent = true;
                    // Temporary solutions will be removed after refactoring Authorize.Net (sales) functionality
                    editForm.attr('action', this.orderSaveUrl);
                    editForm.attr('target',
                            jQuery('#order-' + this.iframeId).attr('name'));
                    editForm.append(this.createHiddenElement('controller', this.controller));
                    disableElements('save');
                    // Temporary solutions will be removed after refactoring Authorize.Net (sales) functionality
                    order._realSubmit();
                } else {
                    editForm.attr('action', this.nativeAction);
                    editForm.attr('target', '_top');
                    disableElements('save');
                    // Temporary solutions will be removed after refactoring Authorize.Net (sales) functionality
                    order._realSubmit();
                }
            }
        },

        recollectQuote: function () {
            var area = ['sidebar', 'items', 'shipping_method', 'billing_method', 'totals', 'giftmessage'];

            area = order.prepareArea(area);
            var url = order.loadBaseUrl + 'block/' + area;
            var info = $('order-items_grid').select('input', 'select', 'textarea');
            var data = {};

            for (var i = 0; i < info.length; i++) {
                if (!info[i].disabled && (info[i].type != 'checkbox' || info[i].checked)) {
                    data[info[i].name] = info[i].getValue();
                }
            }
            data.reset_shipping = true;
            data.update_items = true;

            if ($('coupons:code') && $F('coupons:code')) {
                data['order[coupon][code]'] = $F('coupons:code');
            }
            data.json = true;
            new Ajax.Request(url, {
                parameters: data,
                loaderArea: 'html-body',
                onSuccess: function (transport) {
                    jQuery('#edit_form').submit();
                }
            });

        },

        saveAdminOrderSuccess: function (data) {
            try {
                response = data.evalJSON(true);
            } catch (e) {
                response = {};
            }

            if (response.directpost) {
                this.orderIncrementId = response.directpost.fields.x_invoice_num;
                var paymentData = {};

                for (var key in response.directpost.fields) {
                    paymentData[key] = response.directpost.fields[key];
                }
                var preparedData = this.preparePaymentRequest(paymentData);

                this.sendPaymentRequest(preparedData);
            } else {
                if (response.redirect) {
                    window.location = response.redirect;
                }

                if (response.error_messages) {
                    var msg = response.error_messages;

                    if (typeof msg == 'object') {
                        msg = msg.join('\n');
                    }

                    if (msg) {
                        alert(msg);
                    }
                }
            }
        },

        preparePaymentRequest: function (data) {
            if ($(this.code + '_cc_cid')) {
                data.x_card_code = $(this.code + '_cc_cid').value;
            }
            var year = $(this.code + '_expiration_yr').value;

            if (year.length > 2) {
                year = year.substring(2);
            }
            var month = parseInt($(this.code + '_expiration').value, 10);

            if (month < 10) {
                month = '0' + month;
            }

            data.x_exp_date = month + '/' + year;
            data.x_card_num = $(this.code + '_cc_number').value;

            return data;
        },

        sendPaymentRequest: function (preparedData) {
            this.recreateIframe();
            this.tmpForm = document.createElement('form');
            this.tmpForm.style.display = 'none';
            this.tmpForm.enctype = 'application/x-www-form-urlencoded';
            this.tmpForm.method = 'POST';
            document.body.appendChild(this.tmpForm);
            this.tmpForm.action = this.cgiUrl;
            this.tmpForm.target = $(this.iframeId).readAttribute('name');
            this.tmpForm.setAttribute('target', $(this.iframeId).readAttribute('name'));

            for (var param in preparedData) {
                this.tmpForm.appendChild(this.createHiddenElement(param, preparedData[param]));
            }

            this.paymentRequestSent = true;
            this.tmpForm.submit();
        },

        createHiddenElement: function (name, value) {
            var field;

            if (isIE) {
                field = document.createElement('input');
                field.setAttribute('type', 'hidden');
                field.setAttribute('name', name);
                field.setAttribute('value', value);
            } else {
                field = document.createElement('input');
                field.type = 'hidden';
                field.name = name;
                field.value = value;
            }

            return field;
        },

        recreateIframe: function () {
            if ($(this.iframeId)) {
                var nextElement = $(this.iframeId).next();
                var src = $(this.iframeId).readAttribute('src');
                var name = $(this.iframeId).readAttribute('name');

                $(this.iframeId).stopObserving();
                $(this.iframeId).remove();
                var iframe = '<iframe id="' + this.iframeId +
                    '" allowtransparency="true" frameborder="0"  name="' + name +
                    '" style="display:none;width:100%;background-color:transparent" src="' + src + '" />';

                Element.insert(nextElement, {
                    'before': iframe
                });
                $(this.iframeId).observe('load', this.onLoadIframe);
            }
        }
    };
}));
