<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Plugin;

use Magento\Customer\Model\Customer\NotificationStorage;
use Magento\Customer\Model\Plugin\CustomerNotification;

class CustomerNotificationTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $session;

    /** @var \Magento\Customer\Model\Customer\NotificationStorage|\PHPUnit_Framework_MockObject_MockObject */
    protected $notificationStorage;

    /** @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerRepository;

    /** @var \Magento\Framework\App\State|\PHPUnit_Framework_MockObject_MockObject */
    protected $appState;

    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $request;

    /** @var \Magento\Backend\App\AbstractAction|\PHPUnit_Framework_MockObject_MockObject */
    protected $abstractAction;

    /** @var CustomerNotification */
    protected $plugin;

    protected function setUp()
    {
        $this->session = $this->getMockBuilder(\Magento\Customer\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->notificationStorage = $this->getMockBuilder(\Magento\Customer\Model\Customer\NotificationStorage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerRepository = $this->getMockBuilder(\Magento\Customer\Api\CustomerRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->abstractAction = $this->getMockBuilder(\Magento\Backend\App\AbstractAction::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->setMethods(['isPost'])
            ->getMockForAbstractClass();
        $this->appState = $this->getMockBuilder(\Magento\Framework\App\State::class)
            ->disableOriginalConstructor()->getMock();
        $this->plugin = new CustomerNotification(
            $this->session,
            $this->notificationStorage,
            $this->appState,
            $this->customerRepository
        );
    }

    public function testBeforeDispatch()
    {
        $customerId = 1;
        $customerGroupId =1;
        $this->appState->expects($this->any())
            ->method('getAreaCode')
            ->willReturn(\Magento\Framework\App\Area::AREA_FRONTEND);
        $this->request->expects($this->any())->method('isPost')->willReturn(true);
        $customerMock = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->getMockForAbstractClass();
        $customerMock->expects($this->any())->method('getGroupId')->willReturn($customerGroupId);
        $this->customerRepository->expects($this->any())
            ->method('getById')
            ->with($customerId)
            ->willReturn($customerMock);
        $this->session->expects($this->any())->method('getCustomerId')->willReturn($customerId);
        $this->session->expects($this->any())->method('setCustomerData')->with($customerMock);
        $this->session->expects($this->any())->method('setCustomerGroupId')->with($customerGroupId);
        $this->session->expects($this->once())->method('regenerateId');
        $this->notificationStorage->expects($this->any())
            ->method('isExists')
            ->with(NotificationStorage::UPDATE_CUSTOMER_SESSION, $customerId)
            ->willReturn(true);

        $this->plugin->beforeDispatch($this->abstractAction, $this->request);
    }
}
