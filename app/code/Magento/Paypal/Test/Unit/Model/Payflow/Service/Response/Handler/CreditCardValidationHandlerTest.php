<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Model\Payflow\Service\Response\Handler;

use Magento\Framework\DataObject;
use Magento\Payment\Model\InfoInterface;
use Magento\Paypal\Model\Info;
use Magento\Paypal\Model\Payflow\Service\Response\Handler\CreditCardValidationHandler;
use PHPUnit\Framework\TestCase;

class CreditCardValidationHandlerTest extends TestCase
{
    public function testHandleCreditCardValidationFields()
    {
        $expectedHandleResult = [
            Info::PAYPAL_CVV2MATCH => 'Y',
            Info::PAYPAL_AVSZIP => 'X',
            Info::PAYPAL_AVSADDR => 'X',
            Info::PAYPAL_IAVS => 'X'
        ];

        $paypalInfoManager = $this->createMock(Info::class);
        $paymentMock = $this->createMock(InfoInterface::class);
        $responseMock = $this->createMock(DataObject::class);

        $responseMock->expects($this->exactly(count($expectedHandleResult)*2))
            ->method('getData')
            ->willReturnMap(
                [
                    [Info::PAYPAL_CVV2MATCH, null, 'Y'],
                    [Info::PAYPAL_AVSZIP, null, 'X'],
                    [Info::PAYPAL_AVSADDR, null, 'X'],
                    [Info::PAYPAL_IAVS, null, 'X'],
                    ['Some other key', null, 'Some other value']
                ]
            );
        $paypalInfoManager->expects($this->once())
            ->method('importToPayment')
            ->with($expectedHandleResult, $paymentMock);

        $handler = new CreditCardValidationHandler($paypalInfoManager);
        $handler->handle($paymentMock, $responseMock);
    }
}
