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

        $this->requestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getPostValue',
                'isPost',
            ])
            ->getMock();

        $this->resultFactoryMock = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->messageManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->getMockForAbstractClass();

        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->contextMock->expects($this->any())
            ->method('getResultFactory')
            ->willReturn($this->resultFactoryMock);
        $this->contextMock->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($this->messageManagerMock);

        $this->reportProcessorMock = $this->getMockBuilder(
            ReportProcessorInterface::class
        )
            ->getMockForAbstractClass();

        $this->historyMock = $this->getMockBuilder(History::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->reportHelperMock = $this->getMockBuilder(Report::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->importMock = $this->getMockBuilder(import::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->abstractSourceMock = $this->getMockBuilder(AbstractSource::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->validate = new Validate(
            $this->contextMock,
            $this->reportProcessorMock,
            $this->historyMock,
            $this->reportHelperMock
        );
        $reflection = new \ReflectionClass($this->validate);
        $importProperty = $reflection->getProperty('import');
        $importProperty->setAccessible(true);
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

        $resultBlock = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->getMock();

        $layoutMock = $this->getMockBuilder(LayoutInterface::class)
            ->getMockForAbstractClass();
        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('import.frame.result')
            ->willReturn($resultBlock);

        $resultLayoutMock = $this->getMockBuilder(Layout::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultLayoutMock->expects($this->once())
            ->method('getLayout')
            ->willReturn($layoutMock);

        $resultRedirectMock = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
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

        $resultBlock = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultBlock->expects($this->once())
            ->method('addError')
            ->with(__('The file was not uploaded.'));

        $layoutMock = $this->getMockBuilder(LayoutInterface::class)
            ->getMockForAbstractClass();
        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('import.frame.result')
            ->willReturn($resultBlock);

        $resultLayoutMock = $this->getMockBuilder(Layout::class)
            ->disableOriginalConstructor()
            ->getMock();
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

        $resultBlock = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultBlock->expects($this->once())
            ->method('addSuccess')
            ->with(__('File is valid! To start import process press "Import" button'));

        $layoutMock = $this->getMockBuilder(LayoutInterface::class)
            ->getMockForAbstractClass();
        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('import.frame.result')
            ->willReturn($resultBlock);

        $resultLayoutMock = $this->getMockBuilder(Layout::class)
            ->disableOriginalConstructor()
            ->getMock();
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

        $this->resultFactoryMock->expects($this->any())
            ->method('create')
            ->with(ResultFactory::TYPE_LAYOUT)
            ->willReturn($resultLayoutMock);
        $this->assertEquals($resultLayoutMock, $this->validate->execute());
    }
}
