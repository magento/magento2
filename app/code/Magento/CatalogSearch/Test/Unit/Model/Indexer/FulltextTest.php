<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Test\Unit\Model\Indexer;

use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\Search\Request\Dimension;
use Magento\Framework\Search\Request\DimensionFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FulltextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogSearch\Model\Indexer\Fulltext
     */
    protected $model;

    /**
     * @var \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\Full|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fullAction;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\CatalogSearch\Model\Indexer\IndexerHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $saveHandler;

    /**
     * @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fulltextResource;

    /**
     * @var \Magento\Framework\Search\Request\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchRequestConfig;

    /**
     * @var \Magento\Framework\Search\Request\DimensionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dimensionFactory;

    /**
     * @var \Magento\CatalogSearch\Model\Indexer\Scope\IndexSwitcher|\PHPUnit_Framework_MockObject_MockObject
     */
    private $indexSwitcher;

    /**
     * Holder for MetadataPool mock instance.
     *
     * @var \Magento\Framework\EntityManager\MetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataPool;

    protected function setUp()
    {
        $this->fullAction = $this->getClassMock(\Magento\CatalogSearch\Model\Indexer\Fulltext\Action\Full::class);
        $fullActionFactory = $this->getMock(
            \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\FullFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $fullActionFactory->expects($this->any())->method('create')->willReturn($this->fullAction);
        $this->saveHandler = $this->getClassMock(\Magento\CatalogSearch\Model\Indexer\IndexerHandler::class);
        $indexerHandlerFactory = $this->getMock(
            \Magento\CatalogSearch\Model\Indexer\IndexerHandlerFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $indexerHandlerFactory->expects($this->any())->method('create')->willReturn($this->saveHandler);

        $this->storeManager = $this->getMockForAbstractClass(
            \Magento\Store\Model\StoreManagerInterface::class,
            [],
            '',
            false,
            false,
            true,
            []
        );

        $this->dimensionFactory = $this->getMock(DimensionFactory::class, ['create'], [], '', false);

        $this->fulltextResource = $this->getClassMock(\Magento\CatalogSearch\Model\ResourceModel\Fulltext::class);
        $this->searchRequestConfig = $this->getClassMock(\Magento\Framework\Search\Request\Config::class);

        $this->indexSwitcher = $this->getMockBuilder(\Magento\CatalogSearch\Model\Indexer\Scope\IndexSwitcher::class)
            ->disableOriginalConstructor()
            ->setMethods(['switchIndex'])
            ->getMock();

        $this->metadataPool = $this->getMockBuilder(\Magento\Framework\EntityManager\MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $objectManagerHelper->getObject(
            \Magento\CatalogSearch\Model\Indexer\Fulltext::class,
            [
                'fullActionFactory' => $fullActionFactory,
                'indexerHandlerFactory' => $indexerHandlerFactory,
                'storeManager' => $this->storeManager,
                'dimensionFactory' => $this->dimensionFactory,
                'fulltextResource' => $this->fulltextResource,
                'searchRequestConfig' => $this->searchRequestConfig,
                'data' => [],
                'indexSwitcher' => $this->indexSwitcher,
            ]
        );
        $objectManagerHelper->setBackwardCompatibleProperty($this->model, 'metadataPool', $this->metadataPool);
    }

    /**
     * @param string $className
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getClassMock($className)
    {
        return $this->getMock($className, [], [], '', false);
    }

    public function testExecute()
    {
        $ids = [1, 2, 3];
        $stores = [0 => 'Store 1', 1 => 'Store 2'];
        $indexData = new \ArrayObject([]);
        $this->storeManager->expects($this->once())->method('getStores')->willReturn($stores);
        $this->saveHandler->expects($this->exactly(count($stores)))->method('deleteIndex');
        $this->saveHandler->expects($this->exactly(2))->method('saveIndex');
        $this->fullAction->expects($this->exactly(2))
            ->method('rebuildStoreIndex')
            ->willReturn(new \ArrayObject([$indexData, $indexData]));
        $this->mockGetRelationsByChild($ids);

        $this->model->execute($ids);
    }

    public function testExecuteFull()
    {
        $stores = [0 => 'Store 1', 1 => 'Store 2'];
        $indexData = new \ArrayObject([new \ArrayObject([]), new \ArrayObject([])]);
        $this->storeManager->expects($this->once())->method('getStores')->willReturn($stores);

        $dimensionScope1 = $this->getMock(Dimension::class, [], ['scope', '1']);
        $dimensionScope2 = $this->getMock(Dimension::class, [], ['scope', '2']);

        $this->dimensionFactory->expects($this->any())->method('create')->willReturnOnConsecutiveCalls(
            $dimensionScope1,
            $dimensionScope2
        );
        $this->indexSwitcher->expects($this->exactly(2))->method('switchIndex')
            ->withConsecutive(
                [$this->equalTo([$dimensionScope1])],
                [$this->equalTo([$dimensionScope2])]
            );

        $this->saveHandler->expects($this->exactly(count($stores)))->method('cleanIndex')
            ->withConsecutive(
                [$this->equalTo([$dimensionScope1])],
                [$this->equalTo([$dimensionScope2])]
            );

        $this->saveHandler->expects($this->exactly(2))->method('saveIndex')
            ->withConsecutive(
                [$this->equalTo([$dimensionScope1]), $this->equalTo($indexData)],
                [$this->equalTo([$dimensionScope2]), $this->equalTo($indexData)]
            );
        $this->fullAction->expects($this->exactly(2))
            ->method('rebuildStoreIndex')
            ->withConsecutive([0], [1])
            ->willReturn($indexData);

        $this->fulltextResource->expects($this->once())->method('resetSearchResults');
        $this->searchRequestConfig->expects($this->once())->method('reset');

        $this->model->executeFull();
    }

    public function testExecuteList()
    {
        $ids = [1, 2, 3];
        $stores = [0 => 'Store 1', 1 => 'Store 2'];
        $indexData = new \ArrayObject([]);
        $this->storeManager->expects($this->once())->method('getStores')->willReturn($stores);
        $this->saveHandler->expects($this->exactly(count($stores)))->method('deleteIndex');
        $this->saveHandler->expects($this->exactly(2))->method('saveIndex');
        $this->fullAction->expects($this->exactly(2))
            ->method('rebuildStoreIndex')
            ->willReturn(new \ArrayObject([$indexData, $indexData]));
        $this->mockGetRelationsByChild($ids);

        $this->model->executeList($ids);
    }

    public function testExecuteRow()
    {
        $id = 1;
        $stores = [0 => 'Store 1', 1 => 'Store 2'];
        $indexData = new \ArrayObject([]);
        $this->storeManager->expects($this->once())->method('getStores')->willReturn($stores);
        $this->saveHandler->expects($this->exactly(count($stores)))->method('deleteIndex');
        $this->saveHandler->expects($this->exactly(2))->method('saveIndex');
        $this->fullAction->expects($this->exactly(2))
            ->method('rebuildStoreIndex')
            ->willReturn(new \ArrayObject([$indexData, $indexData]));
        $this->mockGetRelationsByChild([$id]);

        $this->model->executeRow($id);
    }

    /**
     * Mock getRelationsByChild() method.
     *
     * @param array $ids
     * @return void
     */
    private function mockGetRelationsByChild(array $ids)
    {
        $testTable1 = 'testTable1';
        $testTable2 = 'testTable2';
        $fieldForParent = 'testLinkField';

        $metadata = $this->getMockBuilder(EntityMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $metadata->expects($this->exactly(2))
            ->method('getLinkField')
            ->willReturn($fieldForParent);

        $select = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $select->expects($this->exactly(2))
            ->method('from')
            ->with(['relation' => $testTable1])
            ->willReturnSelf();
        $select->expects($this->exactly(2))
            ->method('distinct')
            ->with(true)
            ->willReturnSelf();
        $select->expects($this->exactly(2))
            ->method('where')
            ->with('relation.child_id IN (?)', $ids)
            ->willReturnSelf();
        $select->expects($this->exactly(2))
            ->method('join')
            ->with(
                ['cpe' => $testTable2],
                'cpe.' . $fieldForParent . ' = relation.parent_id',
                ['cpe.entity_id']
            )->willReturnSelf();

        $connection = $this->getMockBuilder(Mysql::class)
            ->disableOriginalConstructor()
            ->getMock();
        $connection->expects($this->exactly(2))
            ->method('select')
            ->willReturn($select);
        $connection->expects($this->exactly(2))
            ->method('fetchCol')
            ->with($select)
            ->willReturn($ids);

        $this->fulltextResource->expects($this->exactly(2))
            ->method('getConnection')
            ->willReturn($connection);
        $this->fulltextResource->expects($this->exactly(4))
            ->method('getTable')
            ->withConsecutive(
                ['catalog_product_relation'],
                ['catalog_product_entity'],
                ['catalog_product_relation'],
                ['catalog_product_entity']
            )
            ->will($this->onConsecutiveCalls(
                $testTable1,
                $testTable2,
                $testTable1,
                $testTable2
            ));
        $this->metadataPool->expects($this->exactly(2))
            ->method('getMetadata')
            ->with(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->willReturn($metadata);
    }
}
