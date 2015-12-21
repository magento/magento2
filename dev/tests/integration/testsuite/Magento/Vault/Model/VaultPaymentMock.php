<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model;

use Magento\Framework\DataObject;
use Magento\Payment\Gateway\Command;
use Magento\Payment\Model\InfoInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Class VaultPaymentMock
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class VaultPaymentMock implements \Magento\Vault\Model\VaultPaymentInterface
{
    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function executeCommand($commandCode, array $arguments = [])
    {
        //
    }

    /**
     * @inheritdoc
     */
    public function getCode()
    {
        return self::CODE;
    }

    /**
     * @inheritdoc
     */
    public function getFormBlockType()
    {
        return '\Magento\Payment\Block\Form\Cc';
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return self::CODE;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setStore($storeId)
    {
        //
    }

    /**
     * @inheritdoc
     */
    public function getStore()
    {
        //
    }

    /**
     * @inheritdoc
     */
    public function canOrder()
    {
        //
    }

    /**
     * @inheritdoc
     */
    public function canAuthorize()
    {
        //
    }

    /**
     * @inheritdoc
     */
    public function canCapture()
    {
        //
    }

    /**
     * @inheritdoc
     */
    public function canCapturePartial()
    {
        //
    }

    /**
     * @inheritdoc
     */
    public function canCaptureOnce()
    {
        //
    }

    /**
     * @inheritdoc
     */
    public function canRefund()
    {
        //
    }

    /**
     * @inheritdoc
     */
    public function canRefundPartialPerInvoice()
    {
        //
    }

    /**
     * @inheritdoc
     */
    public function canVoid()
    {
        //
    }

    /**
     * @inheritdoc
     */
    public function canUseInternal()
    {
        //
    }

    /**
     * @inheritdoc
     */
    public function canUseCheckout()
    {
        //
    }

    /**
     * @inheritdoc
     */
    public function canEdit()
    {
        //
    }

    /**
     * @inheritdoc
     */
    public function canFetchTransactionInfo()
    {
        //
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetchTransactionInfo(InfoInterface $payment, $transactionId)
    {
        //
    }

    /**
     * @inheritdoc
     */
    public function isGateway()
    {
        //
    }

    /**
     * @inheritdoc
     */
    public function isOffline()
    {
        //
    }

    /**
     * @inheritdoc
     */
    public function isInitializeNeeded()
    {
        //
    }

    /**
     * @inheritdoc
     */
    public function canUseForCountry($country)
    {
        //
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function canUseForCurrency($currencyCode)
    {
        //
    }

    /**
     * @inheritdoc
     */
    public function getInfoBlockType()
    {
        return '\Magento\Payment\Block\Info';
    }

    /**
     * @inheritdoc
     */
    public function getInfoInstance()
    {
        //
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setInfoInstance(InfoInterface $info)
    {
        //
    }

    /**
     * @inheritdoc
     */
    public function validate()
    {
        //
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function order(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        //
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        //
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        //
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        //
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function cancel(\Magento\Payment\Model\InfoInterface $payment)
    {
        //
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function void(\Magento\Payment\Model\InfoInterface $payment)
    {
        //
    }

    /**
     * @inheritdoc
     */
    public function canReviewPayment()
    {
        //
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function acceptPayment(InfoInterface $payment)
    {
        //
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function denyPayment(InfoInterface $payment)
    {
        //
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getConfigData($field, $storeId = null)
    {
        //
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function assignData(DataObject $data)
    {
        //
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isAvailable(CartInterface $quote = null)
    {
        //
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isActive($storeId = null)
    {
        //
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function initialize($paymentAction, $stateObject)
    {
        //
    }

    /**
     * @inheritdoc
     */
    public function getConfigPaymentAction()
    {
        //
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isActiveForPayment($paymentCode, $storeId = null)
    {
        //
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getProviderCode($storeId = null)
    {
        return self::CODE;
    }
}
