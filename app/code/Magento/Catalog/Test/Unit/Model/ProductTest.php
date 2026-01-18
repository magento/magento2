<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface;
use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterfaceFactory;
use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductLinkRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\FilterProductCustomAttribute;
use Magento\Catalog\Model\Indexer\Product\Flat\Processor;
use Magento\Catalog\Api\Data\ProductLinkExtensionFactory;
use Magento\Catalog\Api\Data\ProductLinkInterface;
use Magento\Catalog\Api\Data\ProductLinkInterfaceFactory;
use Magento\Catalog\Helper\Product as HelperProduct;
use Magento\Catalog\Model\Indexer\Product\Category as CategoryIndexer;
use Magento\Catalog\Model\Indexer\Product\Eav\Processor as EavProcessor;
use Magento\Catalog\Model\Indexer\Product\Price\Processor as PriceProcessor;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Backend\Media\EntryConverterPool;
use Magento\Catalog\Model\Product\Attribute\Backend\Media\ImageEntryConverter;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Configuration\Item\OptionFactory as ItemOptionFactory;
use Magento\Catalog\Model\Product\Image\Cache;
use Magento\Catalog\Model\Product\Image\CacheFactory;
use Magento\Catalog\Model\Product\Link as ProductLink;
use Magento\Catalog\Model\Product\LinkTypeProvider;
use Magento\Catalog\Model\Product\Media\Config as MediaConfig;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\OptionFactory;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Catalog\Model\Product\Type\Price;
use Magento\Catalog\Model\Product\Type\Simple;
use Magento\Catalog\Model\Product\Type\Virtual;
use Magento\Catalog\Model\Product\Url as ProductUrl;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductLink\CollectionProvider as ProductLinkCollectionProvider;
use Magento\Catalog\Model\ProductLink\Link;
use Magento\Catalog\Model\ResourceModel\Product as ProductResourceMOdel;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Api\AttributeValue;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\Data\ImageContentInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Api\ExtensionAttributesInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\State;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\CollectionFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Model\ActionValidator\RemoveAction;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Module\Manager;
use Magento\Framework\Pricing\PriceInfo\Base;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class ProductTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var MockObject
     */
    protected $productLinkRepositoryMock;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var Product
     */
    protected $model;

    /**
     * @var Manager|MockObject
     */
    protected $moduleManager;

    /**
     * @var MockObject
     */
    protected $stockItemFactoryMock;

    /**
     * @var IndexerInterface|MockObject
     */
    protected $categoryIndexerMock;

    /**
     * @var Processor|MockObject
     */
    protected $productFlatProcessor;

    /**
     * @var PriceProcessor|MockObject
     */
    protected $productPriceProcessor;

    /**
     * @var Product\Type|MockObject
     */
    protected $productTypeInstanceMock;

    /**
     * @var Product\Option|MockObject
     */
    protected $optionInstanceMock;

    /**
     * @var Base|MockObject
     */
    protected $_priceInfoMock;

    /**
     * @var FilterProductCustomAttribute|MockObject
     */
    private $filterCustomAttribute;

    /**
     * @var Store|MockObject
     */
    private $store;

    /**
     * @var ProductResourceMOdel|MockObject
     */
    private $resource;

    /**
     * @var Registry|MockObject
     */
    private $registry;

    /**
     * @var Category|MockObject
     */
    private $category;

    /**
     * @var Website|MockObject
     */
    private $website;

    /**
     * @var IndexerRegistry|MockObject
     */
    protected $indexerRegistryMock;

    /**
     * @var CategoryRepositoryInterface|MockObject
     */
    private $categoryRepository;

    /**
     * @var HelperProduct|MockObject
     */
    private $_catalogProduct;

    /**
     * @var Cache|MockObject
     */
    protected $imageCache;

    /**
     * @var CacheFactory|MockObject
     */
    protected $imageCacheFactory;

    /**
     * @var MockObject
     */
    protected $mediaGalleryEntryFactoryMock;

    /**
     * @var MockObject
     */
    protected $dataObjectHelperMock;

    /**
     * @var MockObject
     */
    protected $metadataServiceMock;

    /**
     * @var MockObject
     */
    protected $attributeValueFactory;

    /**
     * @var MockObject
     */
    protected $mediaGalleryEntryConverterPoolMock;

    /**
     * @var MockObject
     */
    protected $converterMock;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $eventManagerMock;

    /** @var MockObject */
    protected $mediaConfig;

    /**
     * @var State|MockObject
     */
    private $appStateMock;

    /**
     * @var MockObject
     */
    private $extensionAttributes;

    /**
     * @var MockObject
     */
    private $extensionAttributesFactory;

    /**
     * @var Filesystem
     */
    private $filesystemMock;

    /**
     * @var CollectionFactory
     */
    private $collectionFactoryMock;

    /**
     * @var ProductExtensionInterface|MockObject
     */
    private $productExtAttributes;

    /**
     * @var Config|MockObject
     */
    private $eavConfig;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->categoryIndexerMock = $this->createMock(IndexerInterface::class);

        $this->moduleManager = $this->createPartialMock(
            Manager::class,
            ['isEnabled']
        );
        $this->extensionAttributes = $this->createPartialMockWithReflection(
            ProductExtensionInterface::class,
            ['getStockItem']
        );

        $this->stockItemFactoryMock = $this->createPartialMock(
            StockItemInterfaceFactory::class,
            ['create']
        );
        $this->dataObjectHelperMock = $this->createMock(DataObjectHelper::class);
        $this->productFlatProcessor = $this->createMock(
            Processor::class
        );

        $this->_priceInfoMock = $this->createMock(Base::class);
        $this->productTypeInstanceMock = $this->createMock(Type::class);
        $this->productPriceProcessor = $this->createMock(PriceProcessor::class);

        $this->appStateMock = $this->createPartialMock(
            State::class,
            ['getAreaCode', 'isAreaCodeEmulated']
        );
        $this->appStateMock->method('getAreaCode')->willReturn(FrontNameResolver::AREA_CODE);
      
        $this->eventManagerMock = $this->createStub(ManagerInterface::class);
        $actionValidatorMock = $this->createMock(
            RemoveAction::class
        );
        $actionValidatorMock->method('isAllowed')->willReturn(true);
        $cacheInterfaceMock = $this->createStub(CacheInterface::class);

        $contextMock = $this->createPartialMock(
            Context::class,
            ['getEventDispatcher', 'getCacheManager', 'getAppState', 'getActionValidator']
        );
      
        $contextMock->method('getAppState')->willReturn($this->appStateMock);
        $contextMock->method('getEventDispatcher')->willReturn($this->eventManagerMock);
        $contextMock->method('getCacheManager')->willReturn($cacheInterfaceMock);
        $contextMock->method('getActionValidator')->willReturn($actionValidatorMock);

        $this->optionInstanceMock = $this->createPartialMock(Option::class, ['setProduct', '__sleep']);

        $optionFactory = $this->createPartialMock(
            OptionFactory::class,
            ['create']
        );
        $optionFactory->method('create')->willReturn($this->optionInstanceMock);

        $this->resource = $this->createMock(ProductResourceMOdel::class);

        $this->registry = $this->createMock(Registry::class);

        $this->category = $this->createMock(Category::class);

        $this->store = $this->createMock(Store::class);

        $this->website = $this->createMock(Website::class);

        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->storeManager->method('getStore')->willReturn($this->store);
        $this->storeManager->method('getWebsite')->willReturn($this->website);
        $this->indexerRegistryMock = $this->createPartialMock(
            IndexerRegistry::class,
            ['get']
        );
        $this->categoryRepository = $this->createMock(CategoryRepositoryInterface::class);

        $this->_catalogProduct = $this->createPartialMock(
            HelperProduct::class,
            ['isDataForProductCategoryIndexerWasChanged', 'isDataForPriceIndexerWasChanged']
        );

        $this->imageCache = $this->createMock(Cache::class);
        $this->imageCacheFactory = $this->createPartialMock(CacheFactory::class, ['create']);

        $this->mediaGalleryEntryFactoryMock =
            $this->createPartialMock(ProductAttributeMediaGalleryEntryInterfaceFactory::class, ['create']);

        $this->metadataServiceMock = $this->createMock(ProductAttributeRepositoryInterface::class);
        $this->attributeValueFactory = $this->createMock(AttributeValueFactory::class);

        $this->mediaGalleryEntryConverterPoolMock =
            $this->createPartialMock(
                EntryConverterPool::class,
                ['getConverterByMediaType']
            );

        $this->converterMock =
            $this->createMock(
                ImageEntryConverter::class
            );
        $this->mediaGalleryEntryConverterPoolMock->method('getConverterByMediaType')->willReturn($this->converterMock);
        $this->productLinkRepositoryMock = $this->createMock(ProductLinkRepositoryInterface::class);
        $this->extensionAttributesFactory = $this->createMock(ExtensionAttributesFactory::class);
        $this->filesystemMock = $this->createMock(Filesystem::class);
        $this->collectionFactoryMock = $this->createPartialMock(CollectionFactory::class, ['create']);
        $this->mediaConfig = $this->createMock(MediaConfig::class);
        $this->eavConfig = $this->createMock(Config::class);

        $this->productExtAttributes = $this->createPartialMockWithReflection(
            ProductExtensionInterface::class,
            ['getStockItem', 'setConfigurableProductLinks']
        );
        $this->extensionAttributesFactory
            ->method('create')->willReturn($this->productExtAttributes);

        $this->filterCustomAttribute = $this->createMock(
            FilterProductCustomAttribute::class
        );
        $this->filterCustomAttribute->expects($this->any())
            ->method('execute')
            ->willReturnCallback(function ($attributes) {
                // Return the attributes as-is for the test
                return $attributes;
            });

        $this->model = new Product(
            $contextMock,
            $this->registry,
            $this->extensionAttributesFactory,
            $this->attributeValueFactory,
            $this->storeManager,
            $this->metadataServiceMock,
            $this->createMock(ProductUrl::class),
            $this->createMock(ProductLink::class),
            $this->createMock(ItemOptionFactory::class),
            $this->stockItemFactoryMock,
            $optionFactory,
            $this->createMock(Visibility::class),
            $this->createMock(Status::class),
            $this->mediaConfig,
            $this->productTypeInstanceMock,
            $this->moduleManager,
            $this->_catalogProduct,
            $this->resource,
            $this->createMock(ProductCollection::class),
            $this->collectionFactoryMock,
            $this->filesystemMock,
            $this->indexerRegistryMock,
            $this->productFlatProcessor,
            $this->productPriceProcessor,
            $this->createMock(EavProcessor::class),
            $this->categoryRepository,
            $this->imageCacheFactory,
            $this->createMock(ProductLinkCollectionProvider::class),
            $this->createMock(LinkTypeProvider::class),
            $this->createMock(ProductLinkInterfaceFactory::class),
            $this->createMock(ProductLinkExtensionFactory::class),
            $this->mediaGalleryEntryConverterPoolMock,
            $this->dataObjectHelperMock,
            $this->createMock(JoinProcessorInterface::class),
            ['id' => 1],
            $this->eavConfig,
            $this->filterCustomAttribute
        );
    }

    /**
     * @return void
     */
    public function testGetAttributes(): void
    {
        $productType = $this->createPartialMock(AbstractType::class, ['getSetAttributes','deleteTypeSpecificData']);
        $this->productTypeInstanceMock->method('factory')->willReturn(
            $productType
        );
        $attribute = $this->createPartialMock(AbstractAttribute::class, ['isInGroup']);
        $attribute->method('isInGroup')->willReturn(true);
        $productType->method('getSetAttributes')->willReturn(
            [$attribute]
        );
        $expect = [$attribute];
        $this->assertEquals($expect, $this->model->getAttributes(5));
        $this->assertEquals($expect, $this->model->getAttributes());
    }

    /**
     * @return void
     */
    public function testGetStoreIds(): void
    {
        $expectedStoreIds = [1, 2, 3];
        $websiteIds = ['test'];
        $this->model->setWebsiteIds($websiteIds);
        $this->website->expects($this->once())->method('getStoreIds')->willReturn($expectedStoreIds);
        $this->assertEquals($expectedStoreIds, $this->model->getStoreIds());
    }

    /**
     * @param bool $isObjectNew
     *
     * @return void
     */
    #[DataProvider('getSingleStoreIds')]
    public function testGetStoreSingleSiteModelIds(bool $isObjectNew): void
    {
        $websiteIDs = [0 => 2];
        $this->model->setWebsiteIds(!$isObjectNew ? $websiteIDs : array_flip($websiteIDs));

        $this->model->isObjectNew($isObjectNew);

        $this->website->expects($this->once())
            ->method('getStoreIds')
            ->willReturn($websiteIDs);

        $this->assertEquals($websiteIDs, $this->model->getStoreIds());
    }

    /**
     * @return array
     */
    public static function getSingleStoreIds(): array
    {
        return [
            [
                false
            ],
            [
                true
            ],
        ];
    }

    /**
     * @return void
     */
    public function testGetStoreId(): void
    {
        $this->model->setStoreId(3);
        $this->assertEquals(3, $this->model->getStoreId());
        $this->model->unsStoreId();
        $this->store->expects($this->once())->method('getId')->willReturn(5);
        $this->assertEquals(5, $this->model->getStoreId());
    }

    /**
     * @return void
     */
    public function testGetCategoryCollection(): void
    {
        $collection = $this->createMock(Collection::class);
        $this->resource->expects($this->once())->method('getCategoryCollection')->willReturn($collection);
        $this->assertInstanceOf(Collection::class, $this->model->getCategoryCollection());
    }

    /**
     * @return void
     */
    #[DataProvider('getCategoryCollectionCollectionNullDataProvider')]
    public function testGetCategoryCollectionCollectionNull(
        $initCategoryCollection,
        $getIdResult,
        $productIdCached
    ): void {
        $product = $this->createPartialMock(
            Product::class,
            [
                '_getResource',
                'setCategoryCollection',
                'getId'
            ]
        );

        $abstractDbMock = $this->createPartialMockWithReflection(
            AbstractDb::class,
            ['getCategoryCollection', '_construct']
        );
        $getCategoryCollectionMock = $this->createMock(
            Collection::class
        );
        $product
            ->expects($this->once())
            ->method('setCategoryCollection')
            ->with($getCategoryCollectionMock);
        $product
            ->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($getIdResult);
        $abstractDbMock
            ->expects($this->once())
            ->method('getCategoryCollection')
            ->with($product)
            ->willReturn($getCategoryCollectionMock);
        $product
            ->expects($this->once())
            ->method('_getResource')
            ->willReturn($abstractDbMock);

        $this->setPropertyValue($product, 'categoryCollection', $initCategoryCollection);
        $this->setPropertyValue($product, '_productIdCached', $productIdCached);

        $result = $product->getCategoryCollection();

        $productIdCachedActual = $this->getPropertyValue($product, '_productIdCached');
        $this->assertEquals($getIdResult, $productIdCachedActual);
        $this->assertEquals($initCategoryCollection, $result);
    }

    /**
     * @return array
     */
    public static function getCategoryCollectionCollectionNullDataProvider(): array
    {
        return [
            [
                'initCategoryCollection' => null,
                'getIdResult' => 'getIdResult value',
                'productIdCached' => 'productIdCached value'
            ],
            [
                'initCategoryCollection' => 'value',
                'getIdResult' => 'getIdResult value',
                'productIdCached' => 'not getIdResult value'
            ]
        ];
    }

    /**
     * @return void
     */
    public function testSetCategoryCollection(): void
    {
        $collection = $this->createMock(Collection::class);
        $this->resource->expects($this->once())->method('getCategoryCollection')->willReturn($collection);
        $this->assertSame($this->model->getCategoryCollection(), $this->model->getCategoryCollection());
    }

    /**
     * @return void
     */
    public function testGetCategory(): void
    {
        $this->model->setData('category_ids', [10]);
        $this->category->method('getId')->willReturn(10);
        $this->registry->method('registry')->willReturn($this->category);
        $this->categoryRepository->method('get')->willReturn($this->category);
        $this->assertInstanceOf(Category::class, $this->model->getCategory());
    }

    /**
     * @return void
     */
    public function testGetCategoryId(): void
    {
        $this->model->setData('category_ids', [10]);
        $this->category->method('getId')->willReturn(10);

        $this->registry
            ->method('registry')
            ->willReturnOnConsecutiveCalls(null, $this->category);
        $this->assertFalse($this->model->getCategoryId());
        $this->assertEquals(10, $this->model->getCategoryId());
    }

    /**
     * @return void
     */
    public function testGetIdBySku(): void
    {
        $this->resource->expects($this->once())->method('getIdBySku')->willReturn(5);
        $this->assertEquals(5, $this->model->getIdBySku('someSku'));
    }

    /**
     * @return void
     */
    public function testGetCategoryIds(): void
    {
        $this->model->lockAttribute('category_ids');
        $this->assertEquals([], $this->model->getCategoryIds());
    }

    /**
     * @return void
     */
    public function testGetStatusInitial(): void
    {
        $this->assertEquals(Status::STATUS_ENABLED, $this->model->getStatus());
    }

    /**
     * @return void
     */
    public function testGetStatus(): void
    {
        $this->model->setStatus(null);
        $this->assertEquals(Status::STATUS_ENABLED, $this->model->getStatus());
    }

    /**
     * @return void
     */
    public function testIsInStock(): void
    {
        $this->model->setStatus(Status::STATUS_ENABLED);
        $this->assertTrue($this->model->isInStock());
    }

    /**
     * @return void
     */
    public function testIndexerAfterDeleteCommitProduct(): void
    {
        $this->model->isDeleted(true);
        $this->categoryIndexerMock->expects($this->once())->method('reindexRow');
        $this->productFlatProcessor->expects($this->once())->method('reindexRow');
        $this->productPriceProcessor->expects($this->once())->method('reindexRow');
        $this->prepareCategoryIndexer();
        $this->model->afterDeleteCommit();
    }

    /**
     * @param $productChanged
     * @param $isScheduled
     * @param $productFlatCount
     * @param $categoryIndexerCount
     *
     * @return void
     */
    #[DataProvider('getProductReindexProvider')]
    public function testReindex($productChanged, $isScheduled, $productFlatCount, $categoryIndexerCount): void
    {
        $this->model->setData('entity_id', 1);
        $this->_catalogProduct->expects($this->once())
            ->method('isDataForProductCategoryIndexerWasChanged')
            ->willReturn($productChanged);
        if ($productChanged) {
            $this->indexerRegistryMock->expects($this->exactly($productFlatCount))
                ->method('get')
                ->with(CategoryIndexer::INDEXER_ID)
                ->willReturn($this->categoryIndexerMock);
            $this->categoryIndexerMock->method('isScheduled')->willReturn($isScheduled);
            $this->categoryIndexerMock->expects($this->exactly($categoryIndexerCount))->method('reindexRow');
        }
        $this->productFlatProcessor->expects($this->exactly($productFlatCount))->method('reindexRow');
        $this->model->reindex();
    }

    /**
     * @return array
     */
    public static function getProductReindexProvider(): array
    {
        return [
            'set 1' => [true, false, 1, 1],
            'set 2' => [true, true, 1, 0],
            'set 3' => [false, false, 1, 0]
        ];
    }

    /**
     * @return void
     */
    public function testPriceReindexCallback(): void
    {

        // Configure the catalog product helper mock to return false for price indexer check
        $this->_catalogProduct->method('isDataForPriceIndexerWasChanged')->willReturn(false);
            
        $this->model = new Product(
            $this->createMock(Context::class),
            $this->registry,
            $this->extensionAttributesFactory,
            $this->attributeValueFactory,
            $this->storeManager,
            $this->metadataServiceMock,
            $this->createMock(ProductUrl::class),
            $this->createMock(ProductLink::class),
            $this->createMock(ItemOptionFactory::class),
            $this->stockItemFactoryMock,
            $this->createMock(OptionFactory::class),
            $this->createMock(Visibility::class),
            $this->createMock(Status::class),
            $this->createMock(MediaConfig::class),
            $this->productTypeInstanceMock,
            $this->moduleManager,
            $this->_catalogProduct,
            $this->resource,
            $this->createMock(ProductCollection::class),
            $this->collectionFactoryMock,
            $this->filesystemMock,
            $this->indexerRegistryMock,
            $this->productFlatProcessor,
            $this->productPriceProcessor,
            $this->createMock(EavProcessor::class),
            $this->categoryRepository,
            $this->imageCacheFactory,
            $this->createMock(ProductLinkCollectionProvider::class),
            $this->createMock(LinkTypeProvider::class),
            $this->createMock(ProductLinkInterfaceFactory::class),
            $this->createMock(ProductLinkExtensionFactory::class),
            $this->mediaGalleryEntryConverterPoolMock,
            $this->dataObjectHelperMock,
            $this->createMock(JoinProcessorInterface::class),
            ['id' => 1],
            $this->eavConfig,
            $this->filterCustomAttribute
        );
        $this->model->isObjectNew(true);
        $this->productPriceProcessor->expects($this->once())->method('reindexRow');
        $this->assertNull($this->model->priceReindexCallback());
    }

    /**
     * @param array $expected
     * @param array|null $origData
     * @param array $data
     * @param bool $isDeleted
     * @param bool $isNew
     *
     * @return void
     */
    #[DataProvider('getIdentitiesProvider')]
    public function testGetIdentities(
        array $expected,
        ?array $origData,
        array $data,
        bool $isDeleted = false,
        bool $isNew = false
    ): void {
        if (!empty($data['extension_attributes']) && is_callable($data['extension_attributes'])) {
            $data['extension_attributes'] = $data['extension_attributes']($this);
        }
        $this->model->setIdFieldName('id');
        if (is_array($origData)) {
            foreach ($origData as $key => $value) {
                $this->model->setOrigData($key, $value);
            }
        }
        foreach ($data as $key => $value) {
            $this->model->setData($key, $value);
        }
        $this->model->isDeleted($isDeleted);
        $this->model->isObjectNew($isNew);
        $this->assertEquals($expected, $this->model->getIdentities());
    }

    protected function getMockForExtensionAttribute()
    {
        $extensionAttributesMock = $this->createPartialMockWithReflection(
            ProductExtensionInterface::class,
            ['getStockItem']
        );
        $stockItemMock = $this->createStub(StockItemInterface::class);
        $extensionAttributesMock->method('getStockItem')->willReturn($stockItemMock);
        return $extensionAttributesMock;
    }
    /**
     * @return array
     */
    public static function getIdentitiesProvider(): array
    {
        $extensionAttributesMock = static fn (self $testCase)
        => $testCase->getMockForExtensionAttribute();

        return [
            'no changes' => [
                ['cat_p_1'],
                ['id' => 1, 'name' => 'value', 'category_ids' => [1]],
                ['id' => 1, 'name' => 'value', 'category_ids' => [1]]
            ],
            'new product' => self::getNewProductProviderData(),
            'new disabled product' => self::getNewDisabledProductProviderData(),
            'status and category change' => [
                [0 => 'cat_p_1', 1 => 'cat_c_p_1', 2 => 'cat_c_p_2'],
                ['id' => 1, 'name' => 'value', 'category_ids' => [1], 'status' => Status::STATUS_DISABLED],
                [
                    'id' => 1,
                    'name' => 'value',
                    'category_ids' => [2],
                    'status' => Status::STATUS_ENABLED,
                    'affected_category_ids' => [1, 2],
                    'is_changed_categories' => true
                ]
            ],
            'category change for disabled product' => [
                [0 => 'cat_p_1'],
                ['id' => 1, 'name' => 'value', 'category_ids' => [1], 'status' => Status::STATUS_DISABLED],
                [
                    'id' => 1,
                    'name' => 'value',
                    'category_ids' => [2],
                    'status' => Status::STATUS_DISABLED,
                    'affected_category_ids' => [1, 2],
                    'is_changed_categories' => true
                ]
            ],
            'status change to disabled' => [
                [0 => 'cat_p_1', 1 => 'cat_c_p_7'],
                ['id' => 1, 'name' => 'value', 'category_ids' => [7], 'status' => Status::STATUS_ENABLED],
                ['id' => 1, 'name' => 'value', 'category_ids' => [7], 'status' => Status::STATUS_DISABLED]
            ],
            'status change to enabled' => [
                [0 => 'cat_p_1', 1 => 'cat_c_p_7'],
                ['id' => 1, 'name' => 'value', 'category_ids' => [7], 'status' => Status::STATUS_DISABLED],
                ['id' => 1, 'name' => 'value', 'category_ids' => [7], 'status' => Status::STATUS_ENABLED]
            ],
            'status changed, category unassigned' => self::getStatusAndCategoryChangesData(),
            'no status changes' => [
                [0 => 'cat_p_1'],
                ['id' => 1, 'name' => 'value', 'category_ids' => [1], 'status' => Status::STATUS_ENABLED],
                ['id' => 1, 'name' => 'value', 'category_ids' => [1], 'status' => Status::STATUS_ENABLED]
            ],
            'no stock status changes' => self::getNoStockStatusChangesData($extensionAttributesMock),
            'no stock status data 1' => [
                [0 => 'cat_p_1'],
                ['id' => 1, 'name' => 'value', 'category_ids' => [1], 'status' => Status::STATUS_ENABLED],
                [
                    'id' => 1,
                    'name' => 'value',
                    'category_ids' => [1],
                    'status' => Status::STATUS_ENABLED,
                    ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY => $extensionAttributesMock
                ]
            ],
            'no stock status data 2' => [
                [0 => 'cat_p_1'],
                ['id' => 1, 'name' => 'value', 'category_ids' => [1], 'status' => Status::STATUS_ENABLED],
                [
                    'id' => 1,
                    'name' => 'value',
                    'category_ids' => [1],
                    'status' => Status::STATUS_ENABLED,
                    'stock_data' => ['is_in_stock' => true]
                ]
            ],
            'stock status changes for enabled product' => self::getStatusStockProviderData($extensionAttributesMock),
            'stock status changes for disabled product' => [
                [0 => 'cat_p_1'],
                ['id' => 1, 'name' => 'value', 'category_ids' => [1], 'status' => Status::STATUS_DISABLED],
                [
                    'id' => 1,
                    'name' => 'value',
                    'category_ids' => [1],
                    'status' => Status::STATUS_DISABLED,
                    'stock_data' => ['is_in_stock' => false],
                    ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY => $extensionAttributesMock
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public static function getStatusAndCategoryChangesData(): array
    {
        return [
            [0 => 'cat_p_1', 1 => 'cat_c_p_5'],
            ['id' => 1, 'name' => 'value', 'category_ids' => [5], 'status' => Status::STATUS_DISABLED],
            [
                'id' => 1,
                'name' => 'value',
                'category_ids' => [],
                'status' => Status::STATUS_ENABLED,
                'is_changed_categories' => true,
                'affected_category_ids' => [5]
            ]
        ];
    }

    /**
     * @param $extensionAttributesMock
     *
     * @return array
     */
    public static function getNoStockStatusChangesData($extensionAttributesMock): array
    {
        return [
            [0 => 'cat_p_1'],
            ['id' => 1, 'name' => 'value', 'category_ids' => [1], 'status' => Status::STATUS_ENABLED],
            [
                'id' => 1,
                'name' => 'value',
                'category_ids' => [1],
                'status' => Status::STATUS_ENABLED,
                'stock_data' => ['is_in_stock' => false],
                ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY => $extensionAttributesMock
            ]
        ];
    }

    /**
     * @return array
     */
    public static function getNewProductProviderData(): array
    {
        return [
            ['cat_p_1', 'cat_c_p_1', 'cat_p_new', 'rss_p_new'],
            null,
            [
                'id' => 1,
                'name' => 'value',
                'category_ids' => [1],
                'affected_category_ids' => [1],
                'is_changed_categories' => true
            ],
            false,
            true
        ];
    }

    /**
     * @return array
     */
    private static function getNewDisabledProductProviderData(): array
    {
        return [
            ['cat_p_1'],
            null,
            [
                'id' => 1,
                'name' => 'value',
                'category_ids' => [1],
                'status' => Status::STATUS_DISABLED,
                'affected_category_ids' => [1],
                'is_changed_categories' => true
            ],
            false,
            true
        ];
    }

    /**
     * @param $extensionAttributesMock
     *
     * @return array
     */
    private static function getStatusStockProviderData($extensionAttributesMock): array
    {
        return [
            [0 => 'cat_p_1', 1 => 'cat_c_p_1'],
            ['id' => 1, 'name' => 'value', 'category_ids' => [1], 'status' => Status::STATUS_ENABLED],
            [
                'id' => 1,
                'name' => 'value',
                'category_ids' => [1],
                'status' => Status::STATUS_ENABLED,
                'stock_data' => ['is_in_stock' => true],
                ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY => $extensionAttributesMock
            ]
        ];
    }

    /**
     * Test retrieving price Info.
     *
     * @return void
     */
    public function testGetPriceInfo(): void
    {
        $this->productTypeInstanceMock->expects($this->once())
            ->method('getPriceInfo')
            ->with($this->model)
            ->willReturn($this->_priceInfoMock);
        $this->assertEquals($this->model->getPriceInfo(), $this->_priceInfoMock);
    }

    /**
     * Test for set qty.
     *
     * @return void
     */
    public function testSetQty(): void
    {
        $this->productTypeInstanceMock->expects($this->exactly(2))
            ->method('getPriceInfo')
            ->with($this->model)
            ->willReturn($this->_priceInfoMock);

        //initialize the priceInfo field
        $this->model->getPriceInfo();
        //Calling setQty will reset the priceInfo field
        $this->assertEquals($this->model, $this->model->setQty(1));
        //Call the setQty method with the same qty, getPriceInfo should not be called this time
        $this->assertEquals($this->model, $this->model->setQty(1));
        $this->assertEquals($this->model->getPriceInfo(), $this->_priceInfoMock);
    }

    /**
     * Test reload PriceInfo.
     *
     * @return void
     */
    public function testReloadPriceInfo(): void
    {
        $this->productTypeInstanceMock->expects($this->exactly(2))
            ->method('getPriceInfo')
            ->with($this->model)
            ->willReturn($this->_priceInfoMock);
        $this->assertEquals($this->_priceInfoMock, $this->model->getPriceInfo());
        $this->assertEquals($this->_priceInfoMock, $this->model->reloadPriceInfo());
    }

    /**
     * Test for get qty.
     *
     * @return void
     */
    public function testGetQty(): void
    {
        $this->model->setQty(1);
        $this->assertEquals(1, $this->model->getQty());
    }

    /**
     *  Test for `save` method.
     *
     * @return void
     */
    public function testSave(): void
    {
        $collection = $this->createMock(Collection::class);
        $collection->method('count')->willReturn(1);
        $collection->method('getIterator')->willReturn(new \ArrayObject([]));
        $this->collectionFactoryMock->method('create')->willReturn($collection);
        $this->model->setIsDuplicate(false);
        $this->configureSaveTest();
        $this->model->beforeSave();
        $this->model->afterSave();
    }

    /**
     * Image cache generation would not be performed if area was emulated.
     *
     * @return void
     */
    public function testSaveIfAreaEmulated(): void
    {
        $this->appStateMock->method('isAreaCodeEmulated')->willReturn(true);
        $this->imageCache->expects($this->never())
            ->method('generate')
            ->with($this->model);
        $this->configureSaveTest();
        $this->model->beforeSave();
        $this->model->afterSave();
    }

    /**
     *  Test for `save` method for duplicated product.
     *
     * @return void
     */
    public function testSaveAndDuplicate(): void
    {
        $collection = $this->createMock(Collection::class);
        $collection->method('count')->willReturn(1);
        $collection->method('getIterator')->willReturn(new \ArrayObject([]));
        $this->collectionFactoryMock->method('create')->willReturn($collection);
        $this->model->setIsDuplicate(true);
        $this->configureSaveTest();
        $this->model->beforeSave();
        $this->model->afterSave();
    }

    /**
     * Test for save method behavior with type options.
     *
     * @return void
     */
    public function testSaveWithoutTypeOptions(): void
    {
        $this->model->setCanSaveCustomOptions(false);
        $this->model->setTypeHasOptions(true);
        $this->model->setTypeHasRequiredOptions(true);
        $this->configureSaveTest();
        $this->model->beforeSave();
        $this->model->afterSave();
        $this->assertTrue($this->model->getTypeHasOptions());
        $this->assertTrue($this->model->getTypeHasRequiredOptions());
    }

    /**
     * Test for save method with provided options data.
     *
     * @return void
     */
    public function testSaveWithProvidedRequiredOptions(): void
    {
        $this->model->setData("has_options", "1");
        $this->model->setData("required_options", "1");
        $this->configureSaveTest();
        $this->model->beforeSave();
        $this->model->afterSave();
        $this->assertTrue($this->model->getHasOptions());
        $this->assertTrue($this->model->getRequiredOptions());
    }

    /**
     * Test for save method with provided options settled via magic method
     *
     * @return void
     */
    public function testSaveWithProvidedRequiredOptionsValue(): void
    {
        $this->model->setHasOptions("1");
        $this->model->setRequiredOptions("1");
        $this->model->setData("options", null);
        $this->configureSaveTest();
        $this->model->beforeSave();
        $this->model->afterSave();
        $this->assertTrue($this->model->getHasOptions());
        $this->assertTrue($this->model->getRequiredOptions());
    }

    /**
     * @return void
     */
    public function testGetIsSalableSimple(): void
    {
        $typeInstanceMock =
            $this->createPartialMock(Simple::class, ['isSalable']);
        $typeInstanceMock
            ->expects($this->atLeastOnce())
            ->method('isSalable')
            ->willReturn(true);

        $this->model->setTypeInstance($typeInstanceMock);

        self::assertTrue($this->model->getIsSalable());
    }

    /**
     * @return void
     */
    public function testGetIsSalableHasDataIsSaleable(): void
    {
        $typeInstanceMock = $this->createMock(Simple::class);

        $this->model->setTypeInstance($typeInstanceMock);
        $this->model->setData('is_saleable', true);
        $this->model->setData('is_salable', false);

        self::assertTrue($this->model->getIsSalable());
    }

    /**
     * Configure environment for `testSave` and `testSaveAndDuplicate` methods.
     *
     * @return void
     */
    protected function configureSaveTest(): void
    {
        $productTypeMock = $this->createPartialMock(Simple::class, ['beforeSave', 'save']);
        $productTypeMock->expects($this->once())->method('beforeSave')->willReturnSelf();
        $productTypeMock->expects($this->once())->method('save')->willReturnSelf();

        $this->productTypeInstanceMock->expects($this->once())->method('factory')->with($this->model)
            ->willReturn($productTypeMock);

        $this->model->getResource()->expects($this->any())->method('addCommitCallback')->willReturnSelf();
        $this->model->getResource()->expects($this->any())->method('commit')->willReturnSelf();
    }

    /**
     * Run test fromArray method
     *
     * @return void
     */
    public function testFromArray(): void
    {
        $data = [
            'stock_item' => ['stock-item-data']
        ];

        $stockItemMock = $this->createPartialMockWithReflection(
            AbstractSimpleObject::class,
            ['setProduct']
        );

        $this->moduleManager->expects($this->once())
            ->method('isEnabled')
            ->with('Magento_CatalogInventory')
            ->willReturn(true);
        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray')
            ->with($stockItemMock, $data['stock_item'], StockItemInterface::class)->willReturnSelf();
        $this->stockItemFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($stockItemMock);

        $this->assertEquals($this->model, $this->model->fromArray($data));
    }

    /**
     * @return void
     */
    protected function prepareCategoryIndexer(): void
    {
        $this->indexerRegistryMock->expects($this->once())
            ->method('get')
            ->with(CategoryIndexer::INDEXER_ID)
            ->willReturn($this->categoryIndexerMock);
    }

    /**
     *  Test for getProductLinks().
     *
     * @return void
     */
    public function testGetProductLinks(): void
    {
        $outputRelatedLink = $this->createMock(ProductLinkInterface::class);
        $outputRelatedLink->setSku("Simple Product 1");
        $outputRelatedLink->setLinkType("related");
        $outputRelatedLink->setLinkedProductSku("Simple Product 2");
        $outputRelatedLink->setLinkedProductType("simple");
        $outputRelatedLink->setPosition(0);
        $expectedOutput = [$outputRelatedLink];
        $this->productLinkRepositoryMock->expects($this->once())->method('getList')->willReturn($expectedOutput);
        $typeInstance = $this->createPartialMock(AbstractType::class, ['getSku','deleteTypeSpecificData']);
        $typeInstance->method('getSku')->willReturn('model');
        $this->productTypeInstanceMock->method('factory')->willReturn($typeInstance);
        
        // Set the linkRepository property directly to avoid ObjectManager dependency
        $this->setPropertyValue($this->model, 'linkRepository', $this->productLinkRepositoryMock);
        
        $links = $this->model->getProductLinks();
        $this->assertEquals($links, $expectedOutput);
    }

    /**
     *  Test for setProductLinks().
     *
     * @return void
     */
    public function testSetProductLinks(): void
    {
        $link = $this->createMock(ProductLinkInterface::class);
        $link->setSku("Simple Product 1");
        $link->setLinkType("upsell");
        $link->setLinkedProductSku("Simple Product 2");
        $link->setLinkedProductType("simple");
        $link->setPosition(0);
        $productLinks = [$link];
        $this->model->setProductLinks($productLinks);
        $this->assertEquals($productLinks, $this->model->getProductLinks());
    }

    /**
     * Set up two media attributes: image and small_image.
     *
     * @return void
     */
    protected function setupMediaAttributes(): array
    {
        $productType = $this->createPartialMock(AbstractType::class, ['getSetAttributes','deleteTypeSpecificData']);
        $this->productTypeInstanceMock->method('factory')->willReturn(
            $productType
        );

        $frontendMock = $this->createPartialMock(AbstractFrontend::class, ['getInputType']);
        $frontendMock->method('getInputType')->willReturn('media_image');
        $attributeImage = $this->createPartialMock(AbstractAttribute::class, ['getFrontend', 'getAttributeCode']);
        $attributeImage->method('getFrontend')->willReturn($frontendMock);
        $attributeImage->method('getAttributeCode')->willReturn('image');
        $attributeSmallImage = $this->createPartialMock(AbstractAttribute::class, ['getFrontend', 'getAttributeCode']);
        $attributeSmallImage->method('getFrontend')->willReturn($frontendMock);
        $attributeSmallImage->method('getAttributeCode')->willReturn('small_image');

        $productType->method('getSetAttributes')->willReturn(
            ['image' => $attributeImage, 'small_image' => $attributeSmallImage]
        );

        return [$attributeImage, $attributeSmallImage];
    }

    /**
     * @return void
     */
    public function getMediaAttributes(): void
    {
        $expected = [];
        $mediaAttributes = $this->setupMediaAttributes();
        foreach ($mediaAttributes as $mediaAttribute) {
            $expected[$mediaAttribute->getAttributeCode()] = $mediaAttribute;
        }
        $this->assertEquals($expected, $this->model->getMediaAttributes());
    }

    /**
     * @return void
     */
    public function testGetMediaAttributeValues(): void
    {
        $this->mediaConfig->expects($this->once())->method('getMediaAttributeCodes')
            ->willReturn(['image', 'small_image']);
        $this->model->setData('image', 'imageValue');
        $this->model->setData('small_image', 'smallImageValue');

        $expectedMediaAttributeValues = [
            'image' => 'imageValue',
            'small_image' => 'smallImageValue',
        ];
        $this->assertEquals($expectedMediaAttributeValues, $this->model->getMediaAttributeValues());
    }

    /**
     * @return void
     */
    public function testGetMediaGalleryEntriesNone(): void
    {
        $this->assertNull($this->model->getMediaGalleryEntries());
    }

    /**
     * @return void
     */
    public function testGetMediaGalleryEntries(): void
    {
        $this->setupMediaAttributes();
        $this->model->setData('image', 'imageFile.jpg');
        $this->model->setData('small_image', 'smallImageFile.jpg');

        $mediaEntries = [
            'images' => [
                [
                    'value_id' => 1,
                    'file' => 'imageFile.jpg',
                    'media_type' => 'image'
                ],
                [
                    'value_id' => 2,
                    'file' => 'smallImageFile.jpg',
                    'media_type' => 'image'
                ]
            ]
        ];
        $this->model->setData('media_gallery', $mediaEntries);

        $entry1 = $this->createMock(ProductAttributeMediaGalleryEntryInterface::class);
        $entry2 = $this->createMock(ProductAttributeMediaGalleryEntryInterface::class);

        $this->converterMock->expects($this->exactly(2))->method('convertTo')->willReturnOnConsecutiveCalls(
            $entry1,
            $entry2
        );

        $this->assertEquals([$entry1, $entry2], $this->model->getMediaGalleryEntries());
    }

    /**
     * @return void
     */
    public function testSetMediaGalleryEntries(): void
    {
        $expectedResult = [
            'images' => [
                [
                    'value_id' => 1,
                    'file' => 'file1.jpg',
                    'label' => 'label_text',
                    'position' => 4,
                    'disabled' => false,
                    'types' => ['image'],
                    'content' => [
                        'data' => [
                            ImageContentInterface::NAME => 'product_image',
                            ImageContentInterface::TYPE => 'image/jpg',
                            ImageContentInterface::BASE64_ENCODED_DATA => 'content_data'
                        ]
                    ],
                    'media_type' => 'image'
                ]
            ]
        ];

        $entryMock = $this->createMock(ProductAttributeMediaGalleryEntryInterface::class);

        $result = [
            'value_id' => 1,
            'file' => 'file1.jpg',
            'label' => 'label_text',
            'position' => 4,
            'disabled' => false,
            'types' => ['image'],
            'content' => [
                'data' => [
                    ImageContentInterface::NAME => 'product_image',
                    ImageContentInterface::TYPE => 'image/jpg',
                    ImageContentInterface::BASE64_ENCODED_DATA => 'content_data'
                ]
            ],
            'media_type' => 'image'
        ];

        $this->converterMock->expects($this->once())->method('convertFrom')->with($entryMock)->willReturn($result);

        $this->model->setMediaGalleryEntries([$entryMock]);
        $this->assertEquals($expectedResult, $this->model->getMediaGallery());
    }

    /**
     * @return void
     */
    public function testGetMediaGalleryImagesMerging(): void
    {
        $mediaEntries =
            [
                'images' => [
                    [
                        'value_id' => 1,
                        'file' => 'imageFile.jpg',
                        'media_type' => 'image'
                    ],
                    [
                        'value_id' => 3,
                        'file' => 'imageFile.jpg'
                    ],
                    [
                        'value_id' => 2,
                        'file' => 'smallImageFile.jpg',
                        'media_type' => 'image'
                    ]
                ]
            ];
        $expectedImageDataObject = new DataObject(
            [
                'value_id' => 1,
                'file' => 'imageFile.jpg',
                'media_type' => 'image',
                'url' => 'http://magento.dev/pub/imageFile.jpg',
                'id' => 1,
                'path' => '/var/www/html/pub/imageFile.jpg'
            ]
        );
        $expectedSmallImageDataObject = new DataObject(
            [
                'value_id' => 2,
                'file' => 'smallImageFile.jpg',
                'media_type' => 'image',
                'url' => 'http://magento.dev/pub/smallImageFile.jpg',
                'id' => 2,
                'path' => '/var/www/html/pub/smallImageFile.jpg'
            ]
        );

        $directoryMock = $this->createMock(ReadInterface::class);
        $directoryMock->method('getAbsolutePath')->willReturnOnConsecutiveCalls(
            '/var/www/html/pub/imageFile.jpg',
            '/var/www/html/pub/smallImageFile.jpg'
        );
        $this->mediaConfig->method('getMediaUrl')->willReturnOnConsecutiveCalls(
            'http://magento.dev/pub/imageFile.jpg',
            'http://magento.dev/pub/smallImageFile.jpg'
        );
        $this->filesystemMock->method('getDirectoryRead')->willReturn($directoryMock);
        $this->model->setData('media_gallery', $mediaEntries);
        $imagesCollectionMock = $this->createMock(Collection::class);
        $imagesCollectionMock->method('count')->willReturn(0);
        $imagesCollectionMock->method('getItemById')->willReturnMap(
            [
                [1, null],
                [2, null],
                [3, 'not_null_skeep_foreache']
            ]
        );
        $imagesCollectionMock->expects(self::exactly(2))->method('addItem')
            ->willReturnCallback(
                function ($arg1) use ($expectedImageDataObject, $expectedSmallImageDataObject) {
                    if ($arg1 == $expectedImageDataObject || $arg1 == $expectedSmallImageDataObject) {
                        return null;
                    }
                }
            );
        $this->collectionFactoryMock->method('create')->willReturn($imagesCollectionMock);

        $this->model->getMediaGalleryImages();
    }

    /**
     * Test that getMediaGalleryImages creates new collection when data is an array (not Collection object)
     *
     * @return void
     */
    public function testGetMediaGalleryImagesWhenDataIsArray(): void
    {
        // Set media_gallery_images as an array (simulating cached data that was serialized/unserialized)
        $this->model->setData('media_gallery_images', []);

        $directoryMock = $this->getMockForAbstractClass(ReadInterface::class);
        $this->filesystemMock->method('getDirectoryRead')->willReturn($directoryMock);

        $imagesCollectionMock = $this->createMock(Collection::class);
        $imagesCollectionMock->method('count')->willReturn(0);
        $this->collectionFactoryMock->method('create')->willReturn($imagesCollectionMock);

        $result = $this->model->getMediaGalleryImages();

        // Should return a Collection object, not an array
        $this->assertInstanceOf(Collection::class, $result);
    }

    /**
     * Test that getMediaGalleryImages returns existing collection when it already has items
     *
     * @return void
     */
    public function testGetMediaGalleryImagesWhenCollectionHasItems(): void
    {
        $directoryMock = $this->getMockForAbstractClass(ReadInterface::class);
        $this->filesystemMock->method('getDirectoryRead')->willReturn($directoryMock);

        $imagesCollectionMock = $this->createMock(Collection::class);
        // Collection already has items
        $imagesCollectionMock->method('count')->willReturn(2);
        $this->model->setData('media_gallery_images', $imagesCollectionMock);

        // Set media_gallery with images - these should NOT be processed since collection already has items
        $this->model->setData('media_gallery', ['images' => [['value_id' => 1, 'file' => 'test.jpg']]]);

        $result = $this->model->getMediaGalleryImages();

        // Should return the existing collection
        $this->assertSame($imagesCollectionMock, $result);
    }

    /**
     * Test that getMediaGalleryImages handles case when images is not an array
     *
     * @return void
     */
    public function testGetMediaGalleryImagesWhenImagesIsNotArray(): void
    {
        $directoryMock = $this->getMockForAbstractClass(ReadInterface::class);
        $this->filesystemMock->method('getDirectoryRead')->willReturn($directoryMock);

        $imagesCollectionMock = $this->createMock(Collection::class);
        $imagesCollectionMock->method('count')->willReturn(0);
        $this->collectionFactoryMock->method('create')->willReturn($imagesCollectionMock);

        // Set media_gallery with images as null (not an array)
        $this->model->setData('media_gallery', ['images' => null]);

        $result = $this->model->getMediaGalleryImages();

        $this->assertInstanceOf(Collection::class, $result);
    }

    /**
     * Test that getMediaGalleryImages skips disabled images
     *
     * @return void
     */
    public function testGetMediaGalleryImagesSkipsDisabledImages(): void
    {
        $mediaEntries = [
            'images' => [
                [
                    'value_id' => 1,
                    'file' => 'imageFile.jpg',
                    'media_type' => 'image',
                    'disabled' => 1  // This image should be skipped
                ]
            ]
        ];

        $directoryMock = $this->getMockForAbstractClass(ReadInterface::class);
        $this->filesystemMock->method('getDirectoryRead')->willReturn($directoryMock);
        $this->model->setData('media_gallery', $mediaEntries);

        $imagesCollectionMock = $this->createMock(Collection::class);
        $imagesCollectionMock->method('count')->willReturn(0);
        // addItem should never be called for disabled images
        $imagesCollectionMock->expects($this->never())->method('addItem');
        $this->collectionFactoryMock->method('create')->willReturn($imagesCollectionMock);

        $this->model->getMediaGalleryImages();
    }

    /**
     * Test that getMediaGalleryImages skips removed images
     *
     * @return void
     */
    public function testGetMediaGalleryImagesSkipsRemovedImages(): void
    {
        $mediaEntries = [
            'images' => [
                [
                    'value_id' => 1,
                    'file' => 'imageFile.jpg',
                    'media_type' => 'image',
                    'removed' => 1  // This image should be skipped
                ]
            ]
        ];

        $directoryMock = $this->getMockForAbstractClass(ReadInterface::class);
        $this->filesystemMock->method('getDirectoryRead')->willReturn($directoryMock);
        $this->model->setData('media_gallery', $mediaEntries);

        $imagesCollectionMock = $this->createMock(Collection::class);
        $imagesCollectionMock->method('count')->willReturn(0);
        // addItem should never be called for removed images
        $imagesCollectionMock->expects($this->never())->method('addItem');
        $this->collectionFactoryMock->method('create')->willReturn($imagesCollectionMock);

        $this->model->getMediaGalleryImages();
    }

    /**
     * Test that getMediaGalleryImages skips images without value_id
     *
     * @return void
     */
    public function testGetMediaGalleryImagesSkipsImagesWithoutValueId(): void
    {
        $mediaEntries = [
            'images' => [
                [
                    'file' => 'imageFile.jpg',
                    'media_type' => 'image'
                    // No value_id - this image should be skipped
                ]
            ]
        ];

        $directoryMock = $this->getMockForAbstractClass(ReadInterface::class);
        $this->filesystemMock->method('getDirectoryRead')->willReturn($directoryMock);
        $this->model->setData('media_gallery', $mediaEntries);

        $imagesCollectionMock = $this->createMock(Collection::class);
        $imagesCollectionMock->method('count')->willReturn(0);
        // addItem should never be called for images without value_id
        $imagesCollectionMock->expects($this->never())->method('addItem');
        $this->collectionFactoryMock->method('create')->willReturn($imagesCollectionMock);

        $this->model->getMediaGalleryImages();
    }

    /**
     * @return void
     */
    public function testGetCustomAttributes(): void
    {
        $priceCode = 'price';
        $customAttributeCode = 'color';
        $initialCustomAttributeValue = 'red';
        $newCustomAttributeValue = 'blue';
        $customAttributesMetadata = [$priceCode => 'attribute1', $customAttributeCode => 'attribute2'];
        $this->metadataServiceMock->expects($this->never())->method('getCustomAttributesMetadata');
        $this->eavConfig->expects($this->once())
            ->method('getEntityAttributes')
            ->willReturn($customAttributesMetadata);
        $this->model->setData($priceCode, 10);

        //The color attribute is not set, expect empty custom attribute array
        $this->assertEquals([], $this->model->getCustomAttributes());

        //Set the color attribute;
        $this->model->setData($customAttributeCode, $initialCustomAttributeValue);
        $attributeValue = new AttributeValue();
        $attributeValue2 = new AttributeValue();
        $this->attributeValueFactory->expects($this->exactly(2))->method('create')
            ->willReturnOnConsecutiveCalls($attributeValue, $attributeValue2);
        $this->assertCount(1, $this->model->getCustomAttributes());
        $this->assertNotNull($this->model->getCustomAttribute($customAttributeCode));
        $this->assertEquals(
            $initialCustomAttributeValue,
            $this->model->getCustomAttribute($customAttributeCode)->getValue()
        );

        //Change the attribute value, should reflect in getCustomAttribute
        $this->model->setCustomAttribute($customAttributeCode, $newCustomAttributeValue);
        $this->assertCount(1, $this->model->getCustomAttributes());
        $this->assertNotNull($this->model->getCustomAttribute($customAttributeCode));
        $this->assertEquals(
            $newCustomAttributeValue,
            $this->model->getCustomAttribute($customAttributeCode)->getValue()
        );
    }

    /**
     * @return array
     */
    public function priceDataProvider(): array
    {
        return [
            'receive empty array' => [[]],
            'receive null' => [null],
            'receive non-empty array' => [['non-empty', 'array', 'of', 'values']]
        ];
    }

    /**
     * @return void
     */
    public function testGetOptions(): void
    {
        $option1Id = 2;
        $optionMock1 = $this->createPartialMock(Option::class, ['getId', 'setProduct']);
        $option2Id = 3;
        $optionMock2 = $this->createPartialMock(Option::class, ['getId', 'setProduct']);
        $expectedOptions = [
            $option1Id => $optionMock1,
            $option2Id => $optionMock2
        ];
        $this->model->setOptions($expectedOptions);
        $this->assertEquals($expectedOptions, $this->model->getOptions());

        //Calling the method again, empty options array will be returned
        $this->model->setOptions([]);
        $this->assertEquals([], $this->model->getOptions());
    }

    /**
     * @param $object
     * @param $property
     * @param $value
     */
    protected function setPropertyValue(&$object, $property, $value)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $reflectionProperty = $reflection->getProperty($property);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);
        return $object;
    }

    /**
     * @param $object
     * @param $property
     */
    protected function getPropertyValue(&$object, $property)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $reflectionProperty = $reflection->getProperty($property);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty->getValue($object);
    }

    /**
     * @return void
     */
    public function testGetFinalPrice(): void
    {
        $finalPrice = 11;
        $qty = 1;
        $this->model->setQty($qty);
        $productTypePriceMock = $this->createPartialMock(
            Price::class,
            ['getFinalPrice']
        );

        $productTypePriceMock->expects($this->any())
            ->method('getFinalPrice')
            ->with($qty, $this->model)
            ->willReturn($finalPrice);

        $this->productTypeInstanceMock->expects($this->any())
            ->method('priceFactory')
            ->with($this->model->getTypeId())
            ->willReturn($productTypePriceMock);

        $this->assertEquals($finalPrice, $this->model->getFinalPrice($qty));
        $this->model->setFinalPrice(9.99);
    }

    /**
     * @return void
     */
    public function testGetFinalPricePreset(): void
    {
        $finalPrice = 9.99;
        $qty = 1;
        $this->model->setQty($qty);
        $this->model->setFinalPrice($finalPrice);
        $productTypePriceMock = $this->createPartialMock(
            Price::class,
            ['getFinalPrice']
        );
        $productTypePriceMock->expects($this->any())
            ->method('getFinalPrice')
            ->with($qty, $this->model)
            ->willReturn($finalPrice);

        $this->productTypeInstanceMock->expects($this->any())
            ->method('priceFactory')
            ->with($this->model->getTypeId())
            ->willReturn($productTypePriceMock);

        $this->assertEquals($finalPrice, $this->model->getFinalPrice($qty));
    }

    /**
     * @return void
     */
    public function testGetTypeId(): void
    {
        $productType = $this->createMock(Virtual::class);

        $this->productTypeInstanceMock->expects($this->exactly(2))->method('factory')->willReturn(
            $productType
        );

        $this->model->getTypeInstance();
        $this->model->setTypeId('typeId');
        $this->model->getTypeInstance();
    }

    /**
     * @return void
     */
    public function testGetOptionById(): void
    {
        $optionId = 100;
        $optionMock = $this->createMock(Option::class);
        $this->model->setOptions([$optionMock]);
        $optionMock->expects($this->once())->method('getId')->willReturn($optionId);
        $this->assertEquals($optionMock, $this->model->getOptionById($optionId));
    }

    /**
     * @return void
     */
    public function testGetOptionByIdWithWrongOptionId(): void
    {
        $optionId = 100;
        $optionMock = $this->createMock(Option::class);
        $this->model->setOptions([$optionMock]);
        $optionMock->expects($this->once())->method('getId')->willReturn(200);
        $this->assertNull($this->model->getOptionById($optionId));
    }

    /**
     * @return void
     */
    public function testGetOptionByIdForProductWithoutOptions(): void
    {
        $this->assertNull($this->model->getOptionById(100));
    }

    /**
     * Test addImageToMediaGallery calls getMediaGalleryProcessor
     *
     * @return void
     */
    public function testAddImageToMediaGallery(): void
    {
        $file = '/path/to/image.jpg';
        $mediaAttribute = 'image';
        $move = false;
        $exclude = true;

        // Create a mock for Gallery Processor
        $processorMock = $this->createMock(\Magento\Catalog\Model\Product\Gallery\Processor::class);
        $processorMock->expects($this->once())
            ->method('addImage')
            ->with($this->model, $file, $mediaAttribute, $move, $exclude);

        // Use reflection to set the private mediaGalleryProcessor property
        $reflection = new \ReflectionClass($this->model);
        $property = $reflection->getProperty('mediaGalleryProcessor');
        $property->setAccessible(true);
        $property->setValue($this->model, $processorMock);

        // Mock the type instance to return attributes including media_gallery
        $mediaGalleryAttributeMock = $this->createMock(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class);
        $typeInstanceMock = $this->createMock(\Magento\Catalog\Model\Product\Type\AbstractType::class);
        $typeInstanceMock->method('getSetAttributes')
            ->willReturn(['media_gallery' => $mediaGalleryAttributeMock]);
        $this->model->setTypeInstance($typeInstanceMock);

        $result = $this->model->addImageToMediaGallery($file, $mediaAttribute, $move, $exclude);

        $this->assertSame($this->model, $result);
    }

    /**
     * Test addImageToMediaGallery does nothing when no gallery attribute
     *
     * @return void
     */
    public function testAddImageToMediaGalleryWithoutGalleryAttribute(): void
    {
        $file = '/path/to/image.jpg';

        // Mock the type instance to return attributes without media_gallery
        $typeInstanceMock = $this->createMock(\Magento\Catalog\Model\Product\Type\AbstractType::class);
        $typeInstanceMock->method('getSetAttributes')
            ->willReturn([]);
        $this->model->setTypeInstance($typeInstanceMock);

        $result = $this->model->addImageToMediaGallery($file);

        $this->assertSame($this->model, $result);
    }

    /**
     * Test setAssociatedProductIds sets configurable product links via extension attributes
     *
     * @return void
     */
    public function testSetAssociatedProductIds(): void
    {
        $productIds = [1, 2, 3];

        $this->productExtAttributes->expects($this->once())
            ->method('setConfigurableProductLinks')
            ->with($productIds);

        $result = $this->model->setAssociatedProductIds($productIds);

        $this->assertSame($this->model, $result);
    }

    /**
     * Test getQuantityAndStockStatus returns quantity and stock status data
     *
     * @return void
     */
    public function testGetQuantityAndStockStatus(): void
    {
        $quantityAndStockStatus = ['qty' => 100, 'is_in_stock' => true];
        $this->model->setData('quantity_and_stock_status', $quantityAndStockStatus);

        $this->assertEquals($quantityAndStockStatus, $this->model->getQuantityAndStockStatus());
    }

    /**
     * Test getQuantityAndStockStatus returns null when not set
     *
     * @return void
     */
    public function testGetQuantityAndStockStatusReturnsNullWhenNotSet(): void
    {
        $this->assertNull($this->model->getQuantityAndStockStatus());
    }

    /**
     * Test setQuantityAndStockStatus sets quantity and stock status data
     *
     * @return void
     */
    public function testSetQuantityAndStockStatus(): void
    {
        $quantityAndStockStatus = ['qty' => 50, 'is_in_stock' => false];

        $result = $this->model->setQuantityAndStockStatus($quantityAndStockStatus);

        $this->assertSame($this->model, $result);
        $this->assertEquals($quantityAndStockStatus, $this->model->getData('quantity_and_stock_status'));
    }

    /**
     * Test setStockData sets stock data
     *
     * @return void
     */
    public function testSetStockData(): void
    {
        $stockData = ['qty' => 200, 'is_in_stock' => true, 'manage_stock' => 1];

        $result = $this->model->setStockData($stockData);

        $this->assertSame($this->model, $result);
        $this->assertEquals($stockData, $this->model->getData('stock_data'));
    }

    /**
     * Test _resetState resets internal properties
     *
     * @return void
     */
    public function testResetState(): void
    {
        // Set some data that should be reset
        $this->model->setData('custom_options', ['option1' => 'value1']);

        // Call _resetState
        $this->model->_resetState();

        // Use reflection to verify internal properties are reset
        $reflection = new \ReflectionClass($this->model);

        $customOptionsProperty = $reflection->getProperty('_customOptions');
        $customOptionsProperty->setAccessible(true);
        $this->assertEquals([], $customOptionsProperty->getValue($this->model));

        $errorsProperty = $reflection->getProperty('_errors');
        $errorsProperty->setAccessible(true);
        $this->assertEquals([], $errorsProperty->getValue($this->model));

        $canAffectOptionsProperty = $reflection->getProperty('_canAffectOptions');
        $canAffectOptionsProperty->setAccessible(true);
        $this->assertFalse($canAffectOptionsProperty->getValue($this->model));

        $productIdCachedProperty = $reflection->getProperty('_productIdCached');
        $productIdCachedProperty->setAccessible(true);
        $this->assertNull($productIdCachedProperty->getValue($this->model));
    }

    /**
     * Test getUrlModel returns the URL model
     *
     * @return void
     */
    public function testGetUrlModel(): void
    {
        $result = $this->model->getUrlModel();

        $this->assertInstanceOf(\Magento\Catalog\Model\Product\Url::class, $result);
    }

    /**
     * Test validate dispatches events and calls resource validate
     *
     * @return void
     */
    public function testValidate(): void
    {
        $validationResult = ['error' => false];

        $this->resource->expects($this->once())
            ->method('validate')
            ->with($this->model)
            ->willReturn($validationResult);

        $this->eventManagerMock->expects($this->exactly(2))
            ->method('dispatch')
            ->willReturnCallback(function ($eventName) {
                static $callCount = 0;
                $callCount++;
                if ($callCount === 1) {
                    $this->assertEquals('catalog_product_validate_before', $eventName);
                } elseif ($callCount === 2) {
                    $this->assertEquals('catalog_product_validate_after', $eventName);
                }
            });

        $result = $this->model->validate();

        $this->assertEquals($validationResult, $result);
    }

    /**
     * Test getProductLinks calls getLinkRepository when product has sku and id
     *
     * @return void
     */
    public function testGetProductLinksCallsLinkRepository(): void
    {
        $sku = 'test-sku';
        $productId = 123;
        $links = [$this->createMock(\Magento\Catalog\Api\Data\ProductLinkInterface::class)];

        $this->model->setData('sku', $sku);
        $this->model->setId($productId);

        // Mock the type instance for getSku()
        $typeInstanceMock = $this->createMock(\Magento\Catalog\Model\Product\Type\AbstractType::class);
        $typeInstanceMock->method('getSku')
            ->with($this->model)
            ->willReturn($sku);
        $this->model->setTypeInstance($typeInstanceMock);

        // Use reflection to set the linkRepository
        $linkRepositoryMock = $this->createMock(\Magento\Catalog\Api\ProductLinkRepositoryInterface::class);
        $linkRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($this->model)
            ->willReturn($links);

        $reflection = new \ReflectionClass($this->model);
        $property = $reflection->getProperty('linkRepository');
        $property->setAccessible(true);
        $property->setValue($this->model, $linkRepositoryMock);

        $result = $this->model->getProductLinks();

        $this->assertEquals($links, $result);
    }

    /**
     * Test getProductLinks returns empty array when product has no sku or id
     *
     * @return void
     */
    public function testGetProductLinksReturnsEmptyArrayWhenNoSkuOrId(): void
    {
        // Mock the type instance to return null/empty sku
        $typeInstanceMock = $this->createMock(\Magento\Catalog\Model\Product\Type\AbstractType::class);
        $typeInstanceMock->method('getSku')
            ->with($this->model)
            ->willReturn(null);
        $this->model->setTypeInstance($typeInstanceMock);

        // Don't set id
        $result = $this->model->getProductLinks();

        $this->assertEquals([], $result);
    }

    /**
     * Test getMediaGalleryProcessor returns cached processor on second call
     *
     * @return void
     */
    public function testGetMediaGalleryProcessorReturnsCachedProcessor(): void
    {
        $file = '/path/to/image.jpg';

        // Create a mock for Gallery Processor
        $processorMock = $this->createMock(\Magento\Catalog\Model\Product\Gallery\Processor::class);
        $processorMock->expects($this->exactly(2))
            ->method('addImage');

        // Use reflection to set the private mediaGalleryProcessor property
        $reflection = new \ReflectionClass($this->model);
        $property = $reflection->getProperty('mediaGalleryProcessor');
        $property->setAccessible(true);
        $property->setValue($this->model, $processorMock);

        // Mock the type instance to return attributes including media_gallery
        $mediaGalleryAttributeMock = $this->createMock(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class);
        $typeInstanceMock = $this->createMock(\Magento\Catalog\Model\Product\Type\AbstractType::class);
        $typeInstanceMock->method('getSetAttributes')
            ->willReturn(['media_gallery' => $mediaGalleryAttributeMock]);
        $this->model->setTypeInstance($typeInstanceMock);

        // Call twice to verify processor is reused
        $this->model->addImageToMediaGallery($file);
        $this->model->addImageToMediaGallery($file);
    }

    /**
     * Test getLinkRepository returns cached repository on second call
     *
     * @return void
     */
    public function testGetLinkRepositoryReturnsCachedRepository(): void
    {
        $sku = 'test-sku';
        $productId = 123;
        $links = [];

        $this->model->setData('sku', $sku);
        $this->model->setId($productId);

        // Mock the type instance for getSku()
        $typeInstanceMock = $this->createMock(\Magento\Catalog\Model\Product\Type\AbstractType::class);
        $typeInstanceMock->method('getSku')
            ->with($this->model)
            ->willReturn($sku);
        $this->model->setTypeInstance($typeInstanceMock);

        // Use reflection to set the linkRepository
        $linkRepositoryMock = $this->createMock(\Magento\Catalog\Api\ProductLinkRepositoryInterface::class);
        $linkRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($this->model)
            ->willReturn($links);

        $reflection = new \ReflectionClass($this->model);
        $property = $reflection->getProperty('linkRepository');
        $property->setAccessible(true);
        $property->setValue($this->model, $linkRepositoryMock);

        // Call getProductLinks - this will cache the result in _links
        $this->model->getProductLinks();

        // Call again - should use cached _links, not call repository again
        $result = $this->model->getProductLinks();

        $this->assertEquals($links, $result);
    }

    /**
     * Test getMediaGalleryProcessor lazy loads via ObjectManager when not set
     *
     * @return void
     */
    public function testGetMediaGalleryProcessorLazyLoading(): void
    {
        $file = '/path/to/image.jpg';

        // Save original ObjectManager instance if it exists
        $originalObjectManager = null;
        try {
            $originalObjectManager = \Magento\Framework\App\ObjectManager::getInstance();
        } catch (\RuntimeException $e) {
            // ObjectManager not initialized yet
        }

        // Create a mock for Gallery Processor
        $processorMock = $this->createMock(\Magento\Catalog\Model\Product\Gallery\Processor::class);
        $processorMock->expects($this->once())
            ->method('addImage');

        // Mock ObjectManager
        $objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $objectManagerMock->expects($this->once())
            ->method('get')
            ->with(\Magento\Catalog\Model\Product\Gallery\Processor::class)
            ->willReturn($processorMock);

        // Set the mock ObjectManager
        \Magento\Framework\App\ObjectManager::setInstance($objectManagerMock);

        // Ensure mediaGalleryProcessor is null (not pre-set)
        $reflection = new \ReflectionClass($this->model);
        $property = $reflection->getProperty('mediaGalleryProcessor');
        $property->setAccessible(true);
        $property->setValue($this->model, null);

        // Mock the type instance to return attributes including media_gallery
        $mediaGalleryAttributeMock = $this->createMock(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class);
        $typeInstanceMock = $this->createMock(\Magento\Catalog\Model\Product\Type\AbstractType::class);
        $typeInstanceMock->method('getSetAttributes')
            ->willReturn(['media_gallery' => $mediaGalleryAttributeMock]);
        $this->model->setTypeInstance($typeInstanceMock);

        // This should trigger lazy loading of mediaGalleryProcessor
        $this->model->addImageToMediaGallery($file);

        // Restore original ObjectManager if it existed
        if ($originalObjectManager !== null) {
            \Magento\Framework\App\ObjectManager::setInstance($originalObjectManager);
        }
    }

    /**
     * Test getLinkRepository lazy loads via ObjectManager when not set
     *
     * @return void
     */
    public function testGetLinkRepositoryLazyLoading(): void
    {
        $sku = 'test-sku';
        $productId = 123;
        $links = [];

        // Save original ObjectManager instance if it exists
        $originalObjectManager = null;
        try {
            $originalObjectManager = \Magento\Framework\App\ObjectManager::getInstance();
        } catch (\RuntimeException $e) {
            // ObjectManager not initialized yet
        }

        $this->model->setData('sku', $sku);
        $this->model->setId($productId);

        // Mock the type instance for getSku()
        $typeInstanceMock = $this->createMock(\Magento\Catalog\Model\Product\Type\AbstractType::class);
        $typeInstanceMock->method('getSku')
            ->with($this->model)
            ->willReturn($sku);
        $this->model->setTypeInstance($typeInstanceMock);

        // Create a mock for LinkRepository
        $linkRepositoryMock = $this->createMock(\Magento\Catalog\Api\ProductLinkRepositoryInterface::class);
        $linkRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($this->model)
            ->willReturn($links);

        // Mock ObjectManager
        $objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $objectManagerMock->expects($this->once())
            ->method('get')
            ->with(\Magento\Catalog\Api\ProductLinkRepositoryInterface::class)
            ->willReturn($linkRepositoryMock);

        // Set the mock ObjectManager
        \Magento\Framework\App\ObjectManager::setInstance($objectManagerMock);

        // Ensure linkRepository is null (not pre-set)
        $reflection = new \ReflectionClass($this->model);
        $property = $reflection->getProperty('linkRepository');
        $property->setAccessible(true);
        $property->setValue($this->model, null);

        // This should trigger lazy loading of linkRepository
        $result = $this->model->getProductLinks();

        $this->assertEquals($links, $result);

        // Restore original ObjectManager if it existed
        if ($originalObjectManager !== null) {
            \Magento\Framework\App\ObjectManager::setInstance($originalObjectManager);
        }
    }

    /**
     * Test constructor uses ObjectManager fallback for optional parameters when null
     *
     * @return void
     */
    public function testConstructorObjectManagerFallback(): void
    {
        // Save original ObjectManager instance if it exists
        $originalObjectManager = null;
        try {
            $originalObjectManager = \Magento\Framework\App\ObjectManager::getInstance();
        } catch (\RuntimeException $e) {
            // ObjectManager not initialized yet
        }

        // Mock ObjectManager to return mocks for optional parameters
        $eavConfigMock = $this->createMock(\Magento\Eav\Model\Config::class);
        $filterCustomAttributeMock = $this->createMock(FilterProductCustomAttribute::class);

        $objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $objectManagerMock->method('get')
            ->willReturnMap([
                [\Magento\Eav\Model\Config::class, $eavConfigMock],
                [FilterProductCustomAttribute::class, $filterCustomAttributeMock],
            ]);

        \Magento\Framework\App\ObjectManager::setInstance($objectManagerMock);

        // Create Product without optional parameters to trigger ObjectManager fallback
        $product = new Product(
            $this->createMock(\Magento\Framework\Model\Context::class),
            $this->createMock(\Magento\Framework\Registry::class),
            $this->extensionAttributesFactory,
            $this->attributeValueFactory,
            $this->storeManager,
            $this->metadataServiceMock,
            $this->createMock(ProductUrl::class),
            $this->createMock(ProductLink::class),
            $this->createMock(ItemOptionFactory::class),
            $this->stockItemFactoryMock,
            $this->createMock(OptionFactory::class),
            $this->createMock(Visibility::class),
            $this->createMock(Status::class),
            $this->createMock(MediaConfig::class),
            $this->productTypeInstanceMock,
            $this->moduleManager,
            $this->_catalogProduct,
            $this->resource,
            $this->createMock(ProductCollection::class),
            $this->collectionFactoryMock,
            $this->filesystemMock,
            $this->indexerRegistryMock,
            $this->productFlatProcessor,
            $this->productPriceProcessor,
            $this->createMock(EavProcessor::class),
            $this->categoryRepository,
            $this->imageCacheFactory,
            $this->createMock(ProductLinkCollectionProvider::class),
            $this->createMock(LinkTypeProvider::class),
            $this->createMock(ProductLinkInterfaceFactory::class),
            $this->createMock(ProductLinkExtensionFactory::class),
            $this->mediaGalleryEntryConverterPoolMock,
            $this->dataObjectHelperMock,
            $this->createMock(JoinProcessorInterface::class),
            [],
            null,  // Pass null to trigger ObjectManager fallback for eavConfig
            null   // Pass null to trigger ObjectManager fallback for filterCustomAttribute
        );

        $this->assertInstanceOf(\Magento\Catalog\Model\Product::class, $product);

        // Restore original ObjectManager if it existed
        if ($originalObjectManager !== null) {
            \Magento\Framework\App\ObjectManager::setInstance($originalObjectManager);
        }
    }
}
