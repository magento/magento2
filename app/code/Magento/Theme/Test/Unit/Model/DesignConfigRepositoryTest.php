<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Model;

use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Theme\Api\Data\DesignConfigDataInterface;
use Magento\Theme\Api\Data\DesignConfigInterface;
use Magento\Theme\Model\Data\Design\Config;
use Magento\Theme\Model\Design\Config\Storage;
use Magento\Theme\Model\Design\Config\Validator;
use Magento\Theme\Model\DesignConfigRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DesignConfigRepositoryTest extends TestCase
{
    /** @var Storage|MockObject */
    protected $configStorage;

    /** @var ReinitableConfigInterface|MockObject */
    protected $reinitableConfig;

    /** @var IndexerRegistry|MockObject */
    protected $indexerRegistry;

    /** @var DesignConfigInterface|MockObject */
    protected $designConfig;

    /** @var \Magento\Theme\Api\Data\DesignConfigExtensionInterface|MockObject */
    protected $designExtension;

    /** @var DesignConfigDataInterface|MockObject */
    protected $designConfigData;

    /** @var IndexerInterface|MockObject */
    protected $indexer;

    /** @var DesignConfigRepository */
    protected $repository;

    /**
     * @var MockObject
     */
    protected $validator;

    protected function setUp(): void
    {
        $this->configStorage = $this->createMock(Storage::class);
        $this->reinitableConfig = $this->getMockForAbstractClass(
            ReinitableConfigInterface::class,
            [],
            '',
            false
        );
        $this->indexerRegistry = $this->createMock(IndexerRegistry::class);
        $this->designConfig = $this->getMockForAbstractClass(
            DesignConfigInterface::class,
            [],
            '',
            false
        );
        $this->designExtension = $this->getMockForAbstractClass(
            \Magento\Theme\Api\Data\DesignConfigExtensionInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getDesignConfigData']
        );
        $this->designConfigData = $this->getMockForAbstractClass(
            DesignConfigDataInterface::class,
            [],
            '',
            false
        );
        $this->indexer = $this->getMockForAbstractClass(
            IndexerInterface::class,
            [],
            '',
            false
        );

        $this->validator = $this->createMock(Validator::class);
        $objectManagerHelper = new ObjectManager($this);
        $this->repository = $objectManagerHelper->getObject(
            DesignConfigRepository::class,
            [
                'configStorage' => $this->configStorage,
                'reinitableConfig' => $this->reinitableConfig,
                'indexerRegistry' => $this->indexerRegistry,
                'validator' => $this->validator
            ]
        );
    }

    public function testSave()
    {
        $this->designConfig->expects($this->exactly(2))
            ->method('getExtensionAttributes')
            ->willReturn($this->designExtension);
        $this->designExtension->expects($this->once())
            ->method('getDesignConfigData')
            ->willReturn([$this->designConfigData]);
        $this->configStorage->expects($this->once())
            ->method('save')
            ->willReturn($this->designConfig);
        $this->reinitableConfig->expects($this->once())
            ->method('reinit');
        $this->indexerRegistry->expects($this->once())
            ->method('get')
            ->with(Config::DESIGN_CONFIG_GRID_INDEXER_ID)
            ->willReturn($this->indexer);
        $this->indexer->expects($this->once())
            ->method('reindexAll');
        $this->validator->expects($this->once())->method('validate')->with($this->designConfig);
        $this->assertSame($this->designConfig, $this->repository->save($this->designConfig));
    }

    public function testSaveWithoutConfig()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage(
            'The config can\'t be saved because it\'s empty. Complete the config and try again.'
        );
        $this->designConfig->expects($this->exactly(2))
            ->method('getExtensionAttributes')
            ->willReturn($this->designExtension);
        $this->designExtension->expects($this->once())
            ->method('getDesignConfigData')
            ->willReturn(false);
        $this->repository->save($this->designConfig);
    }

    public function testDelete()
    {
        $this->designConfig->expects($this->exactly(2))
            ->method('getExtensionAttributes')
            ->willReturn($this->designExtension);
        $this->designExtension->expects($this->once())
            ->method('getDesignConfigData')
            ->willReturn([$this->designConfigData]);
        $this->configStorage->expects($this->once())
            ->method('delete')
            ->with($this->designConfig);
        $this->reinitableConfig->expects($this->once())
            ->method('reinit');
        $this->indexerRegistry->expects($this->once())
            ->method('get')
            ->with(Config::DESIGN_CONFIG_GRID_INDEXER_ID)
            ->willReturn($this->indexer);
        $this->indexer->expects($this->once())
            ->method('reindexAll');
        $this->assertSame($this->designConfig, $this->repository->delete($this->designConfig));
    }
}
