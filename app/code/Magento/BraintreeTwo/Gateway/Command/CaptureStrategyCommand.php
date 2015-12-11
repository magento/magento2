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
     * @var \Magento\Payment\Gateway\Command\CommandPoolInterface
     */
    private $commandPool;

    /**
     * @param \Magento\Payment\Gateway\Command\CommandPoolInterface $commandPool
     */
    public function __construct(CommandPoolInterface $commandPool)
    {
        $this->commandPool = $commandPool;
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

        // if auth transaction is exists execute capture command
        $command = $paymentInfo->getAuthorizationTransaction() ? self::CAPTURE : self::SALE;
        return $this->commandPool->get($command)->execute($commandSubject);
    }
}