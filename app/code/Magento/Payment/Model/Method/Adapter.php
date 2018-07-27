<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model\Method;

use Magento\Framework\DataObject;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Command\CommandManagerInterface;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Payment\Gateway\Config\ValueHandlerPoolInterface;
use Magento\Payment\Gateway\Validator\ValidatorPoolInterface;

/**
 * Payment method facade. Abstract method adapter
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Adapter implements MethodInterface
{
    /**
     * @var ValueHandlerPoolInterface
     */
    private $valueHandlerPool;

    /**
     * @var ValidatorPoolInterface
     */
    private $validatorPool;

    /**
     * @var CommandPoolInterface
     */
    private $commandPool;

    /**
     * @var int
     */
    private $storeId;

    /**
     * @var string
     */
    private $formBlockType;

    /**
     * @var string
     */
    private $infoBlockType;

    /**
     * @var InfoInterface
     */
    private $infoInstance;

    /**
     * @var string
     */
    private $code;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var PaymentDataObjectFactory
     */
    private $paymentDataObjectFactory;

    /**
     * @var \Magento\Payment\Gateway\Command\CommandManagerInterface
     */
    private $commandExecutor;

    /**
     * @param ManagerInterface $eventManager
     * @param ValueHandlerPoolInterface $valueHandlerPool
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param string $code
     * @param string $formBlockType
     * @param string $infoBlockType
     * @param CommandPoolInterface $commandPool
     * @param ValidatorPoolInterface $validatorPool
     * @param CommandManagerInterface $commandExecutor
     */
    public function __construct(
        ManagerInterface $eventManager,
        ValueHandlerPoolInterface $valueHandlerPool,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        $code,
        $formBlockType,
        $infoBlockType,
        CommandPoolInterface $commandPool = null,
        ValidatorPoolInterface $validatorPool = null,
        CommandManagerInterface $commandExecutor = null
    ) {
        $this->valueHandlerPool = $valueHandlerPool;
        $this->validatorPool = $validatorPool;
        $this->commandPool = $commandPool;
        $this->code = $code;
        $this->infoBlockType = $infoBlockType;
        $this->formBlockType = $formBlockType;
        $this->eventManager = $eventManager;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->commandExecutor = $commandExecutor;
    }

    /**
     * Returns Validator pool
     *
     * @return ValidatorPoolInterface
     * @throws \DomainException
     */
    public function getValidatorPool()
    {
        if ($this->validatorPool === null) {
            throw new \DomainException('Validator pool is not configured for use.');
        }
        return $this->validatorPool;
    }

    /**
     * @inheritdoc
     */
    public function canOrder()
    {
        return $this->canPerformCommand('order');
    }

    /**
     * @inheritdoc
     */
    public function canAuthorize()
    {
        return $this->canPerformCommand('authorize');
    }

    /**
     * @inheritdoc
     */
    public function canCapture()
    {
        return $this->canPerformCommand('capture');
    }

    /**
     * @inheritdoc
     */
    public function canCapturePartial()
    {
        return $this->canPerformCommand('capture_partial');
    }

    /**
     * @inheritdoc
     */
    public function canCaptureOnce()
    {
        return $this->canPerformCommand('capture_once');
    }

    /**
     * @inheritdoc
     */
    public function canRefund()
    {
        return $this->canPerformCommand('refund');
    }

    /**
     * @inheritdoc
     */
    public function canRefundPartialPerInvoice()
    {
        return $this->canPerformCommand('refund_partial_per_invoice');
    }

    /**
     * @inheritdoc
     */
    public function canVoid()
    {
        return $this->canPerformCommand('void');
    }

    /**
     * @inheritdoc
     */
    public function canUseInternal()
    {
        return (bool)$this->getConfiguredValue('can_use_internal');
    }

    /**
     * @inheritdoc
     */
    public function canUseCheckout()
    {
        return (bool)$this->getConfiguredValue('can_use_checkout');
    }

    /**
     * @inheritdoc
     */
    public function canEdit()
    {
        return (bool)$this->getConfiguredValue('can_edit');
    }

    /**
     * @inheritdoc
     */
    public function canFetchTransactionInfo()
    {
        return $this->canPerformCommand('fetch_transaction_info');
    }

    /**
     * @inheritdoc
     */
    public function canReviewPayment()
    {
        return $this->canPerformCommand('review_payment');
    }

    /**
     * @inheritdoc
     */
    public function isGateway()
    {
        return (bool)$this->getConfiguredValue('is_gateway');
    }

    /**
     * @inheritdoc
     */
    public function isOffline()
    {
        return (bool)$this->getConfiguredValue('is_offline');
    }

    /**
     * @inheritdoc
     */
    public function isInitializeNeeded()
    {
        return (bool)(int)$this->getConfiguredValue('can_initialize');
    }

    /**
     * @inheritdoc
     */
    public function isAvailable(CartInterface $quote = null)
    {
        if (!$this->isActive($quote ? $quote->getStoreId() : null)) {
            return false;
        }

        $checkResult = new DataObject();
        $checkResult->setData('is_available', true);
        try {
            $infoInstance = $this->getInfoInstance();
            if ($infoInstance !== null) {
                $validator = $this->getValidatorPool()->get('availability');
                $result = $validator->validate(
                    [
                        'payment' => $this->paymentDataObjectFactory->create($infoInstance)
                    ]
                );

                $checkResult->setData('is_available', $result->isValid());
            }
        } catch (\Exception $e) {
            // pass
        }

        // for future use in observers
        $this->eventManager->dispatch(
            'payment_method_is_active',
            [
                'result' => $checkResult,
                'method_instance' => $this,
                'quote' => $quote
            ]
        );

        return $checkResult->getData('is_available');
    }

    /**
     * @inheritdoc
     */
    public function isActive($storeId = null)
    {
        return $this->getConfiguredValue('active', $storeId);
    }

    /**
     * @inheritdoc
     */
    public function canUseForCountry($country)
    {
        try {
            $validator = $this->getValidatorPool()->get('country');
        } catch (\Exception $e) {
            return true;
        }

        $result = $validator->validate(['country' => $country, 'storeId' => $this->getStore()]);
        return $result->isValid();
    }

    /**
     * @inheritdoc
     */
    public function canUseForCurrency($currencyCode)
    {
        try {
            $validator = $this->getValidatorPool()->get('currency');
        } catch (\Exception $e) {
            return true;
        }

        $result = $validator->validate(['currency' => $currencyCode, 'storeId' => $this->getStore()]);
        return $result->isValid();
    }

    /**
     * Whether payment command is supported and can be executed
     *
     * @param string $commandCode
     * @return bool
     */
    private function canPerformCommand($commandCode)
    {
        return (bool)$this->getConfiguredValue('can_' . $commandCode);
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
        $subject = [
            'field' => $field
        ];

        if ($this->getInfoInstance()) {
            $subject['payment'] = $this->paymentDataObjectFactory->create($this->getInfoInstance());
        }

        return $handler->handle($subject, $storeId ?: $this->getStore());
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
    public function validate()
    {
        try {
            $validator = $this->getValidatorPool()->get('global');
        } catch (\Exception $e) {
            return $this;
        }

        $result = $validator->validate(
            ['payment' => $this->getInfoInstance(), 'storeId' => $this->getStore()]
        );

        if (!$result->isValid()) {
            throw new LocalizedException(
                __(implode("\n", $result->getFailsDescription()))
            );
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function fetchTransactionInfo(InfoInterface $payment, $transactionId)
    {
        $this->executeCommand(
            'fetch_transaction_information',
            ['payment' => $payment, 'transactionId' => $transactionId]
        );
    }

    /**
     * @inheritdoc
     */
    public function order(InfoInterface $payment, $amount)
    {
        $this->executeCommand(
            'order',
            ['payment' => $payment, 'amount' => $amount]
        );

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function authorize(InfoInterface $payment, $amount)
    {
        $this->executeCommand(
            'authorize',
            ['payment' => $payment, 'amount' => $amount]
        );

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function capture(InfoInterface $payment, $amount)
    {
        $this->executeCommand(
            'capture',
            ['payment' => $payment, 'amount' => $amount]
        );

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function refund(InfoInterface $payment, $amount)
    {
        $this->executeCommand(
            'refund',
            ['payment' => $payment, 'amount' => $amount]
        );

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function cancel(InfoInterface $payment)
    {
        $this->executeCommand('cancel', ['payment' => $payment]);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function void(InfoInterface $payment)
    {
        $this->executeCommand('void', ['payment' => $payment]);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function acceptPayment(InfoInterface $payment)
    {
        $this->executeCommand('accept_payment', ['payment' => $payment]);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function denyPayment(InfoInterface $payment)
    {
        $this->executeCommand('deny_payment', ['payment' => $payment]);

        return $this;
    }

    /**
     * @inheritdoc
     */
    private function executeCommand($commandCode, array $arguments = [])
    {
        if (!$this->canPerformCommand($commandCode)) {
            return null;
        }

        /** @var InfoInterface|null $payment */
        $payment = null;
        if (isset($arguments['payment']) && $arguments['payment'] instanceof InfoInterface) {
            $payment = $arguments['payment'];
            $arguments['payment'] = $this->paymentDataObjectFactory->create($arguments['payment']);
        }

        if ($this->commandExecutor !== null) {
            return $this->commandExecutor->executeByCode($commandCode, $payment, $arguments);
        }

        if ($this->commandPool === null) {
            throw new \DomainException('Command pool is not configured for use.');
        }

        $command = $this->commandPool->get($commandCode);

        return $command->execute($arguments);

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
    public function getTitle()
    {
        return $this->getConfiguredValue('title');
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
    public function getFormBlockType()
    {
        return $this->formBlockType;
    }

    /**
     * @inheritdoc
     */
    public function getInfoBlockType()
    {
        return $this->infoBlockType;
    }

    /**
     * @inheritdoc
     */
    public function getInfoInstance()
    {
        return $this->infoInstance;
    }

    /**
     * @inheritdoc
     */
    public function setInfoInstance(InfoInterface $info)
    {
        $this->infoInstance = $info;
    }

    /**
     * @inheritdoc
     * @param DataObject $data
     * @return $this
     */
    public function assignData(\Magento\Framework\DataObject $data)
    {
        $this->eventManager->dispatch(
            'payment_method_assign_data_' . $this->getCode(),
            [
                AbstractDataAssignObserver::METHOD_CODE => $this,
                AbstractDataAssignObserver::MODEL_CODE => $this->getInfoInstance(),
                AbstractDataAssignObserver::DATA_CODE => $data
            ]
        );

        $this->eventManager->dispatch(
            'payment_method_assign_data',
            [
                AbstractDataAssignObserver::METHOD_CODE => $this,
                AbstractDataAssignObserver::MODEL_CODE => $this->getInfoInstance(),
                AbstractDataAssignObserver::DATA_CODE => $data
            ]
        );

        return $this;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function initialize($paymentAction, $stateObject)
    {
        $this->executeCommand(
            'initialize',
            [
                'payment' => $this->getInfoInstance(),
                'paymentAction' => $paymentAction,
                'stateObject' => $stateObject
            ]
        );
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getConfigPaymentAction()
    {
        return $this->getConfiguredValue('payment_action');
    }
}
