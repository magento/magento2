<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Controller\Account;

use Magento\Framework\Controller\ResultFactory;

/**
 * Shopping cart edit tests
 */
class CreateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSession;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutSession;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManager;

    /**
     * @var \Magento\Checkout\Controller\Account\Create
     */
    protected $action;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderCustomerService;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactory;

    /**
     * @var \Magento\Framework\Controller\ResultInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultPage;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->checkoutSession = $this->createPartialMock(\Magento\Checkout\Model\Session::class, ['getLastOrderId']);
        $this->customerSession = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->orderCustomerService = $this->createMock(\Magento\Sales\Api\OrderCustomerManagementInterface::class);
        $this->messageManager = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);

        $contextMock = $this->createPartialMock(
            \Magento\Framework\App\Action\Context::class,
            ['getObjectManager', 'getResultFactory']
        );
        $this->resultFactory = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->once())
            ->method('getResultFactory')
            ->willReturn($this->resultFactory);
        $this->resultPage = $this->getMockBuilder(\Magento\Framework\Controller\ResultInterface::class)
            ->setMethods(['setData'])
            ->getMockForAbstractClass();

        $this->action = $objectManagerHelper->getObject(
            \Magento\Checkout\Controller\Account\Create::class,
            [
                'checkoutSession' => $this->checkoutSession,
                'customerSession' => $this->customerSession,
                'orderCustomerService' => $this->orderCustomerService,
                'messageManager' => $this->messageManager,
                'context' => $contextMock
            ]
        );
    }

    public function testExecuteAddsSessionMessageIfCustomerIsLoggedIn()
    {
        $resultJson = '{"errors": "true", "message": "Customer is already registered"}';
        $this->customerSession->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(true));
        $this->resultFactory->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_JSON)
            ->willReturn($this->resultPage);
        $this->resultPage->expects($this->once())
            ->method('setData')
            ->with(
                [
                    'errors' => true,
                    'message' => __('Customer is already registered')
                ]
            )->willReturn($resultJson);
        $this->assertEquals($resultJson, $this->action->execute());
    }

    public function testExecute()
    {
        $this->customerSession->expects($this->once())->method('isLoggedIn')->will($this->returnValue(false));
        $this->checkoutSession->expects($this->once())->method('getLastOrderId')->will($this->returnValue(100));
        $customer = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $this->orderCustomerService->expects($this->once())
            ->method('create')
            ->with(100)
            ->will($this->returnValue($customer));

        $resultJson = '{"errors":"false", "message":"A letter with further instructions will be sent to your email."}';
        $this->resultFactory->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_JSON)
            ->willReturn($this->resultPage);
        $this->resultPage->expects($this->once())
            ->method('setData')
            ->with(
                [
                    'errors' => false,
                    'message' => __('A letter with further instructions will be sent to your email.')
                ]
            )->willReturn($resultJson);
        $this->assertEquals($resultJson, $this->action->execute());
    }
}
