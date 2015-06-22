<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model\Method;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\MethodInterface;

/**
 * Payment method facade. Abstract method adapter
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Adapter implements MethodInterface
{
    /**
     * @var \Magento\Payment\Gateway\Config\ValueHandlerPoolInterface
     */
    private $valueHandlerPool;

    /**
     * @var \Magento\Payment\Gateway\Validator\ValidatorPoolInterface
     */
    private $validatorPool;

    /**
     * @var \Magento\Payment\Gateway\Command\CommandPoolInterface
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
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @var \Magento\Payment\Gateway\Data\PaymentDataObjectFactory
     */
    private $paymentDataObjectFactory;

    /**
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Payment\Gateway\Config\ValueHandlerPoolInterface $valueHandlerPool
     * @param \Magento\Payment\Gateway\Validator\ValidatorPoolInterface $validatorPool
     * @param \Magento\Payment\Gateway\Command\CommandPoolInterface $commandPool
     * @param \Magento\Payment\Gateway\Data\PaymentDataObjectFactory $paymentDataObjectFactory
     * @param string $code
     * @param string $formBlockType
     * @param string $infoBlockType
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Payment\Gateway\Config\ValueHandlerPoolInterface $valueHandlerPool,
        \Magento\Payment\Gateway\Validator\ValidatorPoolInterface $validatorPool,
        \Magento\Payment\Gateway\Command\CommandPoolInterface $commandPool,
        \Magento\Payment\Gateway\Data\PaymentDataObjectFactory $paymentDataObjectFactory,
        $code,
        $formBlockType,
        $infoBlockType
    ) {
        $this->valueHandlerPool = $valueHandlerPool;
        $this->validatorPool = $validatorPool;
        $this->commandPool = $commandPool;
        $this->code = $code;
        $this->infoBlockType = $infoBlockType;
        $this->formBlockType = $formBlockType;
        $this->eventManager = $eventManager;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
    }

    /**
     * {inheritdoc}
     */
    public function canOrder()
    {
        return $this->canPerformCommand('order');
    }

    /**
     * {inheritdoc}
     */
    public function canAuthorize()
    {
        return $this->canPerformCommand('authorize');
    }

    /**
     * {inheritdoc}
     */
    public function canCapture()
    {
        return $this->canPerformCommand('capture');
    }

    /**
     * {inheritdoc}
     */
    public function canCapturePartial()
    {
        return $this->canPerformCommand('capture_partial');
    }

    /**
     * {inheritdoc}
     */
    public function canCaptureOnce()
    {
        return $this->canPerformCommand('capture_once');
    }

    /**
     * {inheritdoc}
     */
    public function canRefund()
    {
        return $this->canPerformCommand('refund');
    }

    /**
     * {inheritdoc}
     */
    public function canRefundPartialPerInvoice()
    {
        return $this->canPerformCommand('refund_partial_per_invoice');
    }

    /**
     * {inheritdoc}
     */
    public function canVoid()
    {
        return $this->canPerformCommand('void');
    }

    /**
     * {inheritdoc}
     */
    public function canUseInternal()
    {
        return (bool)$this->getConfiguredValue('can_use_internal');
    }

    /**
     * {inheritdoc}
     */
    public function canUseCheckout()
    {
        return (bool)$this->getConfiguredValue('can_use_checkout');
    }

    /**
     * {inheritdoc}
     */
    public function canEdit()
    {
        return (bool)$this->getConfiguredValue('can_edit');
    }

    /**
     * {inheritdoc}
     */
    public function canFetchTransactionInfo()
    {
        return $this->canPerformCommand('fetch_transaction_info');
    }

    /**
     * {inheritdoc}
     */
    public function canReviewPayment()
    {
        return $this->canPerformCommand('review_payment');
    }

    /**
     * {inheritdoc}
     */
    public function isGateway()
    {
        return (bool)$this->getConfiguredValue('is_gateway');
    }

    /**
     * {inheritdoc}
     */
    public function isOffline()
    {
        return (bool)$this->getConfiguredValue('is_offline');
    }

    /**
     * {inheritdoc}
     */
    public function isInitializeNeeded()
    {
        return (bool)(int)$this->getConfiguredValue('can_initialize');
    }

    /**
     * {inheritdoc}
     */
    public function isAvailable($quote = null)
    {
        $checkResult = new \StdClass();
        $isActive = $this->isActive($quote ? $quote->getStoreId() : null);
        $checkResult->isAvailable = $isActive;
        $checkResult->isDeniedInConfig = !$isActive;

        // for future use in observers
        $this->eventManager->dispatch(
            'payment_method_is_active',
            [
                'result' => $checkResult,
                'method_instance' => $this,
                'quote' => $quote
            ]
        );

        return $checkResult->isAvailable;
    }

    /**
     * {inheritdoc}
     */
    public function isActive($storeId = null)
    {
        return $this->getConfigData('active', $storeId);
    }

    /**
     * {inheritdoc}
     */
    public function canUseForCountry($country)
    {
        try {
            $validator = $this->validatorPool->get('country');
        } catch (NotFoundException $e) {
            return true;
        }

        $result = $validator->validate(['country' => $country, 'storeId' => $this->getStore()]);
        return $result->isValid();
    }

    /**
     * {inheritdoc}
     */
    public function canUseForCurrency($currencyCode)
    {
        try {
            $validator = $this->validatorPool->get('currency');
        } catch (NotFoundException $e) {
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
     * @return mixed
     */
    private function getConfiguredValue($field)
    {
        $handler = $this->valueHandlerPool->get($field);
        return $handler->handle($field, $this->getStore());
    }

    /**
     * {inheritdoc}
     */
    public function validate()
    {
        try {
            $validator = $this->validatorPool->get('global');
        } catch (NotFoundException $e) {
            return $this;
        }

        $result = $validator->validate(
            ['payment' => $this->getInfoInstance(), 'storeId' => $this->getStore()]
        );

        if (!$result->isValid()) {
            throw new LocalizedException(
                implode("\n", $result->getFailsDescription())
            );
        }

        return $this;
    }

    /**
     * {inheritdoc}
     */
    public function fetchTransactionInfo(InfoInterface $payment, $transactionId)
    {
        $this->executeCommand(
            'fetch_transaction_information',
            $payment,
            ['transactionId' => $transactionId]
        );
    }

    /**
     * {inheritdoc}
     */
    public function order(InfoInterface $payment, $amount)
    {
        $this->executeCommand(
            'order',
            $payment,
            ['amount' => $amount]
        );
        return $this;
    }

    /**
     * {inheritdoc}
     */
    public function authorize(InfoInterface $payment, $amount)
    {
        $this->executeCommand(
            'authorize',
            $payment,
            ['amount' => $amount]
        );
        return $this;
    }

    /**
     * {inheritdoc}
     */
    public function capture(InfoInterface $payment, $amount)
    {
        $this->executeCommand(
            'capture',
            $payment,
            ['amount' => $amount]
        );

        return $this;
    }

    /**
     * {inheritdoc}
     */
    public function refund(InfoInterface $payment, $amount)
    {
        $this->executeCommand(
            'refund',
            $payment,
            ['amount' => $amount]
        );
        return $this;
    }

    /**
     * {inheritdoc}
     */
    public function cancel(InfoInterface $payment)
    {
        $this->executeCommand(
            'cancel',
            $payment
        );
        return $this;
    }

    /**
     * {inheritdoc}
     */
    public function void(InfoInterface $payment)
    {
        $this->executeCommand(
            'void',
            $payment
        );
        return $this;
    }

    /**
     * {inheritdoc}
     */
    public function acceptPayment(InfoInterface $payment)
    {
        $this->executeCommand(
            'accept_payment',
            $payment
        );
        return $this;
    }

    /**
     * {inheritdoc}
     */
    public function denyPayment(InfoInterface $payment)
    {
        $this->executeCommand(
            'deny_payment',
            $payment
        );
        return false;
    }

    /**
     * Performs command
     *
     * @param string $commandCode
     * @param InfoInterface $payment
     * @param array $arguments
     * @return void
     * @throws NotFoundException
     * @throws \Exception
     */
    private function executeCommand($commandCode, InfoInterface $payment, array $arguments = [])
    {
        if ($this->canPerformCommand($commandCode)) {
            try {
                $command = $this->commandPool->get($commandCode);
                $arguments['payment'] = $this->paymentDataObjectFactory->create($payment);
                $command->execute($arguments);
            } catch (NotFoundException $e) {
                throw $e;
            }
        }
    }

    /**
     * {inheritdoc}
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * {inheritdoc}
     */
    public function getTitle()
    {
        return $this->getConfiguredValue('title');
    }

    /**
     * {inheritdoc}
     */
    public function setStore($storeId)
    {
        $this->storeId = (int)$storeId;
    }

    /**
     * {inheritdoc}
     */
    public function getStore()
    {
        return $this->storeId;
    }

    /**
     * {inheritdoc}
     */
    public function getFormBlockType()
    {
        return $this->formBlockType;
    }

    /**
     * {inheritdoc}
     */
    public function getInfoBlockType()
    {
        return $this->infoBlockType;
    }

    /**
     * {inheritdoc}
     */
    public function getInfoInstance()
    {
        if (!$this->infoInstance instanceof InfoInterface) {
            throw new LocalizedException(
                __('We cannot retrieve the payment information object instance.')
            );
        }

        return $this->infoInstance;
    }

    /**
     * {inheritdoc}
     */
    public function setInfoInstance(InfoInterface $info)
    {
        $this->infoInstance = $info;
    }

    /**
     * {inheritdoc}
     */
    public function getConfigData($field, $storeId = null)
    {
        if ($storeId === null) {
            return $this->getConfiguredValue($field);
        }

        $handler = $this->valueHandlerPool->get($field);
        return $handler->handle($field, (int)$storeId);
    }

    /**
     * {inheritdoc}
     */
    public function assignData($data)
    {
        if (is_array($data)) {
            $this->getInfoInstance()->addData($data);
        } elseif ($data instanceof \Magento\Framework\Object) {
            $this->getInfoInstance()->addData($data->getData());
        }
        return $this;
    }

    /**
     * {inheritdoc}
     */
    public function initialize($paymentAction, $stateObject)
    {
        $this->executeCommand(
            'initialize',
            $this->getInfoInstance(),
            ['paymentAction' => $paymentAction, 'stateObject' => $stateObject]
        );
        return $this;
    }

    /**
     * {inheritdoc}
     */
    public function getConfigPaymentAction()
    {
        return $this->getConfiguredValue('payment_action');
    }
}
