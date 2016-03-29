<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Unit\Helper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class ReportTest
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
            'Magento\Framework\App\Helper\Context',
            [],
            [],
            '',
            false
        );
        $this->timezone = $this->getMock(
            'Magento\Framework\Stdlib\DateTime\Timezone',
            ['date', 'getConfigTimezone', 'diff', 'format'],
            [],
            '',
            false
        );
        $this->varDirectory = $this->getMock(
            'Magento\Framework\Filesystem\Directory\Write',
            ['getRelativePath', 'readFile', 'isFile', 'stat'],
            [],
            '',
            false
        );
        $this->filesystem = $this->getMock(
            'Magento\Framework\Filesystem',
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
            'Magento\ImportExport\Helper\Report',
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
        $logger = $this->getMock('Psr\Log\LoggerInterface', [], [], '', false);
        $filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $importExportData = $this->getMock('Magento\ImportExport\Helper\Data', [], [], '', false);
        $coreConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface', [], [], '', false);
        $importConfig = $this->getMock(
            'Magento\ImportExport\Model\Import\Config',
            ['getEntities'],
            [],
            '',
            false
        );
        $importConfig->expects($this->any())
            ->method('getEntities')
            ->willReturn(['catalog_product' => ['model' => 'catalog_product']]);
        $entityFactory = $this->getMock(
            'Magento\ImportExport\Model\Import\Entity\Factory',
            ['create'],
            [],
            '',
            false
        );
        $product = $this->getMock(
            'Magento\CatalogImportExport\Model\Import\Product',
            ['getEntityTypeCode', 'setParameters'],
            [],
            '',
            false
        );
        $product->expects($this->any())->method('getEntityTypeCode')->willReturn('catalog_product');
        $product->expects($this->any())->method('setParameters')->willReturn('');
        $entityFactory->expects($this->any())->method('create')->willReturn($product);
        $importData = $this->getMock('Magento\ImportExport\Model\ResourceModel\Import\Data', [], [], '', false);
        $csvFactory = $this->getMock('Magento\ImportExport\Model\Export\Adapter\CsvFactory', [], [], '', false);
        $httpFactory = $this->getMock('Magento\Framework\HTTP\Adapter\FileTransferFactory', [], [], '', false);
        $uploaderFactory = $this->getMock('Magento\MediaStorage\Model\File\UploaderFactory', [], [], '', false);
        $behaviorFactory = $this->getMock(
            'Magento\ImportExport\Model\Source\Import\Behavior\Factory',
            [],
            [],
            '',
            false
        );
        $indexerRegistry = $this->getMock('Magento\Framework\Indexer\IndexerRegistry', [], [], '', false);
        $importHistoryModel = $this->getMock('Magento\ImportExport\Model\History', [], [], '', false);
        $localeDate = $this->getMock('Magento\Framework\Stdlib\DateTime\DateTime', [], [], '', false);
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
        $this->assertInstanceOf('Magento\Framework\Phrase', $message);
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
