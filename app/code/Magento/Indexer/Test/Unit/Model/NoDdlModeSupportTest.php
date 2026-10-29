<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Model;

use Magento\Framework\Indexer\ConfigInterface;
use Magento\Indexer\Model\NoDdlModeSupport;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NoDdlModeSupportTest extends TestCase
{
    /**
     * @var ConfigInterface|MockObject
     */
    private $indexerConfigMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->indexerConfigMock = $this->createMock(ConfigInterface::class);
    }

    /**
     * @return void
     */
    public function testIsSupportedReturnsTrueForDeclaredIndexer(): void
    {
        $support = new NoDdlModeSupport($this->indexerConfigMock, ['sample_indexer_a']);

        $this->assertTrue($support->isSupported('sample_indexer_a'));
        $this->assertFalse($support->isSupported('sample_indexer_b'));
    }

    /**
     * @return void
     */
    public function testIsSupportedReturnsFalseWhenNoIndexerIdsDeclared(): void
    {
        $support = new NoDdlModeSupport($this->indexerConfigMock);

        $this->assertFalse($support->isSupported('sample_indexer_a'));
    }

    /**
     * @return void
     */
    public function testGetPairedIndexerIdsReturnsEmptyWhenIndexerHasNoSharedIndex(): void
    {
        $this->indexerConfigMock->method('getIndexer')->with('sample_indexer_a')->willReturn([]);

        $support = new NoDdlModeSupport($this->indexerConfigMock, ['sample_indexer_a', 'sample_indexer_b']);

        $this->assertSame([], $support->getPairedIndexerIds('sample_indexer_a'));
    }

    /**
     * @return void
     */
    public function testGetPairedIndexerIdsReturnsOtherSupportedIndexersInSameSharedIndexGroup(): void
    {
        $this->indexerConfigMock->method('getIndexer')
            ->with('sample_indexer_a')
            ->willReturn(['shared_index' => 'shared_group']);
        $this->indexerConfigMock->method('getIndexers')->willReturn([
            'sample_indexer_a' => ['shared_index' => 'shared_group'],
            'sample_indexer_b' => ['shared_index' => 'shared_group'],
            'sample_indexer_c' => ['shared_index' => 'other_group'],
            'sample_indexer_d' => [],
        ]);

        $support = new NoDdlModeSupport(
            $this->indexerConfigMock,
            ['sample_indexer_a', 'sample_indexer_b', 'sample_indexer_c', 'sample_indexer_d']
        );

        $this->assertSame(['sample_indexer_b'], $support->getPairedIndexerIds('sample_indexer_a'));
    }

    /**
     * @return void
     */
    public function testGetPairedIndexerIdsExcludesIndexersThatDoNotDeclareSupport(): void
    {
        $this->indexerConfigMock->method('getIndexer')
            ->with('sample_indexer_a')
            ->willReturn(['shared_index' => 'shared_group']);
        $this->indexerConfigMock->method('getIndexers')->willReturn([
            'sample_indexer_a' => ['shared_index' => 'shared_group'],
            'sample_indexer_b' => ['shared_index' => 'shared_group'],
        ]);

        $support = new NoDdlModeSupport($this->indexerConfigMock, ['sample_indexer_a']);

        $this->assertSame([], $support->getPairedIndexerIds('sample_indexer_a'));
    }

    /**
     * @return void
     */
    public function testGetIndexerIdForTableReturnsIndexerIdWhenTableIsMappedAndSupported(): void
    {
        $support = new NoDdlModeSupport(
            $this->indexerConfigMock,
            ['sample_indexer_a'],
            ['sample_table' => 'sample_indexer_a']
        );

        $this->assertSame('sample_indexer_a', $support->getIndexerIdForTable('sample_table'));
    }

    /**
     * @return void
     */
    public function testGetIndexerIdForTableReturnsNullWhenTableIsNotMapped(): void
    {
        $support = new NoDdlModeSupport(
            $this->indexerConfigMock,
            ['sample_indexer_a'],
            ['sample_table' => 'sample_indexer_a']
        );

        $this->assertNull($support->getIndexerIdForTable('other_table'));
    }

    /**
     * @return void
     */
    public function testGetIndexerIdForTableReturnsNullWhenMappedIndexerDoesNotDeclareSupport(): void
    {
        $support = new NoDdlModeSupport(
            $this->indexerConfigMock,
            [],
            ['sample_table' => 'sample_indexer_a']
        );

        $this->assertNull($support->getIndexerIdForTable('sample_table'));
    }
}
