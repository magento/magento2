<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Test\Unit\Model\Export\Entity;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\ImportExport\Api\Data\FieldsEnclosureAwareExportInfoInterface;
use Magento\ImportExport\Model\Export\Adapter\AbstractAdapter as ExportWriterAdapter;
use Magento\ImportExport\Model\Export\Adapter\Factory as AdapterFactory;
use Magento\ImportExport\Model\Export\AbstractEntity as ExportAbstractEntity;
use Magento\ImportExport\Model\Export\ConfigInterface;
use Magento\ImportExport\Model\Export\Entity\ExportInfoFactory;
use Magento\ImportExport\Model\Export\Entity\Factory as EntityFactory;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ExportInfoFactoryTest extends TestCase
{
    /**
     * Summary of testCreateBuildsExportInfoAndAdapterParameters
     * @return
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCreateBuildsExportInfoAndAdapterParameters(): void
    {
        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $exportConfig = $this->createMock(ConfigInterface::class);
        $entityFactory = $this->createMock(EntityFactory::class);
        $adapterFactory = $this->createMock(AdapterFactory::class);
        $serializer = $this->createMock(SerializerInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $entity = 'catalog_product';
        $fileFormat = 'csv';
        $exportFilter = ['sku' => ['eq' => 'ABC']];
        $skipAttr = ['media_gallery'];
        $locale = 'en_US';
        $fieldsEnclosure = true;

        $exportConfig->method('getEntities')->willReturn([
            $entity => ['model' => 'EntityModel']
        ]);
        $exportConfig->method('getFileFormats')->willReturn([
            $fileFormat => ['model' => 'WriterModel']
        ]);

        $entityAdapter = $this->getMockBuilder(ExportAbstractEntity::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getEntityTypeCode',
                'getFileName',
                'setParameters',
                'export',
                'exportItem',
                '_getHeaderColumns',
                '_getEntityCollection'
            ])
            ->getMock();
        $entityAdapter->method('getEntityTypeCode')->willReturn($entity);
        $entityAdapter->method('getFileName')->willReturn(null);
        $entityAdapter->expects($this->once())
            ->method('setParameters')
            ->with($this->callback(function (array $params) use ($entity, $fileFormat, $exportFilter, $skipAttr) {
                return isset(
                    $params['fileFormat'],
                    $params['entity'],
                    $params['exportFilter'],
                    $params['skipAttr'],
                    $params['contentType']
                )
                    && $params['fileFormat'] === $fileFormat
                    && $params['entity'] === $entity
                    && $params['exportFilter'] === $exportFilter
                    && $params['skipAttr'] === $skipAttr
                    && is_string($params['contentType']);
            }));
        $entityFactory->expects($this->once())
            ->method('create')
            ->with('EntityModel')
            ->willReturn($entityAdapter);

        $writer = $this->getMockBuilder(ExportWriterAdapter::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getContentType', 'getFileExtension', 'writeRow'])
            ->getMock();
        $writer->method('getContentType')->willReturn('text/csv');
        $writer->method('getFileExtension')->willReturn('csv');
        $adapterFactory->expects($this->once())
            ->method('create')
            ->with('WriterModel')
            ->willReturn($writer);

        $exportInfo = $this->createMock(FieldsEnclosureAwareExportInfoInterface::class);
        $objectManager->expects($this->once())
            ->method('create')
            ->with(FieldsEnclosureAwareExportInfoInterface::class)
            ->willReturn($exportInfo);

        $serializer->expects($this->once())
            ->method('serialize')
            ->with($exportFilter)
            ->willReturn('serialized-filter');

        $exportInfo->expects($this->once())->method('setExportFilter')->with('serialized-filter');
        $exportInfo->expects($this->once())->method('setSkipAttr')->with($skipAttr);
        $exportInfo->expects($this->once())->method('setEntity')->with($entity);
        $exportInfo->expects($this->once())->method('setFileFormat')->with($fileFormat);
        $exportInfo->expects($this->once())->method('setContentType')->with('text/csv');
        $exportInfo->expects($this->once())->method('setLocale')->with($locale);
        $exportInfo->expects($this->once())->method('setFieldsEnclosure')->with($fieldsEnclosure);
        $exportInfo->expects($this->once())
            ->method('setFileName')
            ->with($this->callback(function (string $fileName) use ($entity) {
                return str_starts_with($fileName, $entity . '_') && str_ends_with($fileName, '.csv');
            }));

        $factory = new ExportInfoFactory(
            $objectManager,
            $exportConfig,
            $entityFactory,
            $adapterFactory,
            $serializer,
            $logger
        );

        $result = $factory->create($fileFormat, $entity, $exportFilter, $skipAttr, $locale, $fieldsEnclosure);
        $this->assertSame($exportInfo, $result);
    }

    public function testCreateUsesAdapterProvidedFileName(): void
    {
        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $exportConfig = $this->createMock(ConfigInterface::class);
        $entityFactory = $this->createMock(EntityFactory::class);
        $adapterFactory = $this->createMock(AdapterFactory::class);
        $serializer = $this->createMock(SerializerInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $entity = 'catalog_product';
        $fileFormat = 'csv';
        $exportFilter = [];

        $exportConfig->method('getEntities')->willReturn([
            $entity => ['model' => 'EntityModel']
        ]);
        $exportConfig->method('getFileFormats')->willReturn([
            $fileFormat => ['model' => 'WriterModel']
        ]);

        $entityAdapter = $this->getMockBuilder(ExportAbstractEntity::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getEntityTypeCode',
                'getFileName',
                'setParameters',
                'export',
                'exportItem',
                '_getHeaderColumns',
                '_getEntityCollection'
            ])
            ->getMock();
        $entityAdapter->method('getEntityTypeCode')->willReturn($entity);
        $entityAdapter->method('getFileName')->willReturn('custom_name');
        $entityFactory->method('create')->with('EntityModel')->willReturn($entityAdapter);

        $writer = $this->getMockBuilder(ExportWriterAdapter::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getContentType', 'getFileExtension', 'writeRow'])
            ->getMock();
        $writer->method('getContentType')->willReturn('text/csv');
        $writer->method('getFileExtension')->willReturn('csv');
        $adapterFactory->method('create')->with('WriterModel')->willReturn($writer);

        $exportInfo = $this->createMock(FieldsEnclosureAwareExportInfoInterface::class);
        $objectManager->method('create')->with(FieldsEnclosureAwareExportInfoInterface::class)->willReturn($exportInfo);

        $serializer->method('serialize')->with($exportFilter)->willReturn('[]');
        $exportInfo->expects($this->once())
            ->method('setFileName')
            ->with($this->callback(function (string $fileName) {
                return str_starts_with($fileName, 'custom_name_') && str_ends_with($fileName, '.csv');
            }));

        $factory = new ExportInfoFactory(
            $objectManager,
            $exportConfig,
            $entityFactory,
            $adapterFactory,
            $serializer,
            $logger
        );

        $factory->create($fileFormat, $entity, $exportFilter, []);
        $this->assertTrue(true);
    }

    public function testCreateThrowsForUnknownEntity(): void
    {
        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $exportConfig = $this->createMock(ConfigInterface::class);
        $entityFactory = $this->createMock(EntityFactory::class);
        $adapterFactory = $this->createMock(AdapterFactory::class);
        $serializer = $this->createMock(SerializerInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $exportConfig->method('getEntities')->willReturn([]);
        $exportConfig->method('getFileFormats')->willReturn([
            'csv' => ['model' => 'WriterModel']
        ]);

        $writer = $this->getMockBuilder(ExportWriterAdapter::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getContentType', 'getFileExtension', 'writeRow'])
            ->getMock();
        $writer->method('getContentType')->willReturn('text/csv');
        $writer->method('getFileExtension')->willReturn('csv');
        $adapterFactory->method('create')->with('WriterModel')->willReturn($writer);

        $factory = new ExportInfoFactory(
            $objectManager,
            $exportConfig,
            $entityFactory,
            $adapterFactory,
            $serializer,
            $logger
        );

        $this->expectException(LocalizedException::class);
        $factory->create('csv', 'unknown_entity', [], []);
    }

    public function testCreateThrowsForInvalidFileFormat(): void
    {
        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $exportConfig = $this->createMock(ConfigInterface::class);
        $entityFactory = $this->createMock(EntityFactory::class);
        $adapterFactory = $this->createMock(AdapterFactory::class);
        $serializer = $this->createMock(SerializerInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $exportConfig->method('getFileFormats')->willReturn([
            'csv' => ['model' => 'WriterModel']
        ]);

        $factory = new ExportInfoFactory(
            $objectManager,
            $exportConfig,
            $entityFactory,
            $adapterFactory,
            $serializer,
            $logger
        );

        $this->expectException(LocalizedException::class);
        $factory->create('xlsx', 'catalog_product', [], []);
    }

    public function testCreateThrowsForWrongAdapterType(): void
    {
        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $exportConfig = $this->createMock(ConfigInterface::class);
        $entityFactory = $this->createMock(EntityFactory::class);
        $adapterFactory = $this->createMock(AdapterFactory::class);
        $serializer = $this->createMock(SerializerInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $exportConfig->method('getEntities')->willReturn([
            'catalog_product' => ['model' => 'EntityModel']
        ]);
        $exportConfig->method('getFileFormats')->willReturn([
            'csv' => ['model' => 'WriterModel']
        ]);

        $writer = $this->getMockBuilder(ExportWriterAdapter::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getContentType', 'getFileExtension', 'writeRow'])
            ->getMock();
        $writer->method('getContentType')->willReturn('text/csv');
        $writer->method('getFileExtension')->willReturn('csv');
        $adapterFactory->method('create')->with('WriterModel')->willReturn($writer);

        $entityFactory->method('create')->with('EntityModel')->willReturn(new \stdClass());

        $factory = new ExportInfoFactory(
            $objectManager,
            $exportConfig,
            $entityFactory,
            $adapterFactory,
            $serializer,
            $logger
        );

        $this->expectException(LocalizedException::class);
        $factory->create('csv', 'catalog_product', [], []);
    }

    public function testCreateThrowsWhenWriterCreationFailsLogsAndThrows(): void
    {
        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $exportConfig = $this->createMock(ConfigInterface::class);
        $entityFactory = $this->createMock(EntityFactory::class);
        $adapterFactory = $this->createMock(AdapterFactory::class);
        $serializer = $this->createMock(SerializerInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $exportConfig->method('getFileFormats')->willReturn([
            'csv' => ['model' => 'WriterModel']
        ]);
        $adapterFactory->method('create')
            ->with('WriterModel')
            ->willThrowException(new \Exception('boom'));
        $logger->expects($this->once())->method('critical');

        $factory = new ExportInfoFactory(
            $objectManager,
            $exportConfig,
            $entityFactory,
            $adapterFactory,
            $serializer,
            $logger
        );

        $this->expectException(LocalizedException::class);
        $factory->create('csv', 'catalog_product', [], []);
    }

    public function testCreateThrowsWhenWriterIsWrongType(): void
    {
        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $exportConfig = $this->createMock(ConfigInterface::class);
        $entityFactory = $this->createMock(EntityFactory::class);
        $adapterFactory = $this->createMock(AdapterFactory::class);
        $serializer = $this->createMock(SerializerInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $exportConfig->method('getFileFormats')->willReturn([
            'csv' => ['model' => 'WriterModel']
        ]);
        $adapterFactory->method('create')->with('WriterModel')->willReturn(new \stdClass());

        $factory = new ExportInfoFactory(
            $objectManager,
            $exportConfig,
            $entityFactory,
            $adapterFactory,
            $serializer,
            $logger
        );

        $this->expectException(LocalizedException::class);
        $factory->create('csv', 'catalog_product', [], []);
    }

    public function testCreateThrowsWhenEntityFactoryThrowsLogsAndThrows(): void
    {
        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $exportConfig = $this->createMock(ConfigInterface::class);
        $entityFactory = $this->createMock(EntityFactory::class);
        $adapterFactory = $this->createMock(AdapterFactory::class);
        $serializer = $this->createMock(SerializerInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $exportConfig->method('getEntities')->willReturn([
            'catalog_product' => ['model' => 'EntityModel']
        ]);
        $exportConfig->method('getFileFormats')->willReturn([
            'csv' => ['model' => 'WriterModel']
        ]);

        $writer = $this->getMockBuilder(ExportWriterAdapter::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getContentType', 'getFileExtension', 'writeRow'])
            ->getMock();
        $writer->method('getContentType')->willReturn('text/csv');
        $writer->method('getFileExtension')->willReturn('csv');
        $adapterFactory->method('create')->with('WriterModel')->willReturn($writer);

        $entityFactory->method('create')->with('EntityModel')->willThrowException(new \Exception('boom'));
        $logger->expects($this->once())->method('critical');

        $factory = new ExportInfoFactory(
            $objectManager,
            $exportConfig,
            $entityFactory,
            $adapterFactory,
            $serializer,
            $logger
        );

        $this->expectException(LocalizedException::class);
        $factory->create('csv', 'catalog_product', [], []);
    }

    public function testCreateThrowsWhenEntityCodeMismatch(): void
    {
        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $exportConfig = $this->createMock(ConfigInterface::class);
        $entityFactory = $this->createMock(EntityFactory::class);
        $adapterFactory = $this->createMock(AdapterFactory::class);
        $serializer = $this->createMock(SerializerInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $entity = 'catalog_product';
        $exportConfig->method('getEntities')->willReturn([
            $entity => ['model' => 'EntityModel']
        ]);
        $exportConfig->method('getFileFormats')->willReturn([
            'csv' => ['model' => 'WriterModel']
        ]);

        $writer = $this->getMockBuilder(ExportWriterAdapter::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getContentType', 'getFileExtension', 'writeRow'])
            ->getMock();
        $writer->method('getContentType')->willReturn('text/csv');
        $writer->method('getFileExtension')->willReturn('csv');
        $adapterFactory->method('create')->with('WriterModel')->willReturn($writer);

        $entityAdapter = $this->getMockBuilder(ExportAbstractEntity::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getEntityTypeCode', 'export', 'exportItem', '_getHeaderColumns', '_getEntityCollection'])
            ->getMock();
        $entityAdapter->method('getEntityTypeCode')->willReturn('different_code');
        $entityFactory->method('create')->with('EntityModel')->willReturn($entityAdapter);

        $factory = new ExportInfoFactory(
            $objectManager,
            $exportConfig,
            $entityFactory,
            $adapterFactory,
            $serializer,
            $logger
        );

        $this->expectException(LocalizedException::class);
        $factory->create('csv', $entity, [], []);
    }

    public function testCreateDoesNotSetOptionalFieldsWhenNulls(): void
    {
        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $exportConfig = $this->createMock(ConfigInterface::class);
        $entityFactory = $this->createMock(EntityFactory::class);
        $adapterFactory = $this->createMock(AdapterFactory::class);
        $serializer = $this->createMock(SerializerInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $entity = 'catalog_product';
        $fileFormat = 'csv';
        $exportFilter = [];
        $skipAttr = [];

        $exportConfig->method('getEntities')->willReturn([
            $entity => ['model' => 'EntityModel']
        ]);
        $exportConfig->method('getFileFormats')->willReturn([
            $fileFormat => ['model' => 'WriterModel']
        ]);

        $entityAdapter = $this->getMockBuilder(ExportAbstractEntity::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getEntityTypeCode',
                'getFileName',
                'setParameters',
                'export',
                'exportItem',
                '_getHeaderColumns',
                '_getEntityCollection'
            ])
            ->getMock();
        $entityAdapter->method('getEntityTypeCode')->willReturn($entity);
        $entityAdapter->method('getFileName')->willReturn(null);
        $entityFactory->method('create')->with('EntityModel')->willReturn($entityAdapter);

        $writer = $this->getMockBuilder(ExportWriterAdapter::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getContentType', 'getFileExtension', 'writeRow'])
            ->getMock();
        $writer->method('getContentType')->willReturn('text/csv');
        $writer->method('getFileExtension')->willReturn('csv');
        $adapterFactory->method('create')->with('WriterModel')->willReturn($writer);

        $exportInfo = $this->createMock(FieldsEnclosureAwareExportInfoInterface::class);
        $objectManager->method('create')->with(FieldsEnclosureAwareExportInfoInterface::class)->willReturn($exportInfo);

        $serializer->method('serialize')->with($exportFilter)->willReturn('[]');
        $exportInfo->expects($this->never())->method('setLocale');
        $exportInfo->expects($this->never())->method('setFieldsEnclosure');

        $factory = new ExportInfoFactory(
            $objectManager,
            $exportConfig,
            $entityFactory,
            $adapterFactory,
            $serializer,
            $logger
        );

        $factory->create($fileFormat, $entity, $exportFilter, $skipAttr, null, null);
        $this->assertTrue(true);
    }
}
