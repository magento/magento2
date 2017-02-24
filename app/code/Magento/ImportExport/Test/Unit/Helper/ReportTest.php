<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Unit\Helper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class ReportTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReportTest extends \PHPUnit_Framework_TestCase
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
     * Set up
     */
    protected function setUp()
    {
        $this->context = $this->getMock(
            \Magento\Framework\App\Helper\Context::class,
            [],
            [],
            '',
            false
        );
        $this->timezone = $this->getMock(
            \Magento\Framework\Stdlib\DateTime\Timezone::class,
            ['date', 'getConfigTimezone', 'diff', 'format'],
            [],
            '',
            false
        );
        $this->varDirectory = $this->getMock(
            \Magento\Framework\Filesystem\Directory\Write::class,
            ['getRelativePath', 'readFile', 'isFile', 'stat'],
            [],
            '',
            false
        );
        $this->filesystem = $this->getMock(
            \Magento\Framework\Filesystem::class,
            ['getDirectoryWrite'],
            [],
            '',
            false
        );
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
        $logger = $this->getMock(\Psr\Log\LoggerInterface::class, [], [], '', false);
        $filesystem = $this->getMock(\Magento\Framework\Filesystem::class, [], [], '', false);
        $importExportData = $this->getMock(\Magento\ImportExport\Helper\Data::class, [], [], '', false);
        $coreConfig = $this->getMock(\Magento\Framework\App\Config\ScopeConfigInterface::class, [], [], '', false);
        $importConfig = $this->getMock(
            \Magento\ImportExport\Model\Import\Config::class,
            ['getEntities'],
            [],
            '',
            false
        );
        $importConfig->expects($this->any())
            ->method('getEntities')
            ->willReturn(['catalog_product' => ['model' => 'catalog_product']]);
        $entityFactory = $this->getMock(
            \Magento\ImportExport\Model\Import\Entity\Factory::class,
            ['create'],
            [],
            '',
            false
        );
        $product = $this->getMock(
            \Magento\CatalogImportExport\Model\Import\Product::class,
            ['getEntityTypeCode', 'setParameters'],
            [],
            '',
            false
        );
        $product->expects($this->any())->method('getEntityTypeCode')->willReturn('catalog_product');
        $product->expects($this->any())->method('setParameters')->willReturn('');
        $entityFactory->expects($this->any())->method('create')->willReturn($product);
        $importData = $this->getMock(\Magento\ImportExport\Model\ResourceModel\Import\Data::class, [], [], '', false);
        $csvFactory = $this->getMock(\Magento\ImportExport\Model\Export\Adapter\CsvFactory::class, [], [], '', false);
        $httpFactory = $this->getMock(\Magento\Framework\HTTP\Adapter\FileTransferFactory::class, [], [], '', false);
        $uploaderFactory = $this->getMock(\Magento\MediaStorage\Model\File\UploaderFactory::class, [], [], '', false);
        $behaviorFactory = $this->getMock(
            \Magento\ImportExport\Model\Source\Import\Behavior\Factory::class,
            [],
            [],
            '',
            false
        );
        $indexerRegistry = $this->getMock(\Magento\Framework\Indexer\IndexerRegistry::class, [], [], '', false);
        $importHistoryModel = $this->getMock(\Magento\ImportExport\Model\History::class, [], [], '', false);
        $localeDate = $this->getMock(\Magento\Framework\Stdlib\DateTime\DateTime::class, [], [], '', false);
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

    public function testImportFileExists()
    {
        $this->assertEquals($this->report->importFileExists('file'), true);
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
        $this->report->getReportSize('file');
    }
}
