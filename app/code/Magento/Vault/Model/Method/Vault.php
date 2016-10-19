<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model\Method;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Payment\Gateway\Command;
use Magento\Payment\Gateway\Config\ValueHandlerPoolInterface;
use Magento\Payment\Gateway\ConfigFactoryInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Magento\Vault\Block\Form;
use Magento\Vault\Model\VaultPaymentInterface;

/**
 * Class Vault
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
final class Vault implements VaultPaymentInterface
{
    /**
     * @deprecated
     */
    const TOKEN_METADATA_KEY = 'token_metadata';

    /**
     * @var string
     */
    private static $activeKey = 'active';

    /**
     * @var string
     */
    private static $titleKey = 'title';

    /**
     * @var ConfigFactoryInterface
     */
    private $configFactory;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var MethodInterface
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
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var Command\CommandManagerPoolInterface
     */
    private $commandManagerPool;

    /**
     * @var PaymentTokenManagementInterface
     */
    private $tokenManagement;

    /**
     * @var OrderPaymentExtensionInterfaceFactory
     */
    private $paymentExtensionFactory;

    /**
     * @var string
     */
    private $code;

    /**
     * Constructor
     *
     * @param ConfigInterface $config
     * @param ConfigFactoryInterface $configFactory
     * @param ObjectManagerInterface $objectManager
     * @param MethodInterface $vaultProvider
     * @param ManagerInterface $eventManager
     * @param ValueHandlerPoolInterface $valueHandlerPool
     * @param Command\CommandManagerPoolInterface $commandManagerPool
     * @param PaymentTokenManagementInterface $tokenManagement
     * @param OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory
     * @param string $code
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ConfigInterface $config,
        ConfigFactoryInterface $configFactory,
        ObjectManagerInterface $objectManager,
        MethodInterface $vaultProvider,
        ManagerInterface $eventManager,
        ValueHandlerPoolInterface $valueHandlerPool,
        Command\CommandManagerPoolInterface $commandManagerPool,
        PaymentTokenManagementInterface $tokenManagement,
        OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory,
        $code
    ) {
        $this->config = $config;
        $this->configFactory = $configFactory;
        $this->objectManager = $objectManager;
        $this->valueHandlerPool = $valueHandlerPool;
        $this->vaultProvider = $vaultProvider;
        $this->eventManager = $eventManager;
        $this->commandManagerPool = $commandManagerPool;
        $this->tokenManagement = $tokenManagement;
        $this->paymentExtensionFactory = $paymentExtensionFactory;
        $this->code = $code;
    }

    /**
     * @return MethodInterface
     */
    private function getVaultProvider()
    {
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
        return $this->code;
    }

    /**
     * @inheritdoc
     */
    public function getFormBlockType()
    {
        return Form::class;
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->getConfiguredValue(self::$titleKey);
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
        return false;
    }

    /**
     * @inheritdoc
     */
    public function canAuthorize()
    {
        return $this->getVaultProvider()->canAuthorize()
        && $this->getVaultProvider()->getConfigData(static::CAN_AUTHORIZE);
    }

    /**
     * @inheritdoc
     */
    public function canCapture()
    {
        return $this->getVaultProvider()->canCapture()
        && $this->getVaultProvider()->getConfigData(static::CAN_CAPTURE);
    }

    /**
     * @inheritdoc
     */
    public function canCapturePartial()
    {
        return false;
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
        return false;
    }

    /**
     * @inheritdoc
     */
    public function canRefundPartialPerInvoice()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function canVoid()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function canUseInternal()
    {
        $isInternalAllowed = $this->getConfiguredValue('can_use_internal');
        // if config has't been specified for Vault, need to check payment provider option
        if ($isInternalAllowed === null) {
            return $this->getVaultProvider()->canUseInternal();
        }
        return (bool) $isInternalAllowed;
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
        return false;
    }

    /**
     * @inheritdoc
     */
    public function fetchTransactionInfo(InfoInterface $payment, $transactionId)
    {
        throw new \DomainException("Not implemented");
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
        throw new \DomainException("Not implemented");
    }

    /**
     * @inheritdoc
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if (!$payment instanceof OrderPaymentInterface) {
            throw new \DomainException('Not implemented');
        }
        /** @var $payment OrderPaymentInterface */

        $this->attachTokenExtensionAttribute($payment);

        $commandExecutor = $this->commandManagerPool->get(
            $this->getVaultProvider()->getCode()
        );

        $commandExecutor->executeByCode(
            VaultPaymentInterface::VAULT_AUTHORIZE_COMMAND,
            $payment,
            [
                'amount' => $amount
            ]
        );

        $payment->setMethod($this->getVaultProvider()->getCode());

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if (!$payment instanceof OrderPaymentInterface) {
            throw new \DomainException('Not implemented');
        }
        /** @var $payment Payment */

        if ($payment->getAuthorizationTransaction()) {
            throw new \DomainException('Capture can not be performed through vault');
        }

        $this->attachTokenExtensionAttribute($payment);

        $commandExecutor = $this->commandManagerPool->get(
            $this->getVaultProvider()->getCode()
        );

        $commandExecutor->executeByCode(
            VaultPaymentInterface::VAULT_SALE_COMMAND,
            $payment,
            [
                'amount' => $amount
            ]
        );

        $payment->setMethod($this->getVaultProvider()->getCode());
    }

    /**
     * @param OrderPaymentInterface $orderPayment
     * @return void
     */
    private function attachTokenExtensionAttribute(OrderPaymentInterface $orderPayment)
    {
        $additionalInformation = $orderPayment->getAdditionalInformation();
        if (empty($additionalInformation[PaymentTokenInterface::PUBLIC_HASH])) {
            throw new \LogicException('Public hash should be defined');
        }

        $customerId = isset($additionalInformation[PaymentTokenInterface::CUSTOMER_ID]) ?
            $additionalInformation[PaymentTokenInterface::CUSTOMER_ID] : null;

        $publicHash = $additionalInformation[PaymentTokenInterface::PUBLIC_HASH];

        $paymentToken = $this->tokenManagement->getByPublicHash($publicHash, $customerId);

        if ($paymentToken === null) {
            throw new \LogicException("No token found");
        }

        $extensionAttributes = $this->getPaymentExtensionAttributes($orderPayment);
        $extensionAttributes->setVaultPaymentToken($paymentToken);
    }

    /**
     * @param OrderPaymentInterface $payment
     * @return \Magento\Sales\Api\Data\OrderPaymentExtensionInterface
     */
    private function getPaymentExtensionAttributes(OrderPaymentInterface $payment)
    {
        $extensionAttributes = $payment->getExtensionAttributes();
        if ($extensionAttributes === null) {
            $extensionAttributes = $this->paymentExtensionFactory->create();
            $payment->setExtensionAttributes($extensionAttributes);
        }

        return $extensionAttributes;
    }

    /**
     * @inheritdoc
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        throw new \DomainException("Not implemented");
    }

    /**
     * @inheritdoc
     */
    public function cancel(\Magento\Payment\Model\InfoInterface $payment)
    {
        throw new \DomainException("Not implemented");
    }

    /**
     * @inheritdoc
     */
    public function void(\Magento\Payment\Model\InfoInterface $payment)
    {
        throw new \DomainException("Not implemented");
    }

    /**
     * @inheritdoc
     */
    public function canReviewPayment()
    {
        throw new \DomainException("Not implemented");
    }

    /**
     * @inheritdoc
     */
    public function acceptPayment(InfoInterface $payment)
    {
        throw new \DomainException("Not implemented");
    }

    /**
     * @inheritdoc
     */
    public function denyPayment(InfoInterface $payment)
    {
        throw new \DomainException("Not implemented");
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
        $this->eventManager->dispatch(
            'payment_method_assign_data_vault',
            [
                AbstractDataAssignObserver::METHOD_CODE => $this,
                AbstractDataAssignObserver::MODEL_CODE => $this->getInfoInstance(),
                AbstractDataAssignObserver::DATA_CODE => $data
            ]
        );

        $this->eventManager->dispatch(
            'payment_method_assign_data_vault_' . $this->getProviderCode(),
            [
                AbstractDataAssignObserver::METHOD_CODE => $this,
                AbstractDataAssignObserver::MODEL_CODE => $this->getInfoInstance(),
                AbstractDataAssignObserver::DATA_CODE => $data
            ]
        );

        return $this->getVaultProvider()->assignData($data);
    }

    /**
     * @inheritdoc
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        return $this->getVaultProvider()->isAvailable($quote)
            && $this->config->getValue(self::$activeKey, $this->getStore() ?: ($quote ? $quote->getStoreId() : null));
    }

    /**
     * @inheritdoc
     */
    public function isActive($storeId = null)
    {
        return $this->getVaultProvider()->isActive($storeId)
            && $this->config->getValue(self::$activeKey, $this->getStore() ?: $storeId);
    }

    /**
     * @inheritdoc
     */
    public function initialize($paymentAction, $stateObject)
    {
        throw new \DomainException("Not implemented");
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
    public function getProviderCode()
    {
        return $this->getVaultProvider()->getCode();
    }
}
