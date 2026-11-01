<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Setup;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Indexer\ConfigInterface;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerInterfaceFactory;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Mview\TriggerCleaner;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Indexer\Model\Indexer\State;
use Magento\Indexer\Model\Indexer\StateFactory;
use Magento\Indexer\Model\ResourceModel\Indexer\State\Collection;
use Magento\Indexer\Model\ResourceModel\Indexer\State\CollectionFactory;
use Magento\Indexer\Setup\Recurring;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RecurringTest extends TestCase
{
    /**
     * @var CollectionFactory|MockObject
     */
    private CollectionFactory|MockObject $stateCollectionFactory;

    /**
     * @var StateFactory|MockObject
     */
    private StateFactory|MockObject $stateFactory;

    /**
     * @var ConfigInterface|MockObject
     */
    private ConfigInterface|MockObject $config;

    /**
     * @var EncryptorInterface|MockObject
     */
    private EncryptorInterface|MockObject $encryptor;

    /**
     * @var EncoderInterface|MockObject
     */
    private EncoderInterface|MockObject $encoder;

    /**
     * @var IndexerInterfaceFactory|MockObject
     */
    private IndexerInterfaceFactory|MockObject $indexerFactory;

    /**
     * @var TriggerCleaner|MockObject
     */
    private TriggerCleaner|MockObject $triggerCleaner;

    /**
     * @var SchemaSetupInterface|MockObject
     */
    private SchemaSetupInterface|MockObject $setup;

    /**
     * @var ModuleContextInterface|MockObject
     */
    private ModuleContextInterface|MockObject $context;

    /**
     * @var Recurring
     */
    private Recurring $model;

    protected function setUp(): void
    {
        $this->stateCollectionFactory = $this->createMock(CollectionFactory::class);
        $this->stateFactory = $this->createMock(StateFactory::class);
        $this->config = $this->createMock(ConfigInterface::class);
        $this->encryptor = $this->createMock(EncryptorInterface::class);
        $this->encoder = $this->createMock(EncoderInterface::class);
        $this->indexerFactory = $this->createMock(IndexerInterfaceFactory::class);
        $this->triggerCleaner = $this->createMock(TriggerCleaner::class);
        $this->setup = $this->createMock(SchemaSetupInterface::class);
        $this->context = $this->createMock(ModuleContextInterface::class);

        $this->model = new Recurring(
            $this->stateCollectionFactory,
            $this->stateFactory,
            $this->config,
            $this->encryptor,
            $this->encoder,
            $this->indexerFactory,
            $this->triggerCleaner
        );
    }

    /**
     * Two config arrays that hold the same data in a different order (including a
     * differently-ordered nested list) must be hashed identically, since only the
     * actual content - not the incidental key/element order - should invalidate an indexer.
     */
    public function testInstallProducesIdenticalHashForSameConfigs(): void
    {
        $indexerId = 'some_indexer';
        $configOrderA = [
            'indexer_id' => $indexerId,
            'b' => 2,
            'a' => 1,
            'sources' => [3, 1, 2],
        ];
        $configOrderB = [
            'sources' => [2, 3, 1],
            'a' => 1,
            'indexer_id' => $indexerId,
            'b' => 2,
        ];
        $encodedConfigs = [];

        $state = $this->createMock(State::class);
        $state->method('loadByIndexer')->with($indexerId)->willReturnSelf();
        $state->method('getIndexerId')->willReturn($indexerId);
        $state->method('getData')->with('state_id')->willReturn(1);
        $this->stateFactory->method('create')->willReturn($state);
        $stateCollection = $this->createMock(Collection::class);
        $stateCollection->method('getItems')->willReturn([$state]);
        $this->stateCollectionFactory->method('create')->willReturn($stateCollection);

        $this->config->expects(self::exactly(4))->method('getIndexers')->willReturnOnConsecutiveCalls(
            [$indexerId => $configOrderA],
            [$indexerId => $configOrderA],
            [$indexerId => $configOrderB],
            [$indexerId => $configOrderB],
        );
        $this->encoder->expects(self::exactly(2))
            ->method('encode')
            ->willReturnCallback(function (array $sortedConfig) use (&$encodedConfigs) {
                $encodedConfigs[] = $sortedConfig;
                return json_encode($sortedConfig);
            });
        $this->encryptor->expects(self::exactly(2))->method('hash')->willReturnArgument(0);

        $indexer = $this->createMock(IndexerInterface::class);
        $indexer->method('load')->with($indexerId)->willReturnSelf();
        $indexer->method('isScheduled')->willReturn(false);
        $this->indexerFactory->method('create')->willReturn($indexer);

        $this->model->install($this->setup, $this->context);
        $this->model->install($this->setup, $this->context);
        self::assertEquals($encodedConfigs[0], $encodedConfigs[1]);
    }
}
