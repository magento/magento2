<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Persistent\Test\Unit\Model;

use Magento\Checkout\Model\Session;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\GroupManagement;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Persistent\Helper\Data;
use Magento\Persistent\Model\QuoteManager;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartExtensionFactory;
use Magento\Quote\Api\Data\CartExtensionInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\ShippingAssignment\ShippingAssignmentProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\RuntimeException;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuoteManagerTest extends TestCase
{
    /**
     * @var QuoteManager
     */
    protected $model;

    /**
     * @var \Magento\Persistent\Helper\Session|MockObject
     */
    protected $persistentSessionMock;

    /**
     * @var Data|MockObject
     */
    protected $persistentDataMock;

    /**
     * @var Session|MockObject
     */
    protected $checkoutSessionMock;

    /**
     * @var Quote|MockObject
     */
    protected $quoteMock;

    /**
     * @var MockObject
     */
    protected $sessionMock;

    /**
     * @var MockObject
     */
    protected $abstractCollectionMock;

    /**
     * @var CartRepositoryInterface|MockObject
     */
    protected $quoteRepositoryMock;

    /**
     * @var CartExtensionFactory|MockObject
     */
    private $cartExtensionFactory;

    /**
     * @var ShippingAssignmentProcessor|MockObject
     */
    private $shippingAssignmentProcessor;

    /**
     * @var CustomerInterfaceFactory|MockObject
     */
    private $customerDataFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->persistentSessionMock = $this->createMock(\Magento\Persistent\Helper\Session::class);
        $this->sessionMock =
            $this->getMockBuilder(\Magento\Persistent\Model\Session::class)->addMethods([
                'setLoadInactive',
                'setCustomerData',
                'clearQuote',
                'clearStorage',
                'getQuote'
            ])
                ->onlyMethods(['removePersistentCookie'])
                ->disableOriginalConstructor()
                ->getMock();
        $this->persistentDataMock = $this->createMock(Data::class);
        $this->checkoutSessionMock = $this->createMock(Session::class);

        $this->abstractCollectionMock =
            $this->createMock(AbstractCollection::class);

        $this->quoteRepositoryMock = $this->getMockForAbstractClass(CartRepositoryInterface::class);
        $this->quoteMock = $this->getMockBuilder(Quote::class)
            ->addMethods([
                'getIsPersistent',
                'setCustomerId',
                'setCustomerEmail',
                'setCustomerFirstname',
                'setCustomerLastname',
                'setCustomerGroupId',
                'setIsPersistent',
                'getCustomerId'
            ])
            ->onlyMethods([
                'getId',
                'getPaymentsCollection',
                'getAddressesCollection',
                'setIsActive',
                'getShippingAddress',
                'getBillingAddress',
                'collectTotals',
                'removeAllAddresses',
                'getIsActive',
                'isVirtual',
                'getItemsQty',
                'getExtensionAttributes',
                'setExtensionAttributes',
                '__wakeup',
                'setCustomer',
                'getCustomer'
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $this->cartExtensionFactory = $this->createPartialMock(CartExtensionFactory::class, ['create']);
        $this->shippingAssignmentProcessor = $this->createPartialMock(ShippingAssignmentProcessor::class, ['create']);
        $this->customerDataFactory = $this->createMock(CustomerInterfaceFactory::class);

        $this->model = new QuoteManager(
            $this->persistentSessionMock,
            $this->persistentDataMock,
            $this->checkoutSessionMock,
            $this->quoteRepositoryMock,
            $this->cartExtensionFactory,
            $this->shippingAssignmentProcessor,
            $this->customerDataFactory
        );
    }

    /**
     * @return void
     */
    public function testSetGuestWithEmptyQuote(): void
    {
        $this->checkoutSessionMock->expects($this->once())
            ->method('getQuote')->willReturn(null);
        $this->quoteMock->expects($this->never())->method('getId');

        $this->persistentSessionMock->expects($this->once())
            ->method('getSession')->willReturn($this->sessionMock);
        $this->sessionMock->expects($this->once())
            ->method('removePersistentCookie')->willReturn($this->sessionMock);

        $this->model->setGuest(false);
    }

    /**
     * @return void
     */
    public function testSetGuestWithEmptyQuoteId(): void
    {
        $this->checkoutSessionMock->expects($this->once())
            ->method('getQuote')->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())->method('getId')->willReturn(null);
        $this->persistentDataMock->expects($this->never())->method('isShoppingCartPersist');

        $this->persistentSessionMock->expects($this->once())
            ->method('getSession')->willReturn($this->sessionMock);
        $this->sessionMock->expects($this->once())
            ->method('removePersistentCookie')->willReturn($this->sessionMock);

        $this->model->setGuest(false);
    }

    /**
     * @return void
     */
    public function testSetGuestWhenShoppingCartAndQuoteAreNotPersistent(): void
    {
        $this->checkoutSessionMock->expects($this->once())
            ->method('getQuote')->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())->method('getId')->willReturn(11);
        $this->persistentDataMock->expects($this->once())
            ->method('isShoppingCartPersist')->willReturn(false);
        $this->quoteMock->expects($this->once())->method('getIsPersistent')->willReturn(false);
        $this->checkoutSessionMock->expects($this->once())
            ->method('clearQuote')->willReturn($this->checkoutSessionMock);
        $this->checkoutSessionMock->expects($this->once())->method('clearStorage');
        $this->quoteMock->expects($this->never())->method('getPaymentsCollection');

        $this->model->setGuest(true);
    }

    /**
     * @return void
     */
    public function testSetGuest(): void
    {
        $customerId = 22;
        $this->checkoutSessionMock->expects($this->once())
            ->method('getQuote')->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())->method('getId')->willReturn(11);
        $this->persistentDataMock->expects($this->never())->method('isShoppingCartPersist');
        $this->quoteMock->expects($this->once())
            ->method('getPaymentsCollection')->willReturn($this->abstractCollectionMock);
        $this->quoteMock->expects($this->once())
            ->method('getAddressesCollection')->willReturn($this->abstractCollectionMock);
        $this->abstractCollectionMock->expects($this->exactly(2))->method('walk')->with('delete');
        $this->quoteMock->expects($this->once())
            ->method('setIsActive')->with(true)->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())
            ->method('setCustomerId')->with(null)->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())
            ->method('setCustomerEmail')->with(null)->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())
            ->method('setCustomerFirstname')->with(null)->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())
            ->method('setCustomerLastname')->with(null)->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())->method('setCustomerGroupId')
            ->with(GroupManagement::NOT_LOGGED_IN_ID)
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())
            ->method('setIsPersistent')->with(false)->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())
            ->method('removeAllAddresses')->willReturn($this->quoteMock);
        $quoteAddressMock = $this->createMock(Address::class);
        $this->quoteMock->expects($this->once())
            ->method('getShippingAddress')->willReturn($quoteAddressMock);
        $this->quoteMock->expects($this->once())
            ->method('getBillingAddress')->willReturn($quoteAddressMock);
        $this->quoteMock->method('getCustomerId')->willReturn($customerId);
        $this->quoteMock->expects($this->once())->method('collectTotals')->willReturn($this->quoteMock);
        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($this->quoteMock);
        $this->persistentSessionMock->expects($this->once())
            ->method('getSession')->willReturn($this->sessionMock);
        $this->sessionMock->expects($this->once())
            ->method('removePersistentCookie')->willReturn($this->sessionMock);
        $this->quoteMock->expects($this->once())->method('isVirtual')->willReturn(false);
        $this->quoteMock->expects($this->once())->method('getItemsQty')->willReturn(1);
        $extensionAttributes = $this->getExtensionAttributesMock();
        $shippingAssignment = $this->createMock(ShippingAssignmentInterface::class);
        $extensionAttributes->expects($this->once())
            ->method('setShippingAssignments')
            ->with([$shippingAssignment]);
        $this->shippingAssignmentProcessor->expects($this->once())
            ->method('create')
            ->with($this->quoteMock)
            ->willReturn($shippingAssignment);
        $this->cartExtensionFactory->expects($this->once())
            ->method('create')
            ->willReturn($extensionAttributes);
        $this->quoteMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn(null);
        $this->quoteMock->expects($this->once())
            ->method('setExtensionAttributes')
            ->with($extensionAttributes);
        $customerMock = $this->createMock(CustomerInterface::class);
        $this->customerDataFactory->method('create')->willReturn($customerMock);
        $this->quoteMock->expects($this->once())
            ->method('setCustomer')
            ->with($customerMock);
        $this->model->setGuest(false);
    }

    /**
     * @return void
     */
    public function testExpireWithActiveQuoteAndCustomerId(): void
    {
        $this->checkoutSessionMock->expects($this->once())
            ->method('setLoadInactive')->willReturn($this->sessionMock);

        $this->sessionMock->expects($this->once())->method('getQuote')->willReturn($this->quoteMock);

        $this->quoteMock->expects($this->once())->method('getIsActive')->willReturn(11);
        $this->quoteMock->expects($this->once())->method('getCustomerId')->willReturn(22);

        $this->checkoutSessionMock->expects($this->once())
            ->method('setCustomerData')->with(null)->willReturn($this->sessionMock);

        $this->sessionMock->expects($this->once())
            ->method('clearQuote')->willReturn($this->sessionMock);
        $this->sessionMock->expects($this->once())
            ->method('clearStorage')->willReturn($this->sessionMock);
        $this->quoteMock->expects($this->never())->method('setIsActive');

        $this->model->expire();
    }

    /**
     * @return void
     */
    public function testExpire(): void
    {
        $this->checkoutSessionMock->expects($this->once())
            ->method('setLoadInactive')->willReturn($this->sessionMock);
        $this->sessionMock->expects($this->once())->method('getQuote')->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())->method('getIsActive')->willReturn(0);
        $this->checkoutSessionMock->expects($this->never())->method('setCustomerData');
        $this->quoteMock->expects($this->once())
            ->method('setIsActive')
            ->with(true)
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())
            ->method('setIsPersistent')
            ->with(false)
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())
            ->method('setCustomerId')
            ->with(null)
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())
            ->method('setCustomerGroupId')
            ->with(GroupManagement::NOT_LOGGED_IN_ID)
            ->willReturn($this->quoteMock);

        $this->model->expire();
    }

    /**
     * @return void
     */
    public function testConvertCustomerCartToGuest(): void
    {
        $quoteId = 1;
        $addressArgs = ['customerAddressId' => null];
        $customerIdArgs = ['customerId' => null];
        $emailArgs = ['email' => null];

        $this->checkoutSessionMock->expects($this->once())
            ->method('getQuoteId')->willReturn($quoteId);
        $this->quoteMock->expects($this->once())->method('getId')->willReturn($quoteId);
        $this->quoteRepositoryMock->expects($this->once())->method('get')->with($quoteId)->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())
            ->method('setIsActive')->with(true)->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())
            ->method('setCustomerId')->with(null)->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())
            ->method('setCustomerEmail')->with(null)->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())
            ->method('setCustomerFirstname')->with(null)->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())
            ->method('setCustomerLastname')->with(null)->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->never())->method('setCustomerGroupId')
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())
            ->method('setIsPersistent')->with(false)->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->exactly(3))
            ->method('getAddressesCollection')->willReturn($this->abstractCollectionMock);
        $customerMock = $this->createMock(CustomerInterface::class);
        $customerMock->expects($this->once())
            ->method('setId')
            ->with(null)
            ->willReturnSelf();
        $this->quoteMock->expects($this->once())
            ->method('getCustomer')->willReturn($customerMock);
        $this->abstractCollectionMock->expects($this->exactly(3))->method('walk')->with(
            $this->logicalOr(
                $this->equalTo('setCustomerAddressId'),
                $this->equalTo($addressArgs),
                $this->equalTo('setCustomerId'),
                $this->equalTo($customerIdArgs),
                $this->equalTo('setEmail'),
                $this->equalTo($emailArgs)
            )
        );
        $this->quoteMock->expects($this->once())->method('collectTotals')->willReturn($this->quoteMock);
        $this->persistentSessionMock->expects($this->once())
            ->method('getSession')->willReturn($this->sessionMock);
        $this->sessionMock->expects($this->once())
            ->method('removePersistentCookie')->willReturn($this->sessionMock);
        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($this->quoteMock);

        $this->model->convertCustomerCartToGuest();
    }

    /**
     * @return void
     */
    public function testConvertCustomerCartToGuestWithEmptyQuote(): void
    {
        $this->checkoutSessionMock->expects($this->once())
            ->method('getQuoteId')->willReturn(null);
        $this->quoteRepositoryMock->expects($this->once())->method('get')->with(null)->willReturn(null);
        $this->model->convertCustomerCartToGuest();
    }

    /**
     * @return void
     */
    public function testConvertCustomerCartToGuestWithEmptyQuoteId(): void
    {
        $this->checkoutSessionMock->expects($this->once())
            ->method('getQuoteId')->willReturn(1);
        $quoteWithNoId = $this->quoteMock = $this->createMock(Quote::class);
        $quoteWithNoId->expects($this->once())->method('getId')->willReturn(null);
        $this->quoteRepositoryMock->expects($this->once())->method('get')->with(1)->willReturn($quoteWithNoId);
        $this->quoteMock->expects($this->once())->method('getId')->willReturn(1);
        $this->model->convertCustomerCartToGuest();
    }

    /**
     * Build CartExtensionInterface mock.
     *
     * @return MockObject
     */
    private function getExtensionAttributesMock(): MockObject
    {
        $extensionMockBuilder = $this->getMockBuilder(CartExtensionInterface::class);
        try {
            $extensionMockBuilder->addMethods(['setShippingAssignments']);
        } catch (RuntimeException $e) {
            // do nothing as CartExtensionInterface already generated and has 'setShippingAssignments' method.
        }

        return $extensionMockBuilder->getMockForAbstractClass();
    }
}
