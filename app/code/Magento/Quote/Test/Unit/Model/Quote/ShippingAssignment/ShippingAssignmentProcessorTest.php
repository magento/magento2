<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\Quote\ShippingAssignment;

use Magento\Quote\Model\Quote\ShippingAssignment\ShippingAssignmentProcessor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Quote\Model\ShippingAssignmentFactory;
use Magento\Quote\Model\Quote\ShippingAssignment\ShippingProcessor;
use Magento\Quote\Model\Quote\Item\CartItemPersister;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\Quote\Api\Data\ShippingInterface;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Framework\Exception\NoSuchEntityException;

class ShippingAssignmentProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ShippingAssignmentProcessor
     */
    private $shippingAssignmentProcessor;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var ShippingAssignmentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $shippingAssignmentFactoryMock;

    /**
     * @var ShippingProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $shippingProcessorMock;

    /**
     * @var CartItemPersister|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartItemPersisterMock;

    /**
     * @var AddressRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addressRepositoryMock;

    /**
     * @var Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteMock;

    /**
     * @var ShippingAssignmentInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $shippingAssignmentMock;

    /**
     * @var QuoteAddress|\PHPUnit_Framework_MockObject_MockObject
     */
    private $shippingAddressMock;

    /**
     * @var ShippingInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $shippingMock;

    protected function setUp()
    {
        $this->shippingAssignmentFactoryMock = $this->getMockBuilder(ShippingAssignmentFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->shippingProcessorMock = $this->getMockBuilder(ShippingProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cartItemPersisterMock = $this->getMockBuilder(CartItemPersister::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->addressRepositoryMock = $this->getMockBuilder(AddressRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->shippingAssignmentMock = $this->getMockBuilder(ShippingAssignmentInterface::class)
            ->getMockForAbstractClass();
        $this->shippingAddressMock = $this->getMockBuilder(QuoteAddress::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->shippingMock = $this->getMockBuilder(ShippingInterface::class)
            ->getMockForAbstractClass();

        $this->quoteMock->expects(static::any())
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddressMock);
        $this->shippingAssignmentMock->expects(static::any())
            ->method('getShipping')
            ->willReturn($this->shippingMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->shippingAssignmentProcessor = $this->objectManagerHelper->getObject(
            ShippingAssignmentProcessor::class,
            [
                'shippingAssignmentFactory' => $this->shippingAssignmentFactoryMock,
                'shippingProcessor' => $this->shippingProcessorMock,
                'cartItemPersister' => $this->cartItemPersisterMock,
                'addressRepository' => $this->addressRepositoryMock
            ]
        );
    }

    public function testSaveWithDeletedCartItems()
    {
        $quoteItemId = 1;

        $this->shippingAssignmentMock->expects(static::once())
            ->method('getItems')
            ->willReturn([$this->createQuoteItemMock($quoteItemId, true)]);
        $this->quoteMock->expects(static::atLeastOnce())
            ->method('getItemById')
            ->with($quoteItemId)
            ->willReturn(null);
        $this->cartItemPersisterMock->expects(static::never())
            ->method('save');
        $this->shippingAddressMock->expects(static::atLeastOnce())
            ->method('getCustomerAddressId')
            ->willReturn(null);
        $this->addressRepositoryMock->expects(static::never())
            ->method('getById');
        $this->shippingProcessorMock->expects(static::once())
            ->method('save')
            ->with($this->shippingMock, $this->quoteMock);

        $this->shippingAssignmentProcessor->save($this->quoteMock, $this->shippingAssignmentMock);
    }

    public function testSaveWithNotExistingCustomerAddress()
    {
        $customerAddressId = 11;

        $this->shippingAssignmentMock->expects(static::atLeastOnce())
            ->method('getItems')
            ->willReturn([]);
        $this->shippingAddressMock->expects(static::atLeastOnce())
            ->method('getCustomerAddressId')
            ->willReturn($customerAddressId);
        $this->addressRepositoryMock->expects(static::once())
            ->method('getById')
            ->with($customerAddressId)
            ->willThrowException(new NoSuchEntityException());
        $this->shippingAddressMock->expects(static::once())
            ->method('setCustomerAddressId')
            ->with(null)
            ->willReturn($this->shippingAddressMock);
        $this->shippingProcessorMock->expects(static::once())
            ->method('save')
            ->with($this->shippingMock, $this->quoteMock);

        $this->shippingAssignmentProcessor->save($this->quoteMock, $this->shippingAssignmentMock);
    }

    /**
     * Create quote item mock
     *
     * @param int|string $id
     * @param bool $isDeleted
     * @return QuoteItem|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createQuoteItemMock($id, $isDeleted)
    {
        $quoteItemMock = $this->getMockBuilder(QuoteItem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $quoteItemMock->expects(static::any())
            ->method('getItemId')
            ->willReturn($id);
        $quoteItemMock->expects(static::any())
            ->method('isDeleted')
            ->willReturn($isDeleted);

        return $quoteItemMock;
    }
}
