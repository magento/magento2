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
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
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
     * @var ObjectManager
     */
    private $objectManager;

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
     * @var RegionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $regionMock;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
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
        $this->estimateAddressFactoryMock = $this->getMockBuilder(EstimateAddressInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->shippingMethodManagerMock = $this->createMock(ShippingMethodManagementInterface::class);
        $this->quoteRepositoryMock = $this->createMock(CartRepositoryInterface::class);
        $this->quoteMock = $this->createMock(Quote::class);
        $this->customerMock = $this->createMock(CustomerInterface::class);
        $this->addressMock = $this->createMock(AddressInterface::class);
        $this->regionMock = $this->createMock(RegionInterface::class);

        $this->model = $this->objectManager->getObject(
            CollectQuote::class,
            [
                'customerSession' => $this->customerSessionMock,
                'customerRepository' => $this->customerRepositoryMock,
                'addressRepository' => $this->addressRepositoryMock,
                'estimatedAddressFactory' => $this->estimateAddressFactoryMock,
                'shippingMethodManager' => $this->shippingMethodManagerMock,
                'quoteRepository' => $this->quoteRepositoryMock
            ]
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
        $this->regionMock->expects(self::once())
            ->method('getRegion')
            ->willReturn($regionId);
        $this->addressMock->expects(self::once())
            ->method('getRegion')
            ->willReturn($this->regionMock);
        $this->addressRepositoryMock->expects(self::once())
            ->method('getById')
            ->with($defaultAddressId)
            ->willReturn($this->addressMock);
        $this->estimateAddressFactoryMock->expects(self::once())
            ->method('create')
            ->willReturn($this->estimateAddressMock);
        $this->quoteRepositoryMock->expects(self::once())
            ->method('save');

        $this->model->collect($this->quoteMock);
    }
}
