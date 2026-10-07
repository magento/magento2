<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Model;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface as ConfigWriter;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\MessageQueue\PoisonPill\PoisonPillPutInterface;
use Magento\Indexer\Model\NoDdlMode;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NoDdlModeTest extends TestCase
{
    private const TABLE_NAME = 'indexer_no_ddl_state';

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var ConfigWriter|MockObject
     */
    private $configWriterMock;

    /**
     * @var TypeListInterface|MockObject
     */
    private $cacheTypeListMock;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @var PoisonPillPutInterface|MockObject
     */
    private $poisonPillPutMock;

    /**
     * @var NoDdlMode
     */
    private $noDdlMode;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->configWriterMock = $this->createMock(ConfigWriter::class);
        $this->cacheTypeListMock = $this->createMock(TypeListInterface::class);
        $this->resourceConnectionMock = $this->createMock(ResourceConnection::class);
        $this->connectionMock = $this->createMock(AdapterInterface::class);
        $this->resourceConnectionMock->method('getConnection')->willReturn($this->connectionMock);
        $this->resourceConnectionMock->method('getTableName')->willReturnArgument(0);
        $this->poisonPillPutMock = $this->createMock(PoisonPillPutInterface::class);

        $this->noDdlMode = new NoDdlMode(
            $this->scopeConfigMock,
            $this->configWriterMock,
            $this->cacheTypeListMock,
            $this->resourceConnectionMock,
            $this->poisonPillPutMock
        );
    }

    /**
     * @return void
     */
    public function testIsEnabledReadsConfigPathForIndexerId(): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('indexer/no_ddl_reindex/sample_indexer_a')
            ->willReturn('1');

        $this->assertTrue($this->noDdlMode->isEnabled('sample_indexer_a'));
    }

    /**
     * @return void
     */
    public function testIsEnabledReturnsFalseWhenConfigDisabled(): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('indexer/no_ddl_reindex/sample_indexer_b')
            ->willReturn('0');

        $this->assertFalse($this->noDdlMode->isEnabled('sample_indexer_b'));
    }

    /**
     * @return void
     */
    public function testIsMainTableActiveDefaultsToTrueWhenNoStateRowExists(): void
    {
        $selectMock = $this->createMock(Select::class);
        $selectMock->method('from')->willReturnSelf();
        $selectMock->method('where')->willReturnSelf();
        $this->connectionMock->method('select')->willReturn($selectMock);
        $this->connectionMock->expects($this->once())->method('fetchOne')->willReturn(false);

        $this->assertTrue($this->noDdlMode->isMainTableActive('sample_indexer_a'));
    }

    /**
     * @return void
     */
    public function testIsMainTableActiveReturnsPersistedValueWhenRowExists(): void
    {
        $selectMock = $this->createMock(Select::class);
        $selectMock->method('from')->willReturnSelf();
        $selectMock->method('where')->willReturnSelf();
        $this->connectionMock->method('select')->willReturn($selectMock);
        $this->connectionMock->expects($this->once())->method('fetchOne')->willReturn('0');

        $this->assertFalse($this->noDdlMode->isMainTableActive('sample_indexer_a'));
    }

    /**
     * @return void
     */
    public function testIsMainTableActiveMemoizesAndDoesNotReQueryForSameIndexerId(): void
    {
        $selectMock = $this->createMock(Select::class);
        $selectMock->method('from')->willReturnSelf();
        $selectMock->method('where')->willReturnSelf();
        $this->connectionMock->method('select')->willReturn($selectMock);
        $this->connectionMock->expects($this->once())->method('fetchOne')->willReturn('1');

        $this->assertTrue($this->noDdlMode->isMainTableActive('sample_indexer_a'));
        $this->assertTrue($this->noDdlMode->isMainTableActive('sample_indexer_a'));
    }

    /**
     * @return void
     */
    public function testFlipActiveTableSeedsRowAndTogglesAtomically(): void
    {
        $this->connectionMock->expects($this->once())
            ->method('insertOnDuplicate')
            ->with(
                self::TABLE_NAME,
                [
                    'indexer_id' => 'sample_indexer_a',
                    'is_main_active' => false,
                ],
                $this->callback(function (array $fields) {
                    return isset($fields['is_main_active'])
                        && $fields['is_main_active'] instanceof \Zend_Db_Expr
                        && (string)$fields['is_main_active'] === 'NOT is_main_active';
                })
            );

        $this->poisonPillPutMock->expects($this->once())->method('put');

        $this->noDdlMode->flipActiveTable('sample_indexer_a');
    }

    /**
     * @return void
     */
    public function testFlipActiveTableInvalidatesCacheSoNextReadReQueriesDb(): void
    {
        $this->connectionMock->method('insertOnDuplicate');

        $selectMock = $this->createMock(Select::class);
        $selectMock->method('from')->willReturnSelf();
        $selectMock->method('where')->willReturnSelf();
        $this->connectionMock->method('select')->willReturn($selectMock);
        $this->connectionMock->expects($this->once())->method('fetchOne')->willReturn('0');

        $this->noDdlMode->flipActiveTable('sample_indexer_a');

        $this->assertFalse($this->noDdlMode->isMainTableActive('sample_indexer_a'));
    }

    /**
     * @return void
     */
    public function testFlipActiveTableInvalidatesCachePerIndexerIdIndependently(): void
    {
        $this->connectionMock->method('insertOnDuplicate');

        $selectMock = $this->createMock(Select::class);
        $selectMock->method('from')->willReturnSelf();
        $selectMock->method('where')->willReturnSelf();
        $this->connectionMock->method('select')->willReturn($selectMock);
        $this->connectionMock->expects($this->exactly(2))
            ->method('fetchOne')
            ->willReturn(false);

        $this->noDdlMode->flipActiveTable('sample_indexer_a');
        $this->noDdlMode->flipActiveTable('sample_indexer_b');

        $this->assertTrue($this->noDdlMode->isMainTableActive('sample_indexer_a'));
        $this->assertTrue($this->noDdlMode->isMainTableActive('sample_indexer_b'));
    }

    /**
     * @return void
     */
    public function testSetEnabledPersistsConfigAndCleansCache(): void
    {
        $this->configWriterMock->expects($this->once())
            ->method('saveConfig')
            ->with('indexer/no_ddl_reindex/sample_indexer_a', 1);
        $this->cacheTypeListMock->expects($this->once())->method('cleanType')->with('config');

        $this->noDdlMode->setEnabled('sample_indexer_a', true);
    }

    /**
     * @return void
     */
    public function testSetDisabledPersistsConfigAndCleansCache(): void
    {
        $this->configWriterMock->expects($this->once())
            ->method('saveConfig')
            ->with('indexer/no_ddl_reindex/sample_indexer_a', 0);
        $this->cacheTypeListMock->expects($this->once())->method('cleanType')->with('config');

        $this->noDdlMode->setEnabled('sample_indexer_a', false);
    }
}
