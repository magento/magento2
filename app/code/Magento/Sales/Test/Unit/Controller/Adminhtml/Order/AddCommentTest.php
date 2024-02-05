<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\App\Request\Http;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Controller\Adminhtml\Order\AddComment;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderCommentSender;
use Magento\Sales\Model\Order\Status\History;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddCommentTest extends TestCase
{
    /**
     * @var AddComment
     */
    private $addCommentController;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var Order|MockObject
     */
    private $orderMock;

    /**
     * @var RedirectFactory|MockObject
     */
    private $resultRedirectFactoryMock;

    /**
     * @var Redirect|MockObject
     */
    private $resultRedirectMock;

    /**
     * @var Http|MockObject
     */
    private $requestMock;

    /**
     * @var OrderRepositoryInterface|MockObject
     */
    private $orderRepositoryMock;

    /**
     * @var AuthorizationInterface|MockObject
     */
    private $authorizationMock;

    /**
     * @var History|MockObject
     */
    private $statusHistoryCommentMock;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /** @var JsonFactory|MockObject */
    private $jsonFactory;

    /** @var Json|MockObject */
    private $resultJson;

    /**
     * Test setup
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->requestMock = $this->createMock(Http::class);
        $this->orderRepositoryMock = $this->getMockForAbstractClass(OrderRepositoryInterface::class);
        $this->orderMock = $this->createMock(Order::class);
        $this->resultRedirectFactoryMock = $this->createMock(RedirectFactory::class);
        $this->resultRedirectMock = $this->createMock(Redirect::class);
        $this->authorizationMock = $this->getMockForAbstractClass(AuthorizationInterface::class);
        $this->statusHistoryCommentMock = $this->createMock(History::class);
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);

        $this->contextMock->expects($this->once())->method('getRequest')->willReturn($this->requestMock);

        $this->resultJson = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->jsonFactory = $this->getMockBuilder(JsonFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManager($this);
        $this->addCommentController = $objectManagerHelper->getObject(
            AddComment::class,
            [
                'context' => $this->contextMock,
                'orderRepository' => $this->orderRepositoryMock,
                '_authorization' => $this->authorizationMock,
                '_objectManager' => $this->objectManagerMock,
                'resultJsonFactory' => $this->jsonFactory
            ]
        );
    }

    /**
     * @param array $historyData
     * @param string $orderStatus
     * @param bool $userHasResource
     * @param bool $expectedNotify
     *
     * @dataProvider executeWillNotifyCustomerDataProvider
     */
    public function testExecuteWillNotifyCustomer(
        array $historyData,
        string $orderStatus,
        bool $userHasResource,
        bool $expectedNotify
    ) {
        $orderId = 30;
        $this->requestMock->expects($this->once())->method('getParam')->with('order_id')->willReturn($orderId);
        $this->orderMock->expects($this->atLeastOnce())->method('getDataByKey')
            ->with('status')->willReturn($orderStatus);
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
            $this->createMock(OrderCommentSender::class)
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
                    'status' => 'processing'
                ],
                'orderStatus' =>'processing',
                'userHasResource' => true,
                'expectedNotify' => true
            ],
            'User Has Access - Notify False' => [
                'postData' => [
                    'comment' => 'Great Product!',
                    'is_customer_notified' => false,
                    'status' => 'processing'
                ],
                'orderStatus' =>'processing',
                'userHasResource' => true,
                'expectedNotify' => false
            ],
            'User Has Access - Notify Unset' => [
                'postData' => [
                    'comment' => 'Great Product!',
                    'status' => 'processing'
                ],
                'orderStatus' =>'fraud',
                'userHasResource' => true,
                'expectedNotify' => false
            ],
            'User No Access - Notify True' => [
                'postData' => [
                    'comment' => 'Great Product!',
                    'is_customer_notified' => true,
                    'status' => 'fraud'
                ],
                'orderStatus' =>'processing',
                'userHasResource' => false,
                'expectedNotify' => false
            ],
            'User No Access - Notify False' => [
                'postData' => [
                    'comment' => 'Great Product!',
                    'is_customer_notified' => false,
                    'status' => 'processing'
                ],
                'orderStatus' =>'complete',
                'userHasResource' => false,
                'expectedNotify' => false
            ],
            'User No Access - Notify Unset' => [
                'postData' => [
                    'comment' => 'Great Product!',
                    'status' => 'processing'
                ],
                'orderStatus' =>'complete',
                'userHasResource' => false,
                'expectedNotify' => false
            ],
        ];
    }

    /**
     * Assert error message for empty comment value
     *
     * @return void
     */
    public function testExecuteForEmptyCommentMessage(): void
    {
        $orderId = 30;
        $orderStatus = 'processing';
        $historyData = [
            'comment' => '',
            'is_customer_notified' => false,
            'status' => 'processing'
        ];

        $this->requestMock->expects($this->once())->method('getParam')->with('order_id')->willReturn($orderId);
        $this->orderMock->expects($this->atLeastOnce())->method('getDataByKey')
            ->with('status')->willReturn($orderStatus);
        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($this->orderMock);
        $this->requestMock->expects($this->once())->method('getPost')->with('history')->willReturn($historyData);

        $this->resultJson->expects($this->once())
            ->method('setData')
            ->with(
                [
                    'error' => true,
                    'message' => 'Please provide a comment text or ' .
                        'update the order status to be able to submit a comment for this order.'
                ]
            )
            ->willReturnSelf();
        $this->jsonFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resultJson);

        $this->assertSame($this->resultJson, $this->addCommentController->execute());
    }
}
