<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order\Invoice;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Controller\Adminhtml\Order\Invoice\PrintAction;
use Magento\Sales\Model\Order\Invoice;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PrintActionTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $requestMock;

    /**
     * @var MockObject
     */
    protected $responseMock;

    /**
     * @var MockObject
     */
    protected $fileFactory;

    /**
     * @var MockObject
     */
    protected $actionFlagMock;

    /**
     * @var MockObject
     */
    protected $sessionMock;

    /**
     * @var MockObject|ObjectManagerInterface
     */
    protected $objectManagerMock;

    /**
     * @var PrintAction
     */
    protected $controller;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->requestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseMock = $this->getMockBuilder(\Magento\Framework\App\Response\Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->actionFlagMock = $this->getMockBuilder(ActionFlag::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);

        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
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

        $this->fileFactory = $this->getMockBuilder(FileFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->controller = $objectManager->getObject(
            PrintAction::class,
            [
                'context' => $contextMock,
                'fileFactory' => $this->fileFactory
            ]
        );
    }

    /**
     * @return void
     */
    public function testExecute(): void
    {
        $invoiceId = 2;

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('invoice_id')
            ->willReturn($invoiceId);

        $invoiceMock = $this->createMock(Invoice::class);

        $pdfMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Pdf\Invoice::class)->addMethods(['render'])
            ->onlyMethods(['getPdf'])
            ->disableOriginalConstructor()
            ->getMock();
        $pdfMock->expects($this->once())
            ->method('getPdf')
            ->willReturnSelf();
        $pdfMock->expects($this->once())
            ->method('render');
        $dateTimeMock = $this->createMock(DateTime::class);

        $invoiceRepository = $this->getMockBuilder(InvoiceRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $invoiceRepository->expects($this->any())
            ->method('get')
            ->willReturn($invoiceMock);

        $this->objectManagerMock
            ->method('create')
            ->willReturnCallback(fn($param) => match ([$param]) {
                [InvoiceRepositoryInterface::class] => $invoiceRepository,
                [\Magento\Sales\Model\Order\Pdf\Invoice::class] => $pdfMock
            });
        $this->objectManagerMock
            ->method('get')
            ->with(DateTime::class)
            ->willReturn($dateTimeMock);

        $this->assertNull($this->controller->execute());
    }
}
