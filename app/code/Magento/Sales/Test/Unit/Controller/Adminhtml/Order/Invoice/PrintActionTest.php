<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order\Invoice;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Controller\Adminhtml\Order\Invoice\PrintAction;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Pdf\Invoice as InvoicePdf;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PrintActionTest extends TestCase
{
    use MockCreationTrait;

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

        $this->requestMock = $this->createMock(Http::class);
        $this->responseMock = $this->createMock(ResponseHttp::class);

        $this->sessionMock = $this->createMock(Session::class);

        $this->actionFlagMock = $this->createMock(ActionFlag::class);

        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);

        $contextMock = $this->createMock(Context::class);
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

        $this->fileFactory = $this->createMock(FileFactory::class);

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

        $pdfMock = $this->createPartialMockWithReflection(
            InvoicePdf::class,
            ['render', 'getPdf']
        );
        $pdfMock->expects($this->once())
            ->method('getPdf')
            ->willReturnSelf();
        $pdfMock->expects($this->once())
            ->method('render');
        $dateTimeMock = $this->createMock(DateTime::class);

        $invoiceRepository = $this->createMock(InvoiceRepositoryInterface::class);
        $invoiceRepository->expects($this->any())
            ->method('get')
            ->willReturn($invoiceMock);

        $this->objectManagerMock
            ->method('create')
            ->willReturnCallback(fn($param) => match ([$param]) {
                [InvoiceRepositoryInterface::class] => $invoiceRepository,
                [InvoicePdf::class] => $pdfMock
            });
        $this->objectManagerMock
            ->method('get')
            ->with(DateTime::class)
            ->willReturn($dateTimeMock);

        $this->assertNull($this->controller->execute());
    }
}
