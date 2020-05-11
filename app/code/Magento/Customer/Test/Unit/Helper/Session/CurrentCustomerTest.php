<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Helper\Session;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\View;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\Module\Manager;
use Magento\Framework\View\Layout;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CurrentCustomerTest extends TestCase
{
    /**
     * @var CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var Session|MockObject
     */
    protected $customerSessionMock;

    /**
     * @var LayoutInterface|MockObject
     */
    protected $layoutMock;

    /**
     * @var CustomerInterfaceFactory|MockObject
     */
    protected $customerInterfaceFactoryMock;

    /**
     * @var CustomerInterface|MockObject
     */
    protected $customerDataMock;

    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    protected $customerRepositoryMock;

    /**
     * @var RequestInterface|MockObject
     */
    protected $requestMock;

    /**
     * @var Manager|MockObject
     */
    protected $moduleManagerMock;

    /**
     * @var ViewInterface|MockObject
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
        $this->customerSessionMock = $this->createMock(Session::class);
        $this->layoutMock = $this->createMock(Layout::class);
        $this->customerInterfaceFactoryMock = $this->getMockBuilder(CustomerInterfaceFactory::class)
            ->addMethods(['setGroupId'])
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerDataMock = $this->getMockForAbstractClass(CustomerInterface::class);
        $this->customerRepositoryMock = $this->getMockForAbstractClass(CustomerRepositoryInterface::class);
        $this->requestMock = $this->createMock(Http::class);
        $this->moduleManagerMock = $this->createMock(Manager::class);
        $this->viewMock = $this->createMock(View::class);

        $this->currentCustomer = new CurrentCustomer(
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
            ->with('Magento_PageCache')
            ->willReturn(true);
        $this->customerSessionMock->expects($this->once())
            ->method('getCustomerGroupId')
            ->willReturn($this->customerGroupId);
        $this->customerInterfaceFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->customerDataMock);
        $this->customerDataMock->expects($this->once())
            ->method('setGroupId')
            ->with($this->customerGroupId)
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
            ->with('Magento_PageCache')
            ->willReturn(false);
        $this->customerSessionMock->expects($this->once())
            ->method('getId')
            ->willReturn($this->customerId);
        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($this->customerId)
            ->willReturn($this->customerDataMock);
        $this->assertEquals($this->customerDataMock, $this->currentCustomer->getCustomer());
    }
}
