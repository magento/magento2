<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Test\Unit\Controller\Adminhtml\Order\Shipment;

use Magento\Backend\App\Action;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class NewActionTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class NewActionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $shipmentLoader;

    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\Shipment\NewAction
     */
    protected $newAction;

    /**
     * @var Action\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $context;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $response;

    /**
     * @var \Magento\Framework\Message\Manager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $objectManager;

    /**
     * @var \Magento\Backend\Model\Session|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $session;

    /**
     * @var \Magento\Framework\App\ActionFlag|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $actionFlag;

    /**
     * @var \Magento\Backend\Helper\Data|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $helper;

    /**
     * @var  \Magento\Framework\App\ViewInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $view;

    /**
     * @var \Magento\Framework\View\Result\Page|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultPageMock;

    /**
     * @var \Magento\Framework\View\Page\Config|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $pageConfigMock;

    /**
     * @var \Magento\Framework\View\Page\Title|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $pageTitleMock;

    /**
     * @var \Magento\Shipping\Model\ShipmentProviderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $shipmentProviderMock;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->shipmentLoader = $this->getMockBuilder(
            \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader::class
        )->disableOriginalConstructor()
            ->setMethods(['setShipmentId', 'setOrderId', 'setShipment', 'setTracking', 'load'])
            ->getMock();
        $this->labelGenerator = $this->getMockBuilder(\Magento\Shipping\Model\Shipping\LabelGenerator::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->shipmentSender = $this->getMockBuilder(\Magento\Sales\Model\Order\Email\Sender\ShipmentSender::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->context = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->context = $this->createPartialMock(\Magento\Backend\App\Action\Context::class, [
                'getRequest', 'getResponse', 'getMessageManager', 'getRedirect', 'getObjectManager',
                'getSession', 'getActionFlag', 'getHelper', 'getView'
            ]);
        $this->response = $this->createPartialMock(
            \Magento\Framework\App\ResponseInterface::class,
            ['setRedirect', 'sendResponse']
        );
        $this->request = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()->getMock();
        $this->messageManager = $this->createPartialMock(
            \Magento\Framework\Message\Manager::class,
            ['addSuccess', 'addError']
        );
        $this->session = $this->createPartialMock(
            \Magento\Backend\Model\Session::class,
            ['setIsUrlNotice', 'getCommentText']
        );
        $this->shipmentProviderMock = $this->getMockBuilder(\Magento\Shipping\Model\ShipmentProviderInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getShipmentData'])
            ->getMockForAbstractClass();
        $this->actionFlag = $this->createPartialMock(\Magento\Framework\App\ActionFlag::class, ['get']);
        $this->helper = $this->createPartialMock(\Magento\Backend\Helper\Data::class, ['getUrl']);
        $this->view = $this->createMock(\Magento\Framework\App\ViewInterface::class);
        $this->resultPageMock = $this->getMockBuilder(\Magento\Framework\View\Result\Page::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageConfigMock = $this->getMockBuilder(\Magento\Framework\View\Page\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageTitleMock = $this->getMockBuilder(\Magento\Framework\View\Page\Title::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->once())
            ->method('getMessageManager')
            ->willReturn($this->messageManager);
        $this->context->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->request);
        $this->context->expects($this->once())
            ->method('getResponse')
            ->willReturn($this->response);
        $this->context->expects($this->once())
            ->method('getObjectManager')
            ->willReturn($this->objectManager);
        $this->context->expects($this->once())
            ->method('getSession')
            ->willReturn($this->session);
        $this->context->expects($this->once())
            ->method('getActionFlag')
            ->willReturn($this->actionFlag);
        $this->context->expects($this->once())
            ->method('getHelper')
            ->willReturn($this->helper);
        $this->context->expects($this->once())->method('getView')->willReturn($this->view);
        $this->newAction = $objectManagerHelper->getObject(
            \Magento\Shipping\Controller\Adminhtml\Order\Shipment\NewAction::class,
            [
                'context' => $this->context, 'shipmentLoader' => $this->shipmentLoader, 'request' => $this->request,
                'response' => $this->response, 'view' => $this->view, 'shipmentProvider' => $this->shipmentProviderMock
            ]
        );
    }

    public function testExecute()
    {
        $shipmentId = 1000012;
        $orderId = 10003;
        $tracking = [];
        $shipmentData = ['items' => [], 'send_email' => ''];
        $shipment = $this->createPartialMock(
            \Magento\Sales\Model\Order\Shipment::class,
            ['load', 'save', 'register', 'getOrder', 'getOrderId', '__wakeup']
        );
        $this->request->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                
                    [
                        ['order_id', null, $orderId],
                        ['shipment_id', null, $shipmentId],
                        ['tracking', null, $tracking],
                    ]
                
            );
        $this->shipmentLoader->expects($this->any())
            ->method('setShipmentId')
            ->with($shipmentId);
        $this->shipmentLoader->expects($this->any())
            ->method('setOrderId')
            ->with($orderId);
        $this->shipmentLoader->expects($this->any())
            ->method('setShipment')
            ->with($shipmentData);
        $this->shipmentLoader->expects($this->any())
            ->method('setTracking')
            ->with($tracking);
        $this->shipmentLoader->expects($this->once())
            ->method('load')
            ->willReturn($shipment);
        $this->session->expects($this->once())
            ->method('getCommentText')
            ->with(true)
            ->willReturn('');
        $this->objectManager->expects($this->atLeastOnce())
            ->method('get')
            ->with(\Magento\Backend\Model\Session::class)
            ->willReturn($this->session);
        $this->view->expects($this->once())
            ->method('loadLayout')
            ->willReturnSelf();
        $this->view->expects($this->once())
            ->method('renderLayout')
            ->willReturnSelf();
        $this->view->expects($this->any())
            ->method('getPage')
            ->willReturn($this->resultPageMock);
        $this->resultPageMock->expects($this->any())
            ->method('getConfig')
            ->willReturn($this->pageConfigMock);
        $this->pageConfigMock->expects($this->any())
            ->method('getTitle')
            ->willReturn($this->pageTitleMock);
        $layout = $this->createMock(\Magento\Framework\View\LayoutInterface::class);
        $menuBlock = $this->createPartialMock(
            \Magento\Framework\View\Element\BlockInterface::class,
            ['toHtml', 'setActive', 'getMenuModel']
        );
        $menuModel = $this->getMockBuilder(\Magento\Backend\Model\Menu::class)
            ->disableOriginalConstructor()->getMock();
        $itemId = 'Magento_Sales::sales_order';
        $parents = [
            new \Magento\Framework\DataObject(['title' => 'title1']),
            new \Magento\Framework\DataObject(['title' => 'title2']),
            new \Magento\Framework\DataObject(['title' => 'title3']),
        ];
        $menuModel->expects($this->once())
            ->method('getParentItems')
            ->with($itemId)
            ->willReturn($parents);
        $menuBlock->expects($this->once())
            ->method('setActive')
            ->with($itemId);
        $menuBlock->expects($this->once())
            ->method('getMenuModel')
            ->willReturn($menuModel);
        $this->view->expects($this->once())
            ->method('getLayout')
            ->willReturn($layout);
        $layout->expects($this->once())
            ->method('getBlock')
            ->with('menu')
            ->willReturn($menuBlock);
        $this->shipmentProviderMock->expects($this->once())
            ->method('getShipmentData')
            ->willReturn($shipmentData);

        $this->assertNull($this->newAction->execute());
    }
}
