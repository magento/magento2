<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Braintree\Test\Unit\Gateway\Response;

use Magento\Braintree\Gateway\Response\CancelDetailsHandler;
use Magento\Braintree\Gateway\SubjectReader;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Sales\Model\Order\Payment;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class CancelDetailsHandlerTest extends TestCase
{
    /**
     * @var CancelDetailsHandler
     */
    private $handler;

    protected function setUp()
    {
        $this->handler = new CancelDetailsHandler(new SubjectReader());
    }

    /**
     * Checks a case when cancel handler closes the current and parent transactions.
     */
    public function testHandle()
    {
        /** @var OrderAdapterInterface|MockObject $order */
        $order = $this->getMockForAbstractClass(OrderAdapterInterface::class);
        /** @var Payment|MockObject $payment */
        $payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods(['setOrder'])
            ->getMock();

        $paymentDO = new PaymentDataObject($order, $payment);
        $response = [
            'payment' => $paymentDO
        ];

        $this->handler->handle($response, []);

        self::assertTrue($payment->getIsTransactionClosed(), 'The current transaction should be closed.');
        self::assertTrue($payment->getShouldCloseParentTransaction(), 'The parent transaction should be closed.');
    }
}
