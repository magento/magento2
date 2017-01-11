<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Test\Unit\Observer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class CategoryUrlPathAutogeneratorObserverTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\CatalogUrlRewrite\Observer\CategoryUrlPathAutogeneratorObserver */
    protected $categoryUrlPathAutogeneratorObserver;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $categoryUrlPathGenerator;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $childrenCategoriesProvider;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $observer;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $category;

    /**
     * @var \Magento\CatalogUrlRewrite\Service\V1\StoreViewService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeViewService;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $categoryResource;

    protected function setUp()
    {
        $this->observer = $this->getMock(
            \Magento\Framework\Event\Observer::class,
            ['getEvent', 'getCategory'],
            [],
            '',
            false
        );
        $this->categoryResource = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Category::class,
            [],
            [],
            '',
            false
        );
        $this->category = $this->getMock(
            \Magento\Catalog\Model\Category::class,
            ['setUrlKey', 'setUrlPath', 'dataHasChangedFor', 'isObjectNew', 'getResource', 'getUrlKey', 'getStoreId'],
            [],
            '',
            false
        );
        $this->category->expects($this->any())->method('getResource')->willReturn($this->categoryResource);
        $this->observer->expects($this->any())->method('getEvent')->willReturnSelf();
        $this->observer->expects($this->any())->method('getCategory')->willReturn($this->category);
        $this->categoryUrlPathGenerator = $this->getMock(
            \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator::class,
            [],
            [],
            '',
            false
        );
        $this->childrenCategoriesProvider = $this->getMock(
            \Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider::class
        );

        $this->storeViewService = $this->getMock(
            \Magento\CatalogUrlRewrite\Service\V1\StoreViewService::class,
            [],
            [],
            '',
            false
        );

        $this->categoryUrlPathAutogeneratorObserver = (new ObjectManagerHelper($this))->getObject(
            \Magento\CatalogUrlRewrite\Observer\CategoryUrlPathAutogeneratorObserver::class,
            [
                'categoryUrlPathGenerator' => $this->categoryUrlPathGenerator,
                'childrenCategoriesProvider' => $this->childrenCategoriesProvider,
                'storeViewService' => $this->storeViewService,
            ]
        );
    }

    public function testSetCategoryUrlAndCategoryPath()
    {
        $this->category->expects($this->once())->method('getUrlKey')->willReturn('category');
        $this->categoryUrlPathGenerator->expects($this->once())->method('getUrlKey')->willReturn('urk_key');
        $this->category->expects($this->once())->method('setUrlKey')->with('urk_key')->willReturnSelf();
        $this->categoryUrlPathGenerator->expects($this->once())->method('getUrlPath')->willReturn('url_path');
        $this->category->expects($this->once())->method('setUrlPath')->with('url_path')->willReturnSelf();
        $this->category->expects($this->once())->method('isObjectNew')->willReturn(true);

        $this->categoryUrlPathAutogeneratorObserver->execute($this->observer);
    }

    public function testExecuteWithoutUrlKeyAndUrlPathUpdating()
    {
        $this->category->expects($this->once())->method('getUrlKey')->willReturn(false);
        $this->category->expects($this->never())->method('setUrlKey');
        $this->category->expects($this->never())->method('setUrlPath');
        $this->categoryUrlPathAutogeneratorObserver->execute($this->observer);
    }

    public function testUrlKeyAndUrlPathUpdating()
    {
        $this->categoryUrlPathGenerator->expects($this->once())->method('getUrlKey')->with($this->category)
            ->willReturn('url_key');
        $this->categoryUrlPathGenerator->expects($this->once())->method('getUrlPath')->with($this->category)
            ->willReturn('url_path');

        $this->category->expects($this->once())->method('getUrlKey')->willReturn('not_formatted_url_key');
        $this->category->expects($this->once())->method('setUrlKey')->with('url_key')->willReturnSelf();
        $this->category->expects($this->once())->method('setUrlPath')->with('url_path')->willReturnSelf();
        // break code execution
        $this->category->expects($this->once())->method('isObjectNew')->willReturn(true);

        $this->categoryUrlPathAutogeneratorObserver->execute($this->observer);
    }

    public function testUrlPathAttributeNoUpdatingIfCategoryIsNew()
    {
        $this->categoryUrlPathGenerator->expects($this->any())->method('getUrlKey')->willReturn('url_key');
        $this->categoryUrlPathGenerator->expects($this->any())->method('getUrlPath')->willReturn('url_path');

        $this->category->expects($this->any())->method('getUrlKey')->willReturn('not_formatted_url_key');
        $this->category->expects($this->any())->method('setUrlKey')->willReturnSelf();
        $this->category->expects($this->any())->method('setUrlPath')->willReturnSelf();

        $this->category->expects($this->once())->method('isObjectNew')->willReturn(true);
        $this->categoryResource->expects($this->never())->method('saveAttribute');

        $this->categoryUrlPathAutogeneratorObserver->execute($this->observer);
    }

    public function testUrlPathAttributeUpdating()
    {
        $this->categoryUrlPathGenerator->expects($this->any())->method('getUrlKey')->willReturn('url_key');
        $this->categoryUrlPathGenerator->expects($this->any())->method('getUrlPath')->willReturn('url_path');

        $this->category->expects($this->any())->method('getUrlKey')->willReturn('not_formatted_url_key');
        $this->category->expects($this->any())->method('setUrlKey')->willReturnSelf();
        $this->category->expects($this->any())->method('setUrlPath')->willReturnSelf();
        $this->category->expects($this->once())->method('isObjectNew')->willReturn(false);

        $this->categoryResource->expects($this->once())->method('saveAttribute')->with($this->category, 'url_path');

        // break code execution
        $this->category->expects($this->once())->method('dataHasChangedFor')->with('url_path')->willReturn(false);

        $this->categoryUrlPathAutogeneratorObserver->execute($this->observer);
    }

    public function testChildrenUrlPathAttributeNoUpdatingIfParentUrlPathIsNotChanged()
    {
        $this->categoryUrlPathGenerator->expects($this->any())->method('getUrlKey')->willReturn('url_key');
        $this->categoryUrlPathGenerator->expects($this->any())->method('getUrlPath')->willReturn('url_path');

        $this->categoryResource->expects($this->once())->method('saveAttribute')->with($this->category, 'url_path');

        $this->category->expects($this->any())->method('getUrlKey')->willReturn('not_formatted_url_key');
        $this->category->expects($this->any())->method('setUrlKey')->willReturnSelf();
        $this->category->expects($this->any())->method('setUrlPath')->willReturnSelf();
        $this->category->expects($this->once())->method('isObjectNew')->willReturn(false);
        // break code execution
        $this->category->expects($this->once())->method('dataHasChangedFor')->with('url_path')->willReturn(false);

        $this->categoryUrlPathAutogeneratorObserver->execute($this->observer);
    }

    public function testChildrenUrlPathAttributeUpdatingForSpecificStore()
    {
        $this->categoryUrlPathGenerator->expects($this->any())->method('getUrlKey')->willReturn('generated_url_key');
        $this->categoryUrlPathGenerator->expects($this->any())->method('getUrlPath')->willReturn('generated_url_path');

        $this->category->expects($this->any())->method('getUrlKey')->willReturn('not_formatted_url_key');
        $this->category->expects($this->any())->method('setUrlKey')->willReturnSelf();
        $this->category->expects($this->any())->method('setUrlPath')->willReturnSelf();
        $this->category->expects($this->any())->method('isObjectNew')->willReturn(false);
        $this->category->expects($this->any())->method('dataHasChangedFor')->willReturn(true);
        // only for specific store
        $this->category->expects($this->atLeastOnce())->method('getStoreId')->willReturn(1);

        $childCategoryResource = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Category::class)
            ->disableOriginalConstructor()->getMock();
        $childCategory = $this->getMockBuilder(\Magento\Catalog\Model\Category::class)
            ->setMethods([
                'getUrlPath',
                'setUrlPath',
                'getResource',
                'getStore',
                'getStoreId',
                'setStoreId'
            ])
            ->disableOriginalConstructor()->getMock();
        $childCategory->expects($this->any())->method('getResource')->willReturn($childCategoryResource);
        $childCategory->expects($this->once())->method('setStoreId')->with(1);

        $this->childrenCategoriesProvider->expects($this->once())->method('getChildren')->willReturn([$childCategory]);
        $childCategory->expects($this->once())->method('setUrlPath')->with('generated_url_path')->willReturnSelf();
        $childCategoryResource->expects($this->once())->method('saveAttribute')->with($childCategory, 'url_path');

        $this->categoryUrlPathAutogeneratorObserver->execute($this->observer);
    }
}
