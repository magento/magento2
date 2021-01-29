<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order\Invoice;

use Magento\Backend\App\Action;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class PrintActionTest
 * @package Magento\Sales\Controller\Adminhtml\Order\Invoice
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PrintActionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $requestMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $responseMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $fileFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $actionFlagMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $sessionMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\ObjectManagerInterface
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\Invoice\PrintAction
     */
    protected $controller;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->responseMock = $this->getMockBuilder(\Magento\Framework\App\Response\Http::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->sessionMock = $this->getMockBuilder(\Magento\Backend\Model\Session::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->actionFlagMock = $this->getMockBuilder(\Magento\Framework\App\ActionFlag::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);

        $contextMock = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $contextMock->expects($this->any())
            ->method('getResponse')
            ->willReturn($this->responseMock);
        $contextMock->expects($this->any())
            ->method('getSession')
            ->willReturn($this->sessionMock);
        $contextMock->expects($this->any())
            ->method('getActionFlag')
            ->willReturn($this->actionFlagMock);
        $contextMock->expects($this->any())
            ->method('getObjectManager')
            ->willReturn($this->objectManagerMock);

        $this->fileFactory = $this->getMockBuilder(\Magento\Framework\App\Response\Http\FileFactory::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->controller = $objectManager->getObject(
            \Magento\Sales\Controller\Adminhtml\Order\Invoice\PrintAction::class,
            [
                'context' => $contextMock,
                'fileFactory' => $this->fileFactory
            ]
        );
    }

    public function testExecute()
    {
        $invoiceId = 2;

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('invoice_id')
            ->willReturn($invoiceId);

        $invoiceMock = $this->createMock(\Magento\Sales\Model\Order\Invoice::class);

        $pdfMock = $this->createPartialMock(\Magento\Sales\Model\Order\Pdf\Invoice::class, ['render', 'getPdf']);
        $pdfMock->expects($this->once())
            ->method('getPdf')
            ->willReturnSelf();
        $pdfMock->expects($this->once())
            ->method('render');
        $dateTimeMock = $this->createMock(\Magento\Framework\Stdlib\DateTime\DateTime::class);

        $invoiceRepository = $this->getMockBuilder(\Magento\Sales\Api\InvoiceRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $invoiceRepository->expects($this->any())
            ->method('get')
            ->willReturn($invoiceMock);

        $this->objectManagerMock->expects($this->at(0))
            ->method('create')
            ->with(\Magento\Sales\Api\InvoiceRepositoryInterface::class)
            ->willReturn($invoiceRepository);
        $this->objectManagerMock->expects($this->at(1))
            ->method('create')
            ->with(\Magento\Sales\Model\Order\Pdf\Invoice::class)
            ->willReturn($pdfMock);
        $this->objectManagerMock->expects($this->at(2))
            ->method('get')
            ->with(\Magento\Framework\Stdlib\DateTime\DateTime::class)
            ->willReturn($dateTimeMock);

        $this->assertNull($this->controller->execute());
    }
}
