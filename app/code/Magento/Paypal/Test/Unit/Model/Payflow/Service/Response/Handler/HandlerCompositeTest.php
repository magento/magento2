<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model\Payflow\Service\Response\Handler;

use Magento\Paypal\Model\Payflow\Service\Response\Handler\HandlerComposite;

class HandlerCompositeTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorSuccess()
    {
        $handler = $this->getMockBuilder(
            \Magento\Paypal\Model\Payflow\Service\Response\Handler\HandlerInterface::class
        )->getMock();

        new HandlerComposite(
            ['some_handler' => $handler]
        );
    }

    public function testConstructorException()
    {
        $this->setExpectedException(
            'LogicException',
            'Type mismatch. Expected type: HandlerInterface. Actual: string, Code: weird_handler'
        );

        new HandlerComposite(
            ['weird_handler' => 'some value']
        );
    }

    public function testHandle()
    {
        $paymentMock = $this->getMockBuilder(\Magento\Payment\Model\InfoInterface::class)
            ->getMock();
        $responseMock = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $handler = $this->getMockBuilder(
            \Magento\Paypal\Model\Payflow\Service\Response\Handler\HandlerInterface::class
        )->getMock();
        $handler->expects($this->once())
            ->method('handle')
            ->with($paymentMock, $responseMock);

        $composite = new HandlerComposite(
            ['some_handler' => $handler]
        );

        $composite->handle($paymentMock, $responseMock);
    }
}
