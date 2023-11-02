<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Model\Indexer\Fulltext\Plugin\Category\Product\Action;

use Magento\Catalog\Model\Indexer\Category\Product\TableMaintainer;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Elasticsearch\Model\Indexer\Fulltext\Plugin\Category\Product\Action\Rows;
use Magento\Catalog\Model\Indexer\Category\Product\Action\Rows as ActionRows;
use Magento\Framework\App\ResourceConnection;
use Magento\CatalogSearch\Model\Indexer\Fulltext as FulltextIndexer;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RowsTest extends TestCase
{
    /**
     * @var IndexerRegistry|MockObject
     */
    private $indexerRegistryMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var Select|MockObject
     */
    private $selectMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceMock;

    /**
     * @var TableMaintainer|MockObject
     */
    private $tableMaintainerMock;

    /**
     * @var Rows
     */
    private $plugin;
    public function setUp(): void
    {
        parent::setUp();
        $this->indexerRegistryMock = $this->createMock(IndexerRegistry::class);
        $this->storeManagerMock =
            $this->getMockBuilder(StoreManagerInterface::class)->getMockForAbstractClass();
        $this->connectionMock =
            $this->getMockBuilder(AdapterInterface::class)->getMockForAbstractClass();
        $this->selectMock = $this->createMock(Select::class);
        $this->connectionMock->expects($this->any())->method('select')->willReturn($this->selectMock);
        $this->tableMaintainerMock = $this->createMock(TableMaintainer::class);
        $this->resourceMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->plugin = new Rows(
            $this->indexerRegistryMock,
            $this->storeManagerMock,
            $this->resourceMock,
            $this->tableMaintainerMock
        );
    }

    /**
     * Test afterExecute method.
     *
     * @return void
     */
    public function testAfterExecute(): void
    {
        $productToReindex = [1];
        $storeId = 1;
        $categoryIds = [4];
        $actionMock = $this->createMock(ActionRows::class);
        $storeMock = $this->createMock(Store::class);
        $storeMock->expects($this->once())->method('getId')->willReturn($storeId);
        $this->storeManagerMock->expects($this->once())->method('getStores')->willReturn([$storeMock]);
        $this->tableMaintainerMock->expects($this->once())->method('getMainTable')->with($storeId)->willReturn('table');

        $this->getProductIdsFromIndex($productToReindex);
        $this->createIndexerMock($productToReindex);

        $this->plugin->afterExecute($actionMock, $actionMock, $categoryIds);
    }

    /**
     * Creates a mock for the indexer registry to add given ids to changelog.
     *
     * @param array $ids
     * @return void
     */
    private function createIndexerMock(array $ids): void
    {
        //schedule catalogsearch indexer changes to improve row index performance instead of executing them right away
        $changelogMock = $this->createMock(\Magento\Framework\Mview\View\Changelog::class);
        $changelogMock->expects($this->once())->method('addList')->with($ids);
        $viewMock = $this->createMock(\Magento\Framework\Mview\ViewInterface::class);
        $viewMock->expects($this->once())->method('getChangelog')->willReturn($changelogMock);

        $indexerMock = $this->createMock(\Magento\Framework\Indexer\IndexerInterface::class);
        $indexerMock->expects($this->once())->method('isScheduled')->willReturn(true);
        $indexerMock->expects($this->once())->method('getView')->willReturn($viewMock);

        $this->indexerRegistryMock->expects($this->once())
            ->method('get')
            ->with(FulltextIndexer::INDEXER_ID)
            ->willReturn($indexerMock);
    }

    /**
     * Mocks the connection to return the given ids.
     *
     * @param array $ids
     * @return void
     */
    private function getProductIdsFromIndex(array $ids): void
    {
        $this->selectMock->expects($this->any())->method('from')->with()->willReturnSelf();
        $this->selectMock->expects($this->any())->method('where')->willReturnSelf();
        $this->connectionMock->expects($this->any())->method('fetchCol')->willReturn($ids);
    }
}
