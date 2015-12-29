<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model\Method;

use Magento\Framework\DataObject;
use Magento\Payment\Gateway\Command;
use Magento\Payment\Gateway\CommandExecutorInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Api\Data\CartInterface;

class NullPaymentProvider implements MethodInterface, CommandExecutorInterface
{
    /**
     * Performs command
     *
     * @param string $commandCode
     * @param array $arguments
     * @return null|Command\ResultInterface
     */
    public function executeCommand($commandCode, array $arguments = [])
    {
        return null;
    }

    /**
     * Retrieve payment method code
     *
     * @return string
     * @api
     */
    public function getCode()
    {
        return null;
    }

    /**
     * Retrieve block type for method form generation
     *
     * @return string
     * @api
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
     * @api
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
     * @api
     */
    public function canOrder()
    {
        return false;
    }

    /**
     * Check authorize availability
     *
     * @return bool
     * @api
     */
    public function canAuthorize()
    {
        return false;
    }

    /**
     * Check capture availability
     *
     * @return bool
     * @api
     */
    public function canCapture()
    {
        return false;
    }

    /**
     * Check partial capture availability
     *
     * @return bool
     * @api
     */
    public function canCapturePartial()
    {
        return false;
    }

    /**
     * Check whether capture can be performed once and no further capture possible
     *
     * @return bool
     * @api
     */
    public function canCaptureOnce()
    {
        return false;
    }

    /**
     * Check refund availability
     *
     * @return bool
     * @api
     */
    public function canRefund()
    {
        return false;
    }

    /**
     * Check partial refund availability for invoice
     *
     * @return bool
     * @api
     */
    public function canRefundPartialPerInvoice()
    {
        return false;
    }

    /**
     * Check void availability
     * @return bool
     * @api
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
     * @api
     */
    public function canEdit()
    {
        return false;
    }

    /**
     * Check fetch transaction info availability
     *
     * @return bool
     * @api
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
     * @api
     */
    public function fetchTransactionInfo(InfoInterface $payment, $transactionId)
    {
        return [];
    }

    /**
     * Retrieve payment system relation flag
     *
     * @return bool
     * @api
     */
    public function isGateway()
    {
        return false;
    }

    /**
     * Retrieve payment method online/offline flag
     *
     * @return bool
     * @api
     */
    public function isOffline()
    {
        return false;
    }

    /**
     * Flag if we need to run payment initialize while order place
     *
     * @return bool
     * @api
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
     * @api
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
     * @api
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
     * @api
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
     * @api
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
     * @api
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
     * @api
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
     * @api
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
     * @api
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
     * @api
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
     * @api
     */
    public function void(\Magento\Payment\Model\InfoInterface $payment)
    {
        return $this;
    }

    /**
     * Whether this method can accept or deny payment
     * @return bool
     * @api
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
     * @api
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
     * @api
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
     * @api
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
     * @api
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
     * @api
     */
    public function getConfigPaymentAction()
    {
        return null;
    }
}
