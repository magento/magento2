<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test class for \Magento\ImportExport\Model\Export
 */
namespace Magento\ImportExport\Test\Unit\Model;

use Magento\Framework\Filesystem;
use Magento\ImportExport\Model\Export;
use Magento\ImportExport\Model\Export\AbstractEntity;
use Magento\ImportExport\Model\Export\Adapter\AbstractAdapter;
use Magento\ImportExport\Model\Export\ConfigInterface;
use Magento\ImportExport\Model\Export\Entity\Factory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ExportTest extends TestCase
{
    /**
     * Extension for export file
     *
     * @var string
     */
    protected $_exportFileExtension = 'csv';

    /**
     * @var MockObject
     */
    protected $_exportConfigMock;

    /**
     * @var AbstractEntity|MockObject
     */
    private $abstractMockEntity;

    /**
     * Return mock for \Magento\ImportExport\Model\Export class
     *
     * @return Export
     */
    protected function _getMageImportExportModelExportMock()
    {
        $this->_exportConfigMock = $this->getMockForAbstractClass(ConfigInterface::class);

        $this->abstractMockEntity = $this->getMockForAbstractClass(
            AbstractEntity::class,
            [],
            '',
            false
        );

        /** @var $mockAdapterTest \Magento\ImportExport\Model\Export\Adapter\AbstractAdapter */
        $mockAdapterTest = $this->getMockForAbstractClass(
            AbstractAdapter::class,
            [],
            '',
            false,
            true,
            true,
            ['getFileExtension']
        );
        $mockAdapterTest->expects(
            $this->any()
        )->method(
            'getFileExtension'
        )->willReturn(
            $this->_exportFileExtension
        );

        $logger = $this->getMockForAbstractClass(LoggerInterface::class);
        $filesystem = $this->createMock(Filesystem::class);
        $entityFactory = $this->createMock(Factory::class);
        $exportAdapterFac = $this->createMock(\Magento\ImportExport\Model\Export\Adapter\Factory::class);
        /** @var \Magento\ImportExport\Model\Export $mockModelExport */
        $mockModelExport = $this->getMockBuilder(Export::class)
            ->setMethods(['getEntityAdapter', '_getEntityAdapter', '_getWriter', 'setWriter'])
            ->setConstructorArgs([$logger, $filesystem, $this->_exportConfigMock, $entityFactory, $exportAdapterFac])
            ->getMock();
        $mockModelExport->expects(
            $this->any()
        )->method(
            'getEntityAdapter'
        )->willReturn(
            $this->abstractMockEntity
        );
        $mockModelExport->expects(
            $this->any()
        )->method(
            '_getEntityAdapter'
        )->willReturn(
            $this->abstractMockEntity
        );
        $mockModelExport->method(
            'setWriter'
        )->willReturn(
            $this->abstractMockEntity
        );
        $mockModelExport->expects($this->any())->method('_getWriter')->willReturn($mockAdapterTest);

        return $mockModelExport;
    }

    /**
     * Tests that export doesn't use `trim` function while counting the number of exported rows.
     *
     * Using PHP `trim` function allocates the same amount of memory as export result and leads
     * to `out of memory` error.
     */
    public function testExportDoesntTrimResult()
    {
        $model = $this->_getMageImportExportModelExportMock();
        $this->abstractMockEntity->method('export')
            ->willReturn("export data  \n\n");
        $model->setData([
            Export::FILTER_ELEMENT_GROUP => [],
            'entity' => 'catalog_product'
        ]);
        $model->export();
        $this->assertStringContainsString(
            'Exported 2 rows',
            var_export($model->getFormatedLogTrace(), true)
        );
    }

    /**
     * Test get file name with adapter file name
     */
    public function testGetFileNameWithAdapterFileName()
    {
        $model = $this->_getMageImportExportModelExportMock();
        $basicFileName = 'test_file_name';
        $model->getEntityAdapter()->setFileName($basicFileName);

        $fileName = $model->getFileName();
        $correctDateTime = $this->_getCorrectDateTime($fileName);
        $this->assertNotNull($correctDateTime);

        $correctFileName = $basicFileName . '_' . $correctDateTime . '.' . $this->_exportFileExtension;
        $this->assertEquals($correctFileName, $fileName);
    }

    /**
     * Test get file name without adapter file name
     */
    public function testGetFileNameWithoutAdapterFileName()
    {
        $model = $this->_getMageImportExportModelExportMock();
        $model->getEntityAdapter()->setFileName(null);
        $basicFileName = 'test_entity';
        $model->setEntity($basicFileName);

        $fileName = $model->getFileName();
        $correctDateTime = $this->_getCorrectDateTime($fileName);
        $this->assertNotNull($correctDateTime);

        $correctFileName = $basicFileName . '_' . $correctDateTime . '.' . $this->_exportFileExtension;
        $this->assertEquals($correctFileName, $fileName);
    }

    /**
     * Get correct file creation time
     *
     * @param string $fileName
     * @return string|null
     */
    protected function _getCorrectDateTime($fileName)
    {
        preg_match('/(\d{8}_\d{6})/', $fileName, $matches);
        if (isset($matches[1])) {
            return $matches[1];
        }
        return null;
    }
}
