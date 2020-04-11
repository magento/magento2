<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Test\Unit\Observer;

use PHPUnit\Framework\TestCase;
use Magento\Persistent\Observer\EmulateCustomerObserver;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Persistent\Helper\Data;
use Magento\Framework\Event\Observer;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Model\Address;
use Magento\Customer\Api\Data\CustomerInterface;

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
        $methods = [
            'setDefaultTaxShippingAddress',
            'setDefaultTaxBillingAddress',
            'setCustomerId',
            'setCustomerGroupId',
            'isLoggedIn',
            'setIsCustomerEmulated',
            '__wakeUp'
        ];
        $this->customerSessionMock = $this->createPartialMock(Session::class, $methods);
        $this->sessionHelperMock = $this->createMock(\Magento\Persistent\Helper\Session::class);
        $this->helperMock = $this->createMock(Data::class);
        $this->observerMock = $this->createMock(Observer::class);
        $this->addressRepositoryMock = $this->createMock(AddressRepositoryInterface::class);
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
            ->will($this->returnValue(false));
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
            ->will($this->returnValue(true));
        $this->helperMock->expects($this->once())->method('isShoppingCartPersist')->will($this->returnValue(false));
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
        $sessionMock = $this->createPartialMock(
            \Magento\Persistent\Model\Session::class,
            ['getCustomerId', '__wakeUp']
        );
        $methods = ['getCountryId', 'getRegion', 'getRegionId', 'getPostcode'];
        $defaultShippingAddressMock = $this->createPartialMock(Address::class, $methods);
        $defaultBillingAddressMock = $this->createPartialMock(Address::class, $methods);
        $customerMock = $this->createMock(CustomerInterface::class);
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
        $defaultBillingAddressMock->expects($this->once())->method('getCountryId')->willReturn($countryId);
        $defaultBillingAddressMock->expects($this->once())->method('getRegion')->willReturn('California');
        $defaultBillingAddressMock->expects($this->once())->method('getRegionId')->willReturn($regionId);
        $defaultBillingAddressMock->expects($this->once())->method('getPostcode')->willReturn($postcode);
        $defaultShippingAddressMock->expects($this->once())->method('getCountryId')->willReturn($countryId);
        $defaultShippingAddressMock->expects($this->once())->method('getRegion')->willReturn('California');
        $defaultShippingAddressMock->expects($this->once())->method('getRegionId')->willReturn($regionId);
        $defaultShippingAddressMock->expects($this->once())->method('getPostcode')->willReturn($postcode);
        $this->helperMock
            ->expects($this->once())
            ->method('canProcess')
            ->with($this->observerMock)
            ->will($this->returnValue(true));
        $this->helperMock->expects($this->once())->method('isShoppingCartPersist')->will($this->returnValue(true));
        $this->sessionHelperMock->expects($this->once())->method('isPersistent')->will($this->returnValue(true));
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->will($this->returnValue(false));
        $this->sessionHelperMock->expects($this->once())->method('getSession')->will($this->returnValue($sessionMock));
        $sessionMock->expects($this->once())->method('getCustomerId')->will($this->returnValue($customerId));
        $this->customerRepositoryMock
            ->expects($this->once())
            ->method('getById')
            ->with(1)
            ->will($this->returnValue($customerMock));
        $customerMock->expects($this->once())->method('getId')->will($this->returnValue($customerId));
        $customerMock->expects($this->once())->method('getGroupId')->will($this->returnValue($customerGroupId));
        $this->customerSessionMock
            ->expects($this->once())
            ->method('setCustomerId')
            ->with($customerId)
            ->will($this->returnSelf());
        $this->customerSessionMock
            ->expects($this->once())
            ->method('setCustomerGroupId')
            ->with($customerGroupId)->will($this->returnSelf());
        $this->customerSessionMock
            ->expects($this->once())
            ->method('setIsCustomerEmulated')
            ->with(true)
            ->will($this->returnSelf());
        $this->model->execute($this->observerMock);
    }

    public function testExecuteWhenSessionNotPersist()
    {
        $this->helperMock
            ->expects($this->once())
            ->method('canProcess')
            ->with($this->observerMock)
            ->will($this->returnValue(true));
        $this->helperMock->expects($this->once())->method('isShoppingCartPersist')->will($this->returnValue(true));
        $this->sessionHelperMock->expects($this->once())->method('isPersistent')->will($this->returnValue(true));
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->will($this->returnValue(true));
        $this->customerRepositoryMock
            ->expects($this->never())
            ->method('get');
        $this->model->execute($this->observerMock);
    }
}
