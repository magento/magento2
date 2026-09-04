<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Observer;

use Magento\Catalog\Api\CategoryRepositoryInterface;
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
     * @var CategoryRepositoryInterface|MockObject
     */
    private $categoryRepository;

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

        $this->categoryRepository = $this->createMock(CategoryRepositoryInterface::class);

        $this->categoryUrlPathAutogeneratorObserver = (new ObjectManagerHelper($this))->getObject(
            CategoryUrlPathAutogeneratorObserver::class,
            [
                'categoryUrlPathGenerator' => $this->categoryUrlPathGenerator,
                'childrenCategoriesProvider' => $this->childrenCategoriesProvider,
                'storeViewService' => $this->storeViewService,
                'categoryRepository' => $this->categoryRepository,
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

        $urlKeyAttribute = $this->createMock(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class);
        $urlKeyAttribute->method('getBackendTable')->willReturn('catalog_category_entity_varchar');
        $urlKeyAttribute->method('getAttributeId')->willReturn(120);
        $this->categoryResource->method('getAttribute')
            ->with('url_key')
            ->willReturn($urlKeyAttribute);
        $connection = $this->createMock(\Magento\Framework\DB\Adapter\AdapterInterface::class);
        $connection->expects($this->once())
            ->method('delete')
            ->with('catalog_category_entity_varchar', [
                'attribute_id = ?' => 120,
                'row_id = ?' => $rowId,
                'store_id = ?' => $storeId,
            ]);
        $this->categoryResource->method('getConnection')->willReturn($connection);

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

    /**
     * When the edited category's own url_path is overridden for a store, and that store also has its
     * own independently overridden url_key, the store-scoped category must be freshly reloaded from the
     * repository rather than reused from memory, since its url_key is unrelated to the current edit.
     *
     * @return void
     * @throws LocalizedException
     */
    public function testStoreScopedCategoryIsReloadedFromRepositoryWhenUrlKeyOverriddenForStore(): void
    {
        $categoryId = 5;
        $storeId = 1;
        $categoryData = [
            'id' => $categoryId,
            'url_key' => 'some_key',
            'url_path' => '',
            'store_ids' => [$storeId],
        ];
        $this->category->setData($categoryData);
        $this->category->isObjectNew(false);
        $this->category->method('getStoreId')->willReturn(Store::DEFAULT_STORE_ID);
        $this->category->method('dataHasChangedFor')->willReturn(true);
        $this->category->method('getData')->willReturnMap([['store_ids', null, [$storeId]]]);

        $this->categoryUrlPathGenerator->method('getUrlKey')->willReturn('generated_url_key');
        $this->categoryUrlPathGenerator->method('getUrlPath')->willReturn('generated_url_path');
        $this->compositeUrlValidator->method('validate')->willReturn([]);

        $this->childrenCategoriesProvider->expects($this->once())
            ->method('getChildrenIds')
            ->with($this->category, true)
            ->willReturn([]);

        $this->storeViewService->expects($this->once())
            ->method('doesEntityHaveOverriddenUrlPathForStore')
            ->with($storeId, $categoryId, Category::ENTITY)
            ->willReturn(true);
        $this->storeViewService->expects($this->once())
            ->method('doesEntityHaveOverriddenUrlKeyForStore')
            ->with($storeId, $categoryId, Category::ENTITY)
            ->willReturn(true);

        $reloadedCategoryResource = $this->createMock(CategoryResource::class);
        $reloadedCategory = $this->createPartialMockWithReflection(
            Category::class,
            ['getResource', 'getStore', 'getStoreId']
        );
        $reloadedCategory->method('getResource')->willReturn($reloadedCategoryResource);

        $this->categoryRepository->expects($this->once())
            ->method('get')
            ->with($categoryId, $storeId)
            ->willReturn($reloadedCategory);

        $reloadedCategoryResource->expects($this->once())
            ->method('saveAttribute')
            ->with($reloadedCategory, 'url_path');

        $this->categoryUrlPathAutogeneratorObserver->execute($this->observer);
    }

    /**
     * When the edited category's own url_path is overridden for a store, but that store does not have
     * its own independently overridden url_key, the store-scoped category must be derived by cloning the
     * in-memory category (preserving its just-changed, not-yet-persisted url_key) rather than reloading
     * it from the repository, which would still read the stale, pre-change url_key from the database.
     *
     * @return void
     * @throws LocalizedException
     */
    public function testStoreScopedCategoryIsClonedInMemoryWhenUrlKeyNotOverriddenForStore(): void
    {
        $categoryId = 5;
        $storeId = 1;
        $categoryData = [
            'id' => $categoryId,
            'url_key' => 'some_key',
            'url_path' => '',
            'store_ids' => [$storeId],
        ];
        $this->category->setData($categoryData);
        $this->category->isObjectNew(false);
        $this->category->method('getStoreId')->willReturn(Store::DEFAULT_STORE_ID);
        $this->category->method('dataHasChangedFor')->willReturn(true);
        $this->category->method('getData')->willReturnMap([['store_ids', null, [$storeId]]]);

        $this->categoryUrlPathGenerator->method('getUrlKey')->willReturn('generated_url_key');
        $this->categoryUrlPathGenerator->method('getUrlPath')->willReturn('generated_url_path');
        $this->compositeUrlValidator->method('validate')->willReturn([]);

        $this->childrenCategoriesProvider->expects($this->once())
            ->method('getChildrenIds')
            ->with($this->category, true)
            ->willReturn([]);

        $this->storeViewService->expects($this->once())
            ->method('doesEntityHaveOverriddenUrlPathForStore')
            ->with($storeId, $categoryId, Category::ENTITY)
            ->willReturn(true);
        $this->storeViewService->expects($this->once())
            ->method('doesEntityHaveOverriddenUrlKeyForStore')
            ->with($storeId, $categoryId, Category::ENTITY)
            ->willReturn(false);

        // A repository reload would return stale (pre-change) url_key data, so it must never happen here.
        $this->categoryRepository->expects($this->never())->method('get');

        $savedCategories = [];
        $this->categoryResource->method('saveAttribute')
            ->willReturnCallback(function ($category) use (&$savedCategories) {
                $savedCategories[] = $category;
                return $this->categoryResource;
            });

        $this->categoryUrlPathAutogeneratorObserver->execute($this->observer);

        // First save is the edited category itself; second is the cloned store-scoped category.
        $this->assertCount(2, $savedCategories);
        $this->assertSame($this->category, $savedCategories[0]);
        $this->assertNotSame($this->category, $savedCategories[1]);
        $this->assertInstanceOf(Category::class, $savedCategories[1]);
    }

    /**
     * When $storeId is the global scope currently being edited, doesEntityHaveOverriddenUrlKeyForStore()
     * always reports true, because the global url_key row always exists - it is the row being edited,
     * not an independent per-store override. The store-scoped category must still be derived by cloning
     * the in-memory category rather than reloading from the repository, or the reload would read the
     * stale, pre-change url_key back out of the database.
     *
     * @return void
     * @throws LocalizedException
     */
    public function testStoreScopedCategoryIsClonedInMemoryWhenStoreIdIsTheEditedGlobalScope(): void
    {
        $categoryId = 5;
        $storeId = Store::DEFAULT_STORE_ID;
        $categoryData = [
            'id' => $categoryId,
            'url_key' => 'some_key',
            'url_path' => '',
            'store_ids' => [$storeId],
        ];
        $this->category->setData($categoryData);
        $this->category->isObjectNew(false);
        $this->category->method('getStoreId')->willReturn(Store::DEFAULT_STORE_ID);
        $this->category->method('dataHasChangedFor')->willReturn(true);
        $this->category->method('getData')->willReturnMap([['store_ids', null, [$storeId]]]);

        $this->categoryUrlPathGenerator->method('getUrlKey')->willReturn('generated_url_key');
        $this->categoryUrlPathGenerator->method('getUrlPath')->willReturn('generated_url_path');
        $this->compositeUrlValidator->method('validate')->willReturn([]);

        $this->childrenCategoriesProvider->expects($this->once())
            ->method('getChildrenIds')
            ->with($this->category, true)
            ->willReturn([]);

        $this->storeViewService->expects($this->once())
            ->method('doesEntityHaveOverriddenUrlPathForStore')
            ->with($storeId, $categoryId, Category::ENTITY)
            ->willReturn(true);
        // The global row always exists, so this always reports true; it must not be consulted
        // when $storeId is the scope being edited.
        $this->storeViewService->expects($this->never())->method('doesEntityHaveOverriddenUrlKeyForStore');

        // A repository reload would return the stale (pre-change) url_key, so it must never happen here.
        $this->categoryRepository->expects($this->never())->method('get');

        $savedCategories = [];
        $this->categoryResource->method('saveAttribute')
            ->willReturnCallback(function ($category) use (&$savedCategories) {
                $savedCategories[] = $category;
                return $this->categoryResource;
            });

        $this->categoryUrlPathAutogeneratorObserver->execute($this->observer);

        // First save is the edited category itself; second is the cloned store-scoped category.
        $this->assertCount(2, $savedCategories);
        $this->assertSame($this->category, $savedCategories[0]);
        $this->assertNotSame($this->category, $savedCategories[1]);
        $this->assertInstanceOf(Category::class, $savedCategories[1]);
    }

    /**
     * When the edited category itself has no url_path override at a store, but a direct child does,
     * the child must still be linked to a store-scoped clone of the edited category (carrying its
     * just-changed, not-yet-persisted url_key) rather than null - passing null would make url path
     * generation reload the parent from the repository and read the stale, pre-save url_key.
     *
     * @return void
     * @throws LocalizedException
     */
    public function testOverriddenChildIsLinkedToStoreScopedParentWhenParentItselfIsNotOverridden(): void
    {
        $categoryId = 5;
        $storeId = 1;
        $childId = 10;
        $categoryData = [
            'id' => $categoryId,
            'url_key' => 'some_key',
            'url_path' => '',
            'store_ids' => [$storeId],
        ];
        $this->category->setData($categoryData);
        $this->category->isObjectNew(false);
        $this->category->method('getStoreId')->willReturn(Store::DEFAULT_STORE_ID);
        $this->category->method('dataHasChangedFor')->willReturn(true);
        $this->category->method('getData')->willReturnMap([['store_ids', null, [$storeId]]]);

        $this->categoryUrlPathGenerator->method('getUrlKey')->willReturn('generated_url_key');
        $this->categoryUrlPathGenerator->method('getUrlPath')->willReturn('generated_url_path');
        $this->compositeUrlValidator->method('validate')->willReturn([]);

        $this->childrenCategoriesProvider->expects($this->once())
            ->method('getChildrenIds')
            ->with($this->category, true)
            ->willReturn([$childId]);

        // The edited category itself has no override at this store; only the direct child does.
        $this->storeViewService->method('doesEntityHaveOverriddenUrlPathForStore')
            ->willReturnCallback(function ($actualStoreId, $entityId) use ($storeId, $childId) {
                if ($actualStoreId !== $storeId) {
                    return false;
                }
                return $entityId === $childId;
            });
        $this->storeViewService->method('doesEntityHaveOverriddenUrlKeyForStore')->willReturn(false);

        $child = $this->createPartialMockWithReflection(
            Category::class,
            ['getResource', 'getStore', 'getStoreId']
        );
        $child->method('getResource')->willReturn($this->createMock(CategoryResource::class));
        $child->setData(['id' => $childId, 'parent_id' => $categoryId, 'level' => 3]);

        $this->categoryRepository->expects($this->once())
            ->method('get')
            ->with($childId, $storeId)
            ->willReturn($child);

        // The parent's own url_path has no override at this store, so it must not be re-saved.
        $this->categoryResource->expects($this->once())->method('saveAttribute');

        $parentArgument = 'not-called';
        $this->categoryUrlPathGenerator->method('getUrlPath')
            ->willReturnCallback(function ($cat, $parent = null) use (&$parentArgument, $childId) {
                if ($cat->getId() === $childId) {
                    $parentArgument = $parent;
                }
                return 'generated_url_path';
            });

        $this->categoryUrlPathAutogeneratorObserver->execute($this->observer);

        $this->assertNotNull($parentArgument, 'Direct overridden child must receive a store-scoped parent, not null.');
        $this->assertNotSame($this->category, $parentArgument);
        $this->assertInstanceOf(Category::class, $parentArgument);
    }

    /**
     * Overridden descendants at a given store scope must be refreshed in level order, with only the
     * direct children of the edited category linked to its store-scoped parent - deeper descendants
     * must resolve their own url_path independently rather than incorrectly inheriting it.
     *
     * @return void
     * @throws LocalizedException
     */
    public function testOverriddenChildrenAreUpdatedInLevelOrderWithCorrectParentForGlobalScope(): void
    {
        $categoryId = 5;
        $storeId = 1;
        $directChildId = 10;
        $grandChildId = 20;
        $categoryData = [
            'id' => $categoryId,
            'url_key' => 'some_key',
            'url_path' => '',
            'store_ids' => [$storeId],
        ];
        $this->category->setData($categoryData);
        $this->category->isObjectNew(false);
        $this->category->method('getStoreId')->willReturn(Store::DEFAULT_STORE_ID);
        $this->category->method('dataHasChangedFor')->willReturn(true);
        $this->category->method('getData')->willReturnMap([['store_ids', null, [$storeId]]]);

        $this->categoryUrlPathGenerator->method('getUrlKey')->willReturn('generated_url_key');
        $this->categoryUrlPathGenerator->method('getUrlPath')->willReturn('generated_url_path');
        $this->compositeUrlValidator->method('validate')->willReturn([]);

        // Discovery order is reversed (grandchild before direct child) to prove level-based sorting.
        $this->childrenCategoriesProvider->expects($this->once())
            ->method('getChildrenIds')
            ->with($this->category, true)
            ->willReturn([$grandChildId, $directChildId]);

        $overriddenIds = [$categoryId, $directChildId, $grandChildId];
        $this->storeViewService->method('doesEntityHaveOverriddenUrlPathForStore')
            ->willReturnCallback(function ($actualStoreId, $entityId) use ($storeId, $overriddenIds) {
                return $actualStoreId === $storeId && in_array($entityId, $overriddenIds, true);
            });
        $this->storeViewService->expects($this->once())
            ->method('doesEntityHaveOverriddenUrlKeyForStore')
            ->with($storeId, $categoryId, Category::ENTITY)
            ->willReturn(false);

        $directChild = $this->createPartialMockWithReflection(
            Category::class,
            ['getResource', 'getStore', 'getStoreId']
        );
        $directChild->method('getResource')->willReturn($this->createMock(CategoryResource::class));
        $directChild->setData(['id' => $directChildId, 'parent_id' => $categoryId, 'level' => 2]);

        $grandChild = $this->createPartialMockWithReflection(
            Category::class,
            ['getResource', 'getStore', 'getStoreId']
        );
        $grandChild->method('getResource')->willReturn($this->createMock(CategoryResource::class));
        $grandChild->setData(['id' => $grandChildId, 'parent_id' => $directChildId, 'level' => 3]);

        $this->categoryRepository->method('get')
            ->willReturnMap([
                [$directChildId, $storeId, $directChild],
                [$grandChildId, $storeId, $grandChild],
            ]);

        $processedIds = [];
        $this->categoryUrlPathGenerator->method('getUrlPath')
            ->willReturnCallback(function ($cat, $parent = null) use (&$processedIds, $directChildId, $grandChildId) {
                if ($cat->getId() === $directChildId || $cat->getId() === $grandChildId) {
                    $processedIds[] = ['id' => $cat->getId(), 'hasParent' => $parent !== null];
                }
                return 'generated_url_path';
            });

        $this->categoryUrlPathAutogeneratorObserver->execute($this->observer);

        // The direct child is processed first (lower level) and is linked to the store-scoped parent;
        // the grandchild is processed after and resolves its url_path without an explicit parent.
        $this->assertSame($directChildId, $processedIds[0]['id']);
        $this->assertTrue($processedIds[0]['hasParent']);
        $this->assertSame($grandChildId, $processedIds[1]['id']);
        $this->assertFalse($processedIds[1]['hasParent']);
    }

    /**
     * When a category is edited directly at a specific store scope (not "All Store Views"), descendants
     * beyond the direct child must still be refreshed in level order and linked to their actual,
     * already-updated in-memory parent - not left to independently resolve a parent that may be stale.
     *
     * @return void
     * @throws LocalizedException
     */
    public function testChildrenAreUpdatedInLevelOrderWithCorrectParentForSpecificStore(): void
    {
        $categoryId = 3;
        $storeId = 2;
        $directChildId = 4;
        $grandChildId = 5;

        $categoryData = [
            'id' => $categoryId,
            'use_default' => ['url_key' => 1],
            'url_key' => null,
            'url_path' => 'one',
            'row_id' => $categoryId,
        ];
        $this->category->setData($categoryData);
        $this->category->isObjectNew(false);
        $this->category->method('getStoreId')->willReturn($storeId);
        $this->category->method('hasChildren')->willReturn(true);
        $this->category->method('getUrlKey')->willReturn(false);
        $this->category->method('getData')->willReturnMap([
            ['use_default', null, ['url_key' => 1]],
            ['row_id', null, $categoryId],
        ]);
        $this->category->method('dataHasChangedFor')->willReturn(true);

        $this->metadataPool->method('getMetadata')
            ->with(CategoryInterface::class)
            ->willReturn($this->entityMetaDataInterface);
        $this->entityMetaDataInterface->method('getLinkField')->willReturn('row_id');
        $this->getDefaultUrlKey->method('execute')->with($categoryId)->willReturn('one');
        $this->compositeUrlValidator->method('validate')->willReturn([]);

        $urlKeyAttribute = $this->createMock(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class);
        $urlKeyAttribute->method('getBackendTable')->willReturn('catalog_category_entity_varchar');
        $urlKeyAttribute->method('getAttributeId')->willReturn(120);
        $this->categoryResource->method('getAttribute')
            ->with('url_key')
            ->willReturn($urlKeyAttribute);
        $connection = $this->createMock(\Magento\Framework\DB\Adapter\AdapterInterface::class);
        $connection->expects($this->once())
            ->method('delete')
            ->with('catalog_category_entity_varchar', [
                'attribute_id = ?' => 120,
                'row_id = ?' => $categoryId,
                'store_id = ?' => $storeId,
            ]);
        $this->categoryResource->method('getConnection')->willReturn($connection);

        $directChild = $this->createPartialMockWithReflection(
            Category::class,
            ['getResource', 'getStore', 'getStoreId', 'setStoreId', 'getUrlPath', 'setUrlPath']
        );
        $directChild->setData(['id' => $directChildId, 'parent_id' => $categoryId, 'level' => 3]);
        $directChild->method('getResource')->willReturn($this->createMock(CategoryResource::class));

        $grandChild = $this->createPartialMockWithReflection(
            Category::class,
            ['getResource', 'getStore', 'getStoreId', 'setStoreId', 'getUrlPath', 'setUrlPath']
        );
        $grandChild->setData(['id' => $grandChildId, 'parent_id' => $directChildId, 'level' => 4]);
        $grandChild->method('getResource')->willReturn($this->createMock(CategoryResource::class));

        // Discovery order is reversed (grandchild before direct child) to prove level-based sorting.
        $this->childrenCategoriesProvider->expects($this->once())
            ->method('getChildren')
            ->willReturn([$grandChild, $directChild]);

        $processed = [];
        $this->categoryUrlPathGenerator->method('getUrlPath')
            ->willReturnCallback(function ($cat, $parent = null) use (&$processed, $directChildId, $grandChildId) {
                if ($cat->getId() === $directChildId || $cat->getId() === $grandChildId) {
                    $processed[] = ['id' => $cat->getId(), 'parent' => $parent];
                }
                return 'generated_url_path';
            });

        $this->categoryUrlPathAutogeneratorObserver->execute($this->observer);

        $this->assertSame($directChildId, $processed[0]['id']);
        $this->assertSame($this->category, $processed[0]['parent']);

        $this->assertSame($grandChildId, $processed[1]['id']);
        $this->assertSame(
            $directChild,
            $processed[1]['parent'],
            'Grandchild must be linked to the in-memory updated direct child, not left to resolve ' .
            'its own parent independently.'
        );
    }
}
