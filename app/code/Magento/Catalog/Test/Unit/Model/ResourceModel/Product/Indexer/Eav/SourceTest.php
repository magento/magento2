<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product\Indexer\Eav;

use Magento\Catalog\Model\ResourceModel\Helper;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\Source;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\Config;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Indexer\Table\StrategyInterface;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\DB\Select;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SourceTest extends TestCase
{
    /**
     * @var Context|MockObject
     */
    private Context $context;

    /**
     * @var StrategyInterface|MockObject
     */
    private StrategyInterface $tableStrategy;

    /**
     * @var Config|MockObject
     */
    private Config $eavConfig;

    /**
     * @var ManagerInterface|MockObject
     */
    private ManagerInterface $eventManager;

    /**
     * @var Helper|MockObject
     */
    private Helper $resourceHelper;

    /**
     * @var AttributeRepositoryInterface|MockObject
     */
    private AttributeRepositoryInterface $attributeRepository;

    /**
     * @var SearchCriteriaBuilder|MockObject
     */
    private SearchCriteriaBuilder $criteriaBuilder;

    /**
     * @var MetadataPool|MockObject
     */
    private MetadataPool $metadataPool;

    /**
     * @var Source
     */
    private Source $indexer;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->context = $this->createMock(Context::class);
        $this->tableStrategy = $this->createMock(StrategyInterface::class);
        $this->eavConfig = $this->createMock(Config::class);
        $this->eventManager = $this->createMock(ManagerInterface::class);
        $this->resourceHelper = $this->createMock(Helper::class);
        $this->attributeRepository = $this->createMock(AttributeRepositoryInterface::class);
        $this->criteriaBuilder = $this->createMock(SearchCriteriaBuilder::class);
        $this->metadataPool = $this->createMock(MetadataPool::class);

        parent::setUp();
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testReindexEntities(): void
    {
        $products = [1, 2];
        $select = $this->createPartialMock(
            Select::class,
            ['from', 'join', 'where', 'joinLeft', 'group', 'columns']
        );
        $select->expects($this->any())->method('from')->willReturn($select);
        $select->expects($this->any())->method('join')->willReturn($select);
        $select->expects($this->any())->method('where')->willReturn($select);
        $select->expects($this->any())->method('joinLeft')->willReturn($select);
        $select->expects($this->any())->method('group')->willReturn($select);
        $select->expects($this->any())->method('columns')->willReturn($select);
        $connection = $this->createMock(AdapterInterface::class);
        $connection->expects($this->once())->method('delete');
        $connection->expects($this->any())->method('select')->willReturn($select);
        $resources = $this->createMock(ResourceConnection::class);
        $resources->expects($this->any())
            ->method('getConnection')
            ->with('test_connection_name')
            ->willReturn($connection);
        $this->context->expects($this->any())->method('getResources')->willReturn($resources);
        $this->tableStrategy->expects($this->any())->method('getTableName')->willReturn('idx_table');
        $this->tableStrategy->expects($this->any())->method('getUseIdxTable')->willReturn(true);
        $metadata = $this->createMock(EntityMetadataInterface::class);
        $this->metadataPool->expects($this->any())->method('getMetadata')->willReturn($metadata);

        $this->indexer = new Source(
            $this->context,
            $this->tableStrategy,
            $this->eavConfig,
            $this->eventManager,
            $this->resourceHelper,
            'test_connection_name',
            $this->attributeRepository,
            $this->criteriaBuilder,
            $this->metadataPool
        );
        $this->indexer->reindexEntities($products);
    }
}
