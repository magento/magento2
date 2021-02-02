<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Test\Unit\Model;

use Magento\Payment\Model\MethodList;
use Magento\Payment\Model\Method\AbstractMethod;

class MethodListTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MethodList
     */
    protected $methodList;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Payment\Api\PaymentMethodListInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentMethodList;

    /**
     * @var \Magento\Payment\Model\Method\InstanceFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentMethodInstanceFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $specificationFactoryMock;

    protected function setUp(): void
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->paymentMethodList = $this->getMockBuilder(\Magento\Payment\Api\PaymentMethodListInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->paymentMethodInstanceFactory = $this->getMockBuilder(
            \Magento\Payment\Model\Method\InstanceFactory::class
        )->disableOriginalConstructor()->getMock();

        $this->specificationFactoryMock = $this->createMock(\Magento\Payment\Model\Checks\SpecificationFactory::class);
        $this->methodList = $this->objectManager->getObject(
            \Magento\Payment\Model\MethodList::class,
            [
                'specificationFactory' => $this->specificationFactoryMock
            ]
        );

        $this->objectManager->setBackwardCompatibleProperty(
            $this->methodList,
            'paymentMethodList',
            $this->paymentMethodList
        );
        $this->objectManager->setBackwardCompatibleProperty(
            $this->methodList,
            'paymentMethodInstanceFactory',
            $this->paymentMethodInstanceFactory
        );
    }

    public function testGetAvailableMethods()
    {
        $storeId = 1;
        $quoteMock = $this->createMock(\Magento\Quote\Model\Quote::class);
        $quoteMock->expects($this->once())->method('getStoreId')->willReturn($storeId);
        $quoteMock->expects($this->atLeastOnce())
            ->method('getPayment')
            ->willReturn($this->createMock(\Magento\Quote\Model\Quote\Payment::class));

        $methodInstanceMock = $this->createMock(\Magento\Payment\Model\Method\AbstractMethod::class);
        $methodInstanceMock->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);

        $compositeMock = $this->createMock(\Magento\Payment\Model\Checks\Composite::class);
        $compositeMock->expects($this->atLeastOnce())
            ->method('isApplicable')
            ->with($methodInstanceMock, $quoteMock)
            ->willReturn(true);

        $this->specificationFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->with([
                AbstractMethod::CHECK_USE_CHECKOUT,
                AbstractMethod::CHECK_USE_FOR_COUNTRY,
                AbstractMethod::CHECK_USE_FOR_CURRENCY,
                AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX
            ])
            ->willReturn($compositeMock);

        $methodMock = $this->getMockForAbstractClass(\Magento\Payment\Api\Data\PaymentMethodInterface::class);
        $this->paymentMethodList->expects($this->once())
            ->method('getActiveList')
            ->willReturn([$methodMock]);
        $this->paymentMethodInstanceFactory->expects($this->once())
            ->method('create')
            ->willReturn($methodInstanceMock);

        $methodInstanceMock->expects($this->atLeastOnce())
            ->method('setInfoInstance')
            ->with($this->createMock(\Magento\Quote\Model\Quote\Payment::class))
            ->willReturnSelf();

        $this->assertEquals([$methodInstanceMock], $this->methodList->getAvailableMethods($quoteMock));
    }
}
