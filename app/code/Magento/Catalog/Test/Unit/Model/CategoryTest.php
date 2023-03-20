<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model;

use Magento\Catalog\Api\CategoryAttributeRepositoryInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\Indexer\Category\Flat\State;
use Magento\Catalog\Model\Indexer\Category\Product;
use Magento\Catalog\Model\ResourceModel\Category\Tree;
use Magento\Catalog\Model\ResourceModel\Category\TreeFactory;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\Framework\Api\AttributeValue;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\MetadataObjectInterface;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ResourceModel\Store\CollectionFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategoryTest extends TestCase
{
    /**
     * @var Category
     */
    private $category;

    /**
     * @var Registry|MockObject
     */
    private $registry;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var Tree|MockObject
     */
    private $categoryTreeResource;

    /**
     * @var MockObject
     */
    private $categoryTreeFactory;

    /**
     * @var CategoryRepositoryInterface|MockObject
     */
    private $categoryRepository;

    /**
     * @var MockObject
     */
    private $storeCollectionFactory;

    /**
     * @var UrlInterface|MockObject
     */
    private $url;

    /**
     * @var MockObject
     */
    private $productCollectionFactory;

    /**
     * @var Config|MockObject
     */
    private $catalogConfig;

    /**
     * @var FilterManager|MockObject
     */
    private $filterManager;

    /**
     * @var State|MockObject
     */
    private $flatState;

    /**
     * @var IndexerInterface|MockObject
     */
    private $flatIndexer;

    /**
     * @var IndexerInterface|MockObject
     */
    private $productIndexer;

    /**
     * @var CategoryUrlPathGenerator|MockObject
     */
    private $categoryUrlPathGenerator;

    /**
     * @var UrlFinderInterface|MockObject
     */
    private $urlFinder;

    /**
     * @var AbstractResource|MockObject
     */
    private $resource;

    /**
     * @var IndexerRegistry|MockObject
     */
    private $indexerRegistry;

    /**
     * @var CategoryAttributeRepositoryInterface|MockObject
     */
    private $metadataServiceMock;

    /**
     * @var MockObject
     */
    private $attributeValueFactory;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->registry = $this->createMock(Registry::class);
        $this->storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->categoryTreeResource = $this->createMock(Tree::class);
        $this->categoryTreeFactory = $this->createPartialMock(
            TreeFactory::class,
            ['create']
        );
        $this->categoryRepository = $this->getMockForAbstractClass(CategoryRepositoryInterface::class);
        $this->storeCollectionFactory = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $this->url = $this->getMockForAbstractClass(UrlInterface::class);
        $this->productCollectionFactory = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class,
            ['create']
        );
        $this->catalogConfig = $this->createMock(Config::class);
        $this->filterManager = $this->getMockBuilder(FilterManager::class)
            ->addMethods(['translitUrl'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->flatState = $this->createMock(State::class);
        $this->flatIndexer = $this->getMockForAbstractClass(IndexerInterface::class);
        $this->productIndexer = $this->getMockForAbstractClass(IndexerInterface::class);
        $this->categoryUrlPathGenerator = $this->createMock(
            CategoryUrlPathGenerator::class
        );
        $this->urlFinder = $this->getMockForAbstractClass(UrlFinderInterface::class);
        $this->resource = $this->createMock(\Magento\Catalog\Model\ResourceModel\Category::class);
        $this->indexerRegistry = $this->createPartialMock(IndexerRegistry::class, ['get']);

        $this->metadataServiceMock = $this->createMock(
            CategoryAttributeRepositoryInterface::class
        );
        $this->attributeValueFactory = $this->getMockBuilder(AttributeValueFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->category = $this->getCategoryModel();
    }

    /**
     * @return void
     */
    public function testFormatUrlKey(): void
    {
        $strIn = 'Some string';
        $resultString = 'some';

        $this->filterManager->expects($this->once())->method('translitUrl')->with($strIn)
            ->willReturn($resultString);

        $this->assertEquals($resultString, $this->category->formatUrlKey($strIn));
    }

    /**
     * @return void
     */
    public function testMoveWhenCannotFindParentCategory(): void
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Sorry, but we can\'t find the new parent category you selected.');
        $this->markTestSkipped('MAGETWO-31165');
        $parentCategory = $this->createPartialMock(
            Category::class,
            ['getId', 'setStoreId', 'load']
        );
        $parentCategory->expects($this->any())->method('setStoreId')->willReturnSelf();
        $parentCategory->expects($this->any())->method('load')->willReturnSelf();
        $this->categoryRepository->expects($this->any())->method('get')->willReturn($parentCategory);

        $store = $this->createMock(Store::class);
        $this->storeManager->expects($this->any())->method('getStore')->willReturn($store);

        $this->category->move(1, 2);
    }

    /**
     * @return void
     */
    public function testMoveWhenCannotFindNewCategory(): void
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Sorry, but we can\'t find the new category you selected.');
        $parentCategory = $this->createPartialMock(
            Category::class,
            ['getId', 'setStoreId', 'load']
        );
        $parentCategory->expects($this->any())->method('getId')->willReturn(5);
        $parentCategory->expects($this->any())->method('setStoreId')->willReturnSelf();
        $parentCategory->expects($this->any())->method('load')->willReturnSelf();
        $this->categoryRepository->expects($this->any())->method('get')->willReturn($parentCategory);

        $store = $this->createMock(Store::class);
        $this->storeManager->expects($this->any())->method('getStore')->willReturn($store);

        $this->category->move(1, 2);
    }

    /**
     * @return void
     */
    public function testMoveWhenParentCategoryIsSameAsChildCategory(): void
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage(
            'We can\'t move the category because the parent category name matches the child category name.'
        );
        $this->markTestSkipped('MAGETWO-31165');
        $parentCategory = $this->createPartialMock(
            Category::class,
            ['getId', 'setStoreId', 'load']
        );
        $parentCategory->expects($this->any())->method('getId')->willReturn(5);
        $parentCategory->expects($this->any())->method('setStoreId')->willReturnSelf();
        $parentCategory->expects($this->any())->method('load')->willReturnSelf();
        $this->categoryRepository->expects($this->any())->method('get')->willReturn($parentCategory);

        $store = $this->createMock(Store::class);
        $this->storeManager->expects($this->any())->method('getStore')->willReturn($store);

        $this->category->setId(5);
        $this->category->move(1, 2);
    }

    /**
     * @return void
     */
    public function testMovePrimaryWorkflow(): void
    {
        $indexer = $this->getMockBuilder(\stdClass::class)->addMethods(['isScheduled'])
            ->disableOriginalConstructor()
            ->getMock();
        $indexer->expects($this->once())->method('isScheduled')->willReturn(true);
        $this->indexerRegistry->expects($this->once())
            ->method('get')
            ->with('catalog_category_product')
            ->willReturn($indexer);
        $parentCategory = $this->createPartialMock(
            Category::class,
            ['getId', 'setStoreId', 'load']
        );
        $parentCategory->expects($this->any())->method('getId')->willReturn(5);
        $parentCategory->expects($this->any())->method('setStoreId')->willReturnSelf();
        $parentCategory->expects($this->any())->method('load')->willReturnSelf();
        $this->categoryRepository->expects($this->any())->method('get')->willReturn($parentCategory);

        $store = $this->createMock(Store::class);
        $this->storeManager->expects($this->any())->method('getStore')->willReturn($store);

        $this->category->setId(3);
        $this->category->move(5, 7);
    }

    /**
     * @return void
     */
    public function testGetUseFlatResourceFalse(): void
    {
        $this->assertFalse($this->category->getUseFlatResource());
    }

    /**
     * @return void
     */
    public function testGetUseFlatResourceTrue(): void
    {
        $this->flatState->expects($this->any())
            ->method('isAvailable')
            ->willReturn(true);

        $category = $this->getCategoryModel();
        $this->assertTrue($category->getUseFlatResource());
    }

    /**
     * @return object
     */
    protected function getCategoryModel(): object
    {
        return $this->objectManager->getObject(
            Category::class,
            [
                'registry' => $this->registry,
                'storeManager' => $this->storeManager,
                'categoryTreeResource' => $this->categoryTreeResource,
                'categoryTreeFactory' => $this->categoryTreeFactory,
                'categoryRepository' => $this->categoryRepository,
                'storeCollectionFactory' => $this->storeCollectionFactory,
                'url' => $this->url,
                'productCollectionFactory' => $this->productCollectionFactory,
                'catalogConfig' => $this->catalogConfig,
                'filter' => $this->filterManager,
                'flatState' => $this->flatState,
                'flatIndexer' => $this->flatIndexer,
                'productIndexer' => $this->productIndexer,
                'categoryUrlPathGenerator' => $this->categoryUrlPathGenerator,
                'urlFinder' => $this->urlFinder,
                'resource' => $this->resource,
                'indexerRegistry' => $this->indexerRegistry,
                'metadataService' => $this->metadataServiceMock,
                'customAttributeFactory' => $this->attributeValueFactory
            ]
        );
    }

    /**
     * @return array
     */
    public function reindexFlatEnabledTestDataProvider(): array
    {
        return [
            'set 1' => [false, false, 1, 1],
            'set 2' => [true,  false, 0, 1],
            'set 3' => [false, true,  1, 0],
            'set 4' => [true,  true,  0, 0]
        ];
    }

    /**
     * @param $flatScheduled
     * @param $productScheduled
     * @param $expectedFlatReindexCalls
     * @param $expectedProductReindexCall
     *
     * @return void
     * @dataProvider reindexFlatEnabledTestDataProvider
     */
    public function testReindexFlatEnabled(
        $flatScheduled,
        $productScheduled,
        $expectedFlatReindexCalls,
        $expectedProductReindexCall
    ): void {
        $affectedProductIds = ["1", "2"];
        $this->category->setAffectedProductIds($affectedProductIds);
        $pathIds = ['path/1/2', 'path/2/3'];
        $this->category->setData('path_ids', $pathIds);
        $this->category->setId('123');

        $this->flatState->expects($this->any())
            ->method('isFlatEnabled')
            ->willReturn(true);

        $this->flatIndexer->expects($this->exactly(1))
            ->method('isScheduled')
            ->willReturn($flatScheduled);
        $this->flatIndexer->expects($this->exactly($expectedFlatReindexCalls))->method('reindexList')->with(['123']);

        $this->productIndexer->expects($this->exactly(1))
            ->method('isScheduled')
            ->willReturn($productScheduled);
        $this->productIndexer->expects($this->exactly($expectedProductReindexCall))
            ->method('reindexList')
            ->with($pathIds);

        $this->indexerRegistry
            ->method('get')
            ->withConsecutive([State::INDEXER_ID], [Product::INDEXER_ID])
            ->willReturnOnConsecutiveCalls($this->flatIndexer, $this->productIndexer);

        $this->category->reindex();
    }

    /**
     * @return array
     */
    public function reindexFlatDisabledTestDataProvider(): array
    {
        return [
            [false, null, null, null, null, null, 0],
            [true, null, null, null, null, null,  0],
            [false, [], null, null, null, null, 0],
            [false, ["1", "2"], null, null, null, null, 1],
            [false, null, 1, null, null, null, 1],
            [false, ["1", "2"], 0, 1, null, null,  1],
            [false, null, 1, 1, null, null, 0],
            [false, ["1", "2"], null, null, 0, 1,  1],
            [false, ["1", "2"], null, null, 1, 0,  1]
        ];
    }

    /**
     * @param bool $productScheduled
     * @param array $affectedIds
     * @param int|string $isAnchorOrig
     * @param int|string $isAnchor
     * @param int $expectedProductReindexCall
     *
     * @return void
     * @dataProvider reindexFlatDisabledTestDataProvider
     */
    public function testReindexFlatDisabled(
        $productScheduled,
        $affectedIds,
        $isAnchorOrig,
        $isAnchor,
        $isActiveOrig,
        $isActive,
        $expectedProductReindexCall
    ): void {
        $this->category->setAffectedProductIds($affectedIds);
        $this->category->setData('is_anchor', $isAnchor);
        $this->category->setOrigData('is_anchor', $isAnchorOrig);
        $this->category->setData('is_active', $isActive);
        $this->category->setOrigData('is_active', $isActiveOrig);

        $this->category->setAffectedProductIds($affectedIds);

        $pathIds = ['path/1/2', 'path/2/3'];
        $this->category->setData('path_ids', $pathIds);
        $this->category->setId('123');

        $this->flatState->expects($this->any())
            ->method('isFlatEnabled')
            ->willReturn(false);

        $this->productIndexer
            ->method('isScheduled')
            ->willReturn($productScheduled);
        $this->productIndexer->expects($this->exactly($expectedProductReindexCall))
            ->method('reindexList')
            ->with($pathIds);

        $this->indexerRegistry
            ->method('get')
            ->with(Product::INDEXER_ID)
            ->willReturn($this->productIndexer);

        $this->category->reindex();
    }

    /**
     * @return void
     */
    public function testGetCustomAttributes(): void
    {
        $interfaceAttributeCode = 'name';
        $customAttributeCode = 'description';
        $initialCustomAttributeValue = 'initial description';
        $newCustomAttributeValue = 'new description';

        $interfaceAttribute = $this->getMockForAbstractClass(MetadataObjectInterface::class);
        $interfaceAttribute->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn($interfaceAttributeCode);
        $colorAttribute = $this->getMockForAbstractClass(MetadataObjectInterface::class);
        $colorAttribute->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn($customAttributeCode);
        $customAttributesMetadata = [$interfaceAttribute, $colorAttribute];

        $this->metadataServiceMock->expects($this->once())
            ->method('getCustomAttributesMetadata')
            ->willReturn($customAttributesMetadata);
        $this->category->setData($interfaceAttributeCode, 10);

        //The description attribute is not set, expect empty custom attribute array
        $this->assertEquals([], $this->category->getCustomAttributes());

        //Set the description attribute;
        $this->category->setData($customAttributeCode, $initialCustomAttributeValue);
        $attributeValue = new AttributeValue();
        $attributeValue2 = new AttributeValue();
        $this->attributeValueFactory->expects($this->exactly(2))->method('create')
            ->willReturnOnConsecutiveCalls($attributeValue, $attributeValue2);
        $this->assertCount(1, $this->category->getCustomAttributes());
        $this->assertNotNull($this->category->getCustomAttribute($customAttributeCode));
        $this->assertEquals(
            $initialCustomAttributeValue,
            $this->category->getCustomAttribute($customAttributeCode)->getValue()
        );

        //Change the attribute value, should reflect in getCustomAttribute
        $this->category->setCustomAttribute($customAttributeCode, $newCustomAttributeValue);
        $this->assertCount(1, $this->category->getCustomAttributes());
        $this->assertNotNull($this->category->getCustomAttribute($customAttributeCode));
        $this->assertEquals(
            $newCustomAttributeValue,
            $this->category->getCustomAttribute($customAttributeCode)->getValue()
        );
    }

    /**
     * @return array
     */
    public function getImageWithAttributeCodeDataProvider(): array
    {
        return [
            ['testimage', 'http://www.example.com/catalog/category/testimage'],
            [false, false]
        ];
    }

    /**
     * @param string|bool $value
     * @param string|bool $url
     *
     * @return void
     * @dataProvider getImageWithAttributeCodeDataProvider
     */
    public function testGetImageWithAttributeCode($value, $url): void
    {
        $storeManager = $this->createPartialMock(StoreManager::class, ['getStore']);
        $store = $this->createPartialMock(Store::class, ['getBaseUrl']);

        $storeManager->expects($this->any())
            ->method('getStore')
            ->willReturn($store);

        $store->expects($this->any())
            ->method('getBaseUrl')
            ->with(UrlInterface::URL_TYPE_MEDIA)
            ->willReturn('http://www.example.com/');

        /** @var Category $model */
        $model = $this->objectManager->getObject(
            Category::class,
            [
                'storeManager' => $storeManager
            ]
        );

        $model->setData('attribute1', $value);

        $result = $model->getImageUrl('attribute1');

        $this->assertEquals($url, $result);
    }

    /**
     * return void
     */
    public function testGetImageWithoutAttributeCode(): void
    {
        $storeManager = $this->createPartialMock(StoreManager::class, ['getStore']);
        $store = $this->createPartialMock(Store::class, ['getBaseUrl']);

        $storeManager->expects($this->any())
            ->method('getStore')
            ->willReturn($store);

        $store->expects($this->any())
            ->method('getBaseUrl')
            ->with(UrlInterface::URL_TYPE_MEDIA)
            ->willReturn('http://www.example.com/');

        /** @var Category $model */
        $model = $this->objectManager->getObject(Category::class, [
            'storeManager' => $storeManager
        ]);

        $model->setData('image', 'myimage');

        $result = $model->getImageUrl();

        $this->assertEquals('http://www.example.com/catalog/category/myimage', $result);
    }
}
