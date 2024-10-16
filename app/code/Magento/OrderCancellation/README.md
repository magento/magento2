# Magento_OrderCancellation module

This module allows to cancel an order and specify the order cancellation reason. Only orders in `RECEIVED`, `PENDING` or `PROCESSING` statuses can be cancelled and if the customer has paid for the order a refund is processed.

This functionality is enabled / disabled by a feature flag that is set at storeView level.

After the cancellation, the customer receive an email confirming it and this cancellation is reflected in the customer's order history.
