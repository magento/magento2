/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Ui/js/modal/modal-component',
    'uiRegistry',
    'underscore'
], function ($, Modal, registry, _) {
    'use strict';

    return Modal.extend({
        defaults: {
            modules: {
                emailProvider: '${ $.emailProvider }'
            }
        },

        /**
         * Initializes component.
         *
         * @returns {Object} Chainable.
         */
        initialize: function () {
            this._super();

            // console.log(this.name);

            return this;
        },

        /**
         * Open modal.
         */
        openModal: function (data) {
            debugger;
            if (data == null){
                // add
                this.setTitle(this.options.title);
                this._super();
            } else {
                // edit
                var addressId = data.uuid;
                var address = {
                    'city': 'city',
                    'company': 'company',
                    'country_id': 'country_id',
                    'customer_id': 'customer_id',
                    'created_at': 'created_at',
                    'default_billing': 'default_billing',
                    'default_shipping': 'default_shipping',
                    'entity_id': 'entity_id',
                    'fax': 'fax',
                    'firstname': 'firstname',
                    'increment_id': 'increment_id',
                    'is_active': 'is_active',
                    'lastname': 'lastname',
                    'middlename': 'middlename',
                    'parent_id': 'parent_id',
                    'postcode': 'postcode',
                    'prefix': 'prefix',
                    'region': 'region',
                    'region_id': 'region_id',
                    'street': [0, 1],
                    'suffix': 'suffix',
                    'telephone': 'telephone',
                    'updated_at': 'updated_at',
                    'vat_id': 'vat_id',
                    'vat_is_valid': 'vat_is_valid',
                    'vat_request_date': 'vat_request_date',
                    'vat_request_id': 'vat_request_id',
                    'vat_request_success': 'vat_request_success'
                };

                var source = registry.get('customer_form.customer_form_data_source');
                var modal = 'data.address_listing.address_form.update_customer_address_form_modal';

                _.each(address, function(value, key) {
                    if (key === 'default_billing' || key === 'default_shipping') {
                        var defaultValue = source.get('data.address.' + addressId + '.' + value);
                        // convert boolean to integer
                        var val = +defaultValue;
                        source.set(modal + '.' + key, val.toString());
                    } else if (key === 'street' && _.isArray(address[key])) {
                        _.each(address[key], function(element, index) {
                            source.set(modal + '.' + key + '[' + index + ']', source.get('data.address.' + addressId + '.' + key + '.' + element));
                        });
                    } else {
                        source.set(modal + '.' + key, source.get('data.address.' + addressId + '.' + value));
                    }
                });

                this.setTitle(this.options.title);
                this._super();
            }
        },

        /**
         * Close popup modal.
         * @public
         */
        closeModal: function () {
            debugger;
            this._clearData();
            this._super();
        },

        /**
         * Clear modal data.
         *
         * @private
         */
        _clearData: function () {
            debugger;
            var address = {
                'city': '',
                'company': '',
                'country_id': '',
                'default_billing': "0",
                'default_shipping': "0",
                'entity_id': '',
                'firstname': '',
                'is_active': '',
                'lastname': '',
                'middlename': '',
                'postcode': '',
                'prefix': '',
                'region': '',
                'region_id': '',
                'street[0]': '',
                'street[1]': '',
                'suffix': '',
                'telephone': '',
                'vat_id': ''
            };

            var source = registry.get('customer_form.customer_form_data_source');
            var modal = 'data.address_listing.address_form.update_customer_address_form_modal';

            _.each(address, function(value, key) {
                source.set(modal + '.' + key, value);
            });
        },

        /**
         * Open modal.
         */
        save: function () {
            debugger;

            var address = {
                'city': 'city',
                'company': 'company',
                'country_id': 'country_id',
                'customer_id': 'customer_id',
                'created_at': 'created_at',
                'default_billing': 'default_billing',
                'default_shipping': 'default_shipping',
                'entity_id': 'entity_id',
                'fax': 'fax',
                'firstname': 'firstname',
                'increment_id': 'increment_id',
                'is_active': 'is_active',
                'lastname': 'lastname',
                'middlename': 'middlename',
                'parent_id': 'parent_id',
                'postcode': 'postcode',
                'prefix': 'prefix',
                'region': 'region',
                'region_id': 'region_id',
                'street': ['street[0]', 'street[1]'],
                'suffix': 'region_id',
                'telephone': 'telephone',
                'updated_at': 'updated_at',
                'vat_id': 'vat_id',
                'vat_is_valid': 'vat_is_valid',
                'vat_request_date': 'vat_request_date',
                'vat_request_id': 'vat_request_id',
                'vat_request_success': 'vat_request_success'
            };

            var source = registry.get('customer_form.customer_form_data_source');
            var formData = source.get('data.address_listing.address_form.update_customer_address_form_modal');
            var entityId = formData.entity_id;

            $.ajax({
                url: this.options.url,
                showLoader: true,
                data: formData,
                type: "POST",
                dataType: 'json',
                success: function(data) {
                    console.log('SUCCESS: ', data);
                },
                error: function(data) {
                    console.log('ERROR: ', data);
                }
            });
        }
    });
});
