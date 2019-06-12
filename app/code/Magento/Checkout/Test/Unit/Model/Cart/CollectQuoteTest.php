<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Model\Cart;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\RegionInterface;
use Magento\Checkout\Model\Cart\CollectQuote;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\EstimateAddressInterface;
use Magento\Quote\Api\Data\EstimateAddressInterfaceFactory;
use Magento\Quote\Api\ShippingMethodManagementInterface;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\TestCase;

/**
 * Class CollectQuoteTest
 */
class CollectQuoteTest extends TestCase
{
    /**
     * @var CollectQuote
     */
    private $model;

    /**
     * @var CustomerSession|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerSessionMock;

    /**
     * @var CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepositoryMock;

    /**
     * @var AddressRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addressRepositoryMock;

    /**
     * @var EstimateAddressInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $estimateAddressFactoryMock;

    /**
     * @var EstimateAddressInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $estimateAddressMock;

    /**
     * @var ShippingMethodManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $shippingMethodManagerMock;

    /**
     * @var CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepositoryMock;

    /**
     * @var Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteMock;

    /**
     * @var CustomerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerMock;

    /**
     * @var AddressInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addressMock;

    /**
     * Set up
     */
    protected function setUp()
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
        $this->addressRepositoryMock = $this->createMock(AddressRepositoryInterface::class);
        $this->estimateAddressMock = $this->createMock(EstimateAddressInterface::class);
        $this->estimateAddressFactoryMock =
            $this->createPartialMock(EstimateAddressInterfaceFactory::class, ['create']);
        $this->shippingMethodManagerMock = $this->createMock(ShippingMethodManagementInterface::class);
        $this->quoteRepositoryMock = $this->createMock(CartRepositoryInterface::class);
        $this->quoteMock = $this->createMock(Quote::class);
        $this->customerMock = $this->createMock(CustomerInterface::class);
        $this->addressMock = $this->createMock(AddressInterface::class);

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
        $regionMock = $this->createMock(RegionInterface::class);

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
