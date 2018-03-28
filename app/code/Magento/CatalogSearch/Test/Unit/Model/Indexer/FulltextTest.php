<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Test\Unit\Model\Indexer;

use Magento\Framework\Search\Request\Dimension;
use Magento\Framework\Search\Request\DimensionFactory;
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
     * @var \Magento\Indexer\Model\ProcessManager
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

        $this->storeManager = $this->getMockForAbstractClass(
            \Magento\Store\Model\StoreManagerInterface::class,
            [],
            '',
            false,
            false,
            true,
            []
        );

        $this->dimensionFactory = $this->createPartialMock(DimensionFactory::class, ['create']);

        $this->fulltextResource = $this->getClassMock(\Magento\CatalogSearch\Model\ResourceModel\Fulltext::class);
        $this->searchRequestConfig = $this->getClassMock(\Magento\Framework\Search\Request\Config::class);

        $this->indexSwitcher = $this->getMockBuilder(\Magento\CatalogSearch\Model\Indexer\Scope\IndexSwitcher::class)
            ->disableOriginalConstructor()
            ->setMethods(['switchIndex'])
            ->getMock();

        $this->processManager = new \Magento\Indexer\Model\ProcessManager();

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
        $indexData = new \ArrayObject([]);
        $this->fulltextResource->expects($this->exactly(2))
            ->method('getRelationsByChild')
            ->willReturn($ids);
        $this->storeManager->expects($this->once())->method('getStores')->willReturn($stores);
        $this->saveHandler->expects($this->exactly(count($stores)))->method('deleteIndex');
        $this->saveHandler->expects($this->exactly(2))->method('saveIndex');
        $this->fullAction->expects($this->exactly(2))
            ->method('rebuildStoreIndex')
            ->willReturn(new \ArrayObject([$indexData, $indexData]));

        $this->model->execute($ids);
    }

    public function testExecuteFull()
    {
        $stores = [0 => 'Store 1', 1 => 'Store 2'];
        $indexData = new \ArrayObject([new \ArrayObject([]), new \ArrayObject([])]);
        $this->storeManager->expects($this->once())->method('getStores')->willReturn($stores);

        $dimensionScope1 = $this->getMockBuilder(Dimension::class)
            ->setConstructorArgs(['scope', '1'])
            ->getMock();
        $dimensionScope2 = $this->getMockBuilder(Dimension::class)
            ->setConstructorArgs(['scope', '2'])
            ->getMock();

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
        $this->fulltextResource->expects($this->exactly(2))
            ->method('getRelationsByChild')
            ->willReturn($ids);
        $this->storeManager->expects($this->once())->method('getStores')->willReturn($stores);
        $this->saveHandler->expects($this->exactly(count($stores)))->method('deleteIndex');
        $this->saveHandler->expects($this->exactly(2))->method('saveIndex');
        $this->fullAction->expects($this->exactly(2))
            ->method('rebuildStoreIndex')
            ->willReturn(new \ArrayObject([$indexData, $indexData]));

        $this->model->executeList($ids);
    }

    public function testExecuteRow()
    {
        $id = 1;
        $stores = [0 => 'Store 1', 1 => 'Store 2'];
        $indexData = new \ArrayObject([]);
        $this->fulltextResource->expects($this->exactly(2))
            ->method('getRelationsByChild')
            ->willReturn([$id]);
        $this->storeManager->expects($this->once())->method('getStores')->willReturn($stores);
        $this->saveHandler->expects($this->exactly(count($stores)))->method('deleteIndex');
        $this->saveHandler->expects($this->exactly(2))->method('saveIndex');
        $this->fullAction->expects($this->exactly(2))
            ->method('rebuildStoreIndex')
            ->willReturn(new \ArrayObject([$indexData, $indexData]));

        $this->model->executeRow($id);
    }
}
