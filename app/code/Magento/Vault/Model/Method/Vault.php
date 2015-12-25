<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model\Method;

use Magento\Framework\DataObject;
use Magento\Payment\Gateway\Command;
use Magento\Payment\Gateway\ConfigFactoryInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Vault\Model\VaultPaymentInterface;
use Magento\Vault\Model\Adminhtml\Source\VaultProvidersMap;
use Magento\Payment\Gateway\Config\ValueHandlerPoolInterface;

/**
 * Class Vault
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class Vault implements VaultPaymentInterface
{
    /**
     * @var ConfigFactoryInterface
     */
    private $configFactory;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var VaultPaymentInterface
     */
    private $vaultProvider;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var int
     */
    private $storeId;

    /**
     * @var ValueHandlerPoolInterface
     */
    private $valueHandlerPool;

    /**
     * @var NullPaymentProvider
     */
    private $nullPaymentProvider;

    /**
     * Constructor
     *
     * @param ConfigInterface $config
     * @param ConfigFactoryInterface $configFactory
     * @param ObjectManagerInterface $objectManager
     * @param NullPaymentProvider $nullPaymentProvider
     * @param ValueHandlerPoolInterface $valueHandlerPool
     */
    public function __construct(
        ConfigInterface $config,
        ConfigFactoryInterface $configFactory,
        ObjectManagerInterface $objectManager,
        NullPaymentProvider $nullPaymentProvider,
        ValueHandlerPoolInterface $valueHandlerPool
    ) {
        $this->config = $config;
        $this->configFactory = $configFactory;
        $this->objectManager = $objectManager;
        $this->valueHandlerPool = $valueHandlerPool;
        $this->nullPaymentProvider = $nullPaymentProvider;
    }

    /**
     * @return VaultPaymentInterface
     */
    private function getVaultProvider()
    {
        if ($this->vaultProvider === null) {
            $providerCode = $this->config->getValue(VaultProvidersMap::VALUE_CODE, $this->getStore());

            if ($providerCode !== null) {
                $providerConfig = $this->configFactory->create($providerCode);

                /** @var MethodInterface $vaultProvider */
                $vaultProvider = $this->objectManager->get($providerConfig->getValue('model'));

                if ($vaultProvider->isActive($this->getStore())) {
                    $this->vaultProvider = $vaultProvider;
                }
            }
        }

        if ($this->vaultProvider === null) {
            $this->vaultProvider = $this->nullPaymentProvider;
        }

        return $this->vaultProvider;
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
        return self::CODE;
    }

    /**
     * @inheritdoc
     */
    public function getFormBlockType()
    {
        return $this->getVaultProvider()->getFormBlockType();
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->getVaultProvider()->getTitle();
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
        return $this->getVaultProvider()->canOrder();
    }

    /**
     * @inheritdoc
     */
    public function canAuthorize()
    {
        return $this->getVaultProvider()->canAuthorize();
    }

    /**
     * @inheritdoc
     */
    public function canCapture()
    {
        return $this->getVaultProvider()->canCapture();
    }

    /**
     * @inheritdoc
     */
    public function canCapturePartial()
    {
        return $this->getVaultProvider()->canCapturePartial();
    }

    /**
     * @inheritdoc
     */
    public function canCaptureOnce()
    {
        return $this->getVaultProvider()->canCaptureOnce();
    }

    /**
     * @inheritdoc
     */
    public function canRefund()
    {
        return $this->getVaultProvider()->canRefund();
    }

    /**
     * @inheritdoc
     */
    public function canRefundPartialPerInvoice()
    {
        return $this->getVaultProvider()->canRefundPartialPerInvoice();
    }

    /**
     * @inheritdoc
     */
    public function canVoid()
    {
        return $this->getVaultProvider()->canVoid();
    }

    /**
     * @inheritdoc
     */
    public function canUseInternal()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function canUseCheckout()
    {
        return $this->getVaultProvider()->canUseCheckout();
    }

    /**
     * @inheritdoc
     */
    public function canEdit()
    {
        return $this->getVaultProvider()->canEdit();
    }

    /**
     * @inheritdoc
     */
    public function canFetchTransactionInfo()
    {
        return $this->getVaultProvider()->canFetchTransactionInfo();
    }

    /**
     * @inheritdoc
     */
    public function fetchTransactionInfo(InfoInterface $payment, $transactionId)
    {
        return $this->getVaultProvider()->fetchTransactionInfo($payment, $transactionId);
    }

    /**
     * @inheritdoc
     */
    public function isGateway()
    {
        return $this->getVaultProvider()->isGateway();
    }

    /**
     * @inheritdoc
     */
    public function isOffline()
    {
        return $this->getVaultProvider()->isOffline();
    }

    /**
     * @inheritdoc
     */
    public function isInitializeNeeded()
    {
        return $this->getVaultProvider()->isInitializeNeeded();
    }

    /**
     * @inheritdoc
     */
    public function canUseForCountry($country)
    {
        return $this->getVaultProvider()->canUseForCountry($country);
    }

    /**
     * @inheritdoc
     */
    public function canUseForCurrency($currencyCode)
    {
        return $this->getVaultProvider()->canUseForCurrency($currencyCode);
    }

    /**
     * @inheritdoc
     */
    public function getInfoBlockType()
    {
        return $this->getVaultProvider()->getInfoBlockType();
    }

    /**
     * @inheritdoc
     */
    public function getInfoInstance()
    {
        return $this->getVaultProvider()->getInfoInstance();
    }

    /**
     * @inheritdoc
     */
    public function setInfoInstance(InfoInterface $info)
    {
        $this->getVaultProvider()->setInfoInstance($info);
    }

    /**
     * @inheritdoc
     */
    public function validate()
    {
        return $this->getVaultProvider()->validate();
    }

    /**
     * @inheritdoc
     */
    public function order(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        return $this->getVaultProvider()->order($payment, $amount);
    }

    /**
     * @inheritdoc
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $this->getVaultProvider()->executeCommand(
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
        $this->getVaultProvider()->executeCommand(
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
        return $this->getVaultProvider()->refund($payment, $amount);
    }

    /**
     * @inheritdoc
     */
    public function cancel(\Magento\Payment\Model\InfoInterface $payment)
    {
        return $this->getVaultProvider()->cancel($payment);
    }

    /**
     * @inheritdoc
     */
    public function void(\Magento\Payment\Model\InfoInterface $payment)
    {
        return $this->getVaultProvider()->void($payment);
    }

    /**
     * @inheritdoc
     */
    public function canReviewPayment()
    {
        return $this->getVaultProvider()->canReviewPayment();
    }

    /**
     * @inheritdoc
     */
    public function acceptPayment(InfoInterface $payment)
    {
        return $this->getVaultProvider()->acceptPayment($payment);
    }

    /**
     * @inheritdoc
     */
    public function denyPayment(InfoInterface $payment)
    {
        return $this->getVaultProvider()->denyPayment($payment);
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
        return $this->getVaultProvider()->assignData($data);
    }

    /**
     * @inheritdoc
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        return $this->getVaultProvider()->isAvailable($quote);
    }

    /**
     * @inheritdoc
     */
    public function isActive($storeId = null)
    {
        return $this->getVaultProvider()->isActive($storeId);
    }

    /**
     * @inheritdoc
     */
    public function initialize($paymentAction, $stateObject)
    {
        return $this->getVaultProvider()->initialize($paymentAction, $stateObject);
    }

    /**
     * @inheritdoc
     */
    public function getConfigPaymentAction()
    {
        return $this->getVaultProvider()->getConfigPaymentAction();
    }

    /**
     * @inheritdoc
     */
    public function executeCommand($commandCode, array $arguments = [])
    {
        return $this->getVaultProvider()->executeCommand($commandCode, $arguments);
    }

    /**
     * @param null $storeId
     * @return string|null
     */
    public function getProviderCode($storeId = null)
    {
        return $this->config->getValue(VaultProvidersMap::VALUE_CODE, $this->getStore() ?: $storeId);
    }

    /**
     * @param string $paymentCode
     * @param null $storeId
     *
     * @return bool
     */
    public function isActiveForPayment($paymentCode, $storeId = null)
    {
        return $this->getProviderCode($this->getStore() ?: $storeId) === $paymentCode
        && $this->isActive($this->getStore() ?: $storeId);
    }
}
