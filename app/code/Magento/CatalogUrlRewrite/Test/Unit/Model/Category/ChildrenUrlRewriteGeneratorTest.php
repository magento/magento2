<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Test\Unit\Model\Category;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ChildrenUrlRewriteGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\CatalogUrlRewrite\Model\Category\ChildrenUrlRewriteGenerator */
    protected $childrenUrlRewriteGenerator;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $category;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $childrenCategoriesProvider;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $categoryUrlRewriteGeneratorFactory;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $categoryUrlRewriteGenerator;

    protected function setUp()
    {
        $this->childrenCategoriesProvider = $this->getMockBuilder(
            'Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider'
        )->disableOriginalConstructor()->getMock();
        $this->category = $this->getMockBuilder('Magento\Catalog\Model\Category')
            ->disableOriginalConstructor()->getMock();
        $this->categoryUrlRewriteGeneratorFactory = $this->getMockBuilder(
            'Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGeneratorFactory'
        )->disableOriginalConstructor()->setMethods(['create'])->getMock();
        $this->categoryUrlRewriteGenerator = $this->getMockBuilder(
            'Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator'
        )->disableOriginalConstructor()->getMock();
        $this->childrenUrlRewriteGenerator = (new ObjectManager($this))->getObject(
            'Magento\CatalogUrlRewrite\Model\Category\ChildrenUrlRewriteGenerator',
            [
                'childrenCategoriesProvider' => $this->childrenCategoriesProvider,
                'categoryUrlRewriteGeneratorFactory' => $this->categoryUrlRewriteGeneratorFactory
            ]
        );
    }

    public function testNoChildrenCategories()
    {
        $this->childrenCategoriesProvider->expects($this->once())->method('getChildren')->with($this->category, false)
            ->will($this->returnValue([]));

        $this->assertEquals([], $this->childrenUrlRewriteGenerator->generate('store_id', $this->category));
    }

    public function testGenerate()
    {
        $storeId = 'store_id';
        $saveRewritesHistory = 'flag';

        $childCategory = $this->getMockBuilder('Magento\Catalog\Model\Category')
            ->disableOriginalConstructor()->getMock();
        $childCategory->expects($this->once())->method('setStoreId')->with($storeId);
        $childCategory->expects($this->once())->method('setData')
            ->with('save_rewrites_history', $saveRewritesHistory);
        $this->childrenCategoriesProvider->expects($this->once())->method('getChildren')->with($this->category, false)
            ->will($this->returnValue([$childCategory]));
        $this->category->expects($this->any())->method('getData')->with('save_rewrites_history')
            ->will($this->returnValue($saveRewritesHistory));
        $this->categoryUrlRewriteGeneratorFactory->expects($this->once())->method('create')
            ->will($this->returnValue($this->categoryUrlRewriteGenerator));
        $this->categoryUrlRewriteGenerator->expects($this->once())->method('generate')->with($childCategory)
            ->will($this->returnValue([['url-1', 'url-2']]));

        $this->assertEquals(
            [['url-1', 'url-2']],
            $this->childrenUrlRewriteGenerator->generate($storeId, $this->category)
        );
    }
}
