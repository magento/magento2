<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Test\Unit\Controller\Adminhtml\Order\Shipment;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\View;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Layout;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;
use Magento\Framework\View\Result\Page;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\Shipment\Track;
use Magento\Shipping\Block\Adminhtml\Order\Tracking;
use Magento\Shipping\Controller\Adminhtml\Order\Shipment\RemoveTrack;
use Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RemoveTrackTest extends TestCase
{
    /**
     * @var ShipmentLoader|MockObject
     */
    protected $shipmentLoaderMock;

    /**
     * @var Http|MockObject
     */
    protected $requestMock;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $objectManagerMock;

    /**
     * @var Track|MockObject
     */
    protected $shipmentTrackMock;

    /**
     * @var \Magento\Sales\Model\Order\Shipment|MockObject
     */
    protected $shipmentMock;

    /**
     * @var View|MockObject
     */
    protected $viewMock;

    /**
     * @var \Magento\Framework\App\Response\Http|MockObject
     */
    protected $responseMock;

    /**
     * @var Page|MockObject
     */
    protected $resultPageMock;

    /**
     * @var Config|MockObject
     */
    protected $pageConfigMock;

    /**
     * @var Title|MockObject
     */
    protected $pageTitleMock;

    /**
     * @var RemoveTrack
     */
    protected $controller;

    protected function setUp(): void
    {
        $this->requestMock = $this->createPartialMock(Http::class, ['getParam']);
        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $this->shipmentTrackMock = $this->createPartialMock(
            Track::class,
            ['load', 'getId', 'delete', '__wakeup']
        );
        $this->shipmentMock = $this->createPartialMock(
            Shipment::class,
            ['getIncrementId', '__wakeup']
        );
        $this->viewMock = $this->createPartialMock(
            View::class,
            ['loadLayout', 'getLayout', 'getPage']
        );
        $this->responseMock = $this->createMock(\Magento\Framework\App\Response\Http::class);
        $this->shipmentLoaderMock = $this->createPartialMock(
            ShipmentLoader::class,
            ['setOrderId', 'setShipmentId', 'setShipment', 'setTracking', 'load']
        );
        $this->resultPageMock = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageTitleMock = $this->getMockBuilder(Title::class)
            ->disableOriginalConstructor()
            ->getMock();

        $contextMock = $this->createPartialMock(
            Context::class,
            ['getRequest', 'getObjectManager', 'getTitle', 'getView', 'getResponse']
        );

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(Track::class)
            ->will($this->returnValue($this->shipmentTrackMock));

        $contextMock->expects($this->any())->method('getRequest')->will($this->returnValue($this->requestMock));
        $contextMock->expects($this->any())
            ->method('getObjectManager')
            ->will($this->returnValue($this->objectManagerMock));
        $contextMock->expects($this->any())->method('getView')->will($this->returnValue($this->viewMock));
        $contextMock->expects($this->any())->method('getResponse')->will($this->returnValue($this->responseMock));

        $this->controller = new RemoveTrack(
            $contextMock,
            $this->shipmentLoaderMock
        );

        $this->viewMock->expects($this->any())
            ->method('getPage')
            ->willReturn($this->resultPageMock);
        $this->resultPageMock->expects($this->any())
            ->method('getConfig')
            ->willReturn($this->pageConfigMock);
        $this->pageConfigMock->expects($this->any())
            ->method('getTitle')
            ->willReturn($this->pageTitleMock);
    }

    /**
     * Shipment load sections
     *
     * @return void
     */
    protected function shipmentLoad()
    {
        $orderId = 1;
        $shipmentId = 1;
        $trackId = 1;
        $shipment = [];
        $tracking = [];

        $this->shipmentTrackMock->expects($this->once())
            ->method('load')
            ->with($trackId)
            ->will($this->returnSelf());
        $this->shipmentTrackMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($trackId));
        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with('track_id')
            ->will($this->returnValue($trackId));
        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with('order_id')
            ->will($this->returnValue($orderId));
        $this->requestMock->expects($this->at(2))
            ->method('getParam')
            ->with('shipment_id')
            ->will($this->returnValue($shipmentId));
        $this->requestMock->expects($this->at(3))
            ->method('getParam')
            ->with('shipment')
            ->will($this->returnValue($shipment));
        $this->requestMock->expects($this->at(4))
            ->method('getParam')
            ->with('tracking')
            ->will($this->returnValue($tracking));
        $this->shipmentLoaderMock->expects($this->once())->method('setOrderId')->with($orderId);
        $this->shipmentLoaderMock->expects($this->once())->method('setShipmentId')->with($shipmentId);
        $this->shipmentLoaderMock->expects($this->once())->method('setShipment')->with($shipment);
        $this->shipmentLoaderMock->expects($this->once())->method('setTracking')->with($tracking);
    }

    /**
     * Represent json json section
     *
     * @param array $errors
     * @return void
     */
    protected function representJson(array $errors)
    {
        $jsonHelper = $this->createPartialMock(Data::class, ['jsonEncode']);
        $jsonHelper->expects($this->once())
            ->method('jsonEncode')
            ->with($errors)
            ->will($this->returnValue('{json}'));
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with(Data::class)
            ->will($this->returnValue($jsonHelper));
        $this->responseMock->expects($this->once())
            ->method('representJson')
            ->with('{json}');
    }

    /**
     * Run test execute method
     */
    public function testExecute()
    {
        $response = 'html-data';
        $this->shipmentLoad();

        $this->shipmentLoaderMock->expects($this->once())
            ->method('load')
            ->will($this->returnValue($this->shipmentMock));
        $this->shipmentTrackMock->expects($this->once())
            ->method('delete')
            ->will($this->returnSelf());

        $layoutMock = $this->createPartialMock(Layout::class, ['getBlock']);
        $trackingBlockMock = $this->createPartialMock(
            Tracking::class,
            ['toHtml']
        );

        $trackingBlockMock->expects($this->once())
            ->method('toHtml')
            ->will($this->returnValue($response));
        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('shipment_tracking')
            ->will($this->returnValue($trackingBlockMock));
        $this->viewMock->expects($this->once())->method('loadLayout')->will($this->returnSelf());
        $this->viewMock->expects($this->any())->method('getLayout')->will($this->returnValue($layoutMock));
        $this->responseMock->expects($this->once())
            ->method('setBody')
            ->with($response);

        $this->assertNull($this->controller->execute());
    }

    /**
     * Run test execute method (fail track load)
     */
    public function testExecuteTrackIdFail()
    {
        $trackId = null;
        $errors = ['error' => true, 'message' => 'We can\'t load track with retrieving identifier right now.'];

        $this->shipmentTrackMock->expects($this->once())
            ->method('load')
            ->with($trackId)
            ->will($this->returnSelf());
        $this->shipmentTrackMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($trackId));
        $this->representJson($errors);

        $this->assertNull($this->controller->execute());
    }

    /**
     * Run test execute method (fail load shipment)
     */
    public function testExecuteShipmentLoadFail()
    {
        $errors = [
            'error' => true,
            'message' => 'We can\'t initialize shipment for delete tracking number.',
        ];
        $this->shipmentLoad();

        $this->shipmentLoaderMock->expects($this->once())
            ->method('load')
            ->will($this->returnValue(null));
        $this->representJson($errors);

        $this->assertNull($this->controller->execute());
    }

    /**
     * Run test execute method (delete exception)
     */
    public function testExecuteDeleteFail()
    {
        $errors = ['error' => true, 'message' => 'We can\'t delete tracking number.'];
        $this->shipmentLoad();

        $this->shipmentLoaderMock->expects($this->once())
            ->method('load')
            ->will($this->returnValue($this->shipmentMock));
        $this->shipmentTrackMock->expects($this->once())
            ->method('delete')
            ->will($this->throwException(new \Exception()));
        $this->representJson($errors);

        $this->assertNull($this->controller->execute());
    }
}
