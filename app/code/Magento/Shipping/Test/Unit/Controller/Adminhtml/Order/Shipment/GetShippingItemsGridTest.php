<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Test\Unit\Controller\Adminhtml\Order\Shipment;

/**
 * Class GetShippingItemsGridTest
 */
class GetShippingItemsGridTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $shipmentLoaderMock;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\App\Response\Http|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\Framework\App\View|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $viewMock;

    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\Shipment\GetShippingItemsGrid
     */
    protected $controller;

    protected function setUp(): void
    {
        $this->requestMock = $this->createPartialMock(
            \Magento\Framework\App\Request\Http::class,
            ['getParam', '__wakeup']
        );
        $this->shipmentLoaderMock = $this->createPartialMock(
            \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader::class,
            ['setOrderId', 'setShipmentId', 'setShipment', 'setTracking', 'load', '__wakeup']
        );
        $this->viewMock = $this->createPartialMock(
            \Magento\Framework\App\View::class,
            ['getLayout', 'renderLayout', '__wakeup']
        );
        $this->responseMock = $this->createPartialMock(
            \Magento\Framework\App\Response\Http::class,
            ['setBody', '__wakeup']
        );

        $contextMock = $this->createPartialMock(
            \Magento\Backend\App\Action\Context::class,
            ['getRequest', 'getResponse', 'getView', '__wakeup']
        );

        $contextMock->expects($this->any())->method('getRequest')->willReturn($this->requestMock);
        $contextMock->expects($this->any())->method('getResponse')->willReturn($this->responseMock);
        $contextMock->expects($this->any())->method('getView')->willReturn($this->viewMock);

        $this->controller = new \Magento\Shipping\Controller\Adminhtml\Order\Shipment\GetShippingItemsGrid(
            $contextMock,
            $this->shipmentLoaderMock
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
        $result = 'result-html';

        $layoutMock = $this->createPartialMock(\Magento\Framework\View\Layout::class, ['createBlock']);
        $gridMock = $this->createPartialMock(
            \Magento\Shipping\Block\Adminhtml\Order\Packaging\Grid::class,
            ['setIndex', 'toHtml']
        );

        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with('order_id')
            ->willReturn($orderId);
        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with('shipment_id')
            ->willReturn($shipmentId);
        $this->requestMock->expects($this->at(2))
            ->method('getParam')
            ->with('shipment')
            ->willReturn($shipment);
        $this->requestMock->expects($this->at(3))
            ->method('getParam')
            ->with('tracking')
            ->willReturn($tracking);
        $this->shipmentLoaderMock->expects($this->once())->method('setOrderId')->with($orderId);
        $this->shipmentLoaderMock->expects($this->once())->method('setShipmentId')->with($shipmentId);
        $this->shipmentLoaderMock->expects($this->once())->method('setShipment')->with($shipment);
        $this->shipmentLoaderMock->expects($this->once())->method('setTracking')->with($tracking);
        $this->shipmentLoaderMock->expects($this->once())->method('load');
        $layoutMock->expects($this->once())
            ->method('createBlock')
            ->with(\Magento\Shipping\Block\Adminhtml\Order\Packaging\Grid::class)
            ->willReturn($gridMock);
        $this->viewMock->expects($this->once())
            ->method('getLayout')
            ->willReturn($layoutMock);
        $this->responseMock->expects($this->once())
            ->method('setBody')
            ->with($result)
            ->willReturnSelf();
        $this->requestMock->expects($this->at(4))
            ->method('getParam')
            ->with('index');
        $gridMock->expects($this->once())
            ->method('setIndex')
            ->willReturnSelf();
        $gridMock->expects($this->once())
            ->method('toHtml')
            ->willReturn($result);

        $this->assertNotEmpty('result-html', $this->controller->execute());
    }
}
