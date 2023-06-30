<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Model\Indexer;

use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\Full;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\FullFactory;
use Magento\Framework\Indexer\SaveHandler\IndexerInterface;
use Magento\CatalogSearch\Model\Indexer\IndexerHandlerFactory;
use Magento\CatalogSearch\Model\Indexer\Scope\State;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\Dimension;
use Magento\Framework\Indexer\DimensionProviderInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Indexer\Model\ProcessManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FulltextTest extends TestCase
{
    /**
     * @var Fulltext
     */
    protected $model;

    /**
     * @var Full|MockObject
     */
    protected $fullAction;

    /**
     * @var IndexerInterface|MockObject
     */
    protected $saveHandler;

    /**
     * @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext|MockObject
     */
    protected $fulltextResource;

    /**
     * @var DimensionProviderInterface|MockObject
     */
    private $dimensionProviderMock;

    /**
     * @var ProcessManager|MockObject
     */
    private $processManager;

    protected function setUp(): void
    {
        $this->fullAction = $this->getClassMock(Full::class);
        $fullActionFactory = $this->createPartialMock(
            FullFactory::class,
            ['create']
        );
        $fullActionFactory->expects($this->any())->method('create')->willReturn($this->fullAction);
        $this->saveHandler = $this->getClassMock(IndexerInterface::class);
        $indexerHandlerFactory = $this->createPartialMock(
            IndexerHandlerFactory::class,
            ['create']
        );
        $indexerHandlerFactory->expects($this->any())->method('create')->willReturn($this->saveHandler);

        $this->fulltextResource = $this->getClassMock(\Magento\CatalogSearch\Model\ResourceModel\Fulltext::class);

        $this->dimensionProviderMock = $this->getMockBuilder(DimensionProviderInterface::class)
            ->getMock();
        $stateMock = $this->getMockBuilder(State::class)
            ->getMock();
        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->processManager = new ProcessManager(
            $this->getClassMock(ResourceConnection::class)
        );

        $this->model = $objectManagerHelper->getObject(
            Fulltext::class,
            [
                'fullActionFactory' => $fullActionFactory,
                'indexerHandlerFactory' => $indexerHandlerFactory,
                'fulltextResource' => $this->fulltextResource,
                'data' => [],
                'dimensionProvider' => $this->dimensionProviderMock,
                'indexScopeState' => $stateMock,
                'processManager' => $this->processManager,
            ]
        );
    }

    /**
     * @param string $className
     * @return MockObject
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
                    $dimension = $this->getMockBuilder(Dimension::class)
                        ->disableOriginalConstructor()
                        ->getMock();
                    $dimension->expects($this->any())
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
