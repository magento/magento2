<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model;

class ShippingAddressAssignmentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Quote\Model\ShippingAddressAssignment
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $shippingAssignmentProcessorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $cartExtensionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $addressMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $extensionAttributeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $shippingAssignmentMock;

    public function setUp()
    {
        $this->cartExtensionFactoryMock = $this->getMock(
            \Magento\Quote\Api\Data\CartExtensionFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->shippingAssignmentProcessorMock = $this->getMock(
            \Magento\Quote\Model\Quote\ShippingAssignment\ShippingAssignmentProcessor::class,
            [],
            [],
            '',
            false
        );
        $this->quoteMock = $this->getMock(\Magento\Quote\Model\Quote::class, [], [], '', false);
        $this->addressMock = $this->getMock(\Magento\Quote\Model\Quote\Address::class, [], [], '', false);
        $this->extensionAttributeMock = $this->getMock(
            \Magento\Quote\Api\Data\CartExtension::class,
            ['setShippingAssignments'],
            [],
            '',
            false
        );

        $this->shippingAssignmentMock = $this->getMock(\Magento\Quote\Api\Data\ShippingAssignmentInterface::class);
        //shipping assignment processing
        $this->quoteMock->expects($this->once())->method('getExtensionAttributes')->willReturn(null);
        $this->cartExtensionFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->extensionAttributeMock);
        $this->shippingAssignmentProcessorMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->shippingAssignmentMock);
        $this->extensionAttributeMock
            ->expects($this->once())
            ->method('setShippingAssignments')
            ->with([$this->shippingAssignmentMock])
            ->willReturnSelf();
        $this->quoteMock->expects($this->once())->method('setExtensionAttributes')->with($this->extensionAttributeMock);
        $this->model = new \Magento\Quote\Model\ShippingAddressAssignment(
            $this->cartExtensionFactoryMock,
            $this->shippingAssignmentProcessorMock
        );
    }

    public function testSetAddressUseForShippingTrue()
    {
        $addressId = 1;
        $addressMock = $this->getMock(\Magento\Quote\Api\Data\AddressInterface::class);
        $this->quoteMock->expects($this->once())->method('getShippingAddress')->willReturn($addressMock);
        $addressMock->expects($this->once())->method('getId')->willReturn($addressId);
        $this->addressMock->expects($this->once())->method('setSameAsBilling')->with(1);
        $this->quoteMock->expects($this->once())->method('removeAddress')->with($addressId);
        $this->quoteMock->expects($this->once())->method('setShippingAddress')->with($this->addressMock);
        $this->model->setAddress($this->quoteMock, $this->addressMock, true);
    }

    public function testSetAddressUseForShippingFalse()
    {
        $addressMock = $this->getMock(\Magento\Quote\Api\Data\AddressInterface::class);
        $this->quoteMock->expects($this->once())->method('getShippingAddress')->willReturn($addressMock);
        $addressMock->expects($this->once())->method('setSameAsBilling')->with(0)->willReturnSelf();
        $this->quoteMock->expects($this->once())->method('setShippingAddress')->with($addressMock);
        $this->model->setAddress($this->quoteMock, $this->addressMock, false);
    }
}
