<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\Quote\Payment;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\DataObject\Copy;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Model\Method\Substitution;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Model\Quote\Payment;
use Magento\Quote\Model\Quote\Payment\ToOrderPayment;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ToOrderPaymentTest extends TestCase
{
    /**
     * @var OrderPaymentRepositoryInterface|MockObject
     */
    protected $orderPaymentRepositoryMock;

    /**
     * @var Copy|MockObject
     */
    protected $objectCopyMock;

    /**
     * @var Payment|MockObject
     */
    protected $paymentMock;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var ToOrderPayment
     */
    protected $converter;

    protected function setUp(): void
    {
        $this->paymentMock = $this->getMockBuilder(Payment::class)
            ->addMethods(['getCcNumber', 'getCcCid'])
            ->onlyMethods(['getMethodInstance', 'getAdditionalInformation'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectCopyMock = $this->createMock(Copy::class);
        $this->orderPaymentRepositoryMock = $this->getMockForAbstractClass(
            OrderPaymentRepositoryInterface::class,
            [],
            '',
            false,
            false
        );
        $this->dataObjectHelper = $this->createMock(DataObjectHelper::class);
        $objectManager = new ObjectManager($this);
        $this->converter = $objectManager->getObject(
            ToOrderPayment::class,
            [
                'orderPaymentRepository' => $this->orderPaymentRepositoryMock,
                'objectCopyService' => $this->objectCopyMock,
                'dataObjectHelper' => $this->dataObjectHelper
            ]
        );
    }

    /**
     * Tests Convert method in payment to order payment converter
     */
    public function testConvert()
    {
        $methodInterface = $this->getMockForAbstractClass(MethodInterface::class);

        $paymentData = ['test' => 'test2'];
        $data = ['some_id' => 1];
        $paymentMethodTitle = 'TestTitle';
        $additionalInfo = ['token' => 'TOKEN-123'];

        $this->paymentMock->expects($this->once())->method('getMethodInstance')->willReturn($methodInterface);
        $methodInterface->expects($this->once())->method('getTitle')->willReturn($paymentMethodTitle);
        $this->objectCopyMock->expects($this->once())->method('getDataFromFieldset')->with(
            'quote_convert_payment',
            'to_order_payment',
            $this->paymentMock
        )->willReturn($paymentData);

        $this->paymentMock->expects($this->once())
            ->method('getAdditionalInformation')
            ->willReturn($additionalInfo);
        $ccNumber = 123456798;
        $ccCid = 1234;
        $this->paymentMock->expects($this->once())
            ->method('getCcNumber')
            ->willReturn($ccNumber);
        $this->paymentMock->expects($this->once())
            ->method('getCcCid')
            ->willReturn($ccCid);

        $orderPayment = $this->getMockForAbstractClass(
            OrderPaymentInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['setCcNumber', 'setCcCid', 'setAdditionalInformation']
        );
        $orderPayment->expects($this->once())
            ->method('setAdditionalInformation')
            ->with(array_merge($additionalInfo, [Substitution::INFO_KEY_TITLE => $paymentMethodTitle]))
            ->willReturnSelf();
        $orderPayment->expects($this->once())
            ->method('setCcNumber')
            ->willReturnSelf();
        $orderPayment->expects($this->once())
            ->method('setCcCid')
            ->willReturnSelf();

        $this->orderPaymentRepositoryMock->expects($this->once())->method('create')->willReturn($orderPayment);
        $this->dataObjectHelper->expects($this->once())
            ->method('populateWithArray')
            ->with(
                $orderPayment,
                array_merge($paymentData, $data),
                OrderPaymentInterface::class
            )
            ->willReturnSelf();

        $this->assertSame($orderPayment, $this->converter->convert($this->paymentMock, $data));
    }
}
