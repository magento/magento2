<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order;

use Magento\Framework\App\Action\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class MassCancelTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MassCancelTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\MassCancel
     */
    protected $massAction;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectMock;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\Framework\Message\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Backend\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionMock;

    /**
     * @var \Magento\Framework\App\ActionFlag|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $actionFlagMock;

    /**
     * @var \Magento\Backend\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperMock;

    /**
     * @var \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderMock;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderCollectionMock;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderCollectionFactoryMock;

    /**
     * @var \Magento\Ui\Component\MassAction\Filter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $orderManagementMock;

    protected function setUp()
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->contextMock = $this->createMock(\Magento\Backend\App\Action\Context::class);
        $this->messageManagerMock = $this->createMock(\Magento\Framework\Message\Manager::class);
        $this->responseMock = $this->createMock(\Magento\Framework\App\ResponseInterface::class);
        $this->requestMock = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManager\ObjectManager::class);

        $resultRedirectFactory = $this->createMock(\Magento\Backend\Model\View\Result\RedirectFactory::class);

        $this->orderCollectionMock = $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resourceCollection = \Magento\Sales\Model\ResourceModel\Order\CollectionFactory::class;
        $this->orderCollectionFactoryMock = $this->getMockBuilder($resourceCollection)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->sessionMock = $this->createPartialMock(\Magento\Backend\Model\Session::class, ['setIsUrlNotice']);
        $this->actionFlagMock = $this->createPartialMock(\Magento\Framework\App\ActionFlag::class, ['get', 'set']);
        $this->helperMock = $this->createPartialMock(\Magento\Backend\Helper\Data::class, ['getUrl']);
        $this->resultRedirectMock = $this->createMock(\Magento\Backend\Model\View\Result\Redirect::class);
        $resultRedirectFactory->expects($this->any())->method('create')->willReturn($this->resultRedirectMock);

        $redirectMock = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resultFactoryMock = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultFactoryMock->expects($this->any())
            ->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT)
            ->willReturn($redirectMock);

        $this->requestMock->expects($this->once())->method('isPost')->willReturn(true);
        $this->contextMock->expects($this->once())->method('getMessageManager')->willReturn($this->messageManagerMock);
        $this->contextMock->expects($this->once())->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->expects($this->once())->method('getResponse')->willReturn($this->responseMock);
        $this->contextMock->expects($this->once())->method('getObjectManager')->willReturn($this->objectManagerMock);
        $this->contextMock->expects($this->once())->method('getSession')->willReturn($this->sessionMock);
        $this->contextMock->expects($this->once())->method('getActionFlag')->willReturn($this->actionFlagMock);
        $this->contextMock->expects($this->once())->method('getHelper')->willReturn($this->helperMock);
        $this->contextMock->expects($this->once())
            ->method('getResultRedirectFactory')
            ->willReturn($resultRedirectFactory);
        $this->contextMock->expects($this->any())
            ->method('getResultFactory')
            ->willReturn($resultFactoryMock);
        $this->filterMock = $this->createMock(\Magento\Ui\Component\MassAction\Filter::class);
        $this->filterMock->expects($this->once())
            ->method('getCollection')
            ->with($this->orderCollectionMock)
            ->willReturn($this->orderCollectionMock);
        $this->orderCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->orderCollectionMock);
        $this->orderManagementMock = $this->createMock(\Magento\Sales\Api\OrderManagementInterface::class);

        $this->massAction = $objectManagerHelper->getObject(
            \Magento\Sales\Controller\Adminhtml\Order\MassCancel::class,
            [
                'context' => $this->contextMock,
                'filter' => $this->filterMock,
                'collectionFactory' => $this->orderCollectionFactoryMock,
                'orderManagement' => $this->orderManagementMock
            ]
        );
    }

    /**
     * Test for selected orders
     * Two orders, only $order1 can be canceled
     */
    public function testExecuteCanCancelOneOrder()
    {
        $order1id = 100;
        $order2id = 200;

        $order1 = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $order2 = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orders = [$order1, $order2];
        $countOrders = count($orders);

        $this->orderCollectionMock->expects($this->any())
            ->method('getItems')
            ->willReturn($orders);

        $order1->expects($this->once())
            ->method('getEntityId')
            ->willReturn($order1id);

        $order2->expects($this->once())
            ->method('getEntityId')
            ->willReturn($order2id);

        $this->orderCollectionMock->expects($this->once())
            ->method('count')
            ->willReturn($countOrders);

        $this->orderManagementMock->expects($this->at(0))->method('cancel')->with($order1id)->willReturn(true);
        $this->orderManagementMock->expects($this->at(1))->method('cancel')->with($order2id)->willReturn(false);

        $this->messageManagerMock->expects($this->once())
            ->method('addError')
            ->with('1 order(s) cannot be canceled.');

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccess')
            ->with('We canceled 1 order(s).');

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('sales/*/')
            ->willReturnSelf();

        $this->massAction->execute();
    }

    /**
     * Test for excluded orders
     * Two orders could't be canceled
     */
    public function testExcludedCannotCancelOrders()
    {
        $order1 = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $order2 = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orders = [$order1, $order2];
        $countOrders = count($orders);

        $order1->expects($this->once())
            ->method('getEntityId')
            ->willReturn(100);

        $order2->expects($this->once())
            ->method('getEntityId')
            ->willReturn(200);

        $this->orderCollectionMock->expects($this->any())
            ->method('getItems')
            ->willReturn([$order1, $order2]);

        $this->orderCollectionMock->expects($this->once())
            ->method('count')
            ->willReturn($countOrders);

        $this->orderManagementMock->expects($this->atLeastOnce())->method('cancel')->willReturn(false);

        $this->messageManagerMock->expects($this->once())
            ->method('addError')
            ->with('You cannot cancel the order(s).');

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('sales/*/')
            ->willReturnSelf();

        $this->massAction->execute();
    }

    /**
     * Order throws exception while canceling
     */
    public function testException()
    {
        $exception = new \Exception('Can not cancel');

        $order1 = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderCollectionMock->expects($this->any())
            ->method('getItems')
            ->willReturn([$order1]);

        $order1->expects($this->once())
            ->method('getEntityId')
            ->willReturn(100);

        $this->orderManagementMock->expects($this->atLeastOnce())->method('cancel')->willThrowException($exception);

        $this->messageManagerMock->expects($this->once())
            ->method('addError')
            ->with('Can not cancel');

        $this->massAction->execute();
    }
}
