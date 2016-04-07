/**
 * Copyright © 2016 Magento. All rights reserved.
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
            braintree: null,
            selectedCardType: null,
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
            require([this.sdkUrl], function (braintree) {
                state(true);
                self.braintree = braintree;
                self.initBraintree();
                $('body').trigger('processStop');
            });
        },

        /**
         * Setup Braintree SDK
         */
        initBraintree: function () {
            var self = this;

            try {
                $('body').trigger('processStart');

                self.braintree.setup(self.clientToken, 'custom', {
                    id: self.selector,
                    hostedFields: self.getHostedFields(),

                    /**
                     * Triggered when sdk was loaded
                     */
                    onReady: function () {
                        $('body').trigger('processStop');
                    },

                    /**
                     * Callback for success response
                     * @param {Object} response
                     */
                    onPaymentMethodReceived: function (response) {
                        if (self.validateCardType()) {
                            self.setPaymentDetails(response.nonce);
                            self.placeOrder();
                        }
                    },

                    /**
                     * Error callback
                     * @param {Object} response
                     */
                    onError: function (response) {
                        self.error(response.message);
                    }
                });
            } catch (e) {
                $('body').trigger('processStop');
                self.error(e.message);
            }
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
                    },

                    /**
                     * Triggered when hosted field is changed
                     * @param {Object} event
                     */
                    onFieldEvent: function (event) {
                        return self.fieldEventHandler(event);
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
         * @param {Object} event
         * @returns {Boolean}
         */
        fieldEventHandler: function (event) {
            var self = this,
                $cardType = $('#' + self.container).find('.icon-type');

            if (event.isEmpty === false) {
                self.validateCardType();
            }

            if (event.type !== 'fieldStateChange') {

                return false;
            }

            // Handle a change in validation or card type
            if (event.target.fieldKey === 'number') {
                self.selectedCardType(null);
            }

            // remove previously set classes
            $cardType.attr('class', 'icon-type');

            if (event.card) {
                $cardType.addClass('icon-type-' + event.card.type);
                self.selectedCardType(
                    validator.getMageCardType(event.card.type, self.getCcAvailableTypes())
                );
            }
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
            this.$selector.validate().form();
            this.$selector.trigger('afterValidate.beforeSubmit');
            $('body').trigger('processStop');

            // validate parent form
            if (this.$selector.validate().errorList.length) {
                return false;
            }

            $('#' + this.container).find('[type="submit"]').trigger('click');
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
            var $input = $(this.getSelector('cc_number'));

            $input.removeClass('braintree-hosted-fields-invalid');

            if (!this.selectedCardType()) {
                $input.addClass('braintree-hosted-fields-invalid');

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
        }
    });
});
