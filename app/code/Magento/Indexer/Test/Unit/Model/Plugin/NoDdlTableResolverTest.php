<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Model\Plugin;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\ConfigInterface;
use Magento\Framework\Indexer\NoDdlModeInterface;
use Magento\Indexer\Model\NoDdlModeSupport;
use Magento\Indexer\Model\Plugin\NoDdlTableResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NoDdlTableResolverTest extends TestCase
{
    /**
     * @var NoDdlModeInterface|MockObject
     */
    private $noDdlModeMock;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceConnectionMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->noDdlModeMock = $this->createMock(NoDdlModeInterface::class);
        $this->resourceConnectionMock = $this->createMock(ResourceConnection::class);
    }

    /**
     * @param string[] $indexerTables
     * @return NoDdlModeSupport
     */
    private function createNoDdlModeSupport(array $indexerTables): NoDdlModeSupport
    {
        return new NoDdlModeSupport(
            $this->createMock(ConfigInterface::class),
            ['sample_indexer'],
            $indexerTables
        );
    }

    /**
     * @return void
     */
    public function testPassesThroughWhenTableIsNotInMap(): void
    {
        $resolver = new NoDdlTableResolver(
            $this->noDdlModeMock,
            $this->createNoDdlModeSupport(['sample_table' => 'sample_indexer'])
        );
        $this->noDdlModeMock->expects($this->never())->method('isEnabled');

        $result = $resolver->afterGetTableName($this->resourceConnectionMock, 'other_table', 'other_table');

        $this->assertSame('other_table', $result);
    }

    /**
     * @return void
     */
    public function testPassesThroughWhenModelEntityIsArray(): void
    {
        $resolver = new NoDdlTableResolver(
            $this->noDdlModeMock,
            $this->createNoDdlModeSupport(['sample_table' => 'sample_indexer'])
        );
        $this->noDdlModeMock->expects($this->never())->method('isEnabled');

        $result = $resolver->afterGetTableName($this->resourceConnectionMock, 'sample_table', ['sample_table']);

        $this->assertSame('sample_table', $result);
    }

    /**
     * @return void
     */
    public function testPassesThroughWhenIndexerIsNotEnabled(): void
    {
        $resolver = new NoDdlTableResolver(
            $this->noDdlModeMock,
            $this->createNoDdlModeSupport(['sample_table' => 'sample_indexer'])
        );
        $this->noDdlModeMock->method('isEnabled')->with('sample_indexer')->willReturn(false);
        $this->noDdlModeMock->expects($this->never())->method('isMainTableActive');

        $result = $resolver->afterGetTableName($this->resourceConnectionMock, 'sample_table', 'sample_table');

        $this->assertSame('sample_table', $result);
    }

    /**
     * @return void
     */
    public function testPassesThroughWhenMainTableIsActive(): void
    {
        $resolver = new NoDdlTableResolver(
            $this->noDdlModeMock,
            $this->createNoDdlModeSupport(['sample_table' => 'sample_indexer'])
        );
        $this->noDdlModeMock->method('isEnabled')->with('sample_indexer')->willReturn(true);
        $this->noDdlModeMock->method('isMainTableActive')->with('sample_indexer')->willReturn(true);

        $result = $resolver->afterGetTableName($this->resourceConnectionMock, 'sample_table', 'sample_table');

        $this->assertSame('sample_table', $result);
    }

    /**
     * @return void
     */
    public function testAppendsReplicaSuffixWhenEnabledAndReplicaIsActive(): void
    {
        $resolver = new NoDdlTableResolver(
            $this->noDdlModeMock,
            $this->createNoDdlModeSupport(['sample_table' => 'sample_indexer'])
        );
        $this->noDdlModeMock->method('isEnabled')->with('sample_indexer')->willReturn(true);
        $this->noDdlModeMock->method('isMainTableActive')->with('sample_indexer')->willReturn(false);

        $result = $resolver->afterGetTableName($this->resourceConnectionMock, 'sample_table', 'sample_table');

        $this->assertSame('sample_table' . NoDdlModeInterface::REPLICA_TABLE_SUFFIX, $result);
    }
}
