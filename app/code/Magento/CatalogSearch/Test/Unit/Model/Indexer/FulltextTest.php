<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Test\Unit\Model\Indexer;

use \Magento\Framework\Indexer\Dimension;
use Magento\Framework\Indexer\DimensionProviderInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FulltextTest extends \PHPUnit\Framework\TestCase
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
     * @var \Magento\CatalogSearch\Model\Indexer\IndexerHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $saveHandler;

    /**
     * @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fulltextResource;

    /**
     * @var \Magento\CatalogSearch\Model\Indexer\Scope\IndexSwitcher|\PHPUnit_Framework_MockObject_MockObject
     */
    private $indexSwitcher;

    /**
     * @var DimensionProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dimensionProviderMock;

    /**
     * @var \Magento\Indexer\Model\ProcessManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $processManager;

    protected function setUp()
    {
        $this->fullAction = $this->getClassMock(\Magento\CatalogSearch\Model\Indexer\Fulltext\Action\Full::class);
        $fullActionFactory = $this->createPartialMock(
            \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\FullFactory::class,
            ['create']
        );
        $fullActionFactory->expects($this->any())->method('create')->willReturn($this->fullAction);
        $this->saveHandler = $this->getClassMock(\Magento\CatalogSearch\Model\Indexer\IndexerHandler::class);
        $indexerHandlerFactory = $this->createPartialMock(
            \Magento\CatalogSearch\Model\Indexer\IndexerHandlerFactory::class,
            ['create']
        );
        $indexerHandlerFactory->expects($this->any())->method('create')->willReturn($this->saveHandler);

        $this->fulltextResource = $this->getClassMock(\Magento\CatalogSearch\Model\ResourceModel\Fulltext::class);

        $this->indexSwitcher = $this->getMockBuilder(\Magento\CatalogSearch\Model\Indexer\Scope\IndexSwitcher::class)
            ->disableOriginalConstructor()
            ->setMethods(['switchIndex'])
            ->getMock();

        $this->dimensionProviderMock = $this->getMockBuilder(DimensionProviderInterface::class)->getMock();
        $stateMock = $this->getMockBuilder(\Magento\CatalogSearch\Model\Indexer\Scope\State::class)
            ->getMock();
        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->processManager = new \Magento\Indexer\Model\ProcessManager(
            $this->getClassMock(\Magento\Framework\App\ResourceConnection::class)
        );

        $this->model = $objectManagerHelper->getObject(
            \Magento\CatalogSearch\Model\Indexer\Fulltext::class,
            [
                'fullActionFactory' => $fullActionFactory,
                'indexerHandlerFactory' => $indexerHandlerFactory,
                'fulltextResource' => $this->fulltextResource,
                'data' => [],
                'indexSwitcher' => $this->indexSwitcher,
                'dimensionProvider' => $this->dimensionProviderMock,
                'indexScopeState' => $stateMock,
                'processManager' => $this->processManager,
            ]
        );
    }

    /**
     * @param string $className
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getClassMock($className)
    {
        return $this->createMock($className);
    }

    public function testExecute()
    {
        $ids = [1, 2, 3];
        $stores = [0 => 'Store 1', 1 => 'Store 2'];
        $this->setupDataProvider($stores);

        $indexData = new \ArrayObject([]);
        $this->fulltextResource->expects($this->exactly(2))
            ->method('getRelationsByChild')
            ->willReturn($ids);
        $this->saveHandler->expects($this->exactly(count($stores)))->method('deleteIndex');
        $this->saveHandler->expects($this->exactly(2))->method('saveIndex');
        $this->saveHandler->expects($this->exactly(2))->method('isAvailable')->willReturn(true);
        $consecutiveStoreRebuildArguments = array_map(
            function ($store) use ($ids) {
                return [$store, $ids];
            },
            $stores
        );
        $this->fullAction->expects($this->exactly(2))
            ->method('rebuildStoreIndex')
            ->withConsecutive(...$consecutiveStoreRebuildArguments)
            ->willReturn(new \ArrayObject([$indexData, $indexData]));

        $this->model->execute($ids);
    }

    /**
     * @param $stores
     */
    private function setupDataProvider($stores)
    {
        $this->dimensionProviderMock->expects($this->once())->method('getIterator')->willReturn(
            (function () use ($stores) {
                foreach ($stores as $storeId) {
                    $dimension = $this->getMockBuilder(Dimension::class)->disableOriginalConstructor()->getMock();
                    $dimension->expects($this->once())
                        ->method('getValue')
                        ->willReturn($storeId);

                    yield ['scope' => $dimension];
                }
            })()
        );
    }

    public function testExecuteFull()
    {
        $stores = [0 => 'Store 1', 1 => 'Store 2'];
        $indexData = new \ArrayObject([new \ArrayObject([]), new \ArrayObject([])]);
        $this->setupDataProvider($stores);

        $this->indexSwitcher->expects($this->exactly(2))->method('switchIndex');

        $this->saveHandler->expects($this->exactly(count($stores)))->method('cleanIndex');
        $this->saveHandler->expects($this->exactly(2))->method('saveIndex');
        $consecutiveStoreRebuildArguments = array_map(
            function ($store) {
                return [$store];
            },
            $stores
        );
        $this->fullAction->expects($this->exactly(2))
            ->method('rebuildStoreIndex')
            ->withConsecutive(...$consecutiveStoreRebuildArguments)
            ->willReturn($indexData);

        $this->fulltextResource->expects($this->exactly(2))->method('resetSearchResultsByStore');

        $this->model->executeFull();
    }

    public function testExecuteList()
    {
        $ids = [1, 2, 3];
        $stores = [0 => 'Store 1', 1 => 'Store 2'];
        $this->setupDataProvider($stores);
        $indexData = new \ArrayObject([]);
        $this->fulltextResource->expects($this->exactly(2))
            ->method('getRelationsByChild')
            ->willReturn($ids);
        $this->saveHandler->expects($this->exactly(count($stores)))->method('deleteIndex');
        $this->saveHandler->expects($this->exactly(2))->method('saveIndex');
        $this->saveHandler->expects($this->exactly(2))->method('isAvailable')->willReturn(true);
        $this->fullAction->expects($this->exactly(2))
            ->method('rebuildStoreIndex')
            ->willReturn(new \ArrayObject([$indexData, $indexData]));

        $this->model->executeList($ids);
    }

    public function testExecuteRow()
    {
        $id = 1;
        $stores = [0 => 'Store 1', 1 => 'Store 2'];
        $this->setupDataProvider($stores);
        $indexData = new \ArrayObject([]);
        $this->fulltextResource->expects($this->exactly(2))
            ->method('getRelationsByChild')
            ->willReturn([$id]);
        $this->saveHandler->expects($this->exactly(count($stores)))->method('deleteIndex');
        $this->saveHandler->expects($this->exactly(2))->method('saveIndex');
        $this->saveHandler->expects($this->exactly(2))->method('isAvailable')->willReturn(true);
        $this->fullAction->expects($this->exactly(2))
            ->method('rebuildStoreIndex')
            ->willReturn(new \ArrayObject([$indexData, $indexData]));

        $this->model->executeRow($id);
    }
}
