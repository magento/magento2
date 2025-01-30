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
use Magento\Framework\View\Layout;
use Magento\Shipping\Block\Adminhtml\Order\Packaging\Grid;
use Magento\Shipping\Controller\Adminhtml\Order\Shipment\GetShippingItemsGrid;
use Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetShippingItemsGridTest extends TestCase
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
     * @var \Magento\Framework\App\Response\Http|MockObject
     */
    protected $responseMock;

    /**
     * @var View|MockObject
     */
    protected $viewMock;

    /**
     * @var GetShippingItemsGrid
     */
    protected $controller;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->requestMock = $this->getMockBuilder(Http::class)
            ->addMethods(['__wakeup'])
            ->onlyMethods(['getParam'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->shipmentLoaderMock = $this->getMockBuilder(ShipmentLoader::class)
            ->addMethods(['setOrderId', 'setShipmentId', 'setShipment', 'setTracking', '__wakeup'])
            ->onlyMethods(['load'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->viewMock = $this->getMockBuilder(View::class)
            ->addMethods(['__wakeup'])
            ->onlyMethods(['getLayout', 'renderLayout'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseMock = $this->createPartialMock(
            \Magento\Framework\App\Response\Http::class,
            ['setBody', '__wakeup']
        );

        $contextMock = $this->getMockBuilder(Context::class)
            ->addMethods(['__wakeup'])
            ->onlyMethods(['getRequest', 'getResponse', 'getView'])
            ->disableOriginalConstructor()
            ->getMock();

        $contextMock->expects($this->any())->method('getRequest')->willReturn($this->requestMock);
        $contextMock->expects($this->any())->method('getResponse')->willReturn($this->responseMock);
        $contextMock->expects($this->any())->method('getView')->willReturn($this->viewMock);

        $this->controller = new GetShippingItemsGrid(
            $contextMock,
            $this->shipmentLoaderMock
        );
    }

    /**
     * Run test execute method
     *
     * @return void
     */
    public function testExecute(): void
    {
        $orderId = 1;
        $shipmentId = 1;
        $shipment = [];
        $tracking = [];
        $result = 'result-html';

        $layoutMock = $this->createPartialMock(Layout::class, ['createBlock']);
        $gridMock = $this->getMockBuilder(Grid::class)
            ->addMethods(['setIndex'])
            ->onlyMethods(['toHtml'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->shipmentLoaderMock->expects($this->once())->method('setOrderId')->with($orderId);
        $this->shipmentLoaderMock->expects($this->once())->method('setShipmentId')->with($shipmentId);
        $this->shipmentLoaderMock->expects($this->once())->method('setShipment')->with($shipment);
        $this->shipmentLoaderMock->expects($this->once())->method('setTracking')->with($tracking);
        $this->shipmentLoaderMock->expects($this->once())->method('load');
        $layoutMock->expects($this->once())
            ->method('createBlock')
            ->with(Grid::class)
            ->willReturn($gridMock);
        $this->viewMock->expects($this->once())
            ->method('getLayout')
            ->willReturn($layoutMock);
        $this->responseMock->expects($this->once())
            ->method('setBody')
            ->with($result)->willReturnSelf();
        $this->requestMock
            ->method('getParam')
            ->willReturnCallback(fn($param) => match ([$param]) {
                ['order_id'] => $orderId,
                ['shipment_id'] => $shipmentId,
                ['shipment'] => $shipment,
                ['tracking'] =>  $tracking,
                ['index'] => null
            });
        $gridMock->expects($this->once())
            ->method('setIndex')->willReturnSelf();
        $gridMock->expects($this->once())
            ->method('toHtml')
            ->willReturn($result);

        $this->assertNotEmpty($this->controller->execute());
    }
}
