<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Controller\Account;

use Magento\Customer\Controller\Account\UpdateSession;
use Magento\Customer\Model\Customer\NotificationStorage;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\Json\Helper\Data;

class UpdateSessionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UpdateSession
     */
    protected $model;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var NotificationStorage|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $notificationStorage;

    /**
     * @var CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerRepository;

    /**
     * @var Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSession;

    /**
     * @var Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $jsonHelper;


    protected function setUp()
    {
        $this->context = $this->getMockBuilder('Magento\Framework\App\Action\Context')
            ->disableOriginalConstructor()
            ->getMock();

        $this->notificationStorage = $this->getMockBuilder('Magento\Customer\Model\Customer\NotificationStorage')
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerSession = $this->getMockBuilder('Magento\Customer\Model\Session')
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerRepository = $this->getMockBuilder('Magento\Customer\Api\CustomerRepositoryInterface')
            ->getMockForAbstractClass();

        $this->jsonHelper = $this->getMockBuilder('Magento\Framework\Json\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new UpdateSession(
            $this->context,
            $this->notificationStorage,
            $this->customerRepository,
            $this->customerSession,
            $this->jsonHelper
        );
    }

    public function testExecute()
    {
        $customerData = ['customer_id' => 1];
        $customerGroup = 1;

        $requestMock = $this->getMockBuilder('Magento\Framework\App\RequestInterface')
            ->setMethods(['getContent'])
            ->getMockForAbstractClass();
        $requestMock->expects($this->any())->method('getContent')->willReturn(json_encode($customerData));

        $reflection = new \ReflectionClass(get_class($this->model));
        $reflectionProperty = $reflection->getProperty('_request');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->model, $requestMock);

        $this->jsonHelper->expects($this->any())->method('jsonDecode')->willReturn($customerData);

        $customer = $this->getMockBuilder('Magento\Customer\Api\Data\CustomerInterface')->getMockForAbstractClass();
        $customer->expects($this->once())->method('getId')->willReturn($customerData['customer_id']);
        $customer->expects($this->once())->method('getGroupId')->willReturn($customerGroup);

        $this->customerRepository->expects($this->once())
            ->method('getById')
            ->with($customerData['customer_id'])
            ->willReturn($customer);

        $this->customerSession->expects($this->once())->method('setCustomerData')->with($customer);
        $this->customerSession->expects($this->once())->method('setCustomerGroupId')->with($customerGroup);
        $this->customerSession->expects($this->once())->method('regenerateId');

        $this->notificationStorage->expects($this->once())
            ->method('isExists')
            ->with(NotificationStorage::UPDATE_CUSTOMER_SESSION, $customerData['customer_id'])
            ->willReturn(true);
        $this->notificationStorage->expects($this->once())
            ->method('remove')
            ->with(NotificationStorage::UPDATE_CUSTOMER_SESSION, $customerData['customer_id']);

        $this->model->execute();
    }
}
