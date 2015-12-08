<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model\Method;

use Magento\Framework\DataObject;
use Magento\Payment\Gateway\Command;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Vault\Model\VaultPaymentInterface;
use Magento\Vault\Model\Adminhtml\Source\VaultProvidersMap;
use Magento\Payment\Gateway\Config\ValueHandlerPoolInterface;

/**
 * Class Vault
 */
final class Vault implements VaultPaymentInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var VaultPaymentInterface
     */
    private $paymentInstance;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string
     */
    private $code;

    /**
     * @var int
     */
    private $storeId;

    /**
     * @var ValueHandlerPoolInterface
     */
    private $valueHandlerPool;

    /**
     * Constructor
     *
     * @param ConfigInterface $config
     * @param ObjectManagerInterface $objectManager
     * @param ValueHandlerPoolInterface $valueHandlerPool
     * @param string $code
     */
    public function __construct(
        ConfigInterface $config,
        ObjectManagerInterface $objectManager,
        ValueHandlerPoolInterface $valueHandlerPool,
        $code
    ) {
        $this->config = $config;
        $this->objectManager = $objectManager;
        $this->code = $code;
        $this->valueHandlerPool = $valueHandlerPool;
    }

    /**
     * @return VaultPaymentInterface
     */
    private function getPaymentInstance()
    {
        if (!isset($this->paymentInstance)) {
            $this->config->setMethodCode(
                $this->config->getValue(VaultProvidersMap::VALUE_CODE, $this->storeId)
            );
            $this->paymentInstance = $this->objectManager->get($this->config->getValue('model'));
            $this->config->setMethodCode($this->code);
        }

        return $this->paymentInstance;
    }

    /**
     * Unifies configured value handling logic
     *
     * @param string $field
     * @param null $storeId
     * @return mixed
     */
    private function getConfiguredValue($field, $storeId = null)
    {
        $handler = $this->valueHandlerPool->get($field);
        $subject = ['field' => $field];

        return $handler->handle($subject, $storeId ?: $this->getStore());
    }

    /**
     * @inheritdoc
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @inheritdoc
     */
    public function getFormBlockType()
    {
        return $this->getPaymentInstance()->getFormBlockType();
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->getPaymentInstance()->getTitle();
    }

    /**
     * @inheritdoc
     */
    public function setStore($storeId)
    {
        $this->storeId = (int)$storeId;
    }

    /**
     * @inheritdoc
     */
    public function getStore()
    {
        return $this->storeId;
    }

    /**
     * @inheritdoc
     */
    public function canOrder()
    {
        return $this->getPaymentInstance()->canOrder();
    }

    /**
     * @inheritdoc
     */
    public function canAuthorize()
    {
        return $this->getPaymentInstance()->canAuthorize();
    }

    /**
     * @inheritdoc
     */
    public function canCapture()
    {
        return $this->getPaymentInstance()->canCapture();
    }

    /**
     * @inheritdoc
     */
    public function canCapturePartial()
    {
        return $this->getPaymentInstance()->canCapturePartial();
    }

    /**
     * @inheritdoc
     */
    public function canCaptureOnce()
    {
        return $this->getPaymentInstance()->canCaptureOnce();
    }

    /**
     * @inheritdoc
     */
    public function canRefund()
    {
        return $this->getPaymentInstance()->canRefund();
    }

    /**
     * @inheritdoc
     */
    public function canRefundPartialPerInvoice()
    {
        return $this->getPaymentInstance()->canRefundPartialPerInvoice();
    }

    /**
     * @inheritdoc
     */
    public function canVoid()
    {
        return $this->getPaymentInstance()->canVoid();
    }

    /**
     * @inheritdoc
     */
    public function canUseInternal()
    {
        return $this->getPaymentInstance()->canUseInternal();
    }

    /**
     * @inheritdoc
     */
    public function canUseCheckout()
    {
        return $this->getPaymentInstance()->canUseCheckout();
    }

    /**
     * @inheritdoc
     */
    public function canEdit()
    {
        return $this->getPaymentInstance()->canEdit();
    }

    /**
     * @inheritdoc
     */
    public function canFetchTransactionInfo()
    {
        return $this->getPaymentInstance()->canFetchTransactionInfo();
    }

    /**
     * @inheritdoc
     */
    public function fetchTransactionInfo(InfoInterface $payment, $transactionId)
    {
        return $this->getPaymentInstance()->fetchTransactionInfo($payment, $transactionId);
    }

    /**
     * @inheritdoc
     */
    public function isGateway()
    {
        return $this->getPaymentInstance()->isGateway();
    }

    /**
     * @inheritdoc
     */
    public function isOffline()
    {
        return $this->getPaymentInstance()->isOffline();
    }

    /**
     * @inheritdoc
     */
    public function isInitializeNeeded()
    {
        return $this->getPaymentInstance()->isInitializeNeeded();
    }

    /**
     * @inheritdoc
     */
    public function canUseForCountry($country)
    {
        return $this->getPaymentInstance()->canUseForCountry($country);
    }

    /**
     * @inheritdoc
     */
    public function canUseForCurrency($currencyCode)
    {
        return $this->getPaymentInstance()->canUseForCurrency($currencyCode);
    }

    /**
     * @inheritdoc
     */
    public function getInfoBlockType()
    {
        return $this->getPaymentInstance()->getInfoBlockType();
    }

    /**
     * @inheritdoc
     */
    public function getInfoInstance()
    {
        return $this->getPaymentInstance()->getInfoInstance();
    }

    /**
     * @inheritdoc
     */
    public function setInfoInstance(InfoInterface $info)
    {
        $this->getPaymentInstance()->setInfoInstance($info);
    }

    /**
     * @inheritdoc
     */
    public function validate()
    {
        return $this->getPaymentInstance()->validate();
    }

    /**
     * @inheritdoc
     */
    public function order(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        return $this->getPaymentInstance()->order($payment, $amount);
    }

    /**
     * @inheritdoc
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $this->getPaymentInstance()->executeCommand(
            VaultPaymentInterface::VAULT_AUTHORIZE_COMMAND,
            [
                'payment' => $payment,
                'amount' => $amount
            ]
        );

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $this->getPaymentInstance()->executeCommand(
            VaultPaymentInterface::VAULT_CAPTURE_COMMAND,
            [
                'payment' => $payment,
                'amount' => $amount
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        return $this->getPaymentInstance()->refund($payment, $amount);
    }

    /**
     * @inheritdoc
     */
    public function cancel(\Magento\Payment\Model\InfoInterface $payment)
    {
        return $this->getPaymentInstance()->cancel($payment);
    }

    /**
     * @inheritdoc
     */
    public function void(\Magento\Payment\Model\InfoInterface $payment)
    {
        return $this->getPaymentInstance()->void($payment);
    }

    /**
     * @inheritdoc
     */
    public function canReviewPayment()
    {
        return $this->getPaymentInstance()->canReviewPayment();
    }

    /**
     * @inheritdoc
     */
    public function acceptPayment(InfoInterface $payment)
    {
        return $this->getPaymentInstance()->acceptPayment($payment);
    }

    /**
     * @inheritdoc
     */
    public function denyPayment(InfoInterface $payment)
    {
        return $this->getPaymentInstance()->denyPayment($payment);
    }

    /**
     * @inheritdoc
     */
    public function getConfigData($field, $storeId = null)
    {
        return $this->getConfiguredValue($field, $storeId);
    }

    /**
     * @inheritdoc
     */
    public function assignData(\Magento\Framework\DataObject $data)
    {
        return $this->getPaymentInstance()->assignData($data);
    }

    /**
     * @inheritdoc
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        return $this->getConfiguredValue('active', $quote ? $quote->getStoreId() : null)
            && $this->getPaymentInstance()->isAvailable($quote);
    }

    /**
     * @inheritdoc
     */
    public function isActive($storeId = null)
    {
        return $this->getConfiguredValue('active', $storeId)
            && $this->getPaymentInstance()->isActive($storeId);
    }

    /**
     * @inheritdoc
     */
    public function initialize($paymentAction, $stateObject)
    {
        return $this->getPaymentInstance()->initialize($paymentAction, $stateObject);
    }

    /**
     * @inheritdoc
     */
    public function getConfigPaymentAction()
    {
        return $this->getPaymentInstance()->getConfigPaymentAction();
    }

    /**
     * @inheritdoc
     */
    public function executeCommand($commandCode, array $arguments = [])
    {
        return $this->getPaymentInstance()->executeCommand($commandCode, $arguments);
    }
}
