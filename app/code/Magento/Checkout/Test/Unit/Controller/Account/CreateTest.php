<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Controller\Account;

use Magento\Checkout\Controller\Account\Create;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Test\Unit\Helper\ResultJsonTestHelper;
use Magento\Sales\Api\OrderCustomerManagementInterface;
use Magento\Checkout\Test\Unit\Helper\SessionOrderIdTestHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Customer\Model\Session as CustomerSession;

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
     * @var ResultJsonTestHelper
     */
    private $resultPage;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->checkoutSession = new SessionOrderIdTestHelper();
        $this->customerSession = $this->createMock(CustomerSession::class);
        $this->orderCustomerService = $this->createMock(OrderCustomerManagementInterface::class);
        $this->messageManager = $this->createMock(ManagerInterface::class);

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
        $this->resultPage = new ResultJsonTestHelper();

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
        $this->resultPage->setReturnJson($resultJson);
        $this->assertEquals($resultJson, $this->action->execute());
    }

    public function testExecute()
    {
        $this->customerSession->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->checkoutSession->setLastOrderId(100);
        $this->orderCustomerService->expects($this->once())
            ->method('create')
            ->with(100)
            ->willReturn(new \stdClass());

        $resultJson = '{"errors":"false", "message":"A letter with further instructions will be sent to your email."}';
        $this->resultFactory->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_JSON)
            ->willReturn($this->resultPage);
        $this->resultPage->setReturnJson($resultJson);
        $this->assertEquals($resultJson, $this->action->execute());
    }
}
