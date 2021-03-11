<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order;

class AddCommentTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\AddComment
     */
    private $addCommentController;

    /**
     * @var \Magento\Backend\App\Action\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    private $contextMock;

    /**
     * @var \Magento\Sales\Model\Order|\PHPUnit\Framework\MockObject\MockObject
     */
    private $orderMock;

    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resultRedirectFactoryMock;

    /**
     * @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resultRedirectMock;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit\Framework\MockObject\MockObject
     */
    private $requestMock;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $orderRepositoryMock;

    /**
     * @var \Magento\Framework\AuthorizationInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $authorizationMock;

    /**
     * @var \Magento\Sales\Model\Order\Status\History|\PHPUnit\Framework\MockObject\MockObject
     */
    private $statusHistoryCommentMock;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $objectManagerMock;

    /**
     * Test setup
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(\Magento\Backend\App\Action\Context::class);
        $this->requestMock = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->orderRepositoryMock = $this->createMock(\Magento\Sales\Api\OrderRepositoryInterface::class);
        $this->orderMock = $this->createMock(\Magento\Sales\Model\Order::class);
        $this->resultRedirectFactoryMock = $this->createMock(\Magento\Backend\Model\View\Result\RedirectFactory::class);
        $this->resultRedirectMock = $this->createMock(\Magento\Backend\Model\View\Result\Redirect::class);
        $this->authorizationMock = $this->createMock(\Magento\Framework\AuthorizationInterface::class);
        $this->statusHistoryCommentMock = $this->createMock(\Magento\Sales\Model\Order\Status\History::class);
        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);

        $this->contextMock->expects($this->once())->method('getRequest')->willReturn($this->requestMock);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->addCommentController = $objectManagerHelper->getObject(
            \Magento\Sales\Controller\Adminhtml\Order\AddComment::class,
            [
                'context' => $this->contextMock,
                'orderRepository' => $this->orderRepositoryMock,
                '_authorization' => $this->authorizationMock,
                '_objectManager' => $this->objectManagerMock
            ]
        );
    }

    /**
     * @param array $historyData
     * @param bool $userHasResource
     * @param bool $expectedNotify
     *
     * @dataProvider executeWillNotifyCustomerDataProvider
     */
    public function testExecuteWillNotifyCustomer(array $historyData, bool $userHasResource, bool $expectedNotify)
    {
        $orderId = 30;
        $this->requestMock->expects($this->once())->method('getParam')->with('order_id')->willReturn($orderId);
        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($this->orderMock);
        $this->requestMock->expects($this->once())->method('getPost')->with('history')->willReturn($historyData);
        $this->authorizationMock->expects($this->any())->method('isAllowed')->willReturn($userHasResource);
        $this->orderMock->expects($this->once())
            ->method('addStatusHistoryComment')
            ->willReturn($this->statusHistoryCommentMock);
        $this->statusHistoryCommentMock->expects($this->once())->method('setIsCustomerNotified')->with($expectedNotify);
        $this->objectManagerMock->expects($this->once())->method('create')->willReturn(
            $this->createMock(\Magento\Sales\Model\Order\Email\Sender\OrderCommentSender::class)
        );

        $this->addCommentController->execute();
    }

    /**
     * @return array
     */
    public function executeWillNotifyCustomerDataProvider()
    {
        return [
            'User Has Access - Notify True' => [
                'postData' => [
                    'comment' => 'Great Product!',
                    'is_customer_notified' => true,
                    'status' => 'Processing'
                ],
                'userHasResource' => true,
                'expectedNotify' => true
            ],
            'User Has Access - Notify False' => [
                'postData' => [
                    'comment' => 'Great Product!',
                    'is_customer_notified' => false,
                    'status' => 'Processing'
                ],
                'userHasResource' => true,
                'expectedNotify' => false
            ],
            'User Has Access - Notify Unset' => [
                'postData' => [
                    'comment' => 'Great Product!',
                    'status' => 'Processing'
                ],
                'userHasResource' => true,
                'expectedNotify' => false
            ],
            'User No Access - Notify True' => [
                'postData' => [
                    'comment' => 'Great Product!',
                    'is_customer_notified' => true,
                    'status' => 'Processing'
                ],
                'userHasResource' => false,
                'expectedNotify' => false
            ],
            'User No Access - Notify False' => [
                'postData' => [
                    'comment' => 'Great Product!',
                    'is_customer_notified' => false,
                    'status' => 'Processing'
                ],
                'userHasResource' => false,
                'expectedNotify' => false
            ],
            'User No Access - Notify Unset' => [
                'postData' => [
                    'comment' => 'Great Product!',
                    'status' => 'Processing'
                ],
                'userHasResource' => false,
                'expectedNotify' => false
            ],
        ];
    }
}
