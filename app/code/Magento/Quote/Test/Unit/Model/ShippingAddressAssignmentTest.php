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
use PHPUnit\Framework\MockObject\RuntimeException;
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

    /**
     * @inheritdoc
     */
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
        $this->addressMock = $this->getMockBuilder(Address::class)
            ->addMethods(['setShippingMethod', 'setShippingDescription', 'setCollectShippingRates'])
            ->onlyMethods(['setSaveInAddressBook', 'setSameAsBilling'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->extensionAttributeMock = $this->getCartExtensionMock();

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

    /**
     * @return void
     */
    public function testSetAddressUseForShippingTrue(): void
    {
        $addressId = 1;
        $shippingMethod = 'flatrate_flatrate';
        $shippingDescription = 'Fixed Rate';
        $saveInAddressBook = 1;

        $quoteShippingAddress = $this->getMockBuilder(Address::class)
            ->addMethods(['setShippingMethod', 'getShippingDescription'])
            ->onlyMethods(['getShippingMethod', 'getSaveInAddressBook', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();

        $quoteShippingAddress->expects($this->once())->method('getShippingMethod')
            ->willReturn($shippingMethod);
        $quoteShippingAddress->expects($this->once())->method('getShippingDescription')
            ->willReturn($shippingDescription);
        $quoteShippingAddress->expects($this->once())->method('getSaveInAddressBook')
            ->willReturn($saveInAddressBook);
        $quoteShippingAddress->expects($this->once())->method('getId')->willReturn($addressId);
        $this->quoteMock->expects($this->exactly(4))->method('getShippingAddress')
            ->willReturn($quoteShippingAddress);

        $this->addressMock->expects($this->once())->method('setShippingMethod')
            ->with($shippingMethod)->willReturnSelf();
        $this->addressMock->expects($this->once())->method('setShippingDescription')
            ->with($shippingDescription)->willReturnSelf();
        $this->addressMock->expects($this->once())->method('setSaveInAddressBook')->with($saveInAddressBook)
            ->willReturnSelf();

        $this->quoteMock->expects($this->once())->method('removeAddress')->with($addressId);
        $this->addressMock->expects($this->once())->method('setSameAsBilling')->with(1);
        $this->addressMock->expects($this->once())->method('setCollectShippingRates')->with(true);
        $this->quoteMock->expects($this->once())->method('setShippingAddress')->with($this->addressMock);

        $this->model->setAddress($this->quoteMock, $this->addressMock, true);
    }

    /**
     * @return void
     */
    public function testSetAddressUseForShippingFalse(): void
    {
        $addressMock = $this->getMockForAbstractClass(AddressInterface::class);
        $this->quoteMock->expects($this->once())->method('getShippingAddress')->willReturn($addressMock);
        $addressMock->expects($this->once())->method('setSameAsBilling')->with(0)->willReturnSelf();
        $this->quoteMock->expects($this->once())->method('setShippingAddress')->with($addressMock);
        $this->model->setAddress($this->quoteMock, $this->addressMock, false);
    }

    /**
     * Build cart extension mock.
     *
     * @return MockObject
     */
    private function getCartExtensionMock(): MockObject
    {
        $mockBuilder = $this->getMockBuilder(CartExtension::class);
        try {
            $mockBuilder->addMethods(['setShippingAssignments']);
        } catch (RuntimeException $e) {
            // CartExtension already generated.
        }

        return $mockBuilder->getMock();
    }
}
