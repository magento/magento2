<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Setup;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Encryption\KeyValidator;
use Magento\Framework\Indexer\ConfigInterface;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerInterfaceFactory;
use Magento\Framework\Indexer\StateInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Math\Random;
use Magento\Framework\Mview\TriggerCleaner;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Indexer\Model\Indexer\State;
use Magento\Indexer\Model\Indexer\StateFactory;
use Magento\Indexer\Model\ResourceModel\Indexer\State\Collection;
use Magento\Indexer\Model\ResourceModel\Indexer\State\CollectionFactory;
use Magento\Indexer\Setup\Recurring;
use PHPUnit\Framework\Attributes\DataProvider;
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

    /**
     * @param string[] $keys
     * @param string|null $storedConfigKey
     * @param string $storedConfigSource
     * @param bool $expectInvalid
     * @param bool $expectSave
     */
    #[DataProvider('keyRotationDataProvider')]
    public function testInstallInvalidatesOnlyOnConfigChangeAfterKeyRotation(
        array $keys,
        ?string $storedConfigKey,
        string $storedConfigSource,
        bool $expectInvalid,
        bool $expectSave
    ): void {
        $indexerId = 'some_indexer';
        $config = ['indexer_id' => $indexerId, 'sources' => ['b', 'a']];
        $encodedConfigs = [
            'current' => json_encode(['indexer_id' => $indexerId, 'sources' => ['a', 'b']]),
            'changed' => json_encode(['indexer_id' => $indexerId, 'sources' => ['a', 'c']]),
        ];
        $storedHash = $storedConfigKey === null
            ? null
            : hash_hmac('md5', $encodedConfigs[$storedConfigSource], $storedConfigKey);
        $expectedHash = hash_hmac('md5', $encodedConfigs['current'], end($keys));

        $state = $this->createStateMock(['state_id' => 1, 'indexer_id' => $indexerId, 'hash_config' => $storedHash]);
        $state->expects($expectInvalid ? self::once() : self::never())
            ->method('setStatus')
            ->with(StateInterface::STATUS_INVALID);
        $state->expects($expectSave ? self::once() : self::never())->method('save');

        $this->runInstallWithRealEncryptor($keys, [$indexerId => $config], [$state], $state);
        self::assertSame($expectSave ? $expectedHash : $storedHash, $state->getData('hash_config'));
    }

    /**
     * @return array
     */
    public static function keyRotationDataProvider(): array
    {
        $oldKey = str_repeat('a', 32);
        $newKey = str_repeat('b', 32);

        return [
            'key rotated, config unchanged' => [[$oldKey, $newKey], $oldKey, 'current', false, true],
            'key rotated, config changed' => [[$oldKey, $newKey], $oldKey, 'changed', true, true],
            'key rotated, hash from unknown key' => [[$oldKey, $newKey], str_repeat('c', 32), 'current', true, true],
            'key rotated, no stored hash' => [[$oldKey, $newKey], null, 'current', true, true],
            'single key, config unchanged' => [[$newKey], $newKey, 'current', false, false],
            'single key, config changed' => [[$newKey], $newKey, 'changed', true, true],
        ];
    }

    public function testInstallCreatesInvalidStateForNewIndexerAfterKeyRotation(): void
    {
        $indexerId = 'new_indexer';
        $keys = [str_repeat('a', 32), str_repeat('b', 32)];
        $config = ['indexer_id' => $indexerId];

        $state = $this->createStateMock([]);
        $state->expects(self::once())->method('setStatus')->with(StateInterface::STATUS_INVALID);
        $state->expects(self::once())->method('save');

        $this->runInstallWithRealEncryptor($keys, [$indexerId => $config], [], $state);
        self::assertSame(hash_hmac('md5', json_encode($config), $keys[1]), $state->getData('hash_config'));
    }

    /**
     * @param array $data
     * @return State|MockObject
     */
    private function createStateMock(array $data): State|MockObject
    {
        $state = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['loadByIndexer', 'setStatus', 'save'])
            ->getMock();
        $state->setData($data);
        $state->method('loadByIndexer')->willReturnSelf();
        $state->method('setStatus')->willReturnSelf();

        return $state;
    }

    /**
     * @param string[] $keys
     * @param array $indexers
     * @param State[] $existingStates
     * @param State|MockObject $state
     * @return void
     */
    private function runInstallWithRealEncryptor(
        array $keys,
        array $indexers,
        array $existingStates,
        State|MockObject $state
    ): void {
        $deploymentConfig = $this->createStub(DeploymentConfig::class);
        $deploymentConfig->method('get')->willReturnMap([[Encryptor::PARAM_CRYPT_KEY, null, implode("\n", $keys)]]);
        $encryptor = new Encryptor($this->createStub(Random::class), $deploymentConfig, new KeyValidator());

        $this->stateFactory->method('create')->willReturn($state);
        $stateCollection = $this->createStub(Collection::class);
        $stateCollection->method('getItems')->willReturn($existingStates);
        $this->stateCollectionFactory->method('create')->willReturn($stateCollection);
        $this->config->method('getIndexers')->willReturn($indexers);
        $this->encoder->method('encode')->willReturnCallback(fn (array $config) => json_encode($config));

        $indexer = $this->createStub(IndexerInterface::class);
        $indexer->method('load')->willReturnSelf();
        $indexer->method('isScheduled')->willReturn(false);
        $this->indexerFactory->method('create')->willReturn($indexer);

        $model = new Recurring(
            $this->stateCollectionFactory,
            $this->stateFactory,
            $this->config,
            $encryptor,
            $this->encoder,
            $this->indexerFactory,
            $this->triggerCleaner
        );
        $model->install($this->setup, $this->context);
    }
}
