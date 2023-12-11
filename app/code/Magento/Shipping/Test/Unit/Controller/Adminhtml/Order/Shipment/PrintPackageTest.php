<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Shipping\Test\Unit\Controller\Adminhtml\Order\Shipment;

use Magento\Backend\App\AbstractAction;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Model\Order\Shipment;
use Magento\Shipping\Controller\Adminhtml\Order\Shipment\PrintPackage;
use Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader;
use Magento\Shipping\Model\Order\Pdf\Packaging;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PrintPackageTest extends TestCase
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
     * @var FileFactory|MockObject
     */
    protected $fileFactoryMock;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $objectManagerMock;

    /**
     * @var Session|MockObject
     */
    protected $sessionMock;

    /**
     * @var ActionFlag|MockObject
     */
    protected $actionFlag;

    /**
     * @var Shipment|MockObject
     */
    protected $shipmentMock;

    /**
     * @var PrintPackage
     */
    protected $controller;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $orderId = 1;
        $shipmentId = 1;
        $shipment = [];
        $tracking = [];

        $this->shipmentLoaderMock = $this->getMockBuilder(ShipmentLoader::class)
            ->addMethods(['setOrderId', 'setShipmentId', 'setShipment', 'setTracking'])
            ->onlyMethods(['load'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->createPartialMock(Http::class, ['getParam']);
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->responseMock = $this->createMock(\Magento\Framework\App\Response\Http::class);
        $this->sessionMock = $this->getMockBuilder(Session::class)
            ->addMethods(['setIsUrlNotice'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->actionFlag = $this->createPartialMock(ActionFlag::class, ['get']);
        $this->shipmentMock = $this->createPartialMock(Shipment::class, ['__wakeup']);
        $this->fileFactoryMock = $this->createPartialMock(
            FileFactory::class,
            ['create']
        );

        $contextMock = $this->createPartialMock(
            Context::class,
            ['getRequest', 'getObjectManager', 'getResponse', 'getSession', 'getActionFlag']
        );

        $contextMock->expects($this->any())->method('getRequest')->willReturn($this->requestMock);
        $contextMock->expects($this->any())
            ->method('getObjectManager')
            ->willReturn($this->objectManagerMock);
        $contextMock->expects($this->any())->method('getResponse')->willReturn($this->responseMock);
        $contextMock->expects($this->any())->method('getSession')->willReturn($this->sessionMock);
        $contextMock->expects($this->any())->method('getActionFlag')->willReturn($this->actionFlag);

        $this->requestMock
            ->method('getParam')
            ->withConsecutive(['order_id'], ['shipment_id'], ['shipment'], ['tracking'])
            ->willReturnOnConsecutiveCalls($orderId, $shipmentId, $shipment, $tracking);
        $this->shipmentLoaderMock->expects($this->once())
            ->method('setOrderId')
            ->with($orderId);
        $this->shipmentLoaderMock->expects($this->once())
            ->method('setShipmentId')
            ->with($shipmentId);
        $this->shipmentLoaderMock->expects($this->once())
            ->method('setShipment')
            ->with($shipment);
        $this->shipmentLoaderMock->expects($this->once())
            ->method('setTracking')
            ->with($tracking);

        $this->controller = new PrintPackage(
            $contextMock,
            $this->shipmentLoaderMock,
            $this->fileFactoryMock
        );
    }

    /**
     * Run test execute method
     *
     * @return void
     */
    public function testExecute(): void
    {
        $date = '9999-99-99_77-77-77';
        $content = 'PDF content';

        $packagingMock = $this->createPartialMock(Packaging::class, ['getPdf']);
        $pdfMock = $this->createPartialMock(\Zend_Pdf::class, ['render']);
        $dateTimeMock = $this->createPartialMock(DateTime::class, ['date']);

        $this->shipmentLoaderMock->expects($this->once())
            ->method('load')
            ->willReturn($this->shipmentMock);
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(Packaging::class)
            ->willReturn($packagingMock);
        $packagingMock->expects($this->once())
            ->method('getPdf')
            ->with($this->shipmentMock)
            ->willReturn($pdfMock);
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with(DateTime::class)
            ->willReturn($dateTimeMock);
        $dateTimeMock->expects($this->once())->method('date')->with('Y-m-d_H-i-s')->willReturn($date);
        $pdfMock->expects($this->once())->method('render')->willReturn($content);
        $this->fileFactoryMock->expects($this->once())
            ->method('create')
            ->with(
                'packingslip' . $date . '.pdf',
                $content,
                DirectoryList::VAR_DIR,
                'application/pdf'
            )->willReturn('result-pdf-content');

        $this->assertEquals('result-pdf-content', $this->controller->execute());
    }

    /**
     * Run test execute method (fail print)
     *
     * @return void
     */
    public function testExecuteFail(): void
    {
        $this->shipmentLoaderMock->expects($this->once())
            ->method('load')
            ->willReturn(false);
        $this->shipmentLoaderMock->expects($this->once())
            ->method('load')
            ->willReturn(false);
        $this->actionFlag->expects($this->once())
            ->method('get')
            ->with('', AbstractAction::FLAG_IS_URLS_CHECKED)
            ->willReturn(true);
        $this->sessionMock->expects($this->once())
            ->method('setIsUrlNotice')
            ->with(true);

        $this->assertNull($this->controller->execute());
    }
}
