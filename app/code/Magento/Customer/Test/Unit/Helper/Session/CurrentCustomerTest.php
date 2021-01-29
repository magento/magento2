<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Helper\Session;

/**
 * Current customer test.
 */
class CurrentCustomerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $customerSessionMock;

    /**
     * @var \Magento\Framework\View\LayoutInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $layoutMock;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterfaceFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $customerInterfaceFactoryMock;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $customerDataMock;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $customerRepositoryMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\Module\Manager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $moduleManagerMock;

    /**
     * @var \Magento\Framework\App\ViewInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $viewMock;

    /**
     * @var int
     */
    protected $customerId = 100;

    /**
     * @var int
     */
    protected $customerGroupId = 500;

    /**
     * Test setup
     */
    protected function setUp(): void
    {
        $this->customerSessionMock = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->layoutMock = $this->createMock(\Magento\Framework\View\Layout::class);
        $this->customerInterfaceFactoryMock = $this->createPartialMock(
            \Magento\Customer\Api\Data\CustomerInterfaceFactory::class,
            ['create', 'setGroupId']
        );
        $this->customerDataMock = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $this->customerRepositoryMock = $this->createMock(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        $this->requestMock = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->moduleManagerMock = $this->createMock(\Magento\Framework\Module\Manager::class);
        $this->viewMock = $this->createMock(\Magento\Framework\App\View::class);

        $this->currentCustomer = new \Magento\Customer\Helper\Session\CurrentCustomer(
            $this->customerSessionMock,
            $this->layoutMock,
            $this->customerInterfaceFactoryMock,
            $this->customerRepositoryMock,
            $this->requestMock,
            $this->moduleManagerMock,
            $this->viewMock
        );
    }

    /**
     * test getCustomer method, method returns depersonalized customer Data
     */
    public function testGetCustomerDepersonalizeCustomerData()
    {
        $this->requestMock->expects($this->once())->method('isAjax')->willReturn(false);
        $this->layoutMock->expects($this->once())->method('isCacheable')->willReturn(true);
        $this->viewMock->expects($this->once())->method('isLayoutLoaded')->willReturn(true);
        $this->moduleManagerMock->expects($this->once())
            ->method('isEnabled')
            ->with($this->equalTo('Magento_PageCache'))
            ->willReturn(true);
        $this->customerSessionMock->expects($this->once())
            ->method('getCustomerGroupId')
            ->willReturn($this->customerGroupId);
        $this->customerInterfaceFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->customerDataMock);
        $this->customerDataMock->expects($this->once())
            ->method('setGroupId')
            ->with($this->equalTo($this->customerGroupId))
            ->willReturnSelf();
        $this->assertEquals($this->customerDataMock, $this->currentCustomer->getCustomer());
    }

    /**
     * test get customer method, method returns customer from service
     */
    public function testGetCustomerLoadCustomerFromService()
    {
        $this->moduleManagerMock->expects($this->once())
            ->method('isEnabled')
            ->with($this->equalTo('Magento_PageCache'))
            ->willReturn(false);
        $this->customerSessionMock->expects($this->once())
            ->method('getId')
            ->willReturn($this->customerId);
        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($this->equalTo($this->customerId))
            ->willReturn($this->customerDataMock);
        $this->assertEquals($this->customerDataMock, $this->currentCustomer->getCustomer());
    }
}
