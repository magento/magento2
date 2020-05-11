<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Persistent\Test\Unit\Observer;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Persistent\Helper\Data;
use Magento\Persistent\Observer\EmulateCustomerObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EmulateCustomerObserverTest extends TestCase
{
    /**
     * @var EmulateCustomerObserver
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $customerRepositoryMock;

    /**
     * @var MockObject
     */
    protected $customerSessionMock;

    /**
     * @var MockObject
     */
    protected $sessionHelperMock;

    /**
     * @var MockObject
     */
    protected $helperMock;

    /**
     * @var MockObject
     */
    protected $observerMock;

    /**
     * @var MockObject
     */
    protected $addressRepositoryMock;

    protected function setUp(): void
    {
        $this->customerRepositoryMock = $this->getMockForAbstractClass(
            CustomerRepositoryInterface::class,
            [],
            '',
            false
        );
        $this->customerSessionMock = $this->getMockBuilder(Session::class)
            ->addMethods(['setDefaultTaxShippingAddress', 'setDefaultTaxBillingAddress', 'setIsCustomerEmulated'])
            ->onlyMethods(['setCustomerId', 'setCustomerGroupId', 'isLoggedIn'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionHelperMock = $this->createMock(\Magento\Persistent\Helper\Session::class);
        $this->helperMock = $this->createMock(Data::class);
        $this->observerMock = $this->createMock(Observer::class);
        $this->addressRepositoryMock = $this->getMockForAbstractClass(AddressRepositoryInterface::class);
        $this->model = new EmulateCustomerObserver(
            $this->sessionHelperMock,
            $this->helperMock,
            $this->customerSessionMock,
            $this->customerRepositoryMock,
            $this->addressRepositoryMock
        );
    }

    public function testExecuteWhenCannotProcessPersistentData()
    {
        $this->helperMock
            ->expects($this->once())
            ->method('canProcess')
            ->with($this->observerMock)
            ->willReturn(false);
        $this->helperMock->expects($this->never())->method('isShoppingCartPersist');
        $this->sessionHelperMock->expects($this->never())->method('isPersistent');
        $this->model->execute($this->observerMock);
    }

    public function testExecuteWhenShoppingCartNotPersist()
    {
        $this->helperMock
            ->expects($this->once())
            ->method('canProcess')
            ->with($this->observerMock)
            ->willReturn(true);
        $this->helperMock->expects($this->once())->method('isShoppingCartPersist')->willReturn(false);
        $this->sessionHelperMock->expects($this->never())->method('isPersistent');
        $this->model->execute($this->observerMock);
    }

    public function testExecuteWhenSessionPersistAndCustomerNotLoggedIn()
    {
        $customerId = 1;
        $customerGroupId = 2;
        $countryId = 3;
        $regionId = 4;
        $postcode = 90210;
        $sessionMock = $this->getMockBuilder(\Magento\Persistent\Model\Session::class)
            ->addMethods(['getCustomerId'])
            ->disableOriginalConstructor()
            ->getMock();
        $defaultShippingAddressMock = $this->getMockBuilder(Address::class)
            ->addMethods(['getCountryId', 'getPostcode'])
            ->onlyMethods(['getRegion', 'getRegionId'])
            ->disableOriginalConstructor()
            ->getMock();
        $defaultBillingAddressMock = $this->getMockBuilder(Address::class)
            ->addMethods(['getCountryId', 'getPostcode'])
            ->onlyMethods(['getRegion', 'getRegionId'])
            ->disableOriginalConstructor()
            ->getMock();
        $customerMock = $this->getMockForAbstractClass(CustomerInterface::class);
        $customerMock
            ->expects($this->once())
            ->method('getDefaultShipping')
            ->willReturn('shippingId');
        $customerMock
            ->expects($this->once())
            ->method('getDefaultBilling')
            ->willReturn('billingId');
        $valueMap = [
            ['shippingId', $defaultShippingAddressMock],
            ['billingId', $defaultBillingAddressMock]
        ];
        $this->addressRepositoryMock->expects($this->any())->method('getById')->willReturnMap($valueMap);
        $this->customerSessionMock
            ->expects($this->once())
            ->method('setDefaultTaxShippingAddress')
            ->with(
                [
                    'country_id' => $countryId,
                    'region_id' => $regionId,
                    'postcode' => $postcode
                ]
            );
        $defaultBillingAddressMock->expects($this->once())
            ->method('getCountryId')->willReturn($countryId);
        $defaultBillingAddressMock->expects($this->once())
            ->method('getRegion')->willReturn('California');
        $defaultBillingAddressMock->expects($this->once())
            ->method('getRegionId')->willReturn($regionId);
        $defaultBillingAddressMock->expects($this->once())
            ->method('getPostcode')->willReturn($postcode);
        $defaultShippingAddressMock->expects($this->once())
            ->method('getCountryId')->willReturn($countryId);
        $defaultShippingAddressMock->expects($this->once())
            ->method('getRegion')->willReturn('California');
        $defaultShippingAddressMock->expects($this->once())
            ->method('getRegionId')->willReturn($regionId);
        $defaultShippingAddressMock->expects($this->once())
            ->method('getPostcode')->willReturn($postcode);
        $this->helperMock
            ->expects($this->once())
            ->method('canProcess')
            ->with($this->observerMock)
            ->willReturn(true);
        $this->helperMock->expects($this->once())->method('isShoppingCartPersist')->willReturn(true);
        $this->sessionHelperMock->expects($this->once())->method('isPersistent')->willReturn(true);
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->sessionHelperMock->expects($this->once())->method('getSession')->willReturn($sessionMock);
        $sessionMock->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $this->customerRepositoryMock
            ->expects($this->once())
            ->method('getById')
            ->with(1)
            ->willReturn($customerMock);
        $customerMock->expects($this->once())->method('getId')->willReturn($customerId);
        $customerMock->expects($this->once())->method('getGroupId')->willReturn($customerGroupId);
        $this->customerSessionMock
            ->expects($this->once())
            ->method('setCustomerId')
            ->with($customerId)->willReturnSelf();
        $this->customerSessionMock
            ->expects($this->once())
            ->method('setCustomerGroupId')
            ->with($customerGroupId)->willReturnSelf();
        $this->customerSessionMock
            ->expects($this->once())
            ->method('setIsCustomerEmulated')
            ->with(true)->willReturnSelf();
        $this->model->execute($this->observerMock);
    }

    public function testExecuteWhenSessionNotPersist()
    {
        $this->helperMock
            ->expects($this->once())
            ->method('canProcess')
            ->with($this->observerMock)
            ->willReturn(true);
        $this->helperMock->expects($this->once())->method('isShoppingCartPersist')->willReturn(true);
        $this->sessionHelperMock->expects($this->once())->method('isPersistent')->willReturn(true);
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $this->customerRepositoryMock
            ->expects($this->never())
            ->method('get');
        $this->model->execute($this->observerMock);
    }
}
