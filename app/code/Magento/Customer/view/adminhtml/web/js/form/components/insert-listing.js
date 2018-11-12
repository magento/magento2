/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/components/insert-listing',
    'underscore'
], function (Insert, _) {
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
            this._delete([parseFloat(data[data['id_field_name']])]);
        },

        deleteMassaction: function (data) {
            var ids = _.map(data, function (val) {
                return parseFloat(val);
            });

            this._delete(ids);
        },

        _delete: function (ids) {
            var defaultShippingId = parseFloat(this.source.get('data.default_shipping_address.entity_id')),
                defaultBillingId = parseFloat(this.source.get('data.default_billing_address.entity_id'));

            if (ids.indexOf(defaultShippingId) !== -1) {
                this.source.set('data.default_shipping_address', []);
            }

            if (ids.indexOf(defaultBillingId) !== -1) {
                this.source.set('data.default_billing_address', []);
            }
        }
    });
});
