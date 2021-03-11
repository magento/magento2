<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\ImportExport\Model\Export
 */
namespace Magento\ImportExport\Test\Unit\Model;

class ExportTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Extension for export file
     *
     * @var string
     */
    protected $_exportFileExtension = 'csv';

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_exportConfigMock;

    /**
     * Return mock for \Magento\ImportExport\Model\Export class
     *
     * @return \Magento\ImportExport\Model\Export
     */
    protected function _getMageImportExportModelExportMock()
    {
        $this->_exportConfigMock = $this->createMock(\Magento\ImportExport\Model\Export\ConfigInterface::class);

        /** @var $abstractMockEntity \Magento\ImportExport\Model\Export\AbstractEntity */
        $abstractMockEntity = $this->getMockForAbstractClass(
            \Magento\ImportExport\Model\Export\AbstractEntity::class,
            [],
            '',
            false
        );

        /** @var $mockAdapterTest \Magento\ImportExport\Model\Export\Adapter\AbstractAdapter */
        $mockAdapterTest = $this->getMockForAbstractClass(
            \Magento\ImportExport\Model\Export\Adapter\AbstractAdapter::class,
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

        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $filesystem = $this->createMock(\Magento\Framework\Filesystem::class);
        $entityFactory = $this->createMock(\Magento\ImportExport\Model\Export\Entity\Factory::class);
        $exportAdapterFac = $this->createMock(\Magento\ImportExport\Model\Export\Adapter\Factory::class);
        /** @var $mockModelExport \Magento\ImportExport\Model\Export */
        $mockModelExport = $this->getMockBuilder(\Magento\ImportExport\Model\Export::class)
            ->setMethods(['getEntityAdapter', '_getEntityAdapter', '_getWriter'])
            ->setConstructorArgs([$logger, $filesystem, $this->_exportConfigMock, $entityFactory, $exportAdapterFac])
            ->getMock();
        $mockModelExport->expects(
            $this->any()
        )->method(
            'getEntityAdapter'
        )->willReturn(
            $abstractMockEntity
        );
        $mockModelExport->expects(
            $this->any()
        )->method(
            '_getEntityAdapter'
        )->willReturn(
            $abstractMockEntity
        );
        $mockModelExport->expects($this->any())->method('_getWriter')->willReturn($mockAdapterTest);

        return $mockModelExport;
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
