<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Plugin\Block;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class TopmenuTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TopmenuTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Plugin\Block\Topmenu
     */
    protected $block;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Store\Model\Store
     */
    protected $storeMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Catalog\Model\Layer\Resolver
     */
    protected $layerResolverMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Catalog\Model\Layer
     */
    protected $catalogLayerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryCollectionFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    protected $categoryCollectionMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Catalog\Helper\Category
     */
    protected $categoryHelperMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Catalog\Model\Category
     */
    protected $childrenCategoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Catalog\Model\Category
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
        $this->categoryHelperMock = $this->_getCleanMock(\Magento\Catalog\Helper\Category::class);
        $this->catalogLayerMock = $this->_getCleanMock(\Magento\Catalog\Model\Layer::class);
        $this->categoryMock = $this->_getCleanMock(\Magento\Catalog\Model\Category::class);
        $this->layerResolverMock = $this->_getCleanMock(\Magento\Catalog\Model\Layer\Resolver::class);
        $this->storeMock = $this->_getCleanMock(\Magento\Store\Model\Store::class);
        $this->storeManagerMock = $this->_getCleanMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->categoryCollectionMock = $this->_getCleanMock(
            \Magento\Catalog\Model\ResourceModel\Category\Collection::class
        );
        $this->categoryCollectionFactoryMock = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Category\StateDependentCollectionFactory::class,
            ['create']
        );

        $this->catalogLayerMock->expects($this->once())->method('getCurrentCategory')
            ->willReturn($this->childrenCategoryMock);

        $this->storeManagerMock->expects($this->atLeastOnce())->method('getStore')
            ->willReturn($this->storeMock);

        $this->categoryMock->expects($this->atLeastOnce())->method('getParentId')
            ->willReturn($categoryParentId);
        $this->categoryMock->expects($this->once())->method('getParentIds')
            ->willReturn($categoryParentIds);

        $this->layerResolverMock->expects($this->once())->method('get')
            ->willReturn($this->catalogLayerMock);

        $this->storeMock->expects($this->once())->method('getRootCategoryId')
            ->willReturn($rootCategoryId);

        $this->categoryCollectionMock->expects($this->once())->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->categoryMock]));

        $this->categoryCollectionFactoryMock->expects($this->once())->method('create')
            ->willReturn($this->categoryCollectionMock);

        $this->block = (new ObjectManager($this))->getObject(
            \Magento\Catalog\Plugin\Block\Topmenu::class,
            [
                'catalogCategory' => $this->categoryHelperMock,
                'categoryCollectionFactory' => $this->categoryCollectionFactoryMock,
                'storeManager' => $this->storeManagerMock,
                'layerResolver' => $this->layerResolverMock,
            ]
        );
    }

    /**
     * Get clean mock by class name
     *
     * @param string $className
     * @return \PHPUnit\Framework\MockObject\MockObject
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
        $treeMock = $this->createMock(\Magento\Framework\Data\Tree::class);

        $parentCategoryNodeMock = $this->_getCleanMock(\Magento\Framework\Data\Tree\Node::class);
        $parentCategoryNodeMock->expects($this->once())->method('getTree')->willReturn($treeMock);
        $parentCategoryNodeMock->expects($this->once())->method('addChild');

        $blockMock = $this->_getCleanMock(\Magento\Theme\Block\Html\Topmenu::class);
        $blockMock->expects($this->once())->method('getMenu')->willReturn($parentCategoryNodeMock);

        $this->block->beforeGetHtml($blockMock);
    }
}
