<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test design config indexer model
 */
namespace Magento\Theme\Test\Unit\Model\Indexer\Design;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection;
use Magento\Framework\Indexer\FieldsetInterface;
use Magento\Framework\Indexer\FieldsetPool;
use Magento\Framework\Indexer\HandlerInterface;
use Magento\Framework\Indexer\HandlerPool;
use Magento\Framework\Indexer\IndexStructureInterface;
use Magento\Framework\Indexer\SaveHandler\Batch;
use Magento\Framework\Indexer\SaveHandlerFactory;
use Magento\Framework\Indexer\ScopeResolver\FlatScopeResolver;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;
use Magento\Framework\Indexer\StructureFactory;
use Magento\Theme\Model\Data\Design\Config as DesignConfig;
use Magento\Theme\Model\Indexer\Design\Config;
use Magento\Theme\Model\ResourceModel\Design\Config\Scope\CollectionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Theme\Model\Indexer\Design\IndexerHandler;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigTest extends TestCase
{
    /**
     * @var AdapterInterface|MockObject
     */
    private $adapter;
    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceConnection;
    /**
     * @var Batch|MockObject
     */
    private $batch;
    /**
     * @var IndexStructureInterface|MockObject
     */
    private $indexerStructure;
    /**
     * @var IndexScopeResolver|MockObject
     */
    private $indexScopeResolver;
    /**
     * @var FlatScopeResolver|MockObject
     */
    private $flatScopeResolver;
    /**
     * @var SaveHandlerFactory|MockObject
     */
    private $saveHandlerFactory;
    /**
     * @var StructureFactory|MockObject
     */
    private $structureFactory;
    /**
     * @var FieldsetInterface|MockObject
     */
    private $indexerFieldset;
    /**
     * @var FieldsetPool|MockObject
     */
    private $fieldsetPool;
    /**
     * @var HandlerInterface|MockObject
     */
    private $indexerHandler;
    /**
     * @var HandlerPool|MockObject
     */
    private $handlerPool;
    /**
     * @var Collection|MockObject
     */
    private $collection;
    /**
     * @var CollectionFactory|MockObject
     */
    private $collectionFactory;

    protected function setUp(): void
    {
        $this->indexerStructure = $this->getMockBuilder(IndexStructureInterface::class)
            ->getMockForAbstractClass();
        $this->structureFactory = $this->getMockBuilder(StructureFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceConnection = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->adapter = $this->getMockBuilder(AdapterInterface::class)
            ->getMockForAbstractClass();
        $this->batch = $this->getMockBuilder(Batch::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->indexScopeResolver = $this->getMockBuilder(IndexScopeResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->flatScopeResolver = $this->getMockBuilder(FlatScopeResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->saveHandlerFactory = $this->getMockBuilder(SaveHandlerFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fieldsetPool = $this->getMockBuilder(FieldsetPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->collectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->indexerHandler = $this->getMockBuilder(HandlerInterface::class)
            ->getMockForAbstractClass();
        $this->handlerPool = $this->getMockBuilder(HandlerPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->indexerFieldset = $this->getMockBuilder(FieldsetInterface::class)
            ->getMockForAbstractClass();
    }

    /**
     * Generate flat index table name from design config grid index ID
     *
     * @return string
     */
    private function getFlatIndexTableName(): string
    {
        return DesignConfig::DESIGN_CONFIG_GRID_INDEXER_ID . '_flat';
    }

    /**
     * Initialize and return Design Config Indexer Model
     *
     * @return Config
     */
    private function getDesignConfigIndexerModel(): Config
    {
        $this->structureFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->indexerStructure);
        $this->resourceConnection
            ->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->adapter);
        $this->flatScopeResolver->expects($this->any())
            ->method('resolve')
            ->willReturn($this->getFlatIndexTableName());

        $indexer = new IndexerHandler(
            $this->indexerStructure,
            $this->resourceConnection,
            $this->batch,
            $this->indexScopeResolver,
            $this->flatScopeResolver,
            [
                'fieldsets' => [],
                'indexer_id' => DesignConfig::DESIGN_CONFIG_GRID_INDEXER_ID
            ]
        );

        $this->saveHandlerFactory->expects($this->any())
            ->method('create')
            ->willReturn($indexer);

        $this->indexerFieldset->expects($this->any())
            ->method('addDynamicData')
            ->willReturnArgument(0);

        $this->fieldsetPool->expects($this->any())
            ->method('get')
            ->willReturn($this->indexerFieldset);

        $this->handlerPool->expects($this->any())
            ->method('get')
            ->willReturn($this->indexerHandler);

        $this->collectionFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->collection);

        return new Config(
            $this->structureFactory,
            $this->saveHandlerFactory,
            $this->fieldsetPool,
            $this->handlerPool,
            $this->collectionFactory,
            [
                'fieldsets' => ['test_fieldset' => [
                    'fields' => [
                        'first_field' => [
                            'name' => 'firstField',
                            'origin' => null,
                            'type' => 'filterable',
                            'handler' => null,
                        ],
                        'second_field' => [
                            'name' => 'secondField',
                            'origin' => null,
                            'type' => 'searchable',
                            'handler' => null,
                        ],
                    ],
                    'provider' => $this->indexerFieldset,
                ]
                ],
                'saveHandler' => 'saveHandlerClass',
                'structure' => 'structureClass',
            ]
        );
    }

    public function testFullReindex()
    {
        $this->adapter->expects($this->any())
            ->method('isTableExists')
            ->willReturn(true);
        $this->indexerStructure->expects($this->never())->method('create')
            ->with(DesignConfig::DESIGN_CONFIG_GRID_INDEXER_ID);
        $this->adapter->expects($this->once())->method('delete')
            ->with($this->getFlatIndexTableName());
        $this->batch->expects($this->any())
            ->method('getItems')->willReturn([]);

        $this->getDesignConfigIndexerModel()->executeFull();
    }

    public function testFullReindexWithFlatTableCreate()
    {
        $this->adapter->expects($this->any())->method('isTableExists')
            ->willReturn(false);
        $this->indexerStructure->expects($this->once())->method('create')
            ->with(DesignConfig::DESIGN_CONFIG_GRID_INDEXER_ID);
        $this->adapter->expects($this->never())->method('delete')
            ->with($this->getFlatIndexTableName());
        $this->batch->expects($this->any())->method('getItems')
            ->willReturn([]);

        $this->getDesignConfigIndexerModel()->executeFull();
    }

    public function testPartialReindex()
    {
        $this->adapter->expects($this->any())->method('isTableExists')
            ->willReturn(true);
        $this->indexerStructure->expects($this->never())->method('create')
            ->with(DesignConfig::DESIGN_CONFIG_GRID_INDEXER_ID);
        $this->adapter->expects($this->once())->method('delete')
            ->with($this->getFlatIndexTableName(), ['entity_id IN(?)' => [1, 2, 3]]);
        $this->batch->expects($this->any())->method('getItems')
            ->willReturn([[1, 2, 3]]);

        $this->getDesignConfigIndexerModel()->executeList([1, 2, 3]);
    }
}
