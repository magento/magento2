<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\QuoteRepository;

use Magento\Quote\Model\QuoteRepository\SaveHandler;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResourceModel;
use Magento\Quote\Model\Quote\Item\CartItemPersister;
use Magento\Quote\Model\Quote\Address\BillingAddressPersister;
use Magento\Quote\Model\Quote\ShippingAssignment\ShippingAssignmentPersister;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\Quote\Api\Data\CartExtensionInterface;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Framework\Exception\NoSuchEntityException;

class SaveHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SaveHandler
     */
    private $saveHandler;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var QuoteResourceModel|\PHPUnit\Framework\MockObject\MockObject
     */
    private $quoteResourceModelMock;

    /**
     * @var CartItemPersister|\PHPUnit\Framework\MockObject\MockObject
     */
    private $cartItemPersisterMock;

    /**
     * @var BillingAddressPersister|\PHPUnit\Framework\MockObject\MockObject
     */
    private $billingAddressPersisterMock;

    /**
     * @var ShippingAssignmentPersister|\PHPUnit\Framework\MockObject\MockObject
     */
    private $shippingAssignmentPersisterMock;

    /**
     * @var AddressRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $addressRepositoryMock;

    /**
     * @var Quote|\PHPUnit\Framework\MockObject\MockObject
     */
    private $quoteMock;

    /**
     * @var QuoteAddress|\PHPUnit\Framework\MockObject\MockObject
     */
    private $billingAddressMock;

    /**
     * @var CartExtensionInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $extensionAttributesMock;

    protected function setUp(): void
    {
        $this->quoteResourceModelMock = $this->getMockBuilder(QuoteResourceModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cartItemPersisterMock = $this->getMockBuilder(CartItemPersister::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->billingAddressPersisterMock = $this->getMockBuilder(BillingAddressPersister::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->shippingAssignmentPersisterMock = $this->getMockBuilder(ShippingAssignmentPersister::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->addressRepositoryMock = $this->getMockBuilder(AddressRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getItems', 'setLastAddedItem', 'getBillingAddress', 'getExtensionAttributes', 'isVirtual',
                    'collectTotals'
                ]
            )
            ->getMock();
        $this->billingAddressMock = $this->getMockBuilder(QuoteAddress::class)
            ->setMethods(['getCustomerAddress', 'getCustomerAddressId', 'setCustomerAddressId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->extensionAttributesMock = $this->getMockBuilder(CartExtensionInterface::class)
            ->setMethods(['getShippingAssignments'])
            ->getMockForAbstractClass();

        $this->quoteMock->expects(static::any())
            ->method('getBillingAddress')
            ->willReturn($this->billingAddressMock);
        $this->quoteMock->expects(static::any())
            ->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributesMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->saveHandler = $this->objectManagerHelper->getObject(
            SaveHandler::class,
            [
                'quoteResource' => $this->quoteResourceModelMock,
                'cartItemPersister' => $this->cartItemPersisterMock,
                'billingAddressPersister' => $this->billingAddressPersisterMock,
                'shippingAssignmentPersister' => $this->shippingAssignmentPersisterMock,
                'addressRepository' => $this->addressRepositoryMock
            ]
        );
    }

    public function testSaveForVirtualQuote()
    {
        $quoteItemMock = $this->createQuoteItemMock(false);
        
        $this->quoteMock->expects(static::atLeastOnce())
            ->method('getItems')
            ->willReturn([$quoteItemMock]);
        $this->cartItemPersisterMock->expects(static::once())
            ->method('save')
            ->with($this->quoteMock, $quoteItemMock)
            ->willReturn($quoteItemMock);
        $this->quoteMock->expects(static::once())
            ->method('setLastAddedItem')
            ->with($quoteItemMock)
            ->willReturnSelf();
        $this->billingAddressMock->expects(static::atLeastOnce())
            ->method('getCustomerAddressId')
            ->willReturn(null);
        $this->billingAddressMock->expects(static::never())
            ->method('getCustomerAddress');
        $this->billingAddressPersisterMock->expects(static::once())
            ->method('save')
            ->with($this->quoteMock, $this->billingAddressMock);
        $this->quoteMock->expects(static::atLeastOnce())
            ->method('isVirtual')
            ->willReturn(true);
        $this->extensionAttributesMock->expects(static::never())
            ->method('getShippingAssignments');
        $this->quoteMock->expects(static::atLeastOnce())
            ->method('collectTotals')
            ->willReturnSelf();
        $this->quoteResourceModelMock->expects(static::once())
            ->method('save')
            ->with($this->quoteMock)
            ->willReturnSelf();
        
        $this->assertSame($this->quoteMock, $this->saveHandler->save($this->quoteMock));
    }

    public function testSaveWithNotExistingCustomerAddress()
    {
        $customerAddressId = 5;

        $this->quoteMock->expects(static::atLeastOnce())
            ->method('getItems')
            ->willReturn([]);
        $this->quoteMock->expects(static::never())
            ->method('setLastAddedItem');
        $this->billingAddressMock->expects(static::atLeastOnce())
            ->method('getCustomerAddressId')
            ->willReturn($customerAddressId);
        $this->addressRepositoryMock->expects(static::once())
            ->method('getById')
            ->with($customerAddressId)
            ->willThrowException(new NoSuchEntityException());
        $this->billingAddressMock->expects(static::once())
            ->method('setCustomerAddressId')
            ->willReturn(null);
        $this->billingAddressPersisterMock->expects(static::once())
            ->method('save')
            ->with($this->quoteMock, $this->billingAddressMock);
        $this->quoteMock->expects(static::atLeastOnce())
            ->method('isVirtual')
            ->willReturn(true);
        $this->extensionAttributesMock->expects(static::never())
            ->method('getShippingAssignments');
        $this->quoteMock->expects(static::atLeastOnce())
            ->method('collectTotals')
            ->willReturnSelf();
        $this->quoteResourceModelMock->expects(static::once())
            ->method('save')
            ->with($this->quoteMock)
            ->willReturnSelf();

        $this->assertSame($this->quoteMock, $this->saveHandler->save($this->quoteMock));
    }

    /**
     * Create quote item mock
     *
     * @param bool $isDeleted
     * @return QuoteItem|\PHPUnit\Framework\MockObject\MockObject
     */
    private function createQuoteItemMock($isDeleted)
    {
        $quoteItemMock = $this->getMockBuilder(QuoteItem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $quoteItemMock->expects(static::any())
            ->method('isDeleted')
            ->willReturn($isDeleted);

        return $quoteItemMock;
    }
}
