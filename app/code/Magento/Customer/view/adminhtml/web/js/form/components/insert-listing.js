/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/components/insert-listing'
], function (Insert) {
    'use strict';

    return Insert.extend({
        onAction: function (data) {
            this[data.action + 'Action'].call(this, data.data);
        },
        onMassAction: function (data) {
            this[data.action + 'Massaction'].call(this, data.data);
        },

        setDefaultBillingAction: function (data) {
            this.source.set('data.default_billing_address', data);
        },

        setDefaultShippingAction: function (data) {
            this.source.set('data.default_shipping_address', data);
        },

        deleteAction: function (data) {
            var defaultShippingId = parseFloat(this.source.get('data.default_shipping_address.entity_id')),
                defaultBillingId = parseFloat(this.source.get('data.default_billing_address.entity_id'));

            if (parseFloat(data[data['id_field_name']]) === defaultShippingId) {
                this.source.set('data.default_shipping_address', []);
            }
            if (parseFloat(data[data['id_field_name']]) === defaultBillingId) {
                this.source.set('data.default_billing_address', []);
            }
        },

        //TODO: release logic with massaction
        deleteMassaction: function (data) {
            debugger;
            // var defaultShippingId = parseFloat(this.source.get('data.default_shipping_address.entity_id')),
            //     defaultBillingId = parseFloat(this.source.get('data.default_billing_address.entity_id'));
            //
            // if (parseFloat(data[data['id_field_name']]) === defaultShippingId) {
            //     this.source.set('data.default_shipping_address', []);
            // }
            // if (parseFloat(data[data['id_field_name']]) === defaultBillingId) {
            //     this.source.set('data.default_billing_address', []);
            // }
        }
    });
});
