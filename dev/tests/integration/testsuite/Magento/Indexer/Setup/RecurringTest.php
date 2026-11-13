<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Indexer\Setup;

use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Indexer\ConfigInterface;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerInterfaceFactory;
use Magento\Framework\Indexer\StateInterface;
use Magento\Framework\Mview\TriggerCleaner;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Indexer\Model\Indexer\State;
use Magento\Indexer\Model\Indexer\StateFactory;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[DbIsolation(true)]
class RecurringTest extends TestCase
{
    private const INDEXER_ID = 'customer_grid';

    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    /**
     * @var Encryptor
     */
    private Encryptor $encryptor;

    /**
     * @var array
     */
    private array $indexerConfig;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->encryptor = $this->objectManager->get(EncryptorInterface::class);
        $this->indexerConfig = $this->objectManager->get(ConfigInterface::class)->getIndexer(self::INDEXER_ID);
    }

    public function testKeyRotationKeepsIndexerValidWhenConfigIsUnchanged(): void
    {
        $this->runRecurring($this->encryptor, $this->indexerConfig);
        $this->markIndexerValid();
        $hashBeforeRotation = $this->loadState()->getHashConfig();

        $this->runRecurring($this->rotateKey(), $this->indexerConfig);

        $state = $this->loadState();
        self::assertSame(StateInterface::STATUS_VALID, $state->getStatus());
        self::assertNotSame($hashBeforeRotation, $state->getHashConfig());
    }

    public function testKeyRotationInvalidatesIndexerWhenConfigChanged(): void
    {
        $this->runRecurring($this->encryptor, $this->indexerConfig);
        $this->markIndexerValid();

        $changedConfig = $this->indexerConfig;
        $changedConfig['title'] = 'Changed Customer Grid';
        $this->runRecurring($this->rotateKey(), $changedConfig);

        self::assertSame(StateInterface::STATUS_INVALID, $this->loadState()->getStatus());
    }

    /**
     * Return a copy of the application encryptor with a freshly generated key appended, as encryption:key:change does
     *
     * @return Encryptor
     */
    private function rotateKey(): Encryptor
    {
        $rotatedEncryptor = clone $this->encryptor;
        $rotatedEncryptor->setNewKey(
            ConfigOptionsListConstants::STORE_KEY_ENCODED_RANDOM_STRING_PREFIX
            . base64_encode(random_bytes(ConfigOptionsListConstants::STORE_KEY_RANDOM_STRING_SIZE))
        );

        return $rotatedEncryptor;
    }

    /**
     * @param Encryptor $encryptor
     * @param array $indexerConfig
     * @return void
     */
    private function runRecurring(Encryptor $encryptor, array $indexerConfig): void
    {
        $config = $this->createStub(ConfigInterface::class);
        $config->method('getIndexers')->willReturn([self::INDEXER_ID => $indexerConfig]);
        $indexer = $this->createStub(IndexerInterface::class);
        $indexer->method('load')->willReturnSelf();
        $indexer->method('isScheduled')->willReturn(false);
        $indexerFactory = $this->createStub(IndexerInterfaceFactory::class);
        $indexerFactory->method('create')->willReturn($indexer);

        $this->objectManager->create(
            Recurring::class,
            [
                'config' => $config,
                'encryptor' => $encryptor,
                'indexerFactory' => $indexerFactory,
                'triggerCleaner' => $this->createStub(TriggerCleaner::class),
            ]
        )->install(
            $this->createStub(SchemaSetupInterface::class),
            $this->createStub(ModuleContextInterface::class)
        );
    }

    /**
     * @return void
     */
    private function markIndexerValid(): void
    {
        $this->loadState()->setStatus(StateInterface::STATUS_VALID)->save();
    }

    /**
     * @return State
     */
    private function loadState(): State
    {
        return $this->objectManager->get(StateFactory::class)->create()->loadByIndexer(self::INDEXER_ID);
    }
}
