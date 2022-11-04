<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Observer;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\ResourceModel\Category\GetDefaultUrlKey;
use Magento\CatalogUrlRewrite\Observer\CategoryUrlPathAutogeneratorObserver;
use Magento\CatalogUrlRewrite\Service\V1\StoreViewService;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Backend\Model\Validator\UrlKey\CompositeUrlKey;

/**
 * Unit tests for \Magento\CatalogUrlRewrite\Observer\CategoryUrlPathAutogeneratorObserver class.
 */
class CategoryUrlPathAutogeneratorObserverTest extends TestCase
{
    /**
     * @var CategoryUrlPathAutogeneratorObserver
     */
    private $categoryUrlPathAutogeneratorObserver;

    /**
     * @var MockObject
     */
    private $categoryUrlPathGenerator;

    /**
     * @var MockObject
     */
    private $childrenCategoriesProvider;

    /**
     * @var MockObject
     */
    private $observer;

    /**
     * @var MockObject
     */
    private $category;

    /**
     * @var StoreViewService|MockObject
     */
    private $storeViewService;

    /**
     * @var CategoryResource|MockObject
     */
    private $categoryResource;

    /**
     * @var CompositeUrlKey|MockObject
     */
    private $compositeUrlValidator;

    /**
     * @var GetDefaultUrlKey|MockObject
     */
    private $getDefaultUrlKey;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->observer = $this->getMockBuilder(Observer::class)
            ->addMethods(['getCategory'])
            ->onlyMethods(['getEvent'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->categoryResource = $this->createMock(CategoryResource::class);
        $this->category = $this->createPartialMock(
            Category::class,
            [
                'dataHasChangedFor',
                'getResource',
                'getStoreId',
                'formatUrlKey',
                'getId',
                'hasChildren',
            ]
        );
        $this->category->expects($this->any())->method('getResource')->willReturn($this->categoryResource);
        $this->observer->expects($this->any())->method('getEvent')->willReturnSelf();
        $this->observer->expects($this->any())->method('getCategory')->willReturn($this->category);
        $this->categoryUrlPathGenerator = $this->createMock(CategoryUrlPathGenerator::class);
        $this->childrenCategoriesProvider = $this->createMock(ChildrenCategoriesProvider::class);

        $this->storeViewService = $this->createMock(StoreViewService::class);

        $this->compositeUrlValidator = $this->getMockBuilder(CompositeUrlKey::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['validate'])
            ->getMock();

        $this->getDefaultUrlKey = $this->getMockBuilder(GetDefaultUrlKey::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['execute'])
            ->getMock();

        $this->categoryUrlPathAutogeneratorObserver = (new ObjectManagerHelper($this))->getObject(
            CategoryUrlPathAutogeneratorObserver::class,
            [
                'categoryUrlPathGenerator' => $this->categoryUrlPathGenerator,
                'childrenCategoriesProvider' => $this->childrenCategoriesProvider,
                'storeViewService' => $this->storeViewService,
                'compositeUrlValidator' => $this->compositeUrlValidator,
                'getDefaultUrlKey' => $this->getDefaultUrlKey,
            ]
        );
    }

    /**
     * @param $isObjectNew
     * @throws LocalizedException
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
        $this->compositeUrlValidator->expects($this->once())->method('validate')->with('formatted_url_key')->willReturn([]);
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
     * @param bool $isObjectNew
     * @param int $storeId
     * @return void
     * @throws LocalizedException
     * @dataProvider shouldResetUrlPathAndUrlKeyIfUrlKeyIsUsingDefaultValueDataProvider
     */
    public function testShouldResetUrlPathAndUrlKeyIfUrlKeyIsUsingDefaultValue(bool $isObjectNew, int $storeId): void
    {
        $categoryData = [
            'use_default' => ['url_key' => 1],
            'url_key' => 'some_key',
            'url_path' => 'some_path',
        ];
        $this->category->setData($categoryData);
        $this->category->isObjectNew($isObjectNew);
        $this->category->expects($this->any())->method('formatUrlKey')->willReturn('formatted_key');
        $this->category->expects($this->any())->method('getStoreId')->willReturn($storeId);
        $this->category->expects($this->once())
            ->method('hasChildren')
            ->willReturn(false);
        $this->assertEquals($categoryData['url_key'], $this->category->getUrlKey());
        $this->assertEquals($categoryData['url_path'], $this->category->getUrlPath());
        $this->categoryUrlPathAutogeneratorObserver->execute($this->observer);
        $this->assertNull($this->category->getUrlKey());
        $this->assertNull($this->category->getUrlPath());
    }

    /**
     * @return array
     */
    public function shouldResetUrlPathAndUrlKeyIfUrlKeyIsUsingDefaultValueDataProvider(): array
    {
        return [
            [false, 0],
            [false, 1],
            [true, 1],
            [true, 0],
        ];
    }

    /**
     * @return void
     */
    public function testShouldUpdateUrlPathForChildrenIfUrlKeyIsUsingDefaultValueForSpecificStore(): void
    {
        $storeId = 1;
        $categoryId = 1;
        $categoryData = [
            'use_default' => ['url_key' => 1],
            'url_key' => null,
            'url_path' => 'some_path',
        ];

        $this->category->setData($categoryData);
        $this->category->isObjectNew(false);
        $this->category->expects($this->any())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->category->expects($this->once())
            ->method('hasChildren')
            ->willReturn(true);
        $this->category->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($categoryId);
        $this->getDefaultUrlKey->expects($this->once())
            ->method('execute')
            ->with($categoryId)
            ->willReturn('default_url_key');
        $this->category->expects($this->once())
            ->method('dataHasChangedFor')
            ->with('url_path')
            ->willReturn(true);

        $childCategory = $this->getMockBuilder(Category::class)
            ->onlyMethods(
                [
                    'getResource',
                    'getStore',
                    'getStoreId',
                    'setStoreId',
                ]
            )
            ->addMethods(
                [
                    'getUrlPath',
                    'setUrlPath',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $childCategory->expects($this->any())
            ->method('getResource')
            ->willReturn($this->categoryResource);
        $childCategory->expects($this->once())
            ->method('setStoreId')
            ->with($storeId)
            ->willReturnSelf();

        $this->childrenCategoriesProvider->expects($this->once())
            ->method('getChildren')
            ->willReturn([$childCategory]);

        $this->categoryUrlPathAutogeneratorObserver->execute($this->observer);
        $this->assertNull($this->category->getUrlKey());
        $this->assertNull($this->category->getUrlPath());
    }

    /**
     * @param $useDefaultUrlKey
     * @param $isObjectNew
     * @throws LocalizedException
     * @dataProvider shouldThrowExceptionIfUrlKeyIsEmptyDataProvider
     */
    public function testShouldThrowExceptionIfUrlKeyIsEmpty($useDefaultUrlKey, $isObjectNew)
    {
        $this->expectExceptionMessage('Invalid URL key');
        $categoryData = ['use_default' => ['url_key' => $useDefaultUrlKey], 'url_key' => '', 'url_path' => ''];
        $this->category->setData($categoryData);
        $this->category
            ->method('getStoreId')
            ->willReturn(Store::DEFAULT_STORE_ID);
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
        $this->compositeUrlValidator->expects($this->once())->method('validate')->with('formatted_url_key')->willReturn([]);
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
        $this->compositeUrlValidator->expects($this->once())->method('validate')->with('url_key')->willReturn([]);

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

        $childCategoryResource = $this->getMockBuilder(CategoryResource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $childCategory = $this->getMockBuilder(Category::class)
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
        $this->compositeUrlValidator->expects($this->once())->method('validate')->with('generated_url_key')->willReturn([]);

        $this->categoryUrlPathAutogeneratorObserver->execute($this->observer);
    }
}
