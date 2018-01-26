<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\ImportExport\Model\Export
 */
namespace Magento\ImportExport\Test\Unit\Model;

class ExportTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Extension for export file
     *
     * @var string
     */
    protected $_exportFileExtension = 'csv';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_exportConfigMock;

    /**
     * Return mock for \Magento\ImportExport\Model\Export class
     *
     * @return \Magento\ImportExport\Model\Export
     */
    protected function _getMageImportExportModelExportMock()
    {
        $this->_exportConfigMock = $this->getMock('Magento\ImportExport\Model\Export\ConfigInterface');

        /** @var $abstractMockEntity \Magento\ImportExport\Model\Export\AbstractEntity */
        $abstractMockEntity = $this->getMockForAbstractClass(
            'Magento\ImportExport\Model\Export\AbstractEntity',
            [],
            '',
            false
        );

        /** @var $mockAdapterTest \Magento\ImportExport\Model\Export\Adapter\AbstractAdapter */
        $mockAdapterTest = $this->getMockForAbstractClass(
            'Magento\ImportExport\Model\Export\Adapter\AbstractAdapter',
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
        )->will(
            $this->returnValue($this->_exportFileExtension)
        );

        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $entityFactory = $this->getMock(
            'Magento\ImportExport\Model\Export\Entity\Factory',
            [],
            [],
            '',
            false
        );
        $exportAdapterFac = $this->getMock(
            'Magento\ImportExport\Model\Export\Adapter\Factory',
            [],
            [],
            '',
            false
        );
        /** @var $mockModelExport \Magento\ImportExport\Model\Export */
        $mockModelExport = $this->getMock(
            'Magento\ImportExport\Model\Export',
            ['getEntityAdapter', '_getEntityAdapter', '_getWriter'],
            [$logger, $filesystem, $this->_exportConfigMock, $entityFactory, $exportAdapterFac]
        );
        $mockModelExport->expects(
            $this->any()
        )->method(
            'getEntityAdapter'
        )->will(
            $this->returnValue($abstractMockEntity)
        );
        $mockModelExport->expects(
            $this->any()
        )->method(
            '_getEntityAdapter'
        )->will(
            $this->returnValue($abstractMockEntity)
        );
        $mockModelExport->expects($this->any())->method('_getWriter')->will($this->returnValue($mockAdapterTest));

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
