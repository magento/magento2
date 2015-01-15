<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Model;

class MethodListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MethodList
     */
    protected $methodList;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $specificationFactoryMock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->paymentHelperMock = $this->getMock('\Magento\Payment\Helper\Data', [], [], '', false);
        $this->specificationFactoryMock = $this->getMock(
            '\Magento\Payment\Model\Checks\SpecificationFactory', [], [], '', false
        );
        $this->methodList = $this->objectManager->getObject(
            'Magento\Payment\Model\MethodList',
            [
                'paymentHelper' => $this->paymentHelperMock,
                'specificationFactory' => $this->specificationFactoryMock
            ]
        );
    }

    public function testGetAvailableMethods()
    {
        $storeId = 1;
        $quoteMock = $this->getMock('\Magento\Sales\Model\Quote', [], [], '', false);
        $quoteMock->expects($this->once())->method('getStoreId')->will($this->returnValue($storeId));
        $quoteMock->expects($this->atLeastOnce())
            ->method('getPayment')
            ->will($this->returnValue($this->getMock('\Magento\Sales\Model\Quote\Payment', [], [], '', false)));

        $methodMock = $this->getMock('Magento\Payment\Model\Method\AbstractMethod', ['setInfoInstance'], [], '', false);

        $compositeMock = $this->getMock('\Magento\Payment\Model\Checks\Composite', [], [], '', false);
        $compositeMock->expects($this->atLeastOnce())
            ->method('isApplicable')
            ->with($methodMock, $quoteMock)
            ->will($this->returnValue(true));

        $this->specificationFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->will($this->returnValue($compositeMock));

        $storeMethods = [$methodMock];

        $this->paymentHelperMock->expects($this->once())
            ->method('getStoreMethods')
            ->with($storeId, $quoteMock)
            ->will($this->returnValue($storeMethods));

        $methodMock->expects($this->atLeastOnce())
            ->method('setInfoInstance')
            ->with($this->getMock('\Magento\Sales\Model\Quote\Payment', [], [], '', false))
            ->will($this->returnSelf());

        $this->assertEquals([$methodMock], $this->methodList->getAvailableMethods($quoteMock));
    }
}
