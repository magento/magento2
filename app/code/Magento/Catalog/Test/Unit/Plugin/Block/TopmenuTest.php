<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Plugin\Block;

use Magento\Catalog\Helper\Category;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Category\StateDependentCollectionFactory;
use Magento\Catalog\Plugin\Block\Topmenu;
use Magento\Framework\Data\Tree;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TopmenuTest extends TestCase
{
    /**
     * @var Topmenu
     */
    protected $block;

    /**
     * @var MockObject|StoreManagerInterface
     */
    protected $storeManagerMock;

    /**
     * @var MockObject|Store
     */
    protected $storeMock;

    /**
     * @var MockObject|CollectionFactory
     */
    protected $categoryCollectionFactoryMock;

    /**
     * @var MockObject|Collection
     */
    protected $categoryCollectionMock;

    /**
     * @var MockObject|Category
     */
    protected $categoryHelperMock;

    /**
     * @var MockObject|\Magento\Catalog\Model\Category
     */
    protected $childrenCategoryMock;

    /**
     * @var MockObject|\Magento\Catalog\Model\Category
     */
    protected $categoryMock;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $rootCategoryId = 2;
        $categoryParentId = 3;
        $categoryParentIds = [1, 2, 3];

        $this->childrenCategoryMock = $this->_getCleanMock(\Magento\Catalog\Model\Category::class);
        $this->categoryHelperMock = $this->_getCleanMock(Category::class);
        $this->categoryMock = $this->_getCleanMock(\Magento\Catalog\Model\Category::class);
        $this->storeMock = $this->_getCleanMock(Store::class);
        $this->storeManagerMock = $this->_getCleanMock(StoreManagerInterface::class);
        $this->categoryCollectionMock = $this->_getCleanMock(
            Collection::class
        );
        $this->categoryCollectionFactoryMock = $this->createPartialMock(
            StateDependentCollectionFactory::class,
            ['create']
        );

        $this->storeManagerMock->expects($this->atLeastOnce())->method('getStore')
            ->willReturn($this->storeMock);

        $this->categoryMock->expects($this->atLeastOnce())->method('getParentId')
            ->willReturn($categoryParentId);
        $this->categoryMock->expects($this->once())->method('getParentIds')
            ->willReturn($categoryParentIds);

        $this->storeMock->expects($this->once())->method('getRootCategoryId')
            ->willReturn($rootCategoryId);

        $this->categoryCollectionMock->expects($this->once())->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->categoryMock]));

        $this->categoryCollectionFactoryMock->expects($this->once())->method('create')
            ->willReturn($this->categoryCollectionMock);

        $this->block = (new ObjectManager($this))->getObject(
            Topmenu::class,
            [
                'catalogCategory' => $this->categoryHelperMock,
                'categoryCollectionFactory' => $this->categoryCollectionFactoryMock,
                'storeManager' => $this->storeManagerMock
            ]
        );
    }

    /**
     * Get clean mock by class name
     *
     * @param string $className
     * @return MockObject
     */
    protected function _getCleanMock($className)
    {
        return $this->createMock($className);
    }

    /**
     * Test beforeGetHtml
     *
     */
    public function testBeforeGetHtml()
    {
        $treeMock = $this->createMock(Tree::class);

        $parentCategoryNodeMock = $this->_getCleanMock(Node::class);
        $parentCategoryNodeMock->expects($this->once())->method('getTree')->willReturn($treeMock);
        $parentCategoryNodeMock->expects($this->once())->method('addChild');

        $blockMock = $this->_getCleanMock(\Magento\Theme\Block\Html\Topmenu::class);
        $blockMock->expects($this->once())->method('getMenu')->willReturn($parentCategoryNodeMock);

        $this->block->beforeGetHtml($blockMock);
    }
}
