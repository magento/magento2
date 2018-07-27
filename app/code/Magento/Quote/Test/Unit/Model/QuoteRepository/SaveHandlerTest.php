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

/**
 * SaveHandler test.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveHandlerTest extends \PHPUnit_Framework_TestCase
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
     * @var QuoteResourceModel|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteResourceModelMock;

    /**
     * @var CartItemPersister|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartItemPersisterMock;

    /**
     * @var BillingAddressPersister|\PHPUnit_Framework_MockObject_MockObject
     */
    private $billingAddressPersisterMock;

    /**
     * @var ShippingAssignmentPersister|\PHPUnit_Framework_MockObject_MockObject
     */
    private $shippingAssignmentPersisterMock;

    /**
     * @var AddressRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addressRepositoryMock;

    /**
     * @var Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteMock;

    /**
     * @var QuoteAddress|\PHPUnit_Framework_MockObject_MockObject
     */
    private $billingAddressMock;

    /**
     * @var CartExtensionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $extensionAttributesMock;

    protected function setUp()
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
            ->disableOriginalConstructor()
            ->getMock();
        $this->extensionAttributesMock = $this->getMockBuilder(CartExtensionInterface::class)
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

    /**
     * Tests save() method for virtual quote.
     */
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

    /**
     * Tests save() method with not existing customer address.
     */
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
     * Create quote item mock.
     *
     * @param bool $isDeleted
     *
     * @return QuoteItem|\PHPUnit_Framework_MockObject_MockObject
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
