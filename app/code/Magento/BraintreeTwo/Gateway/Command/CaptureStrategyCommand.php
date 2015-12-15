<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Gateway\Command;

use Magento\Payment\Gateway\Command;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\CollectionFactory;

/**
 * Class CaptureStrategyCommand
 */
class CaptureStrategyCommand implements CommandInterface
{
    /**
     * Braintree authorize and capture command
     */
    const SALE = 'sale';

    /**
     * Braintree capture command
     */
    const CAPTURE = 'settlement';

    /**
     * Braintree clone transaction command
     */
    const CLONE_TRANSACTION = 'clone';

    /**
     * @var \Magento\Payment\Gateway\Command\CommandPoolInterface
     */
    private $commandPool;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\CollectionFactory
     */
    private $transactionCollectionFactory;

    /**
     * @param CommandPoolInterface $commandPool
     * @param CollectionFactory $factory
     */
    public function __construct(CommandPoolInterface $commandPool, CollectionFactory $factory)
    {
        $this->commandPool = $commandPool;
        $this->transactionCollectionFactory = $factory;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $commandSubject)
    {
        /** @var \Magento\Payment\Gateway\Data\PaymentDataObjectInterface $paymentDO */
        $paymentDO = SubjectReader::readPayment($commandSubject);

        /** @var \Magento\Sales\Model\Order\Payment $paymentInfo */
        $paymentInfo = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($paymentInfo);

        $command = $this->getCommand($paymentInfo);
        return $this->commandPool->get($command)->execute($commandSubject);
    }

    /**
     * Get execution command name
     * @param Payment $payment
     * @return string
     */
    private function getCommand(Payment $payment)
    {
        // if auth transaction is not exists execute authorize&capture command
        if (!$payment->getAuthorizationTransaction()) {
            return self::SALE;
        }

        // if not exists capture transactions process submit for settlement
        $collection = $this->transactionCollectionFactory->create();
        $collection->addFieldToFilter('payment_id', $payment->getId())
            ->addFieldToFilter('txn_type', Transaction::TYPE_CAPTURE);
        if ($collection->getSize() == 0) {
            return self::CAPTURE;
        }

        return self::CLONE_TRANSACTION;
    }
}