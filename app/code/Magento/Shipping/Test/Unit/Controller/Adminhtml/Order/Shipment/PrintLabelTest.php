<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Test\Unit\Controller\Adminhtml\Order\Shipment;

/**
 * Class PrintLabelTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PrintLabelTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $shipmentLoaderMock;

    /**
     * @var \Magento\Sales\Model\Order\Shipment|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $shipmentMock;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $fileFactoryMock;

    /**
     * @var \Magento\Shipping\Model\Shipping\LabelGenerator|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $labelGenerator;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\App\Response\Http|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\Framework\Message\Manager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Backend\Model\Session|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $sessionMock;

    /**
     * @var \Magento\Framework\App\ActionFlag|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $actionFlag;

    /**
     * @var \Magento\Backend\Helper\Data|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $helperMock;

    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\Shipment\PrintLabel
     */
    protected $controller;

    protected function setUp(): void
    {
        $this->shipmentLoaderMock = $this->createPartialMock(
            \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader::class,
            ['setOrderId', 'setShipmentId', 'setShipment', 'setTracking', 'load']
        );
        $this->labelGenerator = $this->createPartialMock(
            \Magento\Shipping\Model\Shipping\LabelGenerator::class,
            ['createPdfPageFromImageString']
        );
        $this->fileFactoryMock = $this->createPartialMock(
            \Magento\Framework\App\Response\Http\FileFactory::class,
            ['create']
        );
        $this->shipmentMock = $this->createPartialMock(
            \Magento\Sales\Model\Order\Shipment::class,
            ['getIncrementId', 'getShippingLabel', '__wakeup']
        );
        $this->messageManagerMock = $this->createPartialMock(\Magento\Framework\Message\Manager::class, ['addError']);
        $this->requestMock = $this->createPartialMock(\Magento\Framework\App\Request\Http::class, ['getParam']);
        $this->responseMock = $this->createMock(\Magento\Framework\App\Response\Http::class);
        $this->sessionMock = $this->createPartialMock(\Magento\Backend\Model\Session::class, ['setIsUrlNotice']);
        $this->actionFlag = $this->createPartialMock(\Magento\Framework\App\ActionFlag::class, ['get']);
        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->helperMock = $this->createPartialMock(\Magento\Backend\Helper\Data::class, ['getUrl']);
        $contextMock = $this->createPartialMock(\Magento\Backend\App\Action\Context::class, [
                'getRequest',
                'getResponse',
                'getMessageManager',
                'getSession',
                'getActionFlag',
                'getObjectManager',
                'getHelper'
            ]);

        $contextMock->expects($this->any())->method('getRequest')->willReturn($this->requestMock);
        $contextMock->expects($this->any())->method('getResponse')->willReturn($this->responseMock);
        $contextMock->expects($this->any())->method('getSession')->willReturn($this->sessionMock);
        $contextMock->expects($this->any())->method('getActionFlag')->willReturn($this->actionFlag);
        $contextMock->expects($this->any())->method('getHelper')->willReturn($this->helperMock);
        $contextMock->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($this->messageManagerMock);
        $contextMock->expects($this->any())
            ->method('getObjectManager')
            ->willReturn($this->objectManagerMock);
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
            ->willReturn($incrementId);
        $this->fileFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultContent);

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
            ->willReturn(true);
        $this->sessionMock->expects($this->once())->method('setIsUrlNotice')->with(true);
        $this->helperMock->expects($this->once())->method('getUrl')->willReturn('redirect-path');
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
            ->willReturn($this->shipmentMock);
        $this->shipmentMock->expects($this->once())
            ->method('getShippingLabel')
            ->willReturn($labelContent);

        $this->assertEquals($this->fileCreate(), $this->controller->execute());
    }

    /**
     * Run test execute method (create new file for render)
     */
    public function testExecuteFromImageString()
    {
        $labelContent = 'Label-content';
        $pdfPageMock = $this->createPartialMock(\Zend_Pdf_Page::class, ['render', 'getPageDictionary']);
        $pageDictionaryMock = $this->createPartialMock(\Zend_Pdf_Element_Dictionary::class, ['touch', 'getObject']);

        $this->shipmentLoaderMock->expects($this->once())
            ->method('load')
            ->willReturn($this->shipmentMock);
        $this->shipmentMock->expects($this->once())
            ->method('getShippingLabel')
            ->willReturn($labelContent);
        $this->labelGenerator->expects($this->once())
            ->method('createPdfPageFromImageString')
            ->with($labelContent)
            ->willReturn($pdfPageMock);
        $pdfPageMock->expects($this->any())
            ->method('getPageDictionary')
            ->willReturn($pageDictionaryMock);
        $pageDictionaryMock->expects($this->any())
            ->method('getObject')
            ->willReturnSelf();

        $this->assertEquals($this->fileCreate(), $this->controller->execute());
    }

    /**
     * Run test execute method (fail load page from image string)
     */
    public function testExecuteImageStringFail()
    {
        $labelContent = 'Label-content';
        $incrementId = '1000001';

        $loggerMock = $this->createMock(\Psr\Log\LoggerInterface::class);

        $this->shipmentLoaderMock->expects($this->once())
            ->method('load')
            ->willReturn($this->shipmentMock);
        $this->shipmentMock->expects($this->once())
            ->method('getShippingLabel')
            ->willReturn($labelContent);
        $this->shipmentMock->expects($this->once())
            ->method('getIncrementId')
            ->willReturn($incrementId);
        $this->labelGenerator->expects($this->once())
            ->method('createPdfPageFromImageString')
            ->with($labelContent)
            ->willReturn(false);
        $this->messageManagerMock->expects($this->at(0))
            ->method('addError')
            ->with(sprintf('We don\'t recognize or support the file extension in this shipment: %s.', $incrementId))
            ->will($this->throwException(new \Exception()));
        $this->messageManagerMock->expects($this->at(1))
            ->method('addError')
            ->with('An error occurred while creating shipping label.')
            ->willReturnSelf();
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with(\Psr\Log\LoggerInterface::class)
            ->willReturn($loggerMock);
        $loggerMock->expects($this->once())
            ->method('critical');
        $this->requestMock->expects($this->at(4))
            ->method('getParam')
            ->with('shipment_id')
            ->willReturn(1);
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
        $this->messageManagerMock->expects($this->once())->method('addError')->willReturnSelf();
        $this->redirectSection();

        $this->assertNull($this->controller->execute());
    }
}
