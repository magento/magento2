<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Test\Unit\Helper;

use Magento\CatalogImportExport\Model\Import\Product;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\Framework\HTTP\Adapter\FileTransferFactory;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\Intl\DateFormatterFactory;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\ImportExport\Helper\Data;
use Magento\ImportExport\Helper\Report;
use Magento\ImportExport\Model\Export\Adapter\CsvFactory;
use Magento\ImportExport\Model\History;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\Config;
use Magento\ImportExport\Model\Import\Entity\Factory;
use Magento\ImportExport\Model\LocaleEmulatorInterface;
use Magento\ImportExport\Model\Source\Upload;
use Magento\MediaStorage\Model\File\UploaderFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReportTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var Context|MockObject
     */
    protected $context;

    /**
     * @var Filesystem|MockObject
     */
    protected $filesystem;

    /**
     * @var Write|MockObject
     */
    protected $varDirectory;

    /**
     * @var Read|MockObject
     */
    protected $importHistoryDirectory;

    /**
     * @var Report
     */
    protected $report;

    /**
     * @var Http|MockObject
     */
    private $requestMock;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->context = $this->createMock(Context::class);
        $this->requestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->any())->method('getRequest')->willReturn($this->requestMock);
        $this->varDirectory = $this->createPartialMock(
            Write::class,
            ['getRelativePath', 'getAbsolutePath', 'readFile', 'isFile', 'stat']
        );
        $this->importHistoryDirectory = $this->createPartialMock(
            Read::class,
            ['getAbsolutePath']
        );

        $this->filesystem = $this->createPartialMock(
            Filesystem::class,
            ['getDirectoryWrite', 'getDirectoryReadByPath']
        );
        $this->varDirectory
            ->expects($this->any())
            ->method('getRelativePath')
            ->willReturn('path');
        $this->varDirectory
            ->expects($this->any())
            ->method('getAbsolutePath')
            ->willReturn('path');
        $this->varDirectory
            ->expects($this->any())
            ->method('readFile')
            ->willReturn('contents');
        $this->varDirectory
            ->expects($this->any())
            ->method('isFile')
            ->willReturn(true);
        $this->varDirectory
            ->expects($this->any())
            ->method('stat')
            ->willReturn(false);
        $this->filesystem
            ->expects($this->any())
            ->method('getDirectoryWrite')
            ->willReturn($this->varDirectory);
        $this->importHistoryDirectory
            ->expects($this->any())->method('getAbsolutePath')
            ->willReturnArgument(0);
        $this->filesystem
            ->expects($this->any())
            ->method('getDirectoryReadByPath')
            ->willReturn($this->importHistoryDirectory);
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->report = $this->objectManagerHelper->getObject(
            Report::class,
            [
                'context' => $this->context,
                'timeZone' => $this->getTimezone(),
                'filesystem' =>$this->filesystem
            ]
        );
    }

    /**
     * Test getExecutionTime()
     */
    public function testGetExecutionTime()
    {
        $this->markTestSkipped('Invalid mocks used for DateTime object. Investigate later.');

        $startDate = '2000-01-01 01:01:01';
        $endDate = '2000-01-01 02:03:04';
        $executionTime = '01:02:03';

        $startDateMock = $this->createTestProxy(\DateTime::class, ['time' => $startDate]);
        $endDateMock = $this->createTestProxy(\DateTime::class, ['time' => $endDate]);
        $this->getTimezone()->method('date')
            ->willReturnCallback(function ($arg1, $arg2) use ($startDate, $startDateMock, $endDateMock) {
                if ($arg1 == $startDate) {
                    return $startDateMock;
                } elseif ($arg2 == null) {
                    return $endDateMock;
                }
            });

        $this->assertEquals($executionTime, $this->report->getExecutionTime($startDate));
    }

    /**
     * Assert the report update execution time with default UTC timezone.
     *
     * @return void
     */
    public function testGetExecutionTimeDefaultTimezone()
    {
        $this->assertEquals(
            '00:00:03',
            $this->report->getExecutionTime((new \DateTime('now - 3seconds'))->format('Y-m-d H:i:s')),
            'Report update execution time is not a match.'
        );
    }

    /**
     * Test getExecutionTime()
     */
    public function testGetSummaryStats()
    {
        $logger = $this->getMockForAbstractClass(LoggerInterface::class);
        $filesystem = $this->createMock(Filesystem::class);
        $importExportData = $this->createMock(Data::class);
        $coreConfig = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $importConfig = $this->createPartialMock(Config::class, ['getEntities']);
        $importConfig->expects($this->any())
            ->method('getEntities')
            ->willReturn(['catalog_product' => ['model' => 'catalog_product']]);
        $entityFactory = $this->createPartialMock(Factory::class, ['create']);
        $product = $this->createPartialMock(
            Product::class,
            ['getEntityTypeCode', 'setParameters']
        );
        $product->expects($this->any())
            ->method('getEntityTypeCode')
            ->willReturn('catalog_product');
        $product->expects($this->any())
            ->method('setParameters')
            ->willReturn('');
        $entityFactory->expects($this->any())
            ->method('create')
            ->willReturn($product);
        $importData = $this->createMock(\Magento\ImportExport\Model\ResourceModel\Import\Data::class);
        $csvFactory = $this->createMock(CsvFactory::class);
        $httpFactory = $this->createMock(FileTransferFactory::class);
        $uploaderFactory = $this->createMock(UploaderFactory::class);
        $behaviorFactory = $this->createMock(\Magento\ImportExport\Model\Source\Import\Behavior\Factory::class);
        $indexerRegistry = $this->createMock(IndexerRegistry::class);
        $importHistoryModel = $this->createMock(History::class);
        $localeDate = $this->createMock(\Magento\Framework\Stdlib\DateTime\DateTime::class);
        $upload = $this->createMock(Upload::class);
        $localeEmulator = $this->getMockForAbstractClass(LocaleEmulatorInterface::class);
        $localeEmulator->method('emulate')
            ->willReturnCallback(fn (callable $callback) => $callback());
        $this->objectManagerHelper->prepareObjectManager();
        $import = new Import(
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
            $localeDate,
            [],
            null,
            null,
            $upload,
            $localeEmulator
        );
        $import->setData('entity', 'catalog_product');
        $message = $this->report->getSummaryStats($import);
        $this->assertInstanceOf(Phrase::class, $message);
    }

    /**
     * @dataProvider importFileExistsDataProvider
     * @param string $fileName
     * @return void
     */
    public function testImportFileExistsException($fileName)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('File not found');
        $this->importHistoryDirectory->expects($this->any())
            ->method('getAbsolutePath')
            ->will($this->throwException(new ValidatorException(__("Error"))));
        $this->report->importFileExists($fileName);
    }

    /**
     * Test importFileExists()
     */
    public function testImportFileExists()
    {
        $this->assertEquals($this->report->importFileExists('..file..name'), true);
    }

    /**
     * Dataprovider for testImportFileExistsException()
     *
     * @return array
     */
    public static function importFileExistsDataProvider()
    {
        return [
            [
                'fileName' => 'some_folder/../another_folder',
            ],
            [
                'fileName' => 'some_folder\..\another_folder',
            ],
        ];
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
            ->with($this->identicalTo(Import::FIELD_FIELD_SEPARATOR))
            ->willReturn($testDelimiter);
        $this->assertEquals(
            $testDelimiter,
            $this->report->getDelimiter()
        );
    }

    /**
     * Returns Timezone, UTC by default
     *
     * @param string $timezone
     * @return Timezone|MockObject
     */
    private function getTimezone(string $timezone = 'UTC'): Timezone|MockObject
    {
        $localeResolver = $this->getMockBuilder(ResolverInterface::class)->getMock();
        $scopeResolver = $this->getMockBuilder(ScopeResolverInterface::class)->getMock();
        $dateTime = $this->getMockBuilder(DateTime::class)->getMock();
        $scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)->getMock();
        $timezoneMock = $this->getMockBuilder(Timezone::class)
            ->addMethods(['diff', 'format'])
            ->onlyMethods(['getConfigTimezone'])
            ->setConstructorArgs([
                'scopeResolver' => $scopeResolver,
                'localeResolver' => $localeResolver,
                'dateTime' => $dateTime,
                'scopeConfig' => $scopeConfig,
                'scopeType' => 'default',
                'defaultTimezonePath' => 'general/locale/timezone',
                'dateFormatterFactory' => (new DateFormatterFactory())
            ])->getMock();

        $timezoneMock->method('getConfigTimezone')->willReturn($timezone);

        return $timezoneMock;
    }
}
