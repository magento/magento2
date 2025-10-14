<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Search\Test\Unit\Model\ResourceModel\Query;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Search\Model\ResourceModel\Query\Collection;
use Magento\Framework\DB\Helper;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class CollectionTest extends TestCase
{
    /**
     * @var StoreManagerInterface|MockObject
     */
    private StoreManagerInterface $_storeManager;

    /**
     * @var Helper|MockObject
     */
    private Helper $_resourceHelper;

    /**
     * @var EntityFactoryInterface
     */
    private EntityFactoryInterface $entityFactory;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var FetchStrategyInterface
     */
    private FetchStrategyInterface $fetchStrategy;

    /**
     * @var ManagerInterface
     */
    private ManagerInterface $eventManager;

    /**
     * @var AdapterInterface
     */
    private AdapterInterface $connection;

    /**
     * @var AbstractDb
     */
    private AbstractDb $resource;

    /**
     * @inheritDoc
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->_storeManager = $this->createMock(StoreManagerInterface::class);
        $this->_resourceHelper = $this->createMock(Helper::class);
        $this->entityFactory = $this->createMock(EntityFactoryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->fetchStrategy = $this->createMock(FetchStrategyInterface::class);
        $this->eventManager = $this->createMock(ManagerInterface::class);
        $this->connection = $this->createMock(AdapterInterface::class);
        $this->resource = $this->createMock(AbstractDb::class);
    }

    /**
     * @return void
     */
    public function testIsTopSearchResult(): void
    {
        $term = 'test';
        $storeId = 1;
        $maxCountCacheableSearchTerms = 10;

        $this->resource->expects($this->once())->method('getConnection')->willReturn($this->connection);
        $select = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $select->expects($this->exactly(7))->method('reset')->willReturnSelf();
        $select->expects($this->exactly(3))->method('from')->willReturnSelf();
        $select->expects($this->exactly(3))->method('where')->willReturnSelf();
        $select->expects($this->once())->method('order')->with(['main_table.popularity desc'])->willReturnSelf();
        $select->expects($this->once())->method('limit')->with($maxCountCacheableSearchTerms)->willReturnSelf();
        $select->expects($this->once())->method('assemble')->willReturn(
            "SELECT COUNT(*) FROM (SELECT DISTINCT  `main_table`.`query_text` FROM `search_query` AS `main_table`" .
            " WHERE (main_table.store_id IN (1)) AND (main_table.num_results > 0) " .
            " ORDER BY `main_table`.`popularity` desc LIMIT {$maxCountCacheableSearchTerms}) AS `result`
            WHERE (result.query_text = '{$term}')"
        );
        $select->expects($this->never())->method('distinct');
        $this->connection->expects($this->any())->method('select')->willReturn($select);

        $collection = new Collection(
            $this->entityFactory,
            $this->logger,
            $this->fetchStrategy,
            $this->eventManager,
            $this->_storeManager,
            $this->_resourceHelper,
            $this->connection,
            $this->resource
        );
        $collection->isTopSearchResult($term, $storeId, $maxCountCacheableSearchTerms);
    }
}
