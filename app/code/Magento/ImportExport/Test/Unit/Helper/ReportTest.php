<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Unit\Helper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class ReportTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReportTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\App\Helper\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Timezone|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $timezone;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Write|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $varDirectory;

    /**
     * @var \Magento\ImportExport\Helper\Report
     */
    protected $report;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->context = $this->createMock(\Magento\Framework\App\Helper\Context::class);
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->any())->method('getRequest')->willReturn($this->requestMock);
        $this->timezone = $this->createPartialMock(
            \Magento\Framework\Stdlib\DateTime\Timezone::class,
            ['date', 'getConfigTimezone', 'diff', 'format']
        );
        $this->varDirectory = $this->createPartialMock(
            \Magento\Framework\Filesystem\Directory\Write::class,
            ['getRelativePath', 'readFile', 'isFile', 'stat']
        );
        $this->filesystem = $this->createPartialMock(\Magento\Framework\Filesystem::class, ['getDirectoryWrite']);
        $this->varDirectory->expects($this->any())->method('getRelativePath')->willReturn('path');
        $this->varDirectory->expects($this->any())->method('readFile')->willReturn('contents');
        $this->varDirectory->expects($this->any())->method('isFile')->willReturn(true);
        $this->varDirectory->expects($this->any())->method('stat')->willReturn(100);
        $this->filesystem->expects($this->any())->method('getDirectoryWrite')->willReturn($this->varDirectory);
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->report = $this->objectManagerHelper->getObject(
            \Magento\ImportExport\Helper\Report::class,
            [
                'context' => $this->context,
                'timeZone' => $this->timezone,
                'filesystem' =>$this->filesystem
            ]
        );
    }

    /**
     * Test getExecutionTime()
     */
    public function testGetExecutionTime()
    {
        $time = '01:02:03';
        $this->timezone->expects($this->any())->method('date')->willReturnSelf();
        $this->timezone->expects($this->any())->method('getConfigTimezone')->willReturn('America/Los_Angeles');
        $this->timezone->expects($this->any())->method('diff')->willReturnSelf();
        $this->timezone->expects($this->any())->method('format')->willReturn($time);
        $this->assertEquals($time, $this->report->getExecutionTime($time));
    }

    /**
     * Test getExecutionTime()
     */
    public function testGetSummaryStats()
    {
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $filesystem = $this->createMock(\Magento\Framework\Filesystem::class);
        $importExportData = $this->createMock(\Magento\ImportExport\Helper\Data::class);
        $coreConfig = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $importConfig = $this->createPartialMock(\Magento\ImportExport\Model\Import\Config::class, ['getEntities']);
        $importConfig->expects($this->any())
            ->method('getEntities')
            ->willReturn(['catalog_product' => ['model' => 'catalog_product']]);
        $entityFactory = $this->createPartialMock(\Magento\ImportExport\Model\Import\Entity\Factory::class, ['create']);
        $product = $this->createPartialMock(
            \Magento\CatalogImportExport\Model\Import\Product::class,
            ['getEntityTypeCode', 'setParameters']
        );
        $product->expects($this->any())->method('getEntityTypeCode')->willReturn('catalog_product');
        $product->expects($this->any())->method('setParameters')->willReturn('');
        $entityFactory->expects($this->any())->method('create')->willReturn($product);
        $importData = $this->createMock(\Magento\ImportExport\Model\ResourceModel\Import\Data::class);
        $csvFactory = $this->createMock(\Magento\ImportExport\Model\Export\Adapter\CsvFactory::class);
        $httpFactory = $this->createMock(\Magento\Framework\HTTP\Adapter\FileTransferFactory::class);
        $uploaderFactory = $this->createMock(\Magento\MediaStorage\Model\File\UploaderFactory::class);
        $behaviorFactory = $this->createMock(\Magento\ImportExport\Model\Source\Import\Behavior\Factory::class);
        $indexerRegistry = $this->createMock(\Magento\Framework\Indexer\IndexerRegistry::class);
        $importHistoryModel = $this->createMock(\Magento\ImportExport\Model\History::class);
        $localeDate = $this->createMock(\Magento\Framework\Stdlib\DateTime\DateTime::class);
        $import = new \Magento\ImportExport\Model\Import(
            $logger,
            $filesystem,
            $importExportData,
            $coreConfig,
            $importConfig,
            $entityFactory,
            $importData,
            $csvFactory,
            $httpFactory,
            $uploaderFactory,
            $behaviorFactory,
            $indexerRegistry,
            $importHistoryModel,
            $localeDate
        );
        $import->setData('entity', 'catalog_product');
        $message = $this->report->getSummaryStats($import);
        $this->assertInstanceOf(\Magento\Framework\Phrase::class, $message);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Filename has not permitted symbols in it
     */
    public function testImportFileExistsException()
    {
        $this->report->importFileExists('some_folder/../another_folder');
    }

    public function testImportFileExists()
    {
        $this->assertEquals($this->report->importFileExists('..file..name'), true);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testImportFileExistsExceptionInverted()
    {
        $this->report->importFileExists('some_folder\..\another_folder');
    }

    /**
     * Test importFileExists()
     */
    public function testGetReportOutput()
    {
        $this->assertEquals($this->report->getReportOutput('report'), 'contents');
    }

    /**
     * Test getReportSize()
     */
    public function testGetReportSize()
    {
        $result = $this->report->getReportSize('file');
        $this->assertNull($result);
    }

    /**
     * Test getDelimiter() take into consideration request param '_import_field_separator'.
     */
    public function testGetDelimiter()
    {
        $testDelimiter = 'some delimiter';
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with($this->identicalTo(\Magento\ImportExport\Model\Import::FIELD_FIELD_SEPARATOR))
            ->willReturn($testDelimiter);
        $this->assertEquals(
            $testDelimiter,
            $this->report->getDelimiter()
        );
    }
}
