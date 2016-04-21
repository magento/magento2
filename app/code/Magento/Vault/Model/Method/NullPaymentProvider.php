<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model\Method;

use Magento\Framework\DataObject;
use Magento\Payment\Gateway\Command;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Api\Data\CartInterface;

class NullPaymentProvider implements MethodInterface
{
    /**
     * Retrieve payment method code
     *
     * @return string
     * 
     */
    public function getCode()
    {
        return null;
    }

    /**
     * Retrieve block type for method form generation
     *
     * @return string
     * 
     * @deprecated
     */
    public function getFormBlockType()
    {
        return null;
    }

    /**
     * Retrieve payment method title
     *
     * @return string
     * 
     */
    public function getTitle()
    {
        return null;
    }

    /**
     * Store id setter
     * @param int $storeId
     * @return void
     */
    public function setStore($storeId)
    {
        return null;
    }

    /**
     * Store id getter
     * @return int
     */
    public function getStore()
    {
        return null;
    }

    /**
     * Check order availability
     *
     * @return bool
     * 
     */
    public function canOrder()
    {
        return false;
    }

    /**
     * Check authorize availability
     *
     * @return bool
     * 
     */
    public function canAuthorize()
    {
        return false;
    }

    /**
     * Check capture availability
     *
     * @return bool
     * 
     */
    public function canCapture()
    {
        return false;
    }

    /**
     * Check partial capture availability
     *
     * @return bool
     * 
     */
    public function canCapturePartial()
    {
        return false;
    }

    /**
     * Check whether capture can be performed once and no further capture possible
     *
     * @return bool
     * 
     */
    public function canCaptureOnce()
    {
        return false;
    }

    /**
     * Check refund availability
     *
     * @return bool
     * 
     */
    public function canRefund()
    {
        return false;
    }

    /**
     * Check partial refund availability for invoice
     *
     * @return bool
     * 
     */
    public function canRefundPartialPerInvoice()
    {
        return false;
    }

    /**
     * Check void availability
     * @return bool
     * 
     */
    public function canVoid()
    {
        return false;
    }

    /**
     * Using internal pages for input payment data
     * Can be used in admin
     *
     * @return bool
     */
    public function canUseInternal()
    {
        return false;
    }

    /**
     * Can be used in regular checkout
     *
     * @return bool
     */
    public function canUseCheckout()
    {
        return false;
    }

    /**
     * Can be edit order (renew order)
     *
     * @return bool
     * 
     */
    public function canEdit()
    {
        return false;
    }

    /**
     * Check fetch transaction info availability
     *
     * @return bool
     * 
     */
    public function canFetchTransactionInfo()
    {
        return false;
    }

    /**
     * Fetch transaction info
     *
     * @param InfoInterface $payment
     * @param string $transactionId
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * 
     */
    public function fetchTransactionInfo(InfoInterface $payment, $transactionId)
    {
        return [];
    }

    /**
     * Retrieve payment system relation flag
     *
     * @return bool
     * 
     */
    public function isGateway()
    {
        return false;
    }

    /**
     * Retrieve payment method online/offline flag
     *
     * @return bool
     * 
     */
    public function isOffline()
    {
        return false;
    }

    /**
     * Flag if we need to run payment initialize while order place
     *
     * @return bool
     * 
     */
    public function isInitializeNeeded()
    {
        return false;
    }

    /**
     * To check billing country is allowed for the payment method
     *
     * @param string $country
     * @return bool
     */
    public function canUseForCountry($country)
    {
        return false;
    }

    /**
     * Check method for processing with base currency
     *
     * @param string $currencyCode
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function canUseForCurrency($currencyCode)
    {
        return false;
    }

    /**
     * Retrieve block type for display method information
     *
     * @return string
     * 
     * @deprecated
     */
    public function getInfoBlockType()
    {
        return null;
    }

    /**
     * Retrieve payment information model object
     *
     * @return InfoInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * 
     * @deprecated
     */
    public function getInfoInstance()
    {
        return null;
    }

    /**
     * Retrieve payment information model object
     *
     * @param InfoInterface $info
     * @return void
     * 
     * @deprecated
     */
    public function setInfoInstance(InfoInterface $info)
    {
        return null;
    }

    /**
     * Validate payment method information object
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * 
     */
    public function validate()
    {
        return $this;
    }

    /**
     * Order payment method
     *
     * @param InfoInterface $payment
     * @param float $amount
     * @return $this
     * 
     */
    public function order(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        return $this;
    }

    /**
     * Authorize payment method
     *
     * @param InfoInterface $payment
     * @param float $amount
     * @return $this
     * 
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        return $this;
    }

    /**
     * Capture payment method
     *
     * @param InfoInterface $payment
     * @param float $amount
     * @return $this
     * 
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        return $this;
    }

    /**
     * Refund specified amount for payment
     *
     * @param InfoInterface $payment
     * @param float $amount
     * @return $this
     * 
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        return $this;
    }

    /**
     * Cancel payment method
     *
     * @param InfoInterface $payment
     * @return $this
     * 
     */
    public function cancel(\Magento\Payment\Model\InfoInterface $payment)
    {
        return $this;
    }

    /**
     * Void payment method
     *
     * @param InfoInterface $payment
     * @return $this
     * 
     */
    public function void(\Magento\Payment\Model\InfoInterface $payment)
    {
        return $this;
    }

    /**
     * Whether this method can accept or deny payment
     * @return bool
     * 
     */
    public function canReviewPayment()
    {
        return false;
    }

    /**
     * Attempt to accept a payment that us under review
     *
     * @param InfoInterface $payment
     * @return false
     * @throws \Magento\Framework\Exception\LocalizedException
     * 
     */
    public function acceptPayment(InfoInterface $payment)
    {
        return false;
    }

    /**
     * Attempt to deny a payment that us under review
     *
     * @param InfoInterface $payment
     * @return false
     * @throws \Magento\Framework\Exception\LocalizedException
     * 
     */
    public function denyPayment(InfoInterface $payment)
    {
        return false;
    }

    /**
     * Retrieve information from payment configuration
     *
     * @param string $field
     * @param int|string|null|\Magento\Store\Model\Store $storeId
     *
     * @return mixed
     */
    public function getConfigData($field, $storeId = null)
    {
        return null;
    }

    /**
     * Assign data to info model instance
     *
     * @param DataObject $data
     * @return $this
     * 
     */
    public function assignData(DataObject $data)
    {
        return $this;
    }

    /**
     * Check whether payment method can be used
     *
     * @param CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(CartInterface $quote = null)
    {
        return false;
    }

    /**
     * Is active
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isActive($storeId = null)
    {
        return false;
    }

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
     * @deprecated
     */
    public function initialize($paymentAction, $stateObject)
    {
        return $this;
    }

    /**
     * Get config payment action url
     * Used to universalize payment actions when processing payment place
     *
     * @return string
     * 
     */
    public function getConfigPaymentAction()
    {
        return null;
    }
}
