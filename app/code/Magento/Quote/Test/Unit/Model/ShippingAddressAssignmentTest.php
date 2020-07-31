<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model;

use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartExtension;
use Magento\Quote\Api\Data\CartExtensionFactory;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\ShippingAssignment\ShippingAssignmentProcessor;
use Magento\Quote\Model\ShippingAddressAssignment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ShippingAddressAssignmentTest extends TestCase
{
    /**
     * @var ShippingAddressAssignment
     */
    private $model;

    /**
     * @var MockObject
     */
    private $shippingAssignmentProcessorMock;

    /**
     * @var MockObject
     */
    private $cartExtensionFactoryMock;

    /**
     * @var MockObject
     */
    private $quoteMock;

    /**
     * @var MockObject
     */
    private $addressMock;

    /**
     * @var MockObject
     */
    private $extensionAttributeMock;

    /**
     * @var MockObject
     */
    private $shippingAssignmentMock;

    protected function setUp(): void
    {
        $this->cartExtensionFactoryMock = $this->createPartialMock(
            CartExtensionFactory::class,
            ['create']
        );
        $this->shippingAssignmentProcessorMock = $this->createMock(
            ShippingAssignmentProcessor::class
        );
        $this->quoteMock = $this->createMock(Quote::class);
        $this->addressMock = $this->createMock(Address::class);
        $this->extensionAttributeMock = $this->getMockBuilder(CartExtension::class)
            ->addMethods(['setShippingAssignments'])
            ->getMock();

        $this->shippingAssignmentMock = $this->getMockForAbstractClass(ShippingAssignmentInterface::class);
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
        $this->model = new ShippingAddressAssignment(
            $this->cartExtensionFactoryMock,
            $this->shippingAssignmentProcessorMock
        );
    }

    public function testSetAddressUseForShippingTrue()
    {
        $addressId = 1;
        $addressMock = $this->getMockForAbstractClass(AddressInterface::class);
        $this->quoteMock->expects($this->once())->method('getShippingAddress')->willReturn($addressMock);
        $addressMock->expects($this->once())->method('getId')->willReturn($addressId);
        $this->addressMock->expects($this->once())->method('setSameAsBilling')->with(1);
        $this->quoteMock->expects($this->once())->method('removeAddress')->with($addressId);
        $this->quoteMock->expects($this->once())->method('setShippingAddress')->with($this->addressMock);
        $this->model->setAddress($this->quoteMock, $this->addressMock, true);
    }

    public function testSetAddressUseForShippingFalse()
    {
        $addressMock = $this->getMockForAbstractClass(AddressInterface::class);
        $this->quoteMock->expects($this->once())->method('getShippingAddress')->willReturn($addressMock);
        $addressMock->expects($this->once())->method('setSameAsBilling')->with(0)->willReturnSelf();
        $this->quoteMock->expects($this->once())->method('setShippingAddress')->with($addressMock);
        $this->model->setAddress($this->quoteMock, $this->addressMock, false);
    }
}
