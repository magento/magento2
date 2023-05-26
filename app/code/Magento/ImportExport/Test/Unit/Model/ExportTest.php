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
use Magento\ImportExport\Model\LocaleEmulatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

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
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->exportConfigMock = $this->getMockForAbstractClass(ConfigInterface::class);
        $this->exportConfigMock->method('getEntities')
            ->willReturn($this->entities);
        $this->exportConfigMock->method('getFileFormats')
            ->willReturn($this->fileFormats);

        $this->exportAbstractEntityMock = $this->getMockBuilder(AbstractEntity::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->exportAdapterMock = $this->getMockBuilder(AbstractAdapter::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getFileExtension'])
            ->getMockForAbstractClass();

        $logger = $this->getMockForAbstractClass(LoggerInterface::class);
        $filesystem = $this->createMock(Filesystem::class);
        $entityFactory = $this->createMock(Factory::class);
        $entityFactory->method('create')
            ->willReturn($this->exportAbstractEntityMock);
        $exportAdapterFac = $this->createMock(Export\Adapter\Factory::class);
        $exportAdapterFac->method('create')
            ->willReturn($this->exportAdapterMock);
        $this->localeEmulator = $this->getMockForAbstractClass(LocaleEmulatorInterface::class);

        $this->model = new Export(
            $logger,
            $filesystem,
            $this->exportConfigMock,
            $entityFactory,
            $exportAdapterFac,
            [],
            $this->localeEmulator
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
