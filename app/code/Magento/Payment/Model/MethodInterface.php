<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model;

use Magento\Framework\DataObject;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Payment interface
 */
interface MethodInterface
{
    /**
     * Retrieve payment method code
     *
     * @return string
     * @api
     */
    public function getCode();

    /**
     * Retrieve block type for method form generation
     *
     * @return string
     * @api
     * @deprecated
     */
    public function getFormBlockType();

    /**
     * Retrieve payment method title
     *
     * @return string
     * @api
     */
    public function getTitle();

    /**
     * Store id setter
     * @param int $storeId
     * @return void
     */
    public function setStore($storeId);

    /**
     * Store id getter
     * @return int
     */
    public function getStore();

    /**
     * Check order availability
     *
     * @return bool
     * @api
     */
    public function canOrder();

    /**
     * Check authorize availability
     *
     * @return bool
     * @api
     */
    public function canAuthorize();

    /**
     * Check capture availability
     *
     * @return bool
     * @api
     */
    public function canCapture();

    /**
     * Check partial capture availability
     *
     * @return bool
     * @api
     */
    public function canCapturePartial();

    /**
     * Check whether capture can be performed once and no further capture possible
     *
     * @return bool
     * @api
     */
    public function canCaptureOnce();

    /**
     * Check refund availability
     *
     * @return bool
     * @api
     */
    public function canRefund();

    /**
     * Check partial refund availability for invoice
     *
     * @return bool
     * @api
     */
    public function canRefundPartialPerInvoice();

    /**
     * Check void availability
     * @return bool
     * @api
     */
    public function canVoid();

    /**
     * Using internal pages for input payment data
     * Can be used in admin
     *
     * @return bool
     */
    public function canUseInternal();

    /**
     * Can be used in regular checkout
     *
     * @return bool
     */
    public function canUseCheckout();

    /**
     * Can be edit order (renew order)
     *
     * @return bool
     * @api
     */
    public function canEdit();

    /**
     * Check fetch transaction info availability
     *
     * @return bool
     * @api
     */
    public function canFetchTransactionInfo();

    /**
     * Fetch transaction info
     *
     * @param InfoInterface $payment
     * @param string $transactionId
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @api
     */
    public function fetchTransactionInfo(InfoInterface $payment, $transactionId);

    /**
     * Retrieve payment system relation flag
     *
     * @return bool
     * @api
     */
    public function isGateway();

    /**
     * Retrieve payment method online/offline flag
     *
     * @return bool
     * @api
     */
    public function isOffline();

    /**
     * Flag if we need to run payment initialize while order place
     *
     * @return bool
     * @api
     */
    public function isInitializeNeeded();

    /**
     * To check billing country is allowed for the payment method
     *
     * @param string $country
     * @return bool
     */
    public function canUseForCountry($country);

    /**
     * Check method for processing with base currency
     *
     * @param string $currencyCode
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function canUseForCurrency($currencyCode);

    /**
     * Retrieve block type for display method information
     *
     * @return string
     * @api
     * @deprecated
     */
    public function getInfoBlockType();

    /**
     * Retrieve payment information model object
     *
     * @return InfoInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @api
     * @deprecated
     */
    public function getInfoInstance();

    /**
     * Retrieve payment information model object
     *
     * @param InfoInterface $info
     * @return void
     * @api
     * @deprecated
     */
    public function setInfoInstance(InfoInterface $info);

    /**
     * Validate payment method information object
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @api
     */
    public function validate();

    /**
     * Order payment abstract method
     *
     * @param InfoInterface $payment
     * @param float $amount
     * @return $this
     * @api
     */
    public function order(\Magento\Payment\Model\InfoInterface $payment, $amount);

    /**
     * Authorize payment abstract method
     *
     * @param InfoInterface $payment
     * @param float $amount
     * @return $this
     * @api
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount);

    /**
     * Capture payment abstract method
     *
     * @param InfoInterface $payment
     * @param float $amount
     * @return $this
     * @api
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount);

    /**
     * Refund specified amount for payment
     *
     * @param InfoInterface $payment
     * @param float $amount
     * @return $this
     * @api
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount);

    /**
     * Cancel payment abstract method
     *
     * @param InfoInterface $payment
     * @return $this
     * @api
     */
    public function cancel(\Magento\Payment\Model\InfoInterface $payment);

    /**
     * Void payment abstract method
     *
     * @param InfoInterface $payment
     * @return $this
     * @api
     */
    public function void(\Magento\Payment\Model\InfoInterface $payment);

    /**
     * Whether this method can accept or deny payment
     * @return bool
     * @api
     */
    public function canReviewPayment();

    /**
     * Attempt to accept a payment that us under review
     *
     * @param InfoInterface $payment
     * @return false
     * @throws \Magento\Framework\Exception\LocalizedException
     * @api
     */
    public function acceptPayment(InfoInterface $payment);

    /**
     * Attempt to deny a payment that us under review
     *
     * @param InfoInterface $payment
     * @return false
     * @throws \Magento\Framework\Exception\LocalizedException
     * @api
     */
    public function denyPayment(InfoInterface $payment);

    /**
     * Retrieve information from payment configuration
     *
     * @param string $field
     * @param int|string|null|\Magento\Store\Model\Store $storeId
     *
     * @return mixed
     */
    public function getConfigData($field, $storeId = null);

    /**
     * Assign data to info model instance
     *
     * @param DataObject $data
     * @return $this
     * @api
     */
    public function assignData(DataObject $data);

    /**
     * Check whether payment method can be used
     *
     * @param CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(CartInterface $quote = null);

    /**
     * Is active
     *
     * @param int|null $storeId
     * @return bool
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
     * @api
     * @deprecated
     */
    public function initialize($paymentAction, $stateObject);

    /**
     * Get config payment action url
     * Used to universalize payment actions when processing payment place
     *
     * @return string
     * @api
     */
    public function getConfigPaymentAction();
}
