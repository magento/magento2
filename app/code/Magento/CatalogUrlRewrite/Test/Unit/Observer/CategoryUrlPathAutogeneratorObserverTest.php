<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Observer;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\ResourceModel\Category\GetDefaultUrlKey;
use Magento\CatalogUrlRewrite\Observer\CategoryUrlPathAutogeneratorObserver;
use Magento\CatalogUrlRewrite\Service\V1\StoreViewService;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\Store;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Backend\Model\Validator\UrlKey\CompositeUrlKey;

/**
 * Unit tests for \Magento\CatalogUrlRewrite\Observer\CategoryUrlPathAutogeneratorObserver class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategoryUrlPathAutogeneratorObserverTest extends TestCase
{
    use MockCreationTrait;

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
     * @var MetadataPool|MockObject
     */
    private $metadataPool;

    /**
     * @var EntityMetadataInterface|MockObject
     */
    private $entityMetaDataInterface;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->observer = $this->createPartialMockWithReflection(
            Observer::class,
            ['getCategory', 'getEvent']
        );
        $this->categoryResource = $this->createMock(CategoryResource::class);
        $this->category = $this->createPartialMockWithReflection(
            Category::class,
            [
                'dataHasChangedFor',
                'getResource',
                'getStoreId',
                'formatUrlKey',
                'hasChildren',
                'getData',
                'getUrlKey',
                'getUrlPath'
            ]
        );
        $this->category->method('getResource')->willReturn($this->categoryResource);
        $this->observer->method('getEvent')->willReturnSelf();
        $this->observer->method('getCategory')->willReturn($this->category);
        $this->categoryUrlPathGenerator = $this->createMock(CategoryUrlPathGenerator::class);
        $this->childrenCategoriesProvider = $this->createMock(ChildrenCategoriesProvider::class);

        $this->storeViewService = $this->createMock(StoreViewService::class);

        $this->compositeUrlValidator = $this->createPartialMock(
            CompositeUrlKey::class,
            ['validate']
        );

        $this->getDefaultUrlKey = $this->createPartialMock(
            GetDefaultUrlKey::class,
            ['execute']
        );

        $this->metadataPool = $this->createPartialMock(
            MetadataPool::class,
            ['getMetadata']
        );

        $this->entityMetaDataInterface = $this->createMock(EntityMetadataInterface::class);

        $this->categoryUrlPathAutogeneratorObserver = (new ObjectManagerHelper($this))->getObject(
            CategoryUrlPathAutogeneratorObserver::class,
            [
                'categoryUrlPathGenerator' => $this->categoryUrlPathGenerator,
                'childrenCategoriesProvider' => $this->childrenCategoriesProvider,
                'storeViewService' => $this->storeViewService,
                'compositeUrlValidator' => $this->compositeUrlValidator,
                'getDefaultUrlKey' => $this->getDefaultUrlKey,
                'metadataPool' => $this->metadataPool
            ]
        );
    }

    /**
     * @param $isObjectNew
     * @throws LocalizedException
     */
    #[DataProvider('shouldFormatUrlKeyAndGenerateUrlPathIfUrlKeyIsNotUsingDefaultValueDataProvider')]
    public function testShouldFormatUrlKeyAndGenerateUrlPathIfUrlKeyIsNotUsingDefaultValue($isObjectNew)
    {
        $expectedUrlKey = 'formatted_url_key';
        $expectedUrlPath = 'generated_url_path';
        $categoryData = ['use_default' => ['url_key' => 0], 'url_key' => 'some_key', 'url_path' => ''];
        
        $urlKeyCallCount = 0;
        $this->category->method('getUrlKey')
            ->willReturnCallback(function () use (&$urlKeyCallCount, $categoryData, $expectedUrlKey) {
                $urlKeyCallCount++;
                return match ($urlKeyCallCount) {
                    1 => $categoryData['url_key'],
                    2 => null,
                    3 => $expectedUrlKey,
                    default => $expectedUrlKey
                };
            });
        
        $urlPathCallCount = 0;
        $this->category->method('getUrlPath')
            ->willReturnCallback(function () use (&$urlPathCallCount, $categoryData, $expectedUrlPath) {
                $urlPathCallCount++;
                return match ($urlPathCallCount) {
                    1 => $categoryData['url_path'],
                    2 => $expectedUrlPath,
                    default => $expectedUrlPath
                };
            });
        $this->category->setData($categoryData);
        $this->category->isObjectNew($isObjectNew);
        $this->categoryUrlPathGenerator->expects($this->once())->method('getUrlKey')->willReturn($expectedUrlKey);
        $this->categoryUrlPathGenerator->expects($this->once())->method('getUrlPath')->willReturn($expectedUrlPath);
        $this->assertEquals($categoryData['url_key'], $this->category->getUrlKey());
        $this->assertEquals($categoryData['url_path'], $this->category->getUrlPath());
        $this->compositeUrlValidator->expects($this->once())->method('validate')
            ->with('formatted_url_key')->willReturn([]);
        $this->categoryUrlPathAutogeneratorObserver->execute($this->observer);
        $this->assertEquals($expectedUrlKey, $this->category->getUrlKey());
        $this->assertEquals($expectedUrlPath, $this->category->getUrlPath());
        $this->categoryResource->expects($this->never())->method('saveAttribute');
    }

    /**
     * @return array
     */
    public static function shouldFormatUrlKeyAndGenerateUrlPathIfUrlKeyIsNotUsingDefaultValueDataProvider()
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
     */
    #[DataProvider('shouldResetUrlPathAndUrlKeyIfUrlKeyIsUsingDefaultValueDataProvider')]
    public function testShouldResetUrlPathAndUrlKeyIfUrlKeyIsUsingDefaultValue(bool $isObjectNew, int $storeId): void
    {
        $categoryData = [
            'use_default' => ['url_key' => 1],
            'url_key' => 'some_key',
            'url_path' => 'some_path',
        ];
        $this->category->setData($categoryData);
        $this->category->isObjectNew($isObjectNew);
        $this->category->method('formatUrlKey')->willReturn('formatted_key');
        $this->category->method('getStoreId')->willReturn($storeId);
        $this->category->expects($this->once())
            ->method('hasChildren')
            ->willReturn(false);
        $this->metadataPool->method('getMetadata')
            ->with(CategoryInterface::class)
            ->willReturn($this->entityMetaDataInterface);
        $this->entityMetaDataInterface->method('getLinkField')
            ->willReturn('row_id');
        $this->category->method('getUrlKey')
            ->willReturn($categoryData['url_key']);
        $this->category->method('getUrlPath')
            ->willReturn($categoryData['url_path']);
        $this->category->method('getData')
            ->willReturnMap(
                [
                    ['use_default', null, ['url_key' => 1]],
                    ['row_id', null, null],
                ]
            );
        $this->assertEquals($categoryData['url_key'], $this->category->getUrlKey());
        $this->assertEquals($categoryData['url_path'], $this->category->getUrlPath());
        $this->categoryUrlPathAutogeneratorObserver->execute($this->observer);
        $this->assertNotEmpty($this->category->getUrlKey());
        $this->assertNotEmpty($this->category->getUrlPath());
    }

    /**
     * @return array
     */
    public static function shouldResetUrlPathAndUrlKeyIfUrlKeyIsUsingDefaultValueDataProvider(): array
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
     * @throws LocalizedException
     */
    public function testShouldUpdateUrlPathForChildrenIfUrlKeyIsUsingDefaultValueForSpecificStore(): void
    {
        $storeId = 1;
        $categoryId = 1;
        $rowId = 1;
        $categoryData = [
            'use_default' => ['url_key' => 1],
            'url_key' => null,
            'url_path' => 'some_path',
            'row_id' => 1
        ];

        $this->category->setData($categoryData);
        $this->category->isObjectNew(false);
        $this->category->method('getStoreId')
            ->willReturn($storeId);
        $this->category->expects($this->once())
            ->method('hasChildren')
            ->willReturn(true);
        $this->metadataPool->method('getMetadata')
            ->with(CategoryInterface::class)
            ->willReturn($this->entityMetaDataInterface);
        $this->entityMetaDataInterface->method('getLinkField')
            ->willReturn('row_id');
        $this->category->method('getUrlKey')
            ->willReturn(false);
        $this->category->method('getData')
            ->willReturnMap(
                [
                    ['use_default', null, ['url_key' => 1]],
                    ['row_id', null, $rowId],
                ]
            );
        $this->getDefaultUrlKey->expects($this->once())
            ->method('execute')
            ->with($categoryId)
            ->willReturn('default_url_key');
        $this->category->expects($this->once())
            ->method('dataHasChangedFor')
            ->with('url_path')
            ->willReturn(true);

        $childCategory = $this->createPartialMockWithReflection(
            Category::class,
            [
                'getResource',
                'getStore',
                'getStoreId',
                'setStoreId',
                'getUrlPath',
                'setUrlPath',
            ]
        );
        $childCategory->method('getResource')
            ->willReturn($this->categoryResource);
        $childCategory->expects($this->once())
            ->method('setStoreId')
            ->with($storeId)
            ->willReturnSelf();

        $this->childrenCategoriesProvider->expects($this->once())
            ->method('getChildren')
            ->willReturn([$childCategory]);

        $this->categoryUrlPathAutogeneratorObserver->execute($this->observer);
        $this->assertFalse($this->category->getUrlKey());
        $this->assertNull($this->category->getUrlPath());
    }

    /**
     * @param $useDefaultUrlKey
     * @param $isObjectNew
     * @throws LocalizedException
     */
    #[DataProvider('shouldThrowExceptionIfUrlKeyIsEmptyDataProvider')]
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
    public static function shouldThrowExceptionIfUrlKeyIsEmptyDataProvider()
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
        $this->categoryUrlPathGenerator->method('getUrlKey')->willReturn($expectedUrlKey);
        $this->categoryUrlPathGenerator->method('getUrlPath')->willReturn($expectedUrlPath);
        $this->categoryResource->expects($this->once())->method('saveAttribute')->with($this->category, 'url_path');
        $this->category->expects($this->once())->method('dataHasChangedFor')->with('url_path')->willReturn(false);
        $this->compositeUrlValidator->expects($this->once())->method('validate')
            ->with('formatted_url_key')->willReturn([]);
        $this->categoryUrlPathAutogeneratorObserver->execute($this->observer);
    }

    public function testChildrenUrlPathAttributeNoUpdatingIfParentUrlPathIsNotChanged()
    {
        $categoryData = ['url_key' => 'some_key', 'url_path' => ''];
        $this->category->setData($categoryData);
        $this->category->isObjectNew(false);

        $this->categoryUrlPathGenerator->method('getUrlKey')->willReturn('url_key');
        $this->categoryUrlPathGenerator->method('getUrlPath')->willReturn('url_path');

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

        $this->categoryUrlPathGenerator->method('getUrlKey')->willReturn('generated_url_key');
        $this->categoryUrlPathGenerator->method('getUrlPath')->willReturn('generated_url_path');
        $this->category->method('dataHasChangedFor')->willReturn(true);
        // only for specific store
        $this->category->expects($this->atLeastOnce())->method('getStoreId')->willReturn(1);

        $childCategoryResource = $this->createMock(CategoryResource::class);
        $childCategory = $this->createPartialMockWithReflection(
            Category::class,
            [
                'setUrlPath',
                'getUrlPath',
                'getResource',
                'getStore',
                'getStoreId',
                'setStoreId'
            ]
        );
        $childCategory->method('getResource')->willReturn($childCategoryResource);
        $childCategory->expects($this->once())->method('setStoreId')->with(1);

        $this->childrenCategoriesProvider->expects($this->once())->method('getChildren')->willReturn([$childCategory]);
        $childCategory->expects($this->once())->method('setUrlPath')->with('generated_url_path')->willReturnSelf();
        $childCategoryResource->expects($this->once())->method('saveAttribute')->with($childCategory, 'url_path');
        $this->compositeUrlValidator->expects($this->once())->method('validate')
            ->with('generated_url_key')->willReturn([]);

        $this->categoryUrlPathAutogeneratorObserver->execute($this->observer);
    }
}
