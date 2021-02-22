<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Test\Unit\Model\Indexer\Scope;

use Magento\CatalogSearch\Model\Indexer\Scope\IndexSwitcher;
use Magento\CatalogSearch\Model\Indexer\Scope\State;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;
use Magento\Framework\Indexer\IndexStructureInterface;
use Magento\Framework\Search\Request\Dimension;
use Magento\CatalogSearch\Model\Indexer\Fulltext as FulltextIndexer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class IndexSwitcherTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $connection;

    /**
     * @var \Magento\Framework\Search\Request\IndexScopeResolverInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $indexScopeResolver;

    /**
     * @var State|\PHPUnit\Framework\MockObject\MockObject
     */
    private $scopeState;

    /**
     * @var IndexSwitcher
     */
    private $indexSwitcher;

    /**
     * @var ResourceConnection|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resource;

    protected function setUp(): void
    {
        $this->resource = $this->getMockBuilder(ResourceConnection::class)
            ->setMethods(['getConnection'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->connection = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->setMethods(['isTableExists', 'dropTable', 'renameTable'])
            ->getMockForAbstractClass();
        $this->resource->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connection);
        $this->indexScopeResolver = $this->getMockBuilder(
            \Magento\Framework\Search\Request\IndexScopeResolverInterface::class
        )
            ->setMethods(['resolve'])
            ->getMockForAbstractClass();
        $this->scopeState = $this->getMockBuilder(State::class)
            ->setMethods(['getState', 'useRegularIndex', 'useTemporaryIndex'])
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->indexSwitcher = $objectManagerHelper->getObject(
            \Magento\CatalogSearch\Model\Indexer\Scope\IndexSwitcher::class,
            [
                'resource' => $this->resource,
                'indexScopeResolver' => $this->indexScopeResolver,
                'state' => $this->scopeState,
            ]
        );
    }

    public function testSwitchRegularIndex()
    {
        $dimensions = [$this->getMockBuilder(Dimension::class)->setConstructorArgs(['scope', '1'])];

        $this->scopeState->expects($this->once())
            ->method('getState')
            ->willReturn(State::USE_REGULAR_INDEX);

        $this->indexScopeResolver->expects($this->never())->method('resolve');
        $this->connection->expects($this->never())->method('renameTable');

        $this->indexSwitcher->switchIndex($dimensions);
    }

    public function testSwitchIndexWithUnknownState()
    {
        $dimensions = [$this->getMockBuilder(Dimension::class)->setConstructorArgs(['scope', '1'])];

        $this->scopeState->expects($this->once())
            ->method('getState')
            ->willReturn('unknown_state');

        $this->indexScopeResolver->expects($this->never())->method('resolve');
        $this->connection->expects($this->never())->method('renameTable');

        $this->indexSwitcher->switchIndex($dimensions);
    }

    public function testSwitchTemporaryIndexWhenRegularIndexExist()
    {
        $dimensions = [$this->getMockBuilder(Dimension::class)->setConstructorArgs(['scope', '1'])];

        $this->scopeState->expects($this->once())
            ->method('getState')
            ->willReturn(State::USE_TEMPORARY_INDEX);

        $this->scopeState->expects($this->at(1))->method('useRegularIndex');
        $this->scopeState->expects($this->at(2))->method('useTemporaryIndex');

        $this->indexScopeResolver->expects($this->exactly(2))->method('resolve')
            ->withConsecutive(
                [$this->equalTo(FulltextIndexer::INDEXER_ID), $this->equalTo($dimensions)],
                [$this->equalTo(FulltextIndexer::INDEXER_ID), $this->equalTo($dimensions)]
            )
            ->willReturnOnConsecutiveCalls(
                'catalogsearch_fulltext_scope1_tmp1',
                'catalogsearch_fulltext_scope1'
            );

        $this->connection->expects($this->exactly(2))->method('isTableExists')
            ->withConsecutive(
                [$this->equalTo('catalogsearch_fulltext_scope1_tmp1'), $this->equalTo(null)],
                [$this->equalTo('catalogsearch_fulltext_scope1'), $this->equalTo(null)]
            )
            ->willReturnOnConsecutiveCalls(
                true,
                true
            );

        $this->connection->expects($this->once())->method('dropTable')->with('catalogsearch_fulltext_scope1', null);
        $this->connection->expects($this->once())
            ->method('renameTable')
            ->with('catalogsearch_fulltext_scope1_tmp1', 'catalogsearch_fulltext_scope1');

        $this->indexSwitcher->switchIndex($dimensions);
    }

    public function testSwitchTemporaryIndexWhenRegularIndexNotExist()
    {
        $dimensions = [$this->getMockBuilder(Dimension::class)->setConstructorArgs(['scope', '1'])];

        $this->scopeState->expects($this->once())
            ->method('getState')
            ->willReturn(State::USE_TEMPORARY_INDEX);

        $this->scopeState->expects($this->at(1))->method('useRegularIndex');
        $this->scopeState->expects($this->at(2))->method('useTemporaryIndex');

        $this->indexScopeResolver->expects($this->exactly(2))->method('resolve')
            ->withConsecutive(
                [$this->equalTo(FulltextIndexer::INDEXER_ID), $this->equalTo($dimensions)],
                [$this->equalTo(FulltextIndexer::INDEXER_ID), $this->equalTo($dimensions)]
            )
            ->willReturnOnConsecutiveCalls(
                'catalogsearch_fulltext_scope1_tmp1',
                'catalogsearch_fulltext_scope1'
            );

        $this->connection->expects($this->exactly(2))->method('isTableExists')
            ->withConsecutive(
                [$this->equalTo('catalogsearch_fulltext_scope1_tmp1'), $this->equalTo(null)],
                [$this->equalTo('catalogsearch_fulltext_scope1'), $this->equalTo(null)]
            )
            ->willReturnOnConsecutiveCalls(
                true,
                false
            );

        $this->connection->expects($this->never())->method('dropTable')->with('catalogsearch_fulltext_scope1', null);
        $this->connection->expects($this->once())
            ->method('renameTable')
            ->with('catalogsearch_fulltext_scope1_tmp1', 'catalogsearch_fulltext_scope1');

        $this->indexSwitcher->switchIndex($dimensions);
    }

    /**
     */
    public function testSwitchWhenTemporaryIndexNotExist()
    {
        $this->expectException(\Magento\CatalogSearch\Model\Indexer\Scope\IndexTableNotExistException::class);
        $this->expectExceptionMessage('Temporary table for index catalogsearch_fulltext doesn\'t exist');

        $dimensions = [$this->getMockBuilder(Dimension::class)->setConstructorArgs(['scope', '1'])];

        $this->scopeState->expects($this->once())
            ->method('getState')
            ->willReturn(State::USE_TEMPORARY_INDEX);

        $this->scopeState->expects($this->never())->method('useRegularIndex');
        $this->scopeState->expects($this->never())->method('useTemporaryIndex');

        $this->indexScopeResolver->expects($this->once())->method('resolve')
            ->with(FulltextIndexer::INDEXER_ID, $dimensions)
            ->willReturn('catalogsearch_fulltext_scope1_tmp1');

        $this->connection->expects($this->once())
            ->method('isTableExists')
            ->with('catalogsearch_fulltext_scope1_tmp1', null)
            ->willReturn(false);

        $this->connection->expects($this->never())->method('dropTable')->with('catalogsearch_fulltext_scope1', null);
        $this->connection->expects($this->never())->method('renameTable');

        $this->indexSwitcher->switchIndex($dimensions);
    }
}
