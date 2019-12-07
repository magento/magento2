<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogUrlRewrite\Test\Unit\Observer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class CategoryUrlPathAutogeneratorObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CatalogUrlRewrite\Observer\CategoryUrlPathAutogeneratorObserver
     */
    private $categoryUrlPathAutogeneratorObserver;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryUrlPathGenerator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $childrenCategoriesProvider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $observer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $category;

    /**
     * @var \Magento\CatalogUrlRewrite\Service\V1\StoreViewService|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeViewService;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryResource;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->observer = $this->createPartialMock(
            \Magento\Framework\Event\Observer::class,
            ['getEvent', 'getCategory']
        );
        $this->categoryResource = $this->createMock(\Magento\Catalog\Model\ResourceModel\Category::class);
        $this->category = $this->createPartialMock(
            \Magento\Catalog\Model\Category::class,
            [
                'dataHasChangedFor',
                'getResource',
                'getStoreId',
                'formatUrlKey'
            ]
        );
        $this->category->expects($this->any())->method('getResource')->willReturn($this->categoryResource);
        $this->observer->expects($this->any())->method('getEvent')->willReturnSelf();
        $this->observer->expects($this->any())->method('getCategory')->willReturn($this->category);
        $this->categoryUrlPathGenerator = $this->createMock(
            \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator::class
        );
        $this->childrenCategoriesProvider = $this->createMock(
            \Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider::class
        );

        $this->storeViewService = $this->createMock(\Magento\CatalogUrlRewrite\Service\V1\StoreViewService::class);

        $this->categoryUrlPathAutogeneratorObserver = (new ObjectManagerHelper($this))->getObject(
            \Magento\CatalogUrlRewrite\Observer\CategoryUrlPathAutogeneratorObserver::class,
            [
                'categoryUrlPathGenerator' => $this->categoryUrlPathGenerator,
                'childrenCategoriesProvider' => $this->childrenCategoriesProvider,
                'storeViewService' => $this->storeViewService,
            ]
        );
    }

    /**
     * @param $isObjectNew
     * @throws \Magento\Framework\Exception\LocalizedException
     * @dataProvider shouldFormatUrlKeyAndGenerateUrlPathIfUrlKeyIsNotUsingDefaultValueDataProvider
     */
    public function testShouldFormatUrlKeyAndGenerateUrlPathIfUrlKeyIsNotUsingDefaultValue($isObjectNew)
    {
        $expectedUrlKey = 'formatted_url_key';
        $expectedUrlPath = 'generated_url_path';
        $categoryData = ['use_default' => ['url_key' => 0], 'url_key' => 'some_key', 'url_path' => ''];
        $this->category->setData($categoryData);
        $this->category->isObjectNew($isObjectNew);
        $this->categoryUrlPathGenerator->expects($this->once())->method('getUrlKey')->willReturn($expectedUrlKey);
        $this->categoryUrlPathGenerator->expects($this->once())->method('getUrlPath')->willReturn($expectedUrlPath);
        $this->assertEquals($categoryData['url_key'], $this->category->getUrlKey());
        $this->assertEquals($categoryData['url_path'], $this->category->getUrlPath());
        $this->categoryUrlPathAutogeneratorObserver->execute($this->observer);
        $this->assertEquals($expectedUrlKey, $this->category->getUrlKey());
        $this->assertEquals($expectedUrlPath, $this->category->getUrlPath());
        $this->categoryResource->expects($this->never())->method('saveAttribute');
    }

    /**
     * @return array
     */
    public function shouldFormatUrlKeyAndGenerateUrlPathIfUrlKeyIsNotUsingDefaultValueDataProvider()
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @param $isObjectNew
     * @throws \Magento\Framework\Exception\LocalizedException
     * @dataProvider shouldResetUrlPathAndUrlKeyIfUrlKeyIsUsingDefaultValueDataProvider
     */
    public function testShouldResetUrlPathAndUrlKeyIfUrlKeyIsUsingDefaultValue($isObjectNew)
    {
        $categoryData = ['use_default' => ['url_key' => 1], 'url_key' => 'some_key', 'url_path' => 'some_path'];
        $this->category->setData($categoryData);
        $this->category->isObjectNew($isObjectNew);
        $this->category->expects($this->any())->method('formatUrlKey')->willReturn('formatted_key');
        $this->assertEquals($categoryData['url_key'], $this->category->getUrlKey());
        $this->assertEquals($categoryData['url_path'], $this->category->getUrlPath());
        $this->categoryUrlPathAutogeneratorObserver->execute($this->observer);
        $this->assertNull($this->category->getUrlKey());
        $this->assertNull($this->category->getUrlPath());
    }

    /**
     * @return array
     */
    public function shouldResetUrlPathAndUrlKeyIfUrlKeyIsUsingDefaultValueDataProvider()
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @param $useDefaultUrlKey
     * @param $isObjectNew
     * @throws \Magento\Framework\Exception\LocalizedException
     * @dataProvider shouldThrowExceptionIfUrlKeyIsEmptyDataProvider
     */
    public function testShouldThrowExceptionIfUrlKeyIsEmpty($useDefaultUrlKey, $isObjectNew)
    {
        $this->expectExceptionMessage('Invalid URL key');
        $categoryData = ['use_default' => ['url_key' => $useDefaultUrlKey], 'url_key' => '', 'url_path' => ''];
        $this->category->setData($categoryData);
        $this->category
            ->method('getStoreId')
            ->willReturn(\Magento\Store\Model\Store::DEFAULT_STORE_ID);
        $this->category->isObjectNew($isObjectNew);
        $this->assertEquals($isObjectNew, $this->category->isObjectNew());
        $this->assertEquals($categoryData['url_key'], $this->category->getUrlKey());
        $this->assertEquals($categoryData['url_path'], $this->category->getUrlPath());
        $this->categoryUrlPathAutogeneratorObserver->execute($this->observer);
        $this->assertEquals($categoryData['url_key'], $this->category->getUrlKey());
        $this->assertEquals($categoryData['url_path'], $this->category->getUrlPath());
    }

    /**
     * @return array
     */
    public function shouldThrowExceptionIfUrlKeyIsEmptyDataProvider()
    {
        return [
            [0, false],
            [0, true],
            [1, false],
        ];
    }

    public function testUrlPathAttributeUpdating()
    {
        $categoryData = ['url_key' => 'some_key', 'url_path' => ''];
        $this->category->setData($categoryData);
        $this->category->isObjectNew(false);
        $expectedUrlKey = 'formatted_url_key';
        $expectedUrlPath = 'generated_url_path';
        $this->categoryUrlPathGenerator->expects($this->any())->method('getUrlKey')->willReturn($expectedUrlKey);
        $this->categoryUrlPathGenerator->expects($this->any())->method('getUrlPath')->willReturn($expectedUrlPath);
        $this->categoryResource->expects($this->once())->method('saveAttribute')->with($this->category, 'url_path');
        $this->category->expects($this->once())->method('dataHasChangedFor')->with('url_path')->willReturn(false);
        $this->categoryUrlPathAutogeneratorObserver->execute($this->observer);
    }

    public function testChildrenUrlPathAttributeNoUpdatingIfParentUrlPathIsNotChanged()
    {
        $categoryData = ['url_key' => 'some_key', 'url_path' => ''];
        $this->category->setData($categoryData);
        $this->category->isObjectNew(false);

        $this->categoryUrlPathGenerator->expects($this->any())->method('getUrlKey')->willReturn('url_key');
        $this->categoryUrlPathGenerator->expects($this->any())->method('getUrlPath')->willReturn('url_path');

        $this->categoryResource->expects($this->once())->method('saveAttribute')->with($this->category, 'url_path');

        // break code execution
        $this->category->expects($this->once())->method('dataHasChangedFor')->with('url_path')->willReturn(false);

        $this->categoryUrlPathAutogeneratorObserver->execute($this->observer);
    }

    public function testChildrenUrlPathAttributeUpdatingForSpecificStore()
    {
        $categoryData = ['url_key' => 'some_key', 'url_path' => ''];
        $this->category->setData($categoryData);
        $this->category->isObjectNew(false);

        $this->categoryUrlPathGenerator->expects($this->any())->method('getUrlKey')->willReturn('generated_url_key');
        $this->categoryUrlPathGenerator->expects($this->any())->method('getUrlPath')->willReturn('generated_url_path');
        $this->category->expects($this->any())->method('dataHasChangedFor')->willReturn(true);
        // only for specific store
        $this->category->expects($this->atLeastOnce())->method('getStoreId')->willReturn(1);

        $childCategoryResource = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Category::class)
            ->disableOriginalConstructor()->getMock();
        $childCategory = $this->getMockBuilder(\Magento\Catalog\Model\Category::class)
            ->setMethods(
                [
                    'getUrlPath',
                    'setUrlPath',
                    'getResource',
                    'getStore',
                    'getStoreId',
                    'setStoreId'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $childCategory->expects($this->any())->method('getResource')->willReturn($childCategoryResource);
        $childCategory->expects($this->once())->method('setStoreId')->with(1);

        $this->childrenCategoriesProvider->expects($this->once())->method('getChildren')->willReturn([$childCategory]);
        $childCategory->expects($this->once())->method('setUrlPath')->with('generated_url_path')->willReturnSelf();
        $childCategoryResource->expects($this->once())->method('saveAttribute')->with($childCategory, 'url_path');

        $this->categoryUrlPathAutogeneratorObserver->execute($this->observer);
    }
}
