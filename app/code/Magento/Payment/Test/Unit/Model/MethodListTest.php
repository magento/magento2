<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Payment\Test\Unit\Model;

use Magento\Payment\Model\MethodList;
use Magento\Payment\Model\Method\AbstractMethod;

class MethodListTest extends \PHPUnit_Framework_TestCase
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
     * @var \Magento\Payment\Api\PaymentMethodListInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentMethodList;

    /**
     * @var \Magento\Payment\Model\Method\InstanceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentMethodInstanceFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $specificationFactoryMock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->paymentMethodList = $this->getMockBuilder(\Magento\Payment\Api\PaymentMethodListInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->paymentMethodInstanceFactory = $this->getMockBuilder(
            \Magento\Payment\Model\Method\InstanceFactory::class
        )->disableOriginalConstructor()->getMock();

        $this->specificationFactoryMock = $this->getMock(
            \Magento\Payment\Model\Checks\SpecificationFactory::class, [], [], '', false
        );
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
        $quoteMock = $this->getMock(\Magento\Quote\Model\Quote::class, [], [], '', false);
        $quoteMock->expects($this->once())->method('getStoreId')->will($this->returnValue($storeId));
        $quoteMock->expects($this->atLeastOnce())
            ->method('getPayment')
            ->will($this->returnValue($this->getMock(\Magento\Quote\Model\Quote\Payment::class, [], [], '', false)));

        $methodInstanceMock = $this->getMock(\Magento\Payment\Model\Method\AbstractMethod::class, [], [], '', false);
        $methodInstanceMock->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);

        $compositeMock = $this->getMock(\Magento\Payment\Model\Checks\Composite::class, [], [], '', false);
        $compositeMock->expects($this->atLeastOnce())
            ->method('isApplicable')
            ->with($methodInstanceMock, $quoteMock)
            ->will($this->returnValue(true));

        $this->specificationFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->with([
                AbstractMethod::CHECK_USE_CHECKOUT,
                AbstractMethod::CHECK_USE_FOR_COUNTRY,
                AbstractMethod::CHECK_USE_FOR_CURRENCY,
                AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX
            ])
            ->will($this->returnValue($compositeMock));

        $methodMock = $this->getMockForAbstractClass(\Magento\Payment\Api\Data\PaymentMethodInterface::class);
        $this->paymentMethodList->expects($this->once())
            ->method('getActiveList')
            ->willReturn([$methodMock]);
        $this->paymentMethodInstanceFactory->expects($this->once())
            ->method('create')
            ->willReturn($methodInstanceMock);

        $methodInstanceMock->expects($this->atLeastOnce())
            ->method('setInfoInstance')
            ->with($this->getMock(\Magento\Quote\Model\Quote\Payment::class, [], [], '', false))
            ->will($this->returnSelf());

        $this->assertEquals([$methodInstanceMock], $this->methodList->getAvailableMethods($quoteMock));
    }
}
