<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

/**
 * Test class for \Magento\ImportExport\Model\Export
 */
namespace Magento\ImportExport\Test\Unit\Model;

use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\Io\File;
use Magento\ImportExport\Model\Export;
use Magento\ImportExport\Model\Export\FileInfo;
use Magento\ImportExport\Model\Export\AbstractEntity;
use Magento\ImportExport\Model\Export\Adapter\AbstractAdapter;
use Magento\ImportExport\Model\Export\ConfigInterface;
use Magento\ImportExport\Model\Export\Entity\Factory;
use Magento\ImportExport\Model\LocaleEmulatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ExportTest extends TestCase
{
    /**
     * @var ConfigInterface|MockObject
     */
    private $exportConfigMock;

    /**
     * @var AbstractEntity|MockObject
     */
    private $exportAbstractEntityMock;

    /**
     * @var AbstractAdapter|MockObject
     */
    private $exportAdapterMock;

    /**
     * @var Export\Adapter\Factory|MockObject
     */
    private $exportAdapterFactoryMock;

    /**
     * @var Export
     */
    private $model;

    /**
     * @var string[]
     */
    private $entities = [
        'entityA' => [
            'model' => 'entityAClass'
        ],
        'entityB' => [
            'model' => 'entityBClass'
        ]
    ];
    /**
     * @var string[]
     */
    private $fileFormats = [
        'csv' => [
            'model' => 'csvFormatClass'
        ],
        'xml' => [
            'model' => 'xmlFormatClass'
        ]
    ];

    /**
     * @var LocaleEmulatorInterface|MockObject
     */
    private $localeEmulator;

    /**
     * @var FileInfo
     */
    private $fileInfo;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->exportConfigMock = $this->createMock(ConfigInterface::class);
        $this->exportConfigMock->method('getEntities')
            ->willReturn($this->entities);
        $this->exportConfigMock->method('getFileFormats')
            ->willReturn($this->fileFormats);

        $this->exportAbstractEntityMock = $this->createPartialMock(
            AbstractEntity::class,
            ['export', 'getEntityTypeCode', 'exportItem', '_getHeaderColumns', '_getEntityCollection']
        );

        $this->exportAdapterMock = $this->createPartialMock(
            AbstractAdapter::class,
            ['getFileExtension', 'writeRow']
        );

        $logger = $this->createMock(LoggerInterface::class);
        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->method('getDirectoryWrite')
            ->willReturn($this->createMock(WriteInterface::class));
        $entityFactory = $this->createMock(Factory::class);
        $entityFactory->method('create')
            ->willReturn($this->exportAbstractEntityMock);
        $this->exportAdapterFactoryMock = $this->createMock(Export\Adapter\Factory::class);
        $this->exportAdapterFactoryMock->method('create')
            ->willReturn($this->exportAdapterMock);
        $this->localeEmulator = $this->createMock(LocaleEmulatorInterface::class);
        $fileIoMock = $this->createMock(File::class);
        $fileIoMock->method('getPathInfo')
            ->willReturnCallback(fn (string $fileName) => ['extension' => pathinfo($fileName, PATHINFO_EXTENSION)]);
        $this->fileInfo = new FileInfo($this->exportConfigMock, $fileIoMock);

        $this->model = new Export(
            $logger,
            $filesystem,
            $this->exportConfigMock,
            $entityFactory,
            $this->exportAdapterFactoryMock,
            [],
            $this->localeEmulator,
            $this->fileInfo
        );
    }

    /**
     * Tests that export doesn't use `trim` function while counting the number of exported rows.
     *
     * Using PHP `trim` function allocates the same amount of memory as export result and leads
     * to `out of memory` error.
     */
    public function testExportDoesntTrimResult()
    {
        $locale = 'fr_FR';
        $this->localeEmulator->method('emulate')
            ->with($this->callback(fn ($callback) => is_callable($callback)), $locale)
            ->willReturnCallback(fn (callable $callback) => $callback());
        $config = [
            'entity' => 'entityA',
            'file_format' => 'csv',
            Export::FILTER_ELEMENT_GROUP => [],
            'locale' => $locale
        ];
        $this->model->setData($config);
        $this->exportAbstractEntityMock->method('getEntityTypeCode')
            ->willReturn($config['entity']);
        $this->exportAdapterMock->method('getFileExtension')
            ->willReturn($config['file_format']);

        $this->exportAbstractEntityMock->method('export')
            ->willReturn("export data  \n\n");
        $this->model->export();
        $this->assertStringContainsString(
            'Exported 2 rows',
            var_export($this->model->getFormatedLogTrace(), true)
        );
    }

    /**
     * Test get file name with adapter file name
     */
    public function testGetFileNameWithAdapterFileName()
    {
        $basicFileName = 'test_file_name';
        $config = [
            'entity' => 'entityA',
            'file_format' => 'csv',
        ];
        $this->model->setData($config);
        $this->exportAbstractEntityMock->method('getEntityTypeCode')
            ->willReturn($config['entity']);
        $this->exportAdapterMock->method('getFileExtension')
            ->willReturn($config['file_format']);
        $this->exportAbstractEntityMock->setFileName($basicFileName);

        $fileName = $this->model->getFileName();
        $correctDateTime = $this->_getCorrectDateTime($fileName);
        $this->assertNotNull($correctDateTime);

        $correctFileName = $basicFileName . '_' . $correctDateTime . '.' . $config['file_format'];
        $this->assertEquals($correctFileName, $fileName);
    }

    public function testQueueFlowUsesTemporaryDestination(): void
    {
        $fileName = 'catalog_product_20260324_120000.csv';
        $config = [
            'entity' => 'entityA',
            'file_format' => 'csv',
            'file_name' => $fileName,
            Export::FILTER_ELEMENT_GROUP => [],
        ];
        $this->model->setData($config);
        $this->localeEmulator->method('emulate')
            ->willReturnCallback(fn (callable $callback) => $callback());
        $this->exportAbstractEntityMock->method('getEntityTypeCode')
            ->willReturn($config['entity']);
        $this->exportAbstractEntityMock->method('export')
            ->willReturn('content');

        $this->exportAdapterFactoryMock->expects(self::once())
            ->method('create')
            ->with(
                'csvFormatClass',
                ['destination' => $this->fileInfo->getInProgressFilePath($fileName)]
            )
            ->willReturn($this->exportAdapterMock);

        self::assertSame('__RESULT_WRITTEN_TO_FILE__', $this->model->export());
    }

    /**
     * Test get file name without adapter file name
     */
    public function testGetFileNameWithoutAdapterFileName()
    {
        $config = [
            'entity' => 'entityA',
            'file_format' => 'csv',
        ];
        $this->model->setData($config);
        $this->exportAbstractEntityMock->method('getEntityTypeCode')
            ->willReturn($config['entity']);
        $this->exportAdapterMock->method('getFileExtension')
            ->willReturn($config['file_format']);
        $this->exportAbstractEntityMock->setFileName(null);

        $fileName = $this->model->getFileName();
        $correctDateTime = $this->_getCorrectDateTime($fileName);
        $this->assertNotNull($correctDateTime);

        $correctFileName = $config['entity'] . '_' . $correctDateTime . '.' . $config['file_format'];
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
