<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model;

class ShippingAddressAssignmentTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Quote\Model\ShippingAddressAssignment
     */
    private $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $shippingAssignmentProcessorMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $cartExtensionFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $quoteMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $addressMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $extensionAttributeMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $shippingAssignmentMock;

    protected function setUp(): void
    {
        $this->cartExtensionFactoryMock = $this->createPartialMock(
            \Magento\Quote\Api\Data\CartExtensionFactory::class,
            ['create']
        );
        $this->shippingAssignmentProcessorMock = $this->createMock(
            \Magento\Quote\Model\Quote\ShippingAssignment\ShippingAssignmentProcessor::class
        );
        $this->quoteMock = $this->createMock(\Magento\Quote\Model\Quote::class);
        $this->addressMock = $this->createMock(\Magento\Quote\Model\Quote\Address::class);
        $this->extensionAttributeMock = $this->createPartialMock(
            \Magento\Quote\Api\Data\CartExtension::class,
            ['setShippingAssignments']
        );

        $this->shippingAssignmentMock = $this->createMock(\Magento\Quote\Api\Data\ShippingAssignmentInterface::class);
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
        $addressMock = $this->createMock(\Magento\Quote\Api\Data\AddressInterface::class);
        $this->quoteMock->expects($this->once())->method('getShippingAddress')->willReturn($addressMock);
        $addressMock->expects($this->once())->method('getId')->willReturn($addressId);
        $this->addressMock->expects($this->once())->method('setSameAsBilling')->with(1);
        $this->quoteMock->expects($this->once())->method('removeAddress')->with($addressId);
        $this->quoteMock->expects($this->once())->method('setShippingAddress')->with($this->addressMock);
        $this->model->setAddress($this->quoteMock, $this->addressMock, true);
    }

    public function testSetAddressUseForShippingFalse()
    {
        $addressMock = $this->createMock(\Magento\Quote\Api\Data\AddressInterface::class);
        $this->quoteMock->expects($this->once())->method('getShippingAddress')->willReturn($addressMock);
        $addressMock->expects($this->once())->method('setSameAsBilling')->with(0)->willReturnSelf();
        $this->quoteMock->expects($this->once())->method('setShippingAddress')->with($addressMock);
        $this->model->setAddress($this->quoteMock, $this->addressMock, false);
    }
}
