# Magento_Paypal module

This module implements integration with the PayPal payment system.

It enables the following payment methods:

* PayPal Express Checkout
* PayPal Payments Standard
* PayPal Payments Pro
* PayPal Credit
* PayFlow Payment Gateway

## MFTF sandbox retry

`AdminCancelPaypalPayflowProOrderTest` preserves its dedicated test card and retries Place Order at most once
after a recognized Payflow Pro decline. `PayflowProRetry` handles both the inline decline banner and the
decline modal. Dismissing the modal reloads Checkout, so the helper waits for the old form to detach, reselects
Payflow Pro, and restores the same card details before resubmitting. Missing success text without a confirmed
decline does not trigger a retry. The existing success, cancellation, authorization, and void assertions remain
mandatory; a second decline still fails the test.
