<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Shipping\Test\Unit\Controller\Adminhtml\Order\Shipment;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Forward;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Backend\Model\View\Result\Page;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Model\Order\Shipment;
use Magento\Shipping\Block\Adminhtml\View;
use Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader;
use Magento\Shipping\Controller\Adminhtml\Order\Shipment\View as OrderShipmentView;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ViewTest extends TestCase
{
    /**
     * @var RequestInterface|MockObject
     */
    protected $requestMock;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $objectManagerMock;

    /**
     * @var ShipmentLoader|MockObject
     */
    protected $shipmentLoaderMock;

    /**
     * @var Shipment|MockObject
     */
    protected $shipmentMock;

    /**
     * @var View|MockObject
     */
    protected $blockMock;

    /**
     * @var PageFactory|MockObject
     */
    protected $resultPageFactoryMock;

    /**
     * @var Page|MockObject
     */
    protected $resultPageMock;

    /**
     * @var ForwardFactory|MockObject
     */
    protected $resultForwardFactoryMock;

    /**
     * @var Forward|MockObject
     */
    protected $resultForwardMock;

    /**
     * @var Config|MockObject
     */
    protected $pageConfigMock;

    /**
     * @var Title|MockObject
     */
    protected $pageTitleMock;

    /**
     * @var RedirectFactory|MockObject
     */
    protected $resultRedirectFactoryMock;

    /**
     * @var Redirect|MockObject
     */
    protected $resultRedirectMock;

    /**
     * @var OrderShipmentView
     */
    protected $controller;

    protected function setUp(): void
    {
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMock();
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMock();
        $this->pageConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageTitleMock = $this->getMockBuilder(Title::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->shipmentLoaderMock = $this->getMockBuilder(ShipmentLoader::class)
            ->addMethods(['setOrderId', 'setShipmentId', 'setShipment', 'setTracking'])
            ->onlyMethods(['load'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->shipmentMock = $this->createPartialMock(
            Shipment::class,
            ['getIncrementId', '__wakeup']
        );
        $this->resultPageFactoryMock = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultPageMock = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultForwardFactoryMock = $this->getMockBuilder(
            ForwardFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultForwardMock = $this->getMockBuilder(Forward::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->blockMock = $this->createPartialMock(
            View::class,
            ['updateBackButtonUrl']
        );

        $this->resultRedirectFactoryMock = $this->getMockBuilder(RedirectFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultRedirectMock = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $context = $objectManager->getObject(
            Context::class,
            [
                'request' => $this->requestMock,
                'objectManager' => $this->objectManagerMock,
                'resultRedirectFactory' => $this->resultRedirectFactoryMock
            ]
        );
        $this->controller = $objectManager->getObject(
            OrderShipmentView::class,
            [
                'context' => $context,
                'shipmentLoader' => $this->shipmentLoaderMock,
                'resultPageFactory' => $this->resultPageFactoryMock,
                'resultForwardFactory' => $this->resultForwardFactoryMock
            ]
        );
    }

    /**
     * Run test execute method
     */
    public function testExecute()
    {
        $orderId = 1;
        $shipmentId = 1;
        $shipment = [];
        $tracking = [];
        $incrementId = '10000001';
        $comeFrom = true;

        $this->loadShipment($orderId, $shipmentId, $shipment, $tracking, $comeFrom, $this->shipmentMock);
        $this->shipmentMock->expects($this->once())->method('getIncrementId')->willReturn($incrementId);
        $this->resultPageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultPageMock);

        $layoutMock = $this->getMockBuilder(Layout::class)
            ->addMethods(['__wakeup'])
            ->onlyMethods(['getBlock'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageMock->expects($this->once())
            ->method('getLayout')
            ->willReturn($layoutMock);
        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('sales_shipment_view')
            ->willReturn($this->blockMock);
        $this->blockMock->expects($this->once())
            ->method('updateBackButtonUrl')
            ->with($comeFrom)
            ->willReturnSelf();

        $this->resultPageMock->expects($this->once())
            ->method('setActiveMenu')
            ->with('Magento_Sales::sales_shipment')
            ->willReturnSelf();
        $this->resultPageMock->expects($this->atLeastOnce())
            ->method('getConfig')
            ->willReturn($this->pageConfigMock);
        $this->pageConfigMock->expects($this->atLeastOnce())
            ->method('getTitle')
            ->willReturn($this->pageTitleMock);
        $this->pageTitleMock->expects($this->exactly(2))
            ->method('prepend')
            ->withConsecutive(
                ['Shipments'],
                ['View Shipment #' . $incrementId]
            )
            ->willReturnSelf();

        $this->assertEquals($this->resultPageMock, $this->controller->execute());
    }

    /**
     * Run test execute method (no shipment)
     */
    public function testExecuteNoShipment()
    {
        $orderId = 1;
        $shipmentId = 1;
        $shipment = [];
        $tracking = [];

        $this->loadShipment($orderId, $shipmentId, $shipment, $tracking, null, false);
        $this->prepareRedirect();
        $this->setPath('sales/shipment');
        $this->assertInstanceOf(
            Redirect::class,
            $this->controller->execute()
        );
    }

    /**
     * @param $orderId
     * @param $shipmentId
     * @param $shipment
     * @param $tracking
     * @param $comeFrom
     * @param $returnShipment
     */
    protected function loadShipment($orderId, $shipmentId, $shipment, $tracking, $comeFrom, $returnShipment)
    {
        $valueMap = [
            ['order_id', null, $orderId],
            ['shipment_id', null, $shipmentId],
            ['shipment', null, $shipment],
            ['tracking', null, $tracking],
            ['come_from', null, $comeFrom],
        ];
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap($valueMap);
        $this->shipmentLoaderMock->expects($this->once())->method('setOrderId')->with($orderId);
        $this->shipmentLoaderMock->expects($this->once())->method('setShipmentId')->with($shipmentId);
        $this->shipmentLoaderMock->expects($this->once())->method('setShipment')->with($shipment);
        $this->shipmentLoaderMock->expects($this->once())->method('setTracking')->with($tracking);
        $this->shipmentLoaderMock->expects($this->once())
            ->method('load')
            ->willReturn($returnShipment);
    }

    /**
     * prepareRedirect
     */
    protected function prepareRedirect()
    {
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRedirectMock);
    }

    /**
     * @param string $path
     * @param array $params
     */
    protected function setPath($path, $params = [])
    {
        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with($path, $params);
    }
}
