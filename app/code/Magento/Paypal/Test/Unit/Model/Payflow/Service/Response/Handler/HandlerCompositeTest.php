<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Model\Payflow\Service\Response\Handler;

use Magento\Framework\DataObject;
use Magento\Payment\Model\InfoInterface;
use Magento\Paypal\Model\Payflow\Service\Response\Handler\HandlerComposite;
use Magento\Paypal\Model\Payflow\Service\Response\Handler\HandlerInterface;
use PHPUnit\Framework\TestCase;

class HandlerCompositeTest extends TestCase
{
    public function testConstructorSuccess()
    {
        $handler = $this->getMockBuilder(
            HandlerInterface::class
        )->getMock();

        $result = new HandlerComposite(
            ['some_handler' => $handler]
        );
        $this->assertNotNull($result);
    }

    public function testConstructorException()
    {
        $this->expectException('LogicException');
        $this->expectExceptionMessage(
            'Type mismatch. Expected type: HandlerInterface. Actual: string, Code: weird_handler'
        );

        new HandlerComposite(
            ['weird_handler' => 'some value']
        );
    }

    public function testHandle()
    {
        $paymentMock = $this->getMockBuilder(InfoInterface::class)
            ->getMock();
        $responseMock = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $handler = $this->getMockBuilder(
            HandlerInterface::class
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
