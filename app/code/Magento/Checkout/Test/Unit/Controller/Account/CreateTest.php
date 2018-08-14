<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Controller\Account;

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
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->checkoutSession = $this->createPartialMock(\Magento\Checkout\Model\Session::class, ['getLastOrderId']);
        $this->customerSession = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->orderCustomerService = $this->createMock(\Magento\Sales\Api\OrderCustomerManagementInterface::class);
        $this->messageManager = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);

        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $contextMock = $this->createPartialMock(\Magento\Framework\App\Action\Context::class, ['getObjectManager']);
        $contextMock->expects($this->once())->method('getObjectManager')->willReturn($this->objectManagerMock);

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
        $jsonFactoryMock = $this->createMock(\Magento\Framework\Controller\Result\JsonFactory::class);
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with(\Magento\Framework\Controller\Result\JsonFactory::class)
            ->willReturn($jsonFactoryMock);
        $jsonMock = $this->createMock(\Magento\Framework\Controller\Result\Json::class);
        $jsonFactoryMock->expects($this->once())->method('create')->willReturn($jsonMock);

        $this->customerSession->expects($this->once())->method('isLoggedIn')->will($this->returnValue(true));

        $jsonMock->expects($this->once())
            ->method('setData')
            ->with(
                [
                    'errors' => true,
                    'message' => __('Customer is already registered')
                ]
            )->willReturnSelf();
        $this->action->execute();
    }

    public function testExecute()
    {
        $jsonFactoryMock = $this->createMock(\Magento\Framework\Controller\Result\JsonFactory::class);
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with(\Magento\Framework\Controller\Result\JsonFactory::class)
            ->willReturn($jsonFactoryMock);
        $jsonMock = $this->createMock(\Magento\Framework\Controller\Result\Json::class);
        $jsonFactoryMock->expects($this->once())->method('create')->willReturn($jsonMock);

        $this->customerSession->expects($this->once())->method('isLoggedIn')->will($this->returnValue(false));
        $this->checkoutSession->expects($this->once())->method('getLastOrderId')->will($this->returnValue(100));
        $customer = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $this->orderCustomerService->expects($this->once())->method('create')->with(100)->will(
            $this->returnValue($customer)
        );

        $jsonMock->expects($this->once())
            ->method('setData')
            ->with(
                [
                    'errors' => false,
                    'message' => __('A letter with further instructions will be sent to your email.')
                ]
            )->willReturnSelf();

        $this->action->execute();
    }
}
