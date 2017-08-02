<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model;

use Magento\Framework\DataObject;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Payment interface
 * @api
 * @since 2.0.0
 */
interface MethodInterface
{
    /**
     * Retrieve payment method code
     *
     * @return string
     *
     * @since 2.0.0
     */
    public function getCode();

    /**
     * Retrieve block type for method form generation
     *
     * @return string
     *
     * @deprecated 2.0.0
     * @since 2.0.0
     */
    public function getFormBlockType();

    /**
     * Retrieve payment method title
     *
     * @return string
     *
     * @since 2.0.0
     */
    public function getTitle();

    /**
     * Store id setter
     * @param int $storeId
     * @return void
     * @since 2.0.0
     */
    public function setStore($storeId);

    /**
     * Store id getter
     * @return int
     * @since 2.0.0
     */
    public function getStore();

    /**
     * Check order availability
     *
     * @return bool
     *
     * @since 2.0.0
     */
    public function canOrder();

    /**
     * Check authorize availability
     *
     * @return bool
     *
     * @since 2.0.0
     */
    public function canAuthorize();

    /**
     * Check capture availability
     *
     * @return bool
     *
     * @since 2.0.0
     */
    public function canCapture();

    /**
     * Check partial capture availability
     *
     * @return bool
     *
     * @since 2.0.0
     */
    public function canCapturePartial();

    /**
     * Check whether capture can be performed once and no further capture possible
     *
     * @return bool
     *
     * @since 2.0.0
     */
    public function canCaptureOnce();

    /**
     * Check refund availability
     *
     * @return bool
     *
     * @since 2.0.0
     */
    public function canRefund();

    /**
     * Check partial refund availability for invoice
     *
     * @return bool
     *
     * @since 2.0.0
     */
    public function canRefundPartialPerInvoice();

    /**
     * Check void availability
     * @return bool
     *
     * @since 2.0.0
     */
    public function canVoid();

    /**
     * Using internal pages for input payment data
     * Can be used in admin
     *
     * @return bool
     * @since 2.0.0
     */
    public function canUseInternal();

    /**
     * Can be used in regular checkout
     *
     * @return bool
     * @since 2.0.0
     */
    public function canUseCheckout();

    /**
     * Can be edit order (renew order)
     *
     * @return bool
     *
     * @since 2.0.0
     */
    public function canEdit();

    /**
     * Check fetch transaction info availability
     *
     * @return bool
     *
     * @since 2.0.0
     */
    public function canFetchTransactionInfo();

    /**
     * Fetch transaction info
     *
     * @param InfoInterface $payment
     * @param string $transactionId
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @since 2.0.0
     */
    public function fetchTransactionInfo(InfoInterface $payment, $transactionId);

    /**
     * Retrieve payment system relation flag
     *
     * @return bool
     *
     * @since 2.0.0
     */
    public function isGateway();

    /**
     * Retrieve payment method online/offline flag
     *
     * @return bool
     *
     * @since 2.0.0
     */
    public function isOffline();

    /**
     * Flag if we need to run payment initialize while order place
     *
     * @return bool
     *
     * @since 2.0.0
     */
    public function isInitializeNeeded();

    /**
     * To check billing country is allowed for the payment method
     *
     * @param string $country
     * @return bool
     * @since 2.0.0
     */
    public function canUseForCountry($country);

    /**
     * Check method for processing with base currency
     *
     * @param string $currencyCode
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function canUseForCurrency($currencyCode);

    /**
     * Retrieve block type for display method information
     *
     * @return string
     *
     * @deprecated 2.0.0
     * @since 2.0.0
     */
    public function getInfoBlockType();

    /**
     * Retrieve payment information model object
     *
     * @return InfoInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @deprecated 2.0.0
     * @since 2.0.0
     */
    public function getInfoInstance();

    /**
     * Retrieve payment information model object
     *
     * @param InfoInterface $info
     * @return void
     *
     * @deprecated 2.0.0
     * @since 2.0.0
     */
    public function setInfoInstance(InfoInterface $info);

    /**
     * Validate payment method information object
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @since 2.0.0
     */
    public function validate();

    /**
     * Order payment abstract method
     *
     * @param InfoInterface $payment
     * @param float $amount
     * @return $this
     *
     * @since 2.0.0
     */
    public function order(\Magento\Payment\Model\InfoInterface $payment, $amount);

    /**
     * Authorize payment abstract method
     *
     * @param InfoInterface $payment
     * @param float $amount
     * @return $this
     *
     * @since 2.0.0
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount);

    /**
     * Capture payment abstract method
     *
     * @param InfoInterface $payment
     * @param float $amount
     * @return $this
     *
     * @since 2.0.0
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount);

    /**
     * Refund specified amount for payment
     *
     * @param InfoInterface $payment
     * @param float $amount
     * @return $this
     *
     * @since 2.0.0
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount);

    /**
     * Cancel payment abstract method
     *
     * @param InfoInterface $payment
     * @return $this
     *
     * @since 2.0.0
     */
    public function cancel(\Magento\Payment\Model\InfoInterface $payment);

    /**
     * Void payment abstract method
     *
     * @param InfoInterface $payment
     * @return $this
     *
     * @since 2.0.0
     */
    public function void(\Magento\Payment\Model\InfoInterface $payment);

    /**
     * Whether this method can accept or deny payment
     * @return bool
     *
     * @since 2.0.0
     */
    public function canReviewPayment();

    /**
     * Attempt to accept a payment that us under review
     *
     * @param InfoInterface $payment
     * @return false
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @since 2.0.0
     */
    public function acceptPayment(InfoInterface $payment);

    /**
     * Attempt to deny a payment that us under review
     *
     * @param InfoInterface $payment
     * @return false
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @since 2.0.0
     */
    public function denyPayment(InfoInterface $payment);

    /**
     * Retrieve information from payment configuration
     *
     * @param string $field
     * @param int|string|null|\Magento\Store\Model\Store $storeId
     *
     * @return mixed
     * @since 2.0.0
     */
    public function getConfigData($field, $storeId = null);

    /**
     * Assign data to info model instance
     *
     * @param DataObject $data
     * @return $this
     *
     * @since 2.0.0
     */
    public function assignData(DataObject $data);

    /**
     * Check whether payment method can be used
     *
     * @param CartInterface|null $quote
     * @return bool
     *
     * @since 2.0.0
     */
    public function isAvailable(CartInterface $quote = null);

    /**
     * Is active
     *
     * @param int|null $storeId
     * @return bool
     *
     * @since 2.0.0
     */
    public function isActive($storeId = null);

    /**
     * Method that will be executed instead of authorize or capture
     * if flag isInitializeNeeded set to true
     *
     * @param string $paymentAction
     * @param object $stateObject
     *
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @since 2.0.0
     */
    public function initialize($paymentAction, $stateObject);

    /**
     * Get config payment action url
     * Used to universalize payment actions when processing payment place
     *
     * @return string
     *
     * @since 2.0.0
     */
    public function getConfigPaymentAction();
}
