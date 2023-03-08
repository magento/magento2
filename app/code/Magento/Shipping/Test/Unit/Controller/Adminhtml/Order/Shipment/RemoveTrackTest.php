<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

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

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->requestMock = $this->createPartialMock(Http::class, ['getParam']);
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
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
        $this->shipmentLoaderMock = $this->getMockBuilder(ShipmentLoader::class)
            ->addMethods(['setOrderId', 'setShipmentId', 'setShipment', 'setTracking'])
            ->onlyMethods(['load'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageMock = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageTitleMock = $this->getMockBuilder(Title::class)
            ->disableOriginalConstructor()
            ->getMock();

        $contextMock = $this->getMockBuilder(Context::class)
            ->addMethods(['getTitle'])
            ->onlyMethods(['getRequest', 'getObjectManager', 'getView', 'getResponse'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(Track::class)
            ->willReturn($this->shipmentTrackMock);

        $contextMock->expects($this->any())->method('getRequest')->willReturn($this->requestMock);
        $contextMock->expects($this->any())
            ->method('getObjectManager')
            ->willReturn($this->objectManagerMock);
        $contextMock->expects($this->any())->method('getView')->willReturn($this->viewMock);
        $contextMock->expects($this->any())->method('getResponse')->willReturn($this->responseMock);

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
    protected function shipmentLoad(): void
    {
        $orderId = 1;
        $shipmentId = 1;
        $trackId = 1;
        $shipment = [];
        $tracking = [];

        $this->shipmentTrackMock->expects($this->once())
            ->method('load')
            ->with($trackId)->willReturnSelf();
        $this->shipmentTrackMock->expects($this->once())
            ->method('getId')
            ->willReturn($trackId);
        $this->requestMock
            ->method('getParam')
            ->withConsecutive(['track_id'], ['order_id'], ['shipment_id'], ['shipment'], ['tracking'])
            ->willReturnOnConsecutiveCalls($trackId, $orderId, $shipmentId, $shipment, $tracking);
        $this->shipmentLoaderMock->expects($this->once())->method('setOrderId')->with($orderId);
        $this->shipmentLoaderMock->expects($this->once())->method('setShipmentId')->with($shipmentId);
        $this->shipmentLoaderMock->expects($this->once())->method('setShipment')->with($shipment);
        $this->shipmentLoaderMock->expects($this->once())->method('setTracking')->with($tracking);
    }

    /**
     * Represent json json section
     *
     * @param array $errors
     *
     * @return void
     */
    protected function representJson(array $errors): void
    {
        $jsonHelper = $this->createPartialMock(Data::class, ['jsonEncode']);
        $jsonHelper->expects($this->once())
            ->method('jsonEncode')
            ->with($errors)
            ->willReturn('{json}');
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with(Data::class)
            ->willReturn($jsonHelper);
        $this->responseMock->expects($this->once())
            ->method('representJson')
            ->with('{json}');
    }

    /**
     * Run test execute method
     *
     * @return void
     */
    public function testExecute(): void
    {
        $response = 'html-data';
        $this->shipmentLoad();

        $this->shipmentLoaderMock->expects($this->once())
            ->method('load')
            ->willReturn($this->shipmentMock);
        $this->shipmentTrackMock->expects($this->once())
            ->method('delete')->willReturnSelf();

        $layoutMock = $this->createPartialMock(Layout::class, ['getBlock']);
        $trackingBlockMock = $this->createPartialMock(
            Tracking::class,
            ['toHtml']
        );

        $trackingBlockMock->expects($this->once())
            ->method('toHtml')
            ->willReturn($response);
        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('shipment_tracking')
            ->willReturn($trackingBlockMock);
        $this->viewMock->expects($this->once())->method('loadLayout')->willReturnSelf();
        $this->viewMock->expects($this->any())->method('getLayout')->willReturn($layoutMock);
        $this->responseMock->expects($this->once())
            ->method('setBody')
            ->with($response);

        $this->assertNull($this->controller->execute());
    }

    /**
     * Run test execute method (fail track load)
     *
     * @return void
     */
    public function testExecuteTrackIdFail(): void
    {
        $trackId = null;
        $errors = ['error' => true, 'message' => 'We can\'t load track with retrieving identifier right now.'];

        $this->shipmentTrackMock->expects($this->once())
            ->method('load')
            ->with($trackId)->willReturnSelf();
        $this->shipmentTrackMock->expects($this->once())
            ->method('getId')
            ->willReturn($trackId);
        $this->representJson($errors);

        $this->assertNull($this->controller->execute());
    }

    /**
     * Run test execute method (fail load shipment)
     *
     * @return void
     */
    public function testExecuteShipmentLoadFail(): void
    {
        $errors = [
            'error' => true,
            'message' => 'We can\'t initialize shipment for delete tracking number.',
        ];
        $this->shipmentLoad();

        $this->shipmentLoaderMock->expects($this->once())
            ->method('load')
            ->willReturn(null);
        $this->representJson($errors);

        $this->assertNull($this->controller->execute());
    }

    /**
     * Run test execute method (delete exception)
     *
     * @return void
     */
    public function testExecuteDeleteFail(): void
    {
        $errors = ['error' => true, 'message' => 'We can\'t delete tracking number.'];
        $this->shipmentLoad();

        $this->shipmentLoaderMock->expects($this->once())
            ->method('load')
            ->willReturn($this->shipmentMock);
        $this->shipmentTrackMock->expects($this->once())
            ->method('delete')
            ->willThrowException(new \Exception());
        $this->representJson($errors);

        $this->assertNull($this->controller->execute());
    }
}
