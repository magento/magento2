<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Test\Unit\Observer;

class EmulateCustomerObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Persistent\Observer\EmulateCustomerObserver
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $observerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressRepositoryMock;

    protected function setUp()
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
        $this->customerSessionMock = $this->getMock(\Magento\Customer\Model\Session::class, $methods, [], '', false);
        $this->sessionHelperMock = $this->getMock(\Magento\Persistent\Helper\Session::class, [], [], '', false);
        $this->helperMock = $this->getMock(\Magento\Persistent\Helper\Data::class, [], [], '', false);
        $this->observerMock = $this->getMock(\Magento\Framework\Event\Observer::class, [], [], '', false);
        $this->addressRepositoryMock = $this->getMock(\Magento\Customer\Api\AddressRepositoryInterface::class);
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
        $sessionMock = $this->getMock(
            \Magento\Persistent\Model\Session::class,
            ['getCustomerId', '__wakeUp'],
            [],
            '',
            false
        );
        $methods = ['getCountryId', 'getRegion', 'getRegionId', 'getPostcode'];
        $defaultShippingAddressMock = $this->getMock(\Magento\Customer\Model\Address::class, $methods, [], '', false);
        $defaultBillingAddressMock = $this->getMock(\Magento\Customer\Model\Address::class, $methods, [], '', false);
        $customerMock = $this->getMock(\Magento\Customer\Api\Data\CustomerInterface::class);
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
