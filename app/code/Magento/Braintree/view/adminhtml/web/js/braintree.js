/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define([
    'jquery',
    'uiComponent',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/lib/view/utils/dom-observer',
    'mage/translate',
    'Magento_Braintree/js/validator'
], function ($, Class, alert, domObserver, $t, validator) {
    'use strict';

    return Class.extend({

        defaults: {
            $selector: null,
            selector: 'edit_form',
            container: 'payment_form_braintree',
            active: false,
            scriptLoaded: false,
            braintreeClient: null,
            braintreeHostedFields: null,
            hostedFieldsInstance: null,
            selectedCardType: null,
            selectorsMapper: {
                'expirationMonth': 'expirationMonth',
                'expirationYear': 'expirationYear',
                'number': 'cc_number',
                'cvv': 'cc_cid'
            },
            imports: {
                onActiveChange: 'active'
            }
        },

        /**
         * Set list of observable attributes
         * @returns {exports.initObservable}
         */
        initObservable: function () {
            var self = this;

            validator.setConfig(this);

            self.$selector = $('#' + self.selector);
            this._super()
                .observe([
                    'active',
                    'scriptLoaded',
                    'selectedCardType'
                ]);

            // re-init payment method events
            self.$selector.off('changePaymentMethod.' + this.code)
                .on('changePaymentMethod.' + this.code, this.changePaymentMethod.bind(this));

            // listen block changes
            domObserver.get('#' + self.container, function () {
                if (self.scriptLoaded()) {
                    self.$selector.off('submit');
                    self.initBraintree();
                }
            });

            return this;
        },

        /**
         * Enable/disable current payment method
         * @param {Object} event
         * @param {String} method
         * @returns {exports.changePaymentMethod}
         */
        changePaymentMethod: function (event, method) {
            this.active(method === this.code);

            return this;
        },

        /**
         * Triggered when payment changed
         * @param {Boolean} isActive
         */
        onActiveChange: function (isActive) {
            if (!isActive) {
                this.$selector.off('submitOrder.braintree');

                return;
            }
            this.disableEventListeners();
            window.order.addExcludedPaymentMethod(this.code);

            if (!this.clientToken) {
                this.error($.mage.__('This payment is not available'));

                return;
            }

            this.enableEventListeners();

            if (!this.scriptLoaded()) {
                this.loadScript();
            }
        },

        /**
         * Load external Braintree SDK
         */
        loadScript: function () {
            var self = this,
                state = self.scriptLoaded;

            $('body').trigger('processStart');
            require([this.sdkUrl, this.hostedFieldsSdkUrl], function (client, hostedFields) {
                state(true);
                self.braintreeClient = client;
                self.braintreeHostedFields = hostedFields;
                self.initBraintree();
                $('body').trigger('processStop');
            });
        },

        /**
         * Retrieves client token and setup Braintree SDK
         */
        initBraintree: function () {
            var self = this;

            try {
                $('body').trigger('processStart');

                $.getJSON(self.clientTokenUrl).done(function (response) {
                    self.clientToken = response.clientToken;
                    self._initBraintree();
                }).fail(function (response) {
                    var failed = JSON.parse(response.responseText);

                    $('body').trigger('processStop');
                    self.error(failed.message);
                });
            } catch (e) {
                $('body').trigger('processStop');
                self.error(e.message);
            }
        },

        /**
         * Setup Braintree SDK
         */
        _initBraintree: function () {
            var self = this;

            self.disableEventListeners();

            self.braintreeClient.create({
                authorization: self.clientToken
            })
                .then(function (clientInstance) {
                    return self.braintreeHostedFields.create({
                        client: clientInstance,
                        fields: self.getHostedFields()
                    });
                })
                .then(function (hostedFieldsInstance) {
                    self.hostedFieldsInstance = hostedFieldsInstance;
                    self.enableEventListeners();
                    self.fieldEventHandler(hostedFieldsInstance);
                    $('body').trigger('processStop');
                })
                .catch(function () {
                    self.error($t('Braintree can\'t be initialized.'));
                });
        },

        /**
         * Get hosted fields configuration
         * @returns {Object}
         */
        getHostedFields: function () {
            var self = this,
                fields = {
                    number: {
                        selector: self.getSelector('cc_number')
                    },
                    expirationMonth: {
                        selector: self.getSelector('cc_exp_month'),
                        placeholder: $t('MM')
                    },
                    expirationYear: {
                        selector: self.getSelector('cc_exp_year'),
                        placeholder: $t('YY')
                    }
                };

            if (self.useCvv) {
                fields.cvv = {
                    selector: self.getSelector('cc_cid')
                };
            }

            return fields;
        },

        /**
         * Function to handle hosted fields events
         * @param {Object} hostedFieldsInstance
         */
        fieldEventHandler: function (hostedFieldsInstance) {
            var self = this,
                $cardType = $('#' + self.container).find('.icon-type');

            hostedFieldsInstance.on('empty', function (event) {
                if (event.emittedBy === 'number') {
                    $cardType.attr('class', 'icon-type');
                    self.selectedCardType(null);
                }

            });

            hostedFieldsInstance.on('validityChange', function (event) {
                var field = event.fields[event.emittedBy],
                    fieldKey = event.emittedBy;

                if (fieldKey === 'number') {
                    $cardType.addClass('icon-type-' + event.cards[0].type);
                }

                if (fieldKey in self.selectorsMapper && field.isValid === false) {
                    self.addInvalidClass(self.selectorsMapper[fieldKey]);
                }
            });

            hostedFieldsInstance.on('blur', function (event) {
                if (event.emittedBy === 'number') {
                    self.validateCardType();
                }
            });

            hostedFieldsInstance.on('cardTypeChange', function (event) {
                if (event.cards.length !== 1) {
                    return;
                }

                $cardType.addClass('icon-type-' + event.cards[0].type);
                self.selectedCardType(
                    validator.getMageCardType(event.cards[0].type, self.getCcAvailableTypes())
                );
            });
        },

        /**
         * Show alert message
         * @param {String} message
         */
        error: function (message) {
            alert({
                content: message
            });
        },

        /**
         * Enable form event listeners
         */
        enableEventListeners: function () {
            this.$selector.on('submitOrder.braintree', this.submitOrder.bind(this));
        },

        /**
         * Disable form event listeners
         */
        disableEventListeners: function () {
            this.$selector.off('submitOrder');
            this.$selector.off('submit');
        },

        /**
         * Store payment details
         * @param {String} nonce
         */
        setPaymentDetails: function (nonce) {
            var $container = $('#' + this.container);

            $container.find('[name="payment[payment_method_nonce]"]').val(nonce);
        },

        /**
         * Trigger order submit
         */
        submitOrder: function () {
            var self = this;

            self.$selector.validate().form();
            self.$selector.trigger('afterValidate.beforeSubmit');

            // validate parent form
            if (self.$selector.validate().errorList.length) {
                $('body').trigger('processStop');

                return false;
            }

            if (!self.validateCardType()) {
                $('body').trigger('processStop');
                self.error($t('Some payment input fields are invalid.'));

                return false;
            }

            self.hostedFieldsInstance.tokenize(function (err, payload) {
                if (err) {
                    $('body').trigger('processStop');
                    self.error($t('Some payment input fields are invalid.'));

                    return false;
                }

                self.setPaymentDetails(payload.nonce);
                $('#' + self.container).find('[type="submit"]').trigger('click');
            });
        },

        /**
         * Place order
         */
        placeOrder: function () {
            $('#' + this.selector).trigger('realOrder');
        },

        /**
         * Get list of currently available card types
         * @returns {Array}
         */
        getCcAvailableTypes: function () {
            var types = [],
                $options = $(this.getSelector('cc_type')).find('option');

            $.map($options, function (option) {
                types.push($(option).val());
            });

            return types;
        },

        /**
         * Validate current entered card type
         * @returns {Boolean}
         */
        validateCardType: function () {
            this.removeInvalidClass('cc_number');

            if (!this.selectedCardType()) {
                this.addInvalidClass('cc_number');

                return false;
            }
            $(this.getSelector('cc_type')).val(this.selectedCardType());

            return true;
        },

        /**
         * Get jQuery selector
         * @param {String} field
         * @returns {String}
         */
        getSelector: function (field) {
            return '#' + this.code + '_' + field;
        },

        /**
         * Add invalid class to field.
         *
         * @param {String} field
         * @returns void
         * @private
         */
        addInvalidClass: function (field) {
            $(this.getSelector(field)).addClass('braintree-hosted-fields-invalid');
        },

        /**
         * Remove invalid class from field.
         *
         * @param {String} field
         * @returns void
         * @private
         */
        removeInvalidClass: function (field) {
            $(this.getSelector(field)).removeClass('braintree-hosted-fields-invalid');
        }
    });
});
