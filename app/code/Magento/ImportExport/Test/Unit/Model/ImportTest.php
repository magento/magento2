<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Test\Unit\Model;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\HTTP\Adapter\FileTransferFactory;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Phrase;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\ImportExport\Helper\Data;
use Magento\ImportExport\Model\Export\Adapter\CsvFactory;
use Magento\ImportExport\Model\History;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\Config;
use Magento\ImportExport\Model\Import\ConfigInterface;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Magento\ImportExport\Model\Import\Entity\Factory;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\Import\Source\Csv;
use Magento\ImportExport\Model\Source\Upload;
use Magento\ImportExport\Test\Unit\Model\Import\AbstractImportTestCase;
use Magento\MediaStorage\Model\File\UploaderFactory;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ImportTest extends AbstractImportTestCase
{

    /**
     * @var AbstractEntity|MockObject
     */
    protected $_entityAdapter;

    /**
     * @var Data|MockObject
     */
    protected $_importExportData = null;

    /**
     * @var ConfigInterface|MockObject
     */
    protected $_importConfig;

    /**
     * @var Factory|MockObject
     */
    protected $_entityFactory;

    /**
     * @var \Magento\ImportExport\Model\ResourceModel\Import\Data|MockObject
     */
    protected $_importData;

    /**
     * @var CsvFactory|MockObject
     */
    protected $_csvFactory;

    /**
     * @var FileTransferFactory|MockObject
     */
    protected $_httpFactory;

    /**
     * @var UploaderFactory|MockObject
     */
    protected $_uploaderFactory;

    /**
     * @var IndexerRegistry|MockObject
     */
    protected $indexerRegistry;

    /**
     * @var \Magento\ImportExport\Model\Source\Import\Behavior\Factory|MockObject
     */
    protected $_behaviorFactory;

    /**
     * @var Filesystem|MockObject
     */
    protected $_filesystem;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $_coreConfig;

    /**
     * @var \Magento\ImportExport\Model\Import|MockObject
     */
    protected $import;

    /**
     * @var History|MockObject
     */
    protected $historyModel;

    /**
     * @var DateTime|MockObject
     */
    protected $dateTime;

    /**
     * @var WriteInterface|MockObject
     */
    protected $_varDirectory;

    /**
     * @var DriverInterface|MockObject
     */
    protected $_driver;

    /**
     * @var ProcessingErrorAggregatorInterface|MockObject
     */
    private $errorAggregatorMock;

    /**
     * @var Upload
     */
    private $upload;

    /**
     * Set up
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        parent::setUp();

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->_filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_importExportData = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_coreConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->_importConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEntityTypeCode', 'getBehavior', 'getEntities', 'getRelatedIndexers'])
            ->getMockForAbstractClass();
        $this->_entityFactory = $this->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->errorAggregatorMock = $this->getErrorAggregatorObject(
            [
                'initValidationStrategy',
                'getErrorsCount',
            ]
        );
        $this->_entityAdapter = $this->getMockBuilder(AbstractEntity::class)
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
        $this->_csvFactory = $this->getMockBuilder(CsvFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_httpFactory = $this->getMockBuilder(FileTransferFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_uploaderFactory = $this->getMockBuilder(UploaderFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_behaviorFactory = $this->getMockBuilder(
            \Magento\ImportExport\Model\Source\Import\Behavior\Factory::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->indexerRegistry = $this->getMockBuilder(IndexerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->historyModel = $this->getMockBuilder(History::class)
            ->disableOriginalConstructor()
            ->setMethods(['updateReport', 'invalidateReport', 'addReport'])
            ->getMock();
        $this->historyModel->expects($this->any())->method('updateReport')->willReturnSelf();
        $this->dateTime = $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_varDirectory = $this->getMockBuilder(WriteInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->_driver = $this->getMockBuilder(DriverInterface::class)
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
        $this->upload = $this->createMock(Upload::class);
        $this->import = $this->getMockBuilder(Import::class)
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
                    $this->dateTime,
                    [],
                    null,
                    null,
                    $this->upload
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
     */
    public function testImportSourceException()
    {
        $this->expectException(AlreadyExistsException::class);
        $exceptionMock = new AlreadyExistsException(
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
            ->willThrowException($exceptionMock);
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
        $this->markTestSkipped('This test has not been implemented yet.');
    }

    /**
     * @todo to implement it.
     */
    public function testGetAttributeType()
    {
        /** @var AbstractAttribute $attribute */
        $attribute = $this->getMockBuilder(AbstractAttribute::class)
            ->setMethods(['getFrontendInput', 'usesSource'])
            ->disableOriginalConstructor()
            ->getMock();
        $attribute->expects($this->any())->method('getFrontendInput')->willReturn('boolean');
        $attribute->expects($this->any())->method('usesSource')->willReturn(true);
        $this->assertEquals('boolean', $this->import->getAttributeType($attribute));
    }

    /**
     * @todo to implement it.
     */
    public function testGetEntity()
    {
        $this->markTestSkipped('This test has not been implemented yet.');
    }

    /**
     * @todo to implement it.
     */
    public function testGetErrorsCount()
    {
        $this->markTestSkipped('This test has not been implemented yet.');
    }

    /**
     * @todo to implement it.
     */
    public function testGetErrorsLimit()
    {
        $this->markTestSkipped('This test has not been implemented yet.');
    }

    /**
     * @todo to implement it.
     */
    public function testGetInvalidRowsCount()
    {
        $this->markTestSkipped('This test has not been implemented yet.');
    }

    /**
     * @todo to implement it.
     */
    public function testGetNotices()
    {
        $this->markTestSkipped('This test has not been implemented yet.');
    }

    /**
     * @todo to implement it.
     */
    public function testGetProcessedEntitiesCount()
    {
        $this->markTestSkipped('This test has not been implemented yet.');
    }

    /**
     * @todo to implement it.
     */
    public function testGetProcessedRowsCount()
    {
        $this->markTestSkipped('This test has not been implemented yet.');
    }

    /**
     * @todo to implement it.
     */
    public function testGetWorkingDir()
    {
        $this->markTestSkipped('This test has not been implemented yet.');
    }

    /**
     * @todo to implement it.
     */
    public function testIsImportAllowed()
    {
        $this->markTestSkipped('This test has not been implemented yet.');
    }

    /**
     * @todo to implement it.
     */
    public function testUploadSource()
    {
        $this->markTestSkipped('This test has not been implemented yet.');
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

        $csvMock = $this->getMockBuilder(Csv::class)
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
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

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
            $this->dateTime,
            [],
            null,
            null,
            $this->upload
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

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

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
            $this->dateTime,
            [],
            null,
            null,
            $this->upload
        );

        $import->setEntity('test');
        $this->assertSame($import, $import->invalidateIndex());
    }

    public function testGetKnownEntity()
    {
        $this->_importConfig->method('getEntities')
            ->willReturn(['test' => []]);

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

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
            $this->dateTime,
            [],
            null,
            null,
            $this->upload
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
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Entity is unknown');
        $this->_importConfig->method('getEntities')
            ->willReturn(['test' => []]);

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

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
            $this->dateTime,
            [],
            null,
            null,
            $this->upload
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
        $this->markTestSkipped('This test has not been implemented yet.');
    }

    /**
     * Cover isReportEntityType().
     *
     * @dataProvider isReportEntityTypeDataProvider
     */
    public function testIsReportEntityType($entity, $getEntityResult, $expectedResult)
    {
        $importMock = $this->getMockBuilder(Import::class)
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
        $this->expectException(LocalizedException::class);
        $importMock = $this->getMockBuilder(Import::class)
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
            ->with(Import::IMPORT_DIR . $fileName)
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
     */
    public function testCreateHistoryReportThrowException()
    {
        $this->expectException(LocalizedException::class);
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
        $phrase = $this->createMock(Phrase::class);
        $phrase->method('render')->willReturn('');
        $this->_driver
            ->expects($this->any())
            ->method('fileGetContents')
            ->willReturnCallback(
                function () use ($phrase) {
                    throw new FileSystemException($phrase);
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
