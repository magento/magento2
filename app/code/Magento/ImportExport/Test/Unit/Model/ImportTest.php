<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Test\Unit\Model;

/**
 * Class ImportTest
 * @package Magento\ImportExport\Test\Unit\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ImportTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Entity adapter.
     *
     * @var \Magento\ImportExport\Model\Import\Entity\AbstractEntity|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_entityAdapter;

    /**
     * Import export data
     *
     * @var \Magento\ImportExport\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_importExportData = null;

    /**
     * @var \Magento\ImportExport\Model\Import\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_importConfig;

    /**
     * @var \Magento\ImportExport\Model\Import\Entity\Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_entityFactory;

    /**
     * @var \Magento\ImportExport\Model\Resource\Import\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_importData;

    /**
     * @var \Magento\ImportExport\Model\Export\Adapter\CsvFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_csvFactory;

    /**
     * @var \Magento\Framework\HTTP\Adapter\FileTransferFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_httpFactory;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_uploaderFactory;

    /**
     * @var \Magento\Indexer\Model\IndexerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexerRegistry;

    /**
     * @var \Magento\ImportExport\Model\Source\Import\Behavior\Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_behaviorFactory;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_filesystem;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_coreConfig;

    /**
     * @var \Magento\ImportExport\Model\Import|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $import;

    public function setUp()
    {
        $logger = $this->getMockBuilder('\Psr\Log\LoggerInterface')
                    ->disableOriginalConstructor()
                    ->getMock();
        $this->_filesystem = $this->getMockBuilder('\Magento\Framework\Filesystem')
                                ->disableOriginalConstructor()
                                ->getMock();
        $this->_importExportData = $this->getMockBuilder('\Magento\ImportExport\Helper\Data')
                                        ->disableOriginalConstructor()
                                        ->getMock();
        $this->_coreConfig = $this->getMockBuilder('\Magento\Framework\App\Config\ScopeConfigInterface')
                                ->disableOriginalConstructor()
                                ->getMock();
        $this->_importConfig = $this->getMockBuilder('\Magento\ImportExport\Model\Import\ConfigInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getEntityTypeCode', 'getBehavior'])
            ->getMockForAbstractClass();
        $this->_entityFactory = $this->getMockBuilder('\Magento\ImportExport\Model\Import\Entity\Factory')
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $this->_importData = $this->getMockBuilder('\Magento\ImportExport\Model\Resource\Import\Data')
                                ->disableOriginalConstructor()
                                ->getMock();
        $this->_csvFactory = $this->getMockBuilder('\Magento\ImportExport\Model\Export\Adapter\CsvFactory')
                                ->disableOriginalConstructor()
                                ->getMock();
        $this->_httpFactory = $this->getMockBuilder('\Magento\Framework\HTTP\Adapter\FileTransferFactory')
                                ->disableOriginalConstructor()
                                ->getMock();
        $this->_uploaderFactory = $this->getMockBuilder('\Magento\MediaStorage\Model\File\UploaderFactory')
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $this->_behaviorFactory = $this->getMockBuilder('\Magento\ImportExport\Model\Source\Import\Behavior\Factory')
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $this->indexerRegistry = $this->getMockBuilder('\Magento\Indexer\Model\IndexerRegistry')
                                    ->disableOriginalConstructor()
                                    ->getMock();

        $this->import = $this->getMockBuilder('\Magento\ImportExport\Model\Import')
            ->setConstructorArgs([
                $logger,
                $this->_filesystem,
                $this->_importExportData,
                $this->_coreConfig,
                $this->_importConfig,
                $this->_entityFactory,
                $this->_importData,
                $this->_csvFactory,
                $this->_httpFactory,
                $this->_uploaderFactory,
                $this->_behaviorFactory,
                $this->indexerRegistry,
            ])
            ->setMethods([
                'getDataSourceModel',
                '_getEntityAdapter',
                'setData',
                'getProcessedEntitiesCount',
                'getProcessedRowsCount',
                'getInvalidRowsCount',
                'getErrorsCount',
                'getEntity',
                'getBehavior',
            ])
            ->getMock();

        $this->_entityAdapter = $this->getMockBuilder('\Magento\ImportExport\Model\Import\Entity\AbstractEntity')
            ->disableOriginalConstructor()
            ->setMethods(['importData'])
            ->getMockForAbstractClass();

    }

    public function testImportSource()
    {
        $entityTypeCode = 'code';
        $this->_importData->expects($this->any())
                        ->method('getEntityTypeCode')
                        ->will($this->returnValue($entityTypeCode));
        $behaviour = 'behaviour';
        $this->_importData->expects($this->once())
                        ->method('getBehavior')
                        ->will($this->returnValue($behaviour));
        $this->import->expects($this->any())
                    ->method('getDataSourceModel')
                    ->will($this->returnValue($this->_importData));

        $this->import->expects($this->any())->method('setData')->withConsecutive(
            ['entity', $entityTypeCode],
            ['behavior', $behaviour]
        );
        $phraseClass = '\Magento\Framework\Phrase';
        $this->import->expects($this->any())
                    ->method('addLogComment')
                    ->with($this->isInstanceOf($phraseClass));
        $this->_entityAdapter->expects($this->once())
                    ->method('importData')
                    ->will($this->returnSelf());
        $this->import->expects($this->once())
                    ->method('_getEntityAdapter')
                    ->will($this->returnValue($this->_entityAdapter));

        $importOnceMethodsReturnNull = [
            'getEntity',
            'getBehavior',
            'getProcessedRowsCount',
            'getProcessedEntitiesCount',
            'getInvalidRowsCount',
            'getErrorsCount',
        ];

        foreach ($importOnceMethodsReturnNull as $method) {
            $this->import->expects($this->once())->method($method)->will($this->returnValue(null));
        }

        $this->import->importSource();
    }

    /**
     * @todo to implement it.
     */
    public function testGetOperationResultMessages()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @todo to implement it.
     */
    public function testGetAttributeType()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @todo to implement it.
     */
    public function testGetEntity()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @todo to implement it.
     */
    public function testGetErrorsCount()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @todo to implement it.
     */
    public function testGetErrorsLimit()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @todo to implement it.
     */
    public function testGetInvalidRowsCount()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @todo to implement it.
     */
    public function testGetNotices()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @todo to implement it.
     */
    public function testGetProcessedEntitiesCount()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @todo to implement it.
     */
    public function testGetProcessedRowsCount()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @todo to implement it.
     */
    public function testGetWorkingDir()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @todo to implement it.
     */
    public function testIsImportAllowed()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @todo to implement it.
     */
    public function testUploadSource()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @todo to implement it.
     */
    public function testValidateSource()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @todo to implement it.
     */
    public function testInvalidateIndex()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @todo to implement it.
     */
    public function testGetEntityBehaviors()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @todo to implement it.
     */
    public function testGetUniqueEntityBehaviors()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }
}
