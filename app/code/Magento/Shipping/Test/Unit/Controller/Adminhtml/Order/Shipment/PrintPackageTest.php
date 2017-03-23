<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Test\Unit\Controller\Adminhtml\Order\Shipment;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class PrintPackageTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PrintPackageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentLoaderMock;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\App\Response\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileFactoryMock;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Backend\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionMock;

    /**
     * @var \Magento\Framework\App\ActionFlag|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $actionFlag;

    /**
     * @var \Magento\Sales\Model\Order\Shipment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentMock;

    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\Shipment\PrintPackage
     */
    protected $controller;

    protected function setUp()
    {
        $orderId = 1;
        $shipmentId = 1;
        $shipment = [];
        $tracking = [];

        $this->shipmentLoaderMock = $this->getMock(
            \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader::class,
            ['setOrderId', 'setShipmentId', 'setShipment', 'setTracking', 'load'],
            [],
            '',
            false
        );
        $this->requestMock = $this->getMock(\Magento\Framework\App\Request\Http::class, ['getParam'], [], '', false);
        $this->objectManagerMock = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->responseMock = $this->getMock(\Magento\Framework\App\Response\Http::class, [], [], '', false);
        $this->sessionMock = $this->getMock(\Magento\Backend\Model\Session::class, ['setIsUrlNotice'], [], '', false);
        $this->actionFlag = $this->getMock(\Magento\Framework\App\ActionFlag::class, ['get'], [], '', false);
        $this->shipmentMock = $this->getMock(\Magento\Sales\Model\Order\Shipment::class, ['__wakeup'], [], '', false);
        $this->fileFactoryMock = $this->getMock(
            \Magento\Framework\App\Response\Http\FileFactory::class,
            ['create'],
            [],
            '',
            false
        );

        $contextMock = $this->getMock(
            \Magento\Backend\App\Action\Context::class,
            ['getRequest', 'getObjectManager', 'getResponse', 'getSession', 'getActionFlag'],
            [],
            '',
            false
        );

        $contextMock->expects($this->any())->method('getRequest')->will($this->returnValue($this->requestMock));
        $contextMock->expects($this->any())
            ->method('getObjectManager')
            ->will($this->returnValue($this->objectManagerMock));
        $contextMock->expects($this->any())->method('getResponse')->will($this->returnValue($this->responseMock));
        $contextMock->expects($this->any())->method('getSession')->will($this->returnValue($this->sessionMock));
        $contextMock->expects($this->any())->method('getActionFlag')->will($this->returnValue($this->actionFlag));

        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with('order_id')
            ->will($this->returnValue($orderId));
        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with('shipment_id')
            ->will($this->returnValue($shipmentId));
        $this->requestMock->expects($this->at(2))
            ->method('getParam')
            ->with('shipment')
            ->will($this->returnValue($shipment));
        $this->requestMock->expects($this->at(3))
            ->method('getParam')
            ->with('tracking')
            ->will($this->returnValue($tracking));
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

        $this->controller = new \Magento\Shipping\Controller\Adminhtml\Order\Shipment\PrintPackage(
            $contextMock,
            $this->shipmentLoaderMock,
            $this->fileFactoryMock
        );
    }

    /**
     * Run test execute method
     */
    public function testExecute()
    {
        $date = '9999-99-99_77-77-77';
        $content = 'PDF content';

        $packagingMock = $this->getMock(
            \Magento\Shipping\Model\Order\Pdf\Packaging::class,
            ['getPdf'],
            [],
            '',
            false
        );
        $pdfMock = $this->getMock(
            \Zend_Pdf::class,
            ['render'],
            [],
            '',
            false
        );
        $dateTimeMock = $this->getMock(
            \Magento\Framework\Stdlib\DateTime\DateTime::class,
            ['date'],
            [],
            '',
            false
        );

        $this->shipmentLoaderMock->expects($this->once())
            ->method('load')
            ->will($this->returnValue($this->shipmentMock));
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(\Magento\Shipping\Model\Order\Pdf\Packaging::class)
            ->will($this->returnValue($packagingMock));
        $packagingMock->expects($this->once())
            ->method('getPdf')
            ->with($this->shipmentMock)
            ->will($this->returnValue($pdfMock));
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with(\Magento\Framework\Stdlib\DateTime\DateTime::class)
            ->will($this->returnValue($dateTimeMock));
        $dateTimeMock->expects($this->once())->method('date')->with('Y-m-d_H-i-s')->will($this->returnValue($date));
        $pdfMock->expects($this->once())->method('render')->will($this->returnValue($content));
        $this->fileFactoryMock->expects($this->once())
            ->method('create')
            ->with(
                'packingslip' . $date . '.pdf',
                $content,
                DirectoryList::VAR_DIR,
                'application/pdf'
            )->will($this->returnValue('result-pdf-content'));

        $this->assertEquals('result-pdf-content', $this->controller->execute());
    }

    /**
     * Run test execute method (fail print)
     */
    public function testExecuteFail()
    {
        $this->shipmentLoaderMock->expects($this->once())
            ->method('load')
            ->will($this->returnValue(false));
        $this->shipmentLoaderMock->expects($this->once())
            ->method('load')
            ->will($this->returnValue(false));
        $this->actionFlag->expects($this->once())
            ->method('get')
            ->with('', \Magento\Backend\App\AbstractAction::FLAG_IS_URLS_CHECKED)
            ->will($this->returnValue(true));
        $this->sessionMock->expects($this->once())
            ->method('setIsUrlNotice')
            ->with(true);

        $this->assertNull($this->controller->execute());
    }
}
