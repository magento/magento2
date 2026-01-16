<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
use Magento\Persistent\Model\Session as PersistentSession;
use Magento\Persistent\Helper\Session as PersistentSessionHelper;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\TestCase;

class EmulateCustomerObserverTest extends TestCase
{

    use MockCreationTrait;

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
        $this->customerRepositoryMock = $this->createMock(
            CustomerRepositoryInterface::class
        );
        $this->customerSessionMock = $this->createPartialMockWithReflection(
            Session::class,
            [
                'setDefaultTaxShippingAddress', 'setDefaultTaxBillingAddress', 'setIsCustomerEmulated',
                'setCustomerId', 'setCustomerGroupId', 'isLoggedIn'
            ]
        );
        $this->sessionHelperMock = $this->createMock(PersistentSessionHelper::class);
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
        $sessionMock = $this->createPartialMockWithReflection(
            PersistentSession::class,
            ['getCustomerId']
        );
        $defaultShippingAddressMock = $this->createPartialMockWithReflection(
            Address::class,
            ['getCountryId', 'getPostcode', 'getRegion', 'getRegionId']
        );
        $defaultBillingAddressMock = $this->createPartialMockWithReflection(
            Address::class,
            ['getCountryId', 'getPostcode', 'getRegion', 'getRegionId']
        );
        $customerMock = $this->createMock(CustomerInterface::class);
        $customerMock
            ->expects($this->once())
            ->method('getDefaultShipping')
            ->willReturn(12345);
        $customerMock
            ->expects($this->once())
            ->method('getDefaultBilling')
            ->willReturn(12346);
        $valueMap = [
            [12345, $defaultShippingAddressMock],
            [12346, $defaultBillingAddressMock]
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
