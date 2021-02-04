<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Unit\Model;

use Magento\Framework\Indexer\IndexerInterface;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;

/**
 * Class ImportTest
 * Test for Import
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ImportTest extends \Magento\ImportExport\Test\Unit\Model\Import\AbstractImportTestCase
{

    /**
     * Entity adapter.
     *
     * @var \Magento\ImportExport\Model\Import\Entity\AbstractEntity|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_entityAdapter;

    /**
     * Import export data
     *
     * @var \Magento\ImportExport\Helper\Data|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_importExportData = null;

    /**
     * @var \Magento\ImportExport\Model\Import\ConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_importConfig;

    /**
     * @var \Magento\ImportExport\Model\Import\Entity\Factory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_entityFactory;

    /**
     * @var \Magento\ImportExport\Model\ResourceModel\Import\Data|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_importData;

    /**
     * @var \Magento\ImportExport\Model\Export\Adapter\CsvFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_csvFactory;

    /**
     * @var \Magento\Framework\HTTP\Adapter\FileTransferFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_httpFactory;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_uploaderFactory;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $indexerRegistry;

    /**
     * @var \Magento\ImportExport\Model\Source\Import\Behavior\Factory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_behaviorFactory;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_filesystem;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_coreConfig;

    /**
     * @var \Magento\ImportExport\Model\Import|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $import;

    /**
     * @var \Magento\ImportExport\Model\History|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $historyModel;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $dateTime;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_varDirectory;

    /**
     * @var \Magento\Framework\Filesystem\DriverInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_driver;

    /**
     * @var ProcessingErrorAggregatorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $errorAggregatorMock;

    /**
     * Set up
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        parent::setUp();

        $logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_filesystem = $this->getMockBuilder(\Magento\Framework\Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_importExportData = $this->getMockBuilder(\Magento\ImportExport\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_coreConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_importConfig = $this->getMockBuilder(\Magento\ImportExport\Model\Import\Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEntityTypeCode', 'getBehavior', 'getEntities', 'getRelatedIndexers'])
            ->getMockForAbstractClass();
        $this->_entityFactory = $this->getMockBuilder(\Magento\ImportExport\Model\Import\Entity\Factory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->errorAggregatorMock = $this->getErrorAggregatorObject(
            [
                'initValidationStrategy',
                'getErrorsCount',
            ]
        );
        $this->_entityAdapter = $this->getMockBuilder(\Magento\ImportExport\Model\Import\Entity\AbstractEntity::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'importData',
                    '_saveValidatedBunches',
                    'getErrorAggregator',
                    'setSource',
                    'validateData',
                ]
            )
            ->getMockForAbstractClass();
        $this->_entityAdapter->method('getErrorAggregator')
            ->willReturn($this->errorAggregatorMock);

        $this->_entityFactory->method('create')->willReturn($this->_entityAdapter);

        $this->_importData = $this->getMockBuilder(\Magento\ImportExport\Model\ResourceModel\Import\Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_csvFactory = $this->getMockBuilder(\Magento\ImportExport\Model\Export\Adapter\CsvFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_httpFactory = $this->getMockBuilder(\Magento\Framework\HTTP\Adapter\FileTransferFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_uploaderFactory = $this->getMockBuilder(\Magento\MediaStorage\Model\File\UploaderFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_behaviorFactory = $this->getMockBuilder(
            \Magento\ImportExport\Model\Source\Import\Behavior\Factory::class
        )->disableOriginalConstructor()->getMock();
        $this->indexerRegistry = $this->getMockBuilder(\Magento\Framework\Indexer\IndexerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->historyModel = $this->getMockBuilder(\Magento\ImportExport\Model\History::class)
            ->disableOriginalConstructor()
            ->setMethods(['updateReport', 'invalidateReport', 'addReport'])
            ->getMock();
        $this->historyModel->expects($this->any())->method('updateReport')->willReturnSelf();
        $this->dateTime = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime\DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_varDirectory = $this->getMockBuilder(\Magento\Framework\Filesystem\Directory\WriteInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->_driver = $this->getMockBuilder(\Magento\Framework\Filesystem\DriverInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->_driver
            ->expects($this->any())
            ->method('fileGetContents')
            ->willReturn('');
        $this->_varDirectory
            ->expects($this->any())
            ->method('getDriver')
            ->willReturn($this->_driver);
        $this->import = $this->getMockBuilder(\Magento\ImportExport\Model\Import::class)
            ->setConstructorArgs(
                [
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
                    $this->historyModel,
                    $this->dateTime
                ]
            )
            ->setMethods(
                [
                    'getDataSourceModel',
                    'setData',
                    'getData',
                    'getProcessedEntitiesCount',
                    'getProcessedRowsCount',
                    'getEntity',
                    'getBehavior',
                    'isReportEntityType',
                    '_getEntityAdapter'
                ]
            )
            ->getMock();
        $this->setPropertyValue($this->import, '_varDirectory', $this->_varDirectory);
    }

    /**
     * Test importSource() method
     *
     * Check that method executes initialization of error aggregator object with
     * 'validation strategy' and 'allowed error count' parameters.
     */
    public function testImportSource()
    {
        $entityTypeCode = 'code';
        $this->_importData->expects($this->any())
                        ->method('getEntityTypeCode')
                        ->willReturn($entityTypeCode);
        $behaviour = 'behaviour';
        $this->_importData->expects($this->once())
                        ->method('getBehavior')
                        ->willReturn($behaviour);
        $this->import->expects($this->any())
                    ->method('getDataSourceModel')
                    ->willReturn($this->_importData);

        $this->import->expects($this->any())->method('setData')->withConsecutive(
            ['entity', $entityTypeCode],
            ['behavior', $behaviour]
        );
        $this->_entityAdapter->expects($this->any())
                    ->method('importData')
                    ->willReturn(true);
        $this->import->expects($this->any())
                    ->method('_getEntityAdapter')
                    ->willReturn($this->_entityAdapter);
        $this->_importConfig
            ->expects($this->any())
            ->method('getEntities')
            ->willReturn(
                [
                    $entityTypeCode => [
                        'model' => $entityTypeCode
                    ]
                ]
            );
        $importOnceMethodsReturnNull = [
            'getBehavior'
        ];

        foreach ($importOnceMethodsReturnNull as $method) {
            $this->import->expects($this->once())->method($method)->willReturn(null);
        }

        $this->assertTrue($this->import->importSource());
    }

    /**
     * Test importSource with expected exception
     *
     */
    public function testImportSourceException()
    {
        $this->expectException(\Magento\Framework\Exception\AlreadyExistsException::class);

        $exceptionMock = new \Magento\Framework\Exception\AlreadyExistsException(
            __('URL key for specified store already exists.')
        );
        $entityTypeCode = 'code';
        $this->_importData->expects($this->any())
            ->method('getEntityTypeCode')
            ->willReturn($entityTypeCode);
        $behaviour = 'behaviour';
        $this->_importData->expects($this->any())
            ->method('getBehavior')
            ->willReturn($behaviour);
        $this->import->expects($this->any())
            ->method('getDataSourceModel')
            ->willReturn($this->_importData);
        $this->import->expects($this->any())->method('setData')->withConsecutive(
            ['entity', $entityTypeCode],
            ['behavior', $behaviour]
        );

        $this->_entityAdapter->expects($this->any())
            ->method('importData')
            ->will($this->throwException($exceptionMock));
        $this->import->expects($this->any())
            ->method('_getEntityAdapter')
            ->willReturn($this->_entityAdapter);

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
        /** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute */
        $attribute = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class)
            ->setMethods(['getFrontendInput', 'usesSource'])
            ->disableOriginalConstructor()->getMock();
        $attribute->expects($this->any())->method('getFrontendInput')->willReturn('boolean');
        $attribute->expects($this->any())->method('usesSource')->willReturn(true);
        $this->assertEquals('boolean', $this->import->getAttributeType($attribute));
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
     * Test validateSource() method
     *
     * Check that method executes initialization of error aggregator object with
     * 'validation strategy' and 'allowed error count' parameters.
     */
    public function testValidateSource()
    {
        $validationStrategy = ProcessingErrorAggregatorInterface::VALIDATION_STRATEGY_STOP_ON_ERROR;
        $allowedErrorCount = 1;

        $this->errorAggregatorMock->expects($this->once())
            ->method('initValidationStrategy')
            ->with($validationStrategy, $allowedErrorCount);
        $this->errorAggregatorMock->expects($this->once())
            ->method('getErrorsCount')
            ->willReturn(0);

        $csvMock = $this->getMockBuilder(\Magento\ImportExport\Model\Import\Source\Csv::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_entityAdapter->expects($this->once())
            ->method('setSource')
            ->with($csvMock)
            ->willReturnSelf();
        $this->_entityAdapter->expects($this->once())
            ->method('validateData');

        $this->import->expects($this->any())
            ->method('_getEntityAdapter')
            ->willReturn($this->_entityAdapter);
        $this->import->expects($this->once())
            ->method('getProcessedRowsCount')
            ->willReturn(0);

        $this->import->expects($this->any())
            ->method('getData')
            ->willReturnMap(
                [
                    [Import::FIELD_NAME_VALIDATION_STRATEGY, null, $validationStrategy],
                    [Import::FIELD_NAME_ALLOWED_ERROR_COUNT, null, $allowedErrorCount],
                ]
            );

        $this->assertTrue($this->import->validateSource($csvMock));

        $logTrace = $this->import->getFormatedLogTrace();
        $this->assertStringContainsString('Begin data validation', $logTrace);
        $this->assertStringContainsString('This file does not contain any data', $logTrace);
        $this->assertStringContainsString('Import data validation is complete', $logTrace);
    }

    public function testInvalidateIndex()
    {
        $indexers = [
            'indexer_1' => 'indexer_1',
            'indexer_2' => 'indexer_2'
        ];
        $indexer1 = $this->getMockBuilder(IndexerInterface::class)
            ->getMockForAbstractClass();
        $indexer2 = clone $indexer1;
        $logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $indexer1->expects($this->once())
            ->method('isScheduled')
            ->willReturn(true);
        $indexer1->expects($this->never())
            ->method('invalidate');
        $indexer2->expects($this->once())
            ->method('isScheduled')
            ->willReturn(false);
        $indexer2->expects($this->once())
            ->method('invalidate');

        $this->_importConfig->expects($this->atLeastOnce())
            ->method('getRelatedIndexers')
            ->willReturn($indexers);
        $this->_importConfig->method('getEntities')
            ->willReturn(['test' => []]);
        $this->indexerRegistry->expects($this->any())
            ->method('get')
            ->willReturnMap(
                [
                    ['indexer_1', $indexer1],
                    ['indexer_2', $indexer2],
                ]
            );

        $import = new Import(
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
            $this->historyModel,
            $this->dateTime
        );

        $import->setEntity('test');
        $import->invalidateIndex();
    }

    public function testInvalidateIndexWithoutIndexers()
    {
        $this->_importConfig->expects($this->once())
            ->method('getRelatedIndexers')
            ->willReturn([]);
        $this->_importConfig->method('getEntities')
            ->willReturn(['test' => []]);

        $logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $import = new Import(
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
            $this->historyModel,
            $this->dateTime
        );

        $import->setEntity('test');
        $this->assertSame($import, $import->invalidateIndex());
    }

    public function testGetKnownEntity()
    {
        $this->_importConfig->method('getEntities')
            ->willReturn(['test' => []]);

        $logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $import = new Import(
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
            $this->historyModel,
            $this->dateTime
        );

        $import->setEntity('test');
        $entity = $import->getEntity();
        self::assertSame('test', $entity);
    }

    /**
     * @dataProvider unknownEntitiesProvider
     */
    public function testGetUnknownEntity($entity)
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('Entity is unknown');

        $this->_importConfig->method('getEntities')
            ->willReturn(['test' => []]);

        $logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $import = new Import(
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
            $this->historyModel,
            $this->dateTime
        );

        $import->setEntity($entity);
        $import->getEntity();
    }

    /**
     * @return array
     */
    public function unknownEntitiesProvider()
    {
        return [
            [''],
            ['foo'],
        ];
    }

    /**
     * @todo to implement it.
     */
    public function testGetUniqueEntityBehaviors()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * Cover isReportEntityType().
     *
     * @dataProvider isReportEntityTypeDataProvider
     */
    public function testIsReportEntityType($entity, $getEntityResult, $expectedResult)
    {
        $importMock = $this->getMockBuilder(\Magento\ImportExport\Model\Import::class)
            ->disableOriginalConstructor()
            ->setMethods(
                ['getEntity', '_getEntityAdapter', 'getEntityTypeCode', 'isNeedToLogInHistory']
            )
            ->getMock();
        $importMock->expects($this->any())->method('_getEntityAdapter')->willReturnSelf();
        $importMock->expects($this->any())->method('getEntityTypeCode')->willReturn('catalog_product');
        $this->_importConfig
            ->expects($this->any())
            ->method('getEntities')
            ->willReturn(
                [
                    'advanced_pricing' => [
                        'model' => 'advanced_pricing'
                    ]
                ]
            );
        $this->_entityFactory->expects($this->any())->method('create')->willReturnSelf();
        $this->setPropertyValue($importMock, '_importConfig', $this->_importConfig);
        $this->setPropertyValue($importMock, '_entityFactory', $this->_entityFactory);
        $importMock
            ->expects($this->any())
            ->method('getEntity')
            ->willReturn($getEntityResult);

        $actualResult = $importMock->isReportEntityType($entity);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * Cover isReportEntityType().
     *
     * @dataProvider isReportEntityTypeExceptionDataProvider
     */
    public function testIsReportEntityTypeException($entity, $getEntitiesResult, $getEntityResult, $expectedResult)
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);

        $importMock = $this->getMockBuilder(\Magento\ImportExport\Model\Import::class)
            ->disableOriginalConstructor()
            ->setMethods(
                ['getEntity', '_getEntityAdapter', 'getEntityTypeCode', 'isNeedToLogInHistory']
            )
            ->getMock();
        $importMock->expects($this->any())->method('_getEntityAdapter')->willReturnSelf();
        $importMock->expects($this->any())->method('getEntityTypeCode')->willReturn('catalog_product');
        $this->_importConfig
            ->expects($this->any())
            ->method('getEntities')
            ->willReturn($getEntitiesResult);
        $this->_entityFactory->expects($this->any())->method('create')->willReturn('');
        $this->setPropertyValue($importMock, '_importConfig', $this->_importConfig);
        $this->setPropertyValue($importMock, '_entityFactory', $this->_entityFactory);
        $importMock
            ->expects($this->any())
            ->method('getEntity')
            ->willReturn($getEntityResult);

        $actualResult = $importMock->isReportEntityType($entity);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * Cover createHistoryReport().
     */
    public function testCreateHistoryReportEmptyReportEntityType()
    {
        $sourceFileRelative = 'sourceFileRelative';
        $entity = 'entity val';
        $extension = null;
        $result = null;

        $this->import
            ->expects($this->once())
            ->method('isReportEntityType')
            ->with($entity)
            ->willReturn(false);
        $this->_varDirectory
            ->expects($this->never())
            ->method('getRelativePath');
        $this->_varDirectory
            ->expects($this->never())
            ->method('copyFile');
        $this->dateTime
            ->expects($this->never())
            ->method('gmtTimestamp');
        $this->historyModel
            ->expects($this->never())
            ->method('addReport');

        $args = [
            $sourceFileRelative,
            $entity,
            $extension,
            $result
        ];
        $actualResult = $this->invokeMethod($this->import, 'createHistoryReport', $args);
        $this->assertEquals($this->import, $actualResult);
    }

    /**
     * Cover createHistoryReport().
     */
    public function testCreateHistoryReportSourceFileRelativeIsArray()
    {
        $sourceFileRelative = [
            'file_name' => 'sourceFileRelative value',
        ];
        $sourceFileRelativeNew = 'sourceFileRelative new value';
        $entity = '';
        $extension = null;
        $result = '';
        $fileName = $sourceFileRelative['file_name'];
        $gmtTimestamp = 1234567;
        $copyName = $gmtTimestamp . '_' . $fileName;

        $this->import
            ->expects($this->once())
            ->method('isReportEntityType')
            ->with($entity)
            ->willReturn(true);
        $this->_varDirectory
            ->expects($this->once())
            ->method('getRelativePath')
            ->with(\Magento\ImportExport\Model\Import::IMPORT_DIR . $fileName)
            ->willReturn($sourceFileRelativeNew);
        $this->dateTime
            ->expects($this->once())
            ->method('gmtTimestamp')
            ->willReturn($gmtTimestamp);
        $this->historyModel
            ->expects($this->once())
            ->method('addReport')
            ->with($copyName);

        $args = [
            $sourceFileRelative,
            $entity,
            $extension,
            $result
        ];
        $actualResult = $this->invokeMethod($this->import, 'createHistoryReport', $args);
        $this->assertEquals($this->import, $actualResult);
    }

    /**
     * Cover createHistoryReport().
     */
    public function testCreateHistoryReportSourceFileRelativeIsNotArrayResultIsSet()
    {
        $sourceFileRelative = 'not array';
        $entity = '';
        $extension = null;
        $result = [
            'name' => 'result value',
        ];
        $fileName = $result['name'];
        $gmtTimestamp = 1234567;
        $copyName = $gmtTimestamp . '_' . $fileName;

        $this->import
            ->expects($this->once())
            ->method('isReportEntityType')
            ->with($entity)
            ->willReturn(true);
        $this->_varDirectory
            ->expects($this->never())
            ->method('getRelativePath');
        $this->dateTime
            ->expects($this->once())
            ->method('gmtTimestamp')
            ->willReturn($gmtTimestamp);
        $this->historyModel
            ->expects($this->once())
            ->method('addReport')
            ->with($copyName);

        $args = [
            $sourceFileRelative,
            $entity,
            $extension,
            $result
        ];
        $actualResult = $this->invokeMethod($this->import, 'createHistoryReport', $args);
        $this->assertEquals($this->import, $actualResult);
    }

    /**
     * Cover createHistoryReport().
     */
    public function testCreateHistoryReportExtensionIsSet()
    {
        $sourceFileRelative = 'not array';
        $entity = 'entity value';
        $extension = 'extension value';
        $result = [];
        $fileName = $entity . $extension;
        $gmtTimestamp = 1234567;
        $copyName = $gmtTimestamp . '_' . $fileName;

        $this->import
            ->expects($this->once())
            ->method('isReportEntityType')
            ->with($entity)
            ->willReturn(true);
        $this->_varDirectory
            ->expects($this->never())
            ->method('getRelativePath');
        $this->dateTime
            ->expects($this->once())
            ->method('gmtTimestamp')
            ->willReturn($gmtTimestamp);
        $this->historyModel
            ->expects($this->once())
            ->method('addReport')
            ->with($copyName);

        $args = [
            $sourceFileRelative,
            $entity,
            $extension,
            $result
        ];
        $actualResult = $this->invokeMethod($this->import, 'createHistoryReport', $args);
        $this->assertEquals($this->import, $actualResult);
    }

    /**
     * Cover createHistoryReport().
     *
     */
    public function testCreateHistoryReportThrowException()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('Source file coping failed');

        $sourceFileRelative = null;
        $entity = '';
        $extension = '';
        $result = '';
        $gmtTimestamp = 1234567;

        $this->import
            ->expects($this->once())
            ->method('isReportEntityType')
            ->with($entity)
            ->willReturn(true);
        $this->_varDirectory
            ->expects($this->never())
            ->method('getRelativePath');
        $phrase = $this->createMock(\Magento\Framework\Phrase::class);
        $this->_driver
            ->expects($this->any())
            ->method('fileGetContents')
            ->willReturnCallback(
                function () use ($phrase) {
                    throw new \Magento\Framework\Exception\FileSystemException($phrase);
                }
            );
        $this->dateTime
            ->expects($this->once())
            ->method('gmtTimestamp')
            ->willReturn($gmtTimestamp);
        $args = [
            $sourceFileRelative,
            $entity,
            $extension,
            $result
        ];
        $actualResult = $this->invokeMethod($this->import, 'createHistoryReport', $args);
        $this->assertEquals($this->import, $actualResult);
    }

    /**
     * Dataprovider for isReportEntityType()
     *
     * @return array
     */
    public function isReportEntityTypeDataProvider()
    {
        return [
            [
                '$entity' => null,
                '$getEntityResult' => null,
                '$expectedResult' => false,
            ],
            [
                '$entity' => 'advanced_pricing',
                '$getEntityResult' => 'advanced_pricing',
                '$expectedResult' => null,
            ],
        ];
    }

    /**
     * Dataprovider for isReportEntityTypeException()
     *
     * @return array
     */
    public function isReportEntityTypeExceptionDataProvider()
    {
        return [
            [
                '$entity' => 'entity',
                '$getEntitiesResult' => ['catalog_product' => ['model' => 'catalog_product']],
                '$getEntityResult' => 'catalog_product',
                '$expectedResult' => false,
            ],
            [
                '$entity' => 'advanced_pricing',
                '$getEntitiesResult' => ['catalog_product' => ['model' => 'catalog_product']],
                '$getEntityResult' => 'advanced_pricing',
                '$expectedResult' => true,
            ],
        ];
    }

    /**
     * Set property for an object.
     *
     * @param object $object
     * @param string $property
     * @param mixed $value
     */
    protected function setPropertyValue(&$object, $property, $value)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $reflectionProperty = $reflection->getProperty($property);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);
        return $object;
    }

    /**
     * Invoke any method of an object.
     *
     * @param $object
     * @param $methodName
     * @param array $parameters
     * @return mixed
     */
    protected function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
