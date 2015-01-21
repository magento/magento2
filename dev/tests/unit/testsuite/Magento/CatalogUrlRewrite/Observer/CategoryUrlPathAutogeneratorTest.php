<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Observer;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class CategoryUrlPathAutogeneratorTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\CatalogUrlRewrite\Observer\CategoryUrlPathAutogenerator */
    protected $categoryUrlPathAutogenerator;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $categoryUrlPathGenerator;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $childrenCategoriesProvider;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $observer;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $category;

    protected function setUp()
    {
        $this->observer = $this->getMock(
            'Magento\Framework\Event\Observer',
            ['getEvent', 'getCategory'],
            [],
            '',
            false
        );
        $this->category = $this->getMock(
            'Magento\Catalog\Model\Category',
            ['setUrlKey', 'setUrlPath', 'dataHasChangedFor', 'isObjectNew', 'getResource', 'getUrlKey'],
            [],
            '',
            false
        );
        $this->observer->expects($this->any())->method('getEvent')->willReturnSelf();
        $this->observer->expects($this->any())->method('getCategory')->willReturn($this->category);
        $this->categoryUrlPathGenerator = $this->getMock(
            'Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator',
            [],
            [],
            '',
            false
        );
        $this->childrenCategoriesProvider = $this->getMock(
            'Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider'
        );

        $this->categoryUrlPathAutogenerator = (new ObjectManagerHelper($this))->getObject(
            'Magento\CatalogUrlRewrite\Observer\CategoryUrlPathAutogenerator',
            [
                'categoryUrlPathGenerator' => $this->categoryUrlPathGenerator,
                'childrenCategoriesProvider' => $this->childrenCategoriesProvider
            ]
        );
    }

    public function testSetCategoryUrlAndCategoryPath()
    {
        $this->category->expects($this->once())->method('getUrlKey')->willReturn('category');
        $this->categoryUrlPathGenerator->expects($this->once())->method('generateUrlKey')->willReturn('urk_key');
        $this->category->expects($this->once())->method('setUrlKey')->with('urk_key')->willReturnSelf();
        $this->categoryUrlPathGenerator->expects($this->once())->method('getUrlPath')->willReturn('url_path');
        $this->category->expects($this->once())->method('setUrlPath')->with('url_path')->willReturnSelf();
        $this->category->expects($this->once())->method('isObjectNew')->willReturn(true);

        $this->categoryUrlPathAutogenerator->invoke($this->observer);
    }

    public function testInvokeWithoutGeneration()
    {
        $this->category->expects($this->once())->method('getUrlKey')->willReturn(false);
        $this->category->expects($this->never())->method('setUrlKey');
        $this->category->expects($this->never())->method('setUrlPath');
        $this->categoryUrlPathAutogenerator->invoke($this->observer);
    }

    public function testUpdateUrlPathForChildren()
    {
        $this->category->expects($this->once())->method('getUrlKey')->willReturn('category');
        $this->category->expects($this->once())->method('setUrlKey')->willReturnSelf();
        $this->category->expects($this->once())->method('setUrlPath')->willReturnSelf();
        $this->category->expects($this->once())->method('isObjectNew')->willReturn(false);
        $this->category->expects($this->once())->method('dataHasChangedFor')->with('url_path')->willReturn(true);
        $categoryResource = $this->getMockBuilder('Magento\Catalog\Model\Resource\Category')
            ->disableOriginalConstructor()->getMock();
        $this->category->expects($this->once())->method('getResource')->willReturn($categoryResource);
        $categoryResource->expects($this->once())->method('saveAttribute')->with($this->category, 'url_path');

        $childCategory = $this->getMockBuilder('Magento\Catalog\Model\Category')
            ->setMethods(['getUrlPath', 'setUrlPath', 'getResource', 'unsUrlPath'])
            ->disableOriginalConstructor()->getMock();

        $this->childrenCategoriesProvider->expects($this->once())->method('getChildren')->willReturn([$childCategory]);
        $childCategoryResource = $this->getMockBuilder('Magento\Catalog\Model\Resource\Category')
            ->disableOriginalConstructor()->getMock();
        $childCategory->expects($this->once())->method('unsUrlPath')->willReturnSelf();
        $childCategory->expects($this->once())->method('getResource')->willReturn($childCategoryResource);
        $childCategoryResource->expects($this->once())->method('saveAttribute')->with($childCategory, 'url_path');
        $childCategory->expects($this->once())->method('setUrlPath')->with('category-url_path')->willReturnSelf();
        $this->categoryUrlPathGenerator->expects($this->any())->method('getUrlPath')->willReturn('category-url_path');

        $this->categoryUrlPathAutogenerator->invoke($this->observer);
    }
}
