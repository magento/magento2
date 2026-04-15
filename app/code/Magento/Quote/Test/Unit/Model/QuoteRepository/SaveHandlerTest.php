<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\QuoteRepository;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Quote\Api\Data\CartExtensionInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\Quote\Model\Quote\Address\BillingAddressPersister;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Quote\Model\Quote\Item\CartItemPersister;
use Magento\Quote\Model\Quote\ShippingAssignment\ShippingAssignmentPersister;
use Magento\Quote\Model\QuoteRepository\SaveHandler;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResourceModel;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveHandlerTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var SaveHandler
     */
    private $saveHandler;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var QuoteResourceModel|MockObject
     */
    private $quoteResourceModelMock;

    /**
     * @var CartItemPersister|MockObject
     */
    private $cartItemPersisterMock;

    /**
     * @var BillingAddressPersister|MockObject
     */
    private $billingAddressPersisterMock;

    /**
     * @var ShippingAssignmentPersister|MockObject
     */
    private $shippingAssignmentPersisterMock;

    /**
     * @var AddressRepositoryInterface|MockObject
     */
    private $addressRepositoryMock;

    /**
     * @var Quote|MockObject
     */
    private $quoteMock;

    /**
     * @var QuoteAddress|MockObject
     */
    private $billingAddressMock;

    /**
     * @var CartExtensionInterface|MockObject
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
        $this->addressRepositoryMock = $this->createMock(AddressRepositoryInterface::class);
        $this->quoteMock = $this->createPartialMockWithReflection(
            Quote::class,
            [
                'setLastAddedItem',
                'getItems',
                'getBillingAddress',
                'getExtensionAttributes',
                'isVirtual',
                'collectTotals'
            ]
        );
        $this->billingAddressMock = $this->createPartialMockWithReflection(
            QuoteAddress::class,
            ['getCustomerAddressId', 'setCustomerAddressId', 'getCustomerAddress']
        );
        $this->extensionAttributesMock = $this->createMock(CartExtensionInterface::class);

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
        // Do not configure getShippingAssignments; not used for virtual quotes
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
        // Do not configure getShippingAssignments; not used for virtual quotes
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
     * @return QuoteItem|MockObject
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
