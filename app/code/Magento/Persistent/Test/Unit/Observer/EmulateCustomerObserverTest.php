<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Test\Unit\Observer;

class EmulateCustomerObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Persistent\Observer\EmulateCustomerObserver
     */
    protected $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $customerRepositoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $customerSessionMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $sessionHelperMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $helperMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $observerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $addressRepositoryMock;

    protected function setUp(): void
    {
        $this->customerRepositoryMock = $this->getMockForAbstractClass(
            \Magento\Customer\Api\CustomerRepositoryInterface::class,
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
        $this->customerSessionMock = $this->createPartialMock(\Magento\Customer\Model\Session::class, $methods);
        $this->sessionHelperMock = $this->createMock(\Magento\Persistent\Helper\Session::class);
        $this->helperMock = $this->createMock(\Magento\Persistent\Helper\Data::class);
        $this->observerMock = $this->createMock(\Magento\Framework\Event\Observer::class);
        $this->addressRepositoryMock = $this->createMock(\Magento\Customer\Api\AddressRepositoryInterface::class);
        $this->model = new \Magento\Persistent\Observer\EmulateCustomerObserver(
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
        $sessionMock = $this->createPartialMock(
            \Magento\Persistent\Model\Session::class,
            ['getCustomerId', '__wakeUp']
        );
        $methods = ['getCountryId', 'getRegion', 'getRegionId', 'getPostcode'];
        $defaultShippingAddressMock = $this->createPartialMock(\Magento\Customer\Model\Address::class, $methods);
        $defaultBillingAddressMock = $this->createPartialMock(\Magento\Customer\Model\Address::class, $methods);
        $customerMock = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
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
            ->with($customerId)
            ->willReturnSelf();
        $this->customerSessionMock
            ->expects($this->once())
            ->method('setCustomerGroupId')
            ->with($customerGroupId)->willReturnSelf();
        $this->customerSessionMock
            ->expects($this->once())
            ->method('setIsCustomerEmulated')
            ->with(true)
            ->willReturnSelf();
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
