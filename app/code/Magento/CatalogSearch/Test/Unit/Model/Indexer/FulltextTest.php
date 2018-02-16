<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Test\Unit\Model\Indexer;

use Magento\CatalogSearch\Model\Indexer\ParentProductsResolver;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext as FulltextResource;
use Magento\Framework\Search\Request\Config as SearchRequestConfig;
use Magento\Framework\Search\Request\DimensionFactory;

class FulltextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogSearch\Model\Indexer\ParentProductsResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $parentProductResolver;

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
     * @var \Magento\Framework\Search\Request\Dimension|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dimension;

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
     *
     */
    protected function setUp()
    {
        $this->fullAction = $this->getClassMock('Magento\CatalogSearch\Model\Indexer\Fulltext\Action\Full');
        $fullActionFactory = $this->getMock(
            'Magento\CatalogSearch\Model\Indexer\Fulltext\Action\FullFactory',
            ['create'],
            [],
            '',
            false
        );
        $fullActionFactory->expects($this->any())->method('create')->willReturn($this->fullAction);
        $this->saveHandler = $this->getClassMock('\Magento\CatalogSearch\Model\Indexer\IndexerHandler');
        $indexerHandlerFactory = $this->getMock(
            '\Magento\CatalogSearch\Model\Indexer\IndexerHandlerFactory',
            ['create'],
            [],
            '',
            false
        );
        $indexerHandlerFactory->expects($this->any())->method('create')->willReturn($this->saveHandler);

        $this->storeManager = $this->getMockForAbstractClass(
            'Magento\Store\Model\StoreManagerInterface',
            [],
            '',
            false,
            false,
            true,
            []
        );

        $this->dimension = $this->getClassMock('\Magento\Framework\Search\Request\Dimension');
        $dimensionFactory = $this->getMock(
            '\Magento\Framework\Search\Request\DimensionFactory',
            ['create'],
            [],
            '',
            false
        );
        $dimensionFactory->expects($this->any())->method('create')->willReturn($this->dimension);

        $this->fulltextResource = $this->getClassMock('\Magento\CatalogSearch\Model\ResourceModel\Fulltext');
        $this->searchRequestConfig = $this->getClassMock('Magento\Framework\Search\Request\Config');

        $this->parentProductResolver = $this->getMockBuilder(ParentProductsResolver::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParentProductIds'])
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\CatalogSearch\Model\Indexer\Fulltext::class,
            [
                'fullActionFactory' => $fullActionFactory,
                'indexerHandlerFactory' => $indexerHandlerFactory,
                'storeManager' => $this->storeManager,
                'dimensionFactory' => $dimensionFactory,
                'fulltextResource' => $this->fulltextResource,
                'searchRequestConfig' => $this->searchRequestConfig,
                'data' => [],
                'parentProductsResolver' => $this->parentProductResolver,
            ]
        );
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
        $this->parentProductResolver->expects($this->once())
            ->method('getParentProductIds')
            ->with($ids)
            ->willReturn(['12']);
        $this->fullAction->expects($this->exactly(2))
            ->method('rebuildStoreIndex')
            ->willReturn(new \ArrayObject([$indexData, $indexData]));

        $this->model->execute($ids);
    }

    public function testExecuteFull()
    {
        $stores = [0 => 'Store 1', 1 => 'Store 2'];
        $indexData = new \ArrayObject([]);
        $this->storeManager->expects($this->once())->method('getStores')->willReturn($stores);
        $this->saveHandler->expects($this->exactly(count($stores)))->method('cleanIndex');
        $this->saveHandler->expects($this->exactly(2))->method('saveIndex');
        $this->fullAction->expects($this->exactly(2))
            ->method('rebuildStoreIndex')
            ->willReturn(new \ArrayObject([$indexData, $indexData]));
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
        $this->parentProductResolver->expects($this->once())
            ->method('getParentProductIds')
            ->with($ids)
            ->willReturn([]);
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
        $this->storeManager->expects($this->once())->method('getStores')->willReturn($stores);
        $this->saveHandler->expects($this->exactly(count($stores)))->method('deleteIndex');
        $this->saveHandler->expects($this->exactly(2))->method('saveIndex');
        $this->parentProductResolver->expects($this->once())
            ->method('getParentProductIds')
            ->with([$id])
            ->willReturn(['12']);
        $this->fullAction->expects($this->exactly(2))
            ->method('rebuildStoreIndex')
            ->willReturn(new \ArrayObject([$indexData, $indexData]));

        $this->model->executeRow($id);
    }
}
