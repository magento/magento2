<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Model\Cart;

use Magento\Checkout\Model\Cart\CollectQuote;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\RegionInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\EstimateAddressInterface;
use Magento\Quote\Api\Data\EstimateAddressInterfaceFactory;
use Magento\Quote\Api\ShippingMethodManagementInterface;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CollectQuoteTest extends TestCase
{
    /**
     * @var CollectQuote
     */
    private $model;

    /**
     * @var CustomerSession|MockObject
     */
    private $customerSessionMock;

    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    private $customerRepositoryMock;

    /**
     * @var AddressRepositoryInterface|MockObject
     */
    private $addressRepositoryMock;

    /**
     * @var EstimateAddressInterfaceFactory|MockObject
     */
    private $estimateAddressFactoryMock;

    /**
     * @var EstimateAddressInterface|MockObject
     */
    private $estimateAddressMock;

    /**
     * @var ShippingMethodManagementInterface|MockObject
     */
    private $shippingMethodManagerMock;

    /**
     * @var CartRepositoryInterface|MockObject
     */
    private $quoteRepositoryMock;

    /**
     * @var Quote|MockObject
     */
    private $quoteMock;

    /**
     * @var CustomerInterface|MockObject
     */
    private $customerMock;

    /**
     * @var AddressInterface|MockObject
     */
    private $addressMock;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->customerSessionMock = $this->createMock(CustomerSession::class);
        $this->customerRepositoryMock = $this->getMockForAbstractClass(
            CustomerRepositoryInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getById']
        );
        $this->addressRepositoryMock = $this->getMockForAbstractClass(AddressRepositoryInterface::class);
        $this->estimateAddressMock = $this->getMockForAbstractClass(EstimateAddressInterface::class);
        $this->estimateAddressFactoryMock =
            $this->createPartialMock(EstimateAddressInterfaceFactory::class, ['create']);
        $this->shippingMethodManagerMock = $this->getMockForAbstractClass(ShippingMethodManagementInterface::class);
        $this->quoteRepositoryMock = $this->getMockForAbstractClass(CartRepositoryInterface::class);
        $this->quoteMock = $this->createMock(Quote::class);
        $this->customerMock = $this->getMockForAbstractClass(CustomerInterface::class);
        $this->addressMock = $this->getMockForAbstractClass(AddressInterface::class);

        $this->model = new CollectQuote(
            $this->customerSessionMock,
            $this->customerRepositoryMock,
            $this->addressRepositoryMock,
            $this->estimateAddressFactoryMock,
            $this->shippingMethodManagerMock,
            $this->quoteRepositoryMock
        );
    }

    /**
     * Test collect method
     */
    public function testCollect()
    {
        $customerId = 1;
        $defaultAddressId = 999;
        $countryId = 'USA';
        $regionId = 'CA';
        $regionMock = $this->getMockForAbstractClass(RegionInterface::class);

        $this->customerSessionMock->expects(self::once())
            ->method('isLoggedIn')
            ->willReturn(true);
        $this->customerSessionMock->expects(self::once())
            ->method('getCustomerId')
            ->willReturn($customerId);
        $this->customerRepositoryMock->expects(self::once())
            ->method('getById')
            ->willReturn($this->customerMock);
        $this->customerMock->expects(self::once())
            ->method('getDefaultShipping')
            ->willReturn($defaultAddressId);
        $this->addressMock->expects(self::once())
            ->method('getCountryId')
            ->willReturn($countryId);
        $regionMock->expects(self::once())
            ->method('getRegion')
            ->willReturn($regionId);
        $this->addressMock->expects(self::once())
            ->method('getRegion')
            ->willReturn($regionMock);
        $this->addressRepositoryMock->expects(self::once())
            ->method('getById')
            ->with($defaultAddressId)
            ->willReturn($this->addressMock);
        $this->estimateAddressFactoryMock->expects(self::once())
            ->method('create')
            ->willReturn($this->estimateAddressMock);
        $this->quoteRepositoryMock->expects(self::once())
            ->method('save')
            ->with($this->quoteMock);

        $this->model->collect($this->quoteMock);
    }

    /**
     * Test with a not logged in customer
     */
    public function testCollectWhenCustomerIsNotLoggedIn()
    {
        $this->customerSessionMock->expects(self::once())
            ->method('isLoggedIn')
            ->willReturn(false);
        $this->customerRepositoryMock->expects(self::never())
            ->method('getById');

        $this->model->collect($this->quoteMock);
    }
}
