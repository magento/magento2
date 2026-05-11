<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Test\Unit\Controller\Adminhtml\Import;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Result\Layout;
use Magento\ImportExport\Block\Adminhtml\Import\Frame\Result;
use Magento\ImportExport\Controller\Adminhtml\Import\Validate;
use Magento\ImportExport\Helper\Report;
use Magento\ImportExport\Model\History;
use Magento\ImportExport\Model\Import\RenderErrorMessages;
use Magento\ImportExport\Model\Report\ReportProcessorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\AbstractSource;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ValidateTest extends TestCase
{
    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var ReportProcessorInterface|MockObject
     */
    private $reportProcessorMock;

    /**
     * @var History|MockObject
     */
    private $historyMock;

    /**
     * @var Report|MockObject
     */
    private $reportHelperMock;

    /**
     * @var Validate
     */
    private $validate;

    /**
     * @var Import
     */
    private $importMock;

    /**
     * @var Http|MockObject
     */
    private $requestMock;

    /**
     * @var ResultFactory|MockObject
     */
    private $resultFactoryMock;

    /**
     * @var ManagerInterface|MockObject
     */
    private $messageManagerMock;

    /**
     * @var AbstractSourceMock|MockObject
     */
    private $abstractSourceMock;

    /**
     * @var EventManagerInterface|MockObject
     */
    private $eventManagerMock;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $objects = [
            [
                Escaper::class,
                $this->createMock(Escaper::class)
            ],
            [
                RenderErrorMessages::class,
                $this->createMock(RenderErrorMessages::class)
            ]
        ];
        $objectManagerHelper->prepareObjectManager($objects);

        $this->requestMock = $this->createPartialMock(
            Http::class,
            ['getPostValue', 'isPost']
        );

        $this->resultFactoryMock = $this->createMock(ResultFactory::class);
        $this->messageManagerMock = $this->createMock(ManagerInterface::class);
        $this->contextMock = $this->createMock(Context::class);
        $this->contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->contextMock->expects($this->any())
            ->method('getResultFactory')
            ->willReturn($this->resultFactoryMock);
        $this->contextMock->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($this->messageManagerMock);

        $this->reportProcessorMock = $this->createMock(ReportProcessorInterface::class);
        $this->historyMock = $this->createMock(History::class);
        $this->reportHelperMock = $this->createMock(Report::class);
        $this->importMock = $this->createMock(Import::class);
        $this->abstractSourceMock = $this->createMock(AbstractSource::class);

        $this->eventManagerMock = $this->createMock(EventManagerInterface::class);

        $this->contextMock->expects($this->any())
            ->method('getEventManager')
            ->willReturn($this->eventManagerMock);

        $this->validate = new Validate(
            $this->contextMock,
            $this->reportProcessorMock,
            $this->historyMock,
            $this->reportHelperMock
        );
        $reflection = new \ReflectionClass($this->validate);
        $importProperty = $reflection->getProperty('import');
        $importProperty->setValue($this->validate, $this->importMock);
    }

    /**
     * Test execute() method
     *
     * Check the case in which no data was posted.
     */
    public function testNoDataWasPosted()
    {
        $data = null;

        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($data);

        $resultBlock = $this->createMock(Result::class);

        $layoutMock = $this->createMock(LayoutInterface::class);
        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('import.frame.result')
            ->willReturn($resultBlock);

        $resultLayoutMock = $this->createMock(Layout::class);
        $resultLayoutMock->expects($this->once())
            ->method('getLayout')
            ->willReturn($layoutMock);

        $resultRedirectMock = $this->createMock(Redirect::class);
        $resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('adminhtml/*/index');

        $this->resultFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturnMap([
                [ResultFactory::TYPE_LAYOUT, [], $resultLayoutMock],
                [ResultFactory::TYPE_REDIRECT, [], $resultRedirectMock],
            ]);

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with(__('Sorry, but the data is invalid or the file is not uploaded.'));

        $this->assertEquals($resultRedirectMock, $this->validate->execute());
    }

    /**
     * Test execute() method
     *
     * Check the case in which the import file was not uploaded.
     */
    public function testFileWasNotUploaded()
    {
        $data = false;

        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($data);
        $this->requestMock->expects($this->once())
            ->method('isPost')
            ->willReturn(true);

        $resultBlock = $this->createMock(Result::class);
        $resultBlock->expects($this->once())
            ->method('addError')
            ->with(__('The file was not uploaded.'));

        $layoutMock = $this->createMock(LayoutInterface::class);
        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('import.frame.result')
            ->willReturn($resultBlock);

        $resultLayoutMock = $this->createMock(Layout::class);
        $resultLayoutMock->expects($this->once())
            ->method('getLayout')
            ->willReturn($layoutMock);

        $this->resultFactoryMock->expects($this->any())
            ->method('create')
            ->with(ResultFactory::TYPE_LAYOUT)
            ->willReturn($resultLayoutMock);

        $this->assertEquals($resultLayoutMock, $this->validate->execute());
    }

    /**
     * Test execute() method
     *
     * Check the case in which the import file was not uploaded.
     */
    public function testFileVerifiedWithImport()
    {
        $data = ['key' => 'value'];

        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($data);

        $resultBlock = $this->createMock(Result::class);
        $resultBlock->expects($this->once())
            ->method('addSuccess')
            ->with(__('File is valid! To start import process press "Import" button'));

        $layoutMock = $this->createMock(LayoutInterface::class);
        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('import.frame.result')
            ->willReturn($resultBlock);

        $resultLayoutMock = $this->createMock(Layout::class);
        $resultLayoutMock->expects($this->once())
            ->method('getLayout')
            ->willReturn($layoutMock);
        $this->importMock->expects($this->once())
            ->method('setData')
            ->with($data)
            ->willReturn($this->importMock);
        $this->importMock->expects($this->once())
            ->method('uploadFileAndGetSource')
            ->willReturn($this->abstractSourceMock);
        $this->importMock->expects($this->once())
            ->method('validateSource')
            ->with($this->abstractSourceMock)
            ->willReturn(true);

        $resultBlock->expects($this->once())
            ->method('addAction')
            ->willReturn(
                ['show', 'import_validation_container'],
                ['value', Import::FIELD_IMPORT_IDS, [1, 2, 3]]
            );
        $resultBlock->expects($this->once())
            ->method('addAction')
            ->willReturn(
                ['show', 'import_validation_container'],
                ['value', '_import_history_id', 1]
            );
        $this->importMock->expects($this->exactly(3))
            ->method('getProcessedRowsCount')
            ->willReturn(2);
        $this->importMock->expects($this->once())
            ->method('isImportAllowed')
            ->willReturn(true);

        $this->importMock->expects($this->once())
            ->method('getProcessedEntitiesCount')
            ->willReturn(10);

        $errorAggregatorMock = $this->createMock(ProcessingErrorAggregatorInterface::class);
        $this->importMock->expects($this->any())
            ->method('getErrorAggregator')
            ->willReturn($errorAggregatorMock);

        $errorAggregatorMock->expects($this->exactly(3))
            ->method('getErrorsCount')
            ->willReturn(2);

        $errorAggregatorMock->expects($this->once())
            ->method('getAllErrors')
            ->willReturn($errorAggregatorMock);

        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with('log_admin_import');

        $this->resultFactoryMock->expects($this->any())
            ->method('create')
            ->with(ResultFactory::TYPE_LAYOUT)
            ->willReturn($resultLayoutMock);
        $this->assertEquals($resultLayoutMock, $this->validate->execute());
    }
}
