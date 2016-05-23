<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Test\Unit\Controller\Adminhtml\Order\Shipment;

/**
 * Class PrintLabelTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PrintLabelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentLoaderMock;

    /**
     * @var \Magento\Sales\Model\Order\Shipment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentMock;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileFactoryMock;

    /**
     * @var \Magento\Shipping\Model\Shipping\LabelGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $labelGenerator;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\App\Response\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\Framework\Message\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManagerMock;

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
     * @var \Magento\Backend\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperMock;

    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\Shipment\PrintLabel
     */
    protected $controller;

    protected function setUp()
    {
        $this->shipmentLoaderMock = $this->getMock(
            'Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader',
            ['setOrderId', 'setShipmentId', 'setShipment', 'setTracking', 'load'],
            [],
            '',
            false
        );
        $this->labelGenerator = $this->getMock(
            'Magento\Shipping\Model\Shipping\LabelGenerator',
            ['createPdfPageFromImageString'],
            [],
            '',
            false
        );
        $this->fileFactoryMock = $this->getMock(
            'Magento\Framework\App\Response\Http\FileFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->shipmentMock = $this->getMock(
            'Magento\Sales\Model\Order\Shipment',
            ['getIncrementId', 'getShippingLabel', '__wakeup'],
            [],
            '',
            false
        );
        $this->messageManagerMock = $this->getMock(
            'Magento\Framework\Message\Manager',
            ['addError'],
            [],
            '',
            false
        );
        $this->requestMock = $this->getMock('Magento\Framework\App\Request\Http', ['getParam'], [], '', false);
        $this->responseMock = $this->getMock('Magento\Framework\App\Response\Http', [], [], '', false);
        $this->sessionMock = $this->getMock('Magento\Backend\Model\Session', ['setIsUrlNotice'], [], '', false);
        $this->actionFlag = $this->getMock('Magento\Framework\App\ActionFlag', ['get'], [], '', false);
        $this->objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->helperMock = $this->getMock(
            'Magento\Backend\Helper\Data',
            ['getUrl'],
            [],
            '',
            false
        );
        $contextMock = $this->getMock(
            'Magento\Backend\App\Action\Context',
            [
                'getRequest',
                'getResponse',
                'getMessageManager',
                'getSession',
                'getActionFlag',
                'getObjectManager',
                'getHelper'
            ],
            [],
            '',
            false
        );

        $contextMock->expects($this->any())->method('getRequest')->will($this->returnValue($this->requestMock));
        $contextMock->expects($this->any())->method('getResponse')->will($this->returnValue($this->responseMock));
        $contextMock->expects($this->any())->method('getSession')->will($this->returnValue($this->sessionMock));
        $contextMock->expects($this->any())->method('getActionFlag')->will($this->returnValue($this->actionFlag));
        $contextMock->expects($this->any())->method('getHelper')->will($this->returnValue($this->helperMock));
        $contextMock->expects($this->any())
            ->method('getMessageManager')
            ->will($this->returnValue($this->messageManagerMock));
        $contextMock->expects($this->any())
            ->method('getObjectManager')
            ->will($this->returnValue($this->objectManagerMock));
        $this->loadShipment();

        $this->controller = new \Magento\Shipping\Controller\Adminhtml\Order\Shipment\PrintLabel(
            $contextMock,
            $this->shipmentLoaderMock,
            $this->labelGenerator,
            $this->fileFactoryMock
        );
    }

    /**
     * Load shipment object
     *
     * @return void
     */
    protected function loadShipment()
    {
        $orderId = 1;
        $shipmentId = 1;
        $shipment = [];
        $tracking = [];

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
    }

    /**
     * Run file create section
     *
     * @return string
     */
    protected function fileCreate()
    {
        $resultContent = 'result-pdf-content';
        $incrementId = '1000001';

        $this->shipmentMock->expects($this->once())
            ->method('getIncrementId')
            ->will($this->returnValue($incrementId));
        $this->fileFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($resultContent));

        return $resultContent;
    }

    /**
     * Redirect into response section
     *
     * @return void
     */
    protected function redirectSection()
    {
        $this->actionFlag->expects($this->once())
            ->method('get')
            ->with('', \Magento\Backend\App\AbstractAction::FLAG_IS_URLS_CHECKED)
            ->will($this->returnValue(true));
        $this->sessionMock->expects($this->once())->method('setIsUrlNotice')->with(true);
        $this->helperMock->expects($this->once())->method('getUrl')->will($this->returnValue('redirect-path'));
        $this->responseMock->expects($this->once())->method('setRedirect');
    }

    /**
     * Run test execute method
     */
    public function testExecute()
    {
        $labelContent = '%PDF-label-content';

        $this->shipmentLoaderMock->expects($this->once())
            ->method('load')
            ->will($this->returnValue($this->shipmentMock));
        $this->shipmentMock->expects($this->once())
            ->method('getShippingLabel')
            ->will($this->returnValue($labelContent));

        $this->assertEquals($this->fileCreate(), $this->controller->execute());
    }

    /**
     * Run test execute method (create new file for render)
     */
    public function testExecuteFromImageString()
    {
        $labelContent = 'Label-content';
        $pdfPageMock = $this->getMock(
            'Zend_Pdf_Page',
            ['render', 'getPageDictionary'],
            [],
            '',
            false
        );
        $pageDictionaryMock = $this->getMock(
            'Zend_Pdf_Element_Dictionary',
            ['touch', 'getObject'],
            [],
            '',
            false
        );

        $this->shipmentLoaderMock->expects($this->once())
            ->method('load')
            ->will($this->returnValue($this->shipmentMock));
        $this->shipmentMock->expects($this->once())
            ->method('getShippingLabel')
            ->will($this->returnValue($labelContent));
        $this->labelGenerator->expects($this->once())
            ->method('createPdfPageFromImageString')
            ->with($labelContent)
            ->will($this->returnValue($pdfPageMock));
        $pdfPageMock->expects($this->any())
            ->method('getPageDictionary')
            ->will($this->returnValue($pageDictionaryMock));
        $pageDictionaryMock->expects($this->any())
            ->method('getObject')
            ->will($this->returnSelf());

        $this->assertEquals($this->fileCreate(), $this->controller->execute());
    }

    /**
     * Run test execute method (fail load page from image string)
     */
    public function testExecuteImageStringFail()
    {
        $labelContent = 'Label-content';
        $incrementId = '1000001';

        $loggerMock = $this->getMock('Psr\Log\LoggerInterface');

        $this->shipmentLoaderMock->expects($this->once())
            ->method('load')
            ->will($this->returnValue($this->shipmentMock));
        $this->shipmentMock->expects($this->once())
            ->method('getShippingLabel')
            ->will($this->returnValue($labelContent));
        $this->shipmentMock->expects($this->once())
            ->method('getIncrementId')
            ->will($this->returnValue($incrementId));
        $this->labelGenerator->expects($this->once())
            ->method('createPdfPageFromImageString')
            ->with($labelContent)
            ->will($this->returnValue(false));
        $this->messageManagerMock->expects($this->at(0))
            ->method('addError')
            ->with(sprintf('We don\'t recognize or support the file extension in this shipment: %s.', $incrementId))
            ->will($this->throwException(new \Exception()));
        $this->messageManagerMock->expects($this->at(1))
            ->method('addError')
            ->with('An error occurred while creating shipping label.')
            ->will($this->returnSelf());
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with('Psr\Log\LoggerInterface')
            ->will($this->returnValue($loggerMock));
        $loggerMock->expects($this->once())
            ->method('critical');
        $this->requestMock->expects($this->at(4))
            ->method('getParam')
            ->with('shipment_id')
            ->will($this->returnValue(1));
        $this->redirectSection();

        $this->assertNull($this->controller->execute());
    }

    /**
     * Run test execute method (fail load shipment model)
     */
    public function testExecuteLoadShipmentFail()
    {
        $this->shipmentLoaderMock->expects($this->once())
            ->method('load')
            ->willThrowException(new \Magento\Framework\Exception\LocalizedException(__('message')));
        $this->messageManagerMock->expects($this->once())->method('addError')->will($this->returnSelf());
        $this->redirectSection();

        $this->assertNull($this->controller->execute());
    }
}
