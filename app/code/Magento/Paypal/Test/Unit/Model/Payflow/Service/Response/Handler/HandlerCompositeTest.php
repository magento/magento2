<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
        $handler = $this->createMock(HandlerInterface::class);

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
        $paymentMock = $this->createMock(InfoInterface::class);
        $responseMock = $this->createMock(DataObject::class);

        $handler = $this->createMock(HandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($paymentMock, $responseMock);

        $composite = new HandlerComposite(
            ['some_handler' => $handler]
        );

        $composite->handle($paymentMock, $responseMock);
    }
}
