<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Controller\Account;

use Magento\Checkout\Controller\Account\Create;
use Magento\Checkout\Model\Session;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\OrderCustomerManagementInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Shopping cart edit tests
 */
class CreateTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $customerSession;

    /**
     * @var MockObject
     */
    protected $checkoutSession;

    /**
     * @var MockObject
     */
    protected $messageManager;

    /**
     * @var Create
     */
    protected $action;

    /**
     * @var MockObject
     */
    protected $orderCustomerService;

    /**
     * @var ResultFactory|MockObject
     */
    private $resultFactory;

    /**
     * @var ResultInterface|MockObject
     */
    private $resultPage;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->checkoutSession = $this->getMockBuilder(Session::class)
            ->addMethods(['getLastOrderId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerSession = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->orderCustomerService = $this->getMockForAbstractClass(OrderCustomerManagementInterface::class);
        $this->messageManager = $this->getMockForAbstractClass(ManagerInterface::class);

        $contextMock = $this->createPartialMock(
            Context::class,
            ['getObjectManager', 'getResultFactory']
        );
        $this->resultFactory = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->once())
            ->method('getResultFactory')
            ->willReturn($this->resultFactory);
        $this->resultPage = $this->getMockBuilder(ResultInterface::class)
            ->addMethods(['setData'])
            ->getMockForAbstractClass();

        $this->action = $objectManagerHelper->getObject(
            Create::class,
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
            ->willReturn(true);
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
        $this->customerSession->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->checkoutSession->expects($this->once())->method('getLastOrderId')->willReturn(100);
        $customer = $this->getMockForAbstractClass(CustomerInterface::class);
        $this->orderCustomerService->expects($this->once())
            ->method('create')
            ->with(100)
            ->willReturn($customer);

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
