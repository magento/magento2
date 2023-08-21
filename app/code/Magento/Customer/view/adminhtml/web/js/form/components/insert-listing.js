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

        /**
         * On action call
         *
         * @param {Object} data - customer address and actions
         */
        onAction: function (data) {
            this[data.action + 'Action'].call(this, data.data);
        },

        /**
         * On mass action call
         *
         * @param {Object} data - customer address
         */
        onMassAction: function (data) {
            this[data.action + 'Massaction'].call(this, data.data);
        },

        /**
         * Set default billing address
         *
         * @param {Object} data - customer address
         */
        setDefaultBillingAction: function (data) {
            this.source.set('data.default_billing_address', data);
        },

        /**
         * Set default shipping address
         *
         * @param {Object} data - customer address
         */
        setDefaultShippingAction: function (data) {
            this.source.set('data.default_shipping_address', data);
        },

        /**
         * Delete customer address
         *
         * @param {Object} data - customer address
         */
        deleteAction: function (data) {
            this._delete([parseFloat(data[data['id_field_name']])]);
        },

        /**
         * Mass action delete
         *
         * @param {Object} data - customer address
         */
        deleteMassaction: function (data) {
            var ids = data.selected || this.selections().selected();

            ids = _.map(ids, function (val) {
                return parseFloat(val);
            });

            this._delete(ids);
        },

        /**
         * Delete customer address and selections by provided ids.
         *
         * @param {Array} ids
         */
        _delete: function (ids) {
            var defaultShippingId = parseFloat(this.source.get('data.default_shipping_address.entity_id')),
                defaultBillingId = parseFloat(this.source.get('data.default_billing_address.entity_id'));

            if (ids.indexOf(defaultShippingId) !== -1) {
                this.source.set('data.default_shipping_address', []);
            }

            if (ids.indexOf(defaultBillingId) !== -1) {
                this.source.set('data.default_billing_address', []);
            }

            _.each(ids, function (id) {
                this.selections().deselect(id.toString(), false);
            }, this);
        }
    });
});
