/************************************************************************
 *
 * Copyright 2023 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ************************************************************************
 */
define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'Magento_Customer/js/customer-data'
],function ($, modal, customerData) {
    'use strict';

    return function (config, element) {
        let order_id = config.order_id,
            options = {
            type: 'popup',
            responsive: true,
            title: 'Cancel Order',
            buttons: [{
                text: $.mage.__('Close'),
                class: 'action-secondary action-dismiss close-modal-button',

                /** @inheritdoc */
                click: function () {
                    this.closeModal();
                }
            }, {
                text: $.mage.__('Confirm'),
                class: 'action-primary action-accept cancel-order-button',

                /** @inheritdoc */
                click: function () {
                    let thisModal = this,
                        reason = $('#cancel-order-reason-' + order_id).find(':selected').text(),
                        mutation = `
mutation cancelOrder($order_id: ID!, $reason: String!) {
  cancelOrder(input: {order_id: $order_id, reason: $reason}) {
    error
    order {
      status
    }
  }
}`;

                    $.ajax({
                        showLoader: true,
                        type: 'POST',
                        url: `${config.url}graphql`,
                        contentType: 'application/json',
                        data: JSON.stringify({
                            query: mutation,
                            variables: {
                                'order_id': config.order_id,
                                'reason': reason
                            }
                        }),
                        complete: function (response) {
                            let type = 'success',
                                message;

                            if (response.responseJSON.data.cancelOrder.error !== null) {
                                message = $.mage.__(response.responseJSON.data.cancelOrder.error);
                                type = 'error';
                            } else {
                                message = $.mage.__(response.responseJSON.data.cancelOrder.order.status);
                                location.reload();
                            }

                            setTimeout(function () {
                                customerData.set('messages', {
                                    messages: [{
                                        text: message,
                                        type: type
                                    }]
                                });
                            }, 1000);
                        }
                    }).always(function () {
                        thisModal.closeModal(true);
                    });
                }
            }]
        };

        $(element).on('click', function () {
            $('#cancel-order-modal-' + order_id).modal('openModal');
        });

        modal(options, $('#cancel-order-modal-' + order_id));
    };
});
