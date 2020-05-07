<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Backend\Media\EntryConverterPool;
use Magento\Catalog\Model\Product\Attribute\Backend\Media\ImageEntryConverter;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Image\Cache;
use Magento\Catalog\Model\Product\Image\CacheFactory;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\OptionFactory;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Catalog\Model\Product\Type\Price;
use Magento\Catalog\Model\Product\Type\Simple;
use Magento\Catalog\Model\Product\Type\Virtual;
use Magento\Catalog\Model\ProductLink\Link;
use Magento\Catalog\Model\ResourceModel\Product as ProductResourceMOdel;
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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class ProductTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $productLinkRepositoryMock;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Catalog\Model\Product
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
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor|MockObject
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
     * @var \Magento\Catalog\Helper\Product|MockObject
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
     */
    protected function setUp(): void
    {
        $this->categoryIndexerMock = $this->getMockForAbstractClass(IndexerInterface::class);

        $this->moduleManager = $this->createPartialMock(
            Manager::class,
            ['isEnabled']
        );
        $this->extensionAttributes = $this->getMockBuilder(ExtensionAttributesInterface::class)
            ->setMethods(['getWebsiteIds', 'setWebsiteIds'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->stockItemFactoryMock = $this->createPartialMock(
            StockItemInterfaceFactory::class,
            ['create']
        );
        $this->dataObjectHelperMock = $this->getMockBuilder(DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productFlatProcessor = $this->createMock(
            Processor::class
        );

        $this->_priceInfoMock = $this->createMock(Base::class);
        $this->productTypeInstanceMock = $this->createMock(Type::class);
        $this->productPriceProcessor = $this->createMock(
            \Magento\Catalog\Model\Indexer\Product\Price\Processor::class
        );

        $this->appStateMock = $this->createPartialMock(
            State::class,
            ['getAreaCode', 'isAreaCodeEmulated']
        );
        $this->appStateMock->expects($this->any())
            ->method('getAreaCode')
            ->willReturn(FrontNameResolver::AREA_CODE);

        $this->eventManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $actionValidatorMock = $this->createMock(
            RemoveAction::class
        );
        $actionValidatorMock->expects($this->any())->method('isAllowed')->willReturn(true);
        $cacheInterfaceMock = $this->getMockForAbstractClass(CacheInterface::class);

        $contextMock = $this->createPartialMock(
            Context::class,
            ['getEventDispatcher', 'getCacheManager', 'getAppState', 'getActionValidator'],
            [],
            '',
            false
        );
        $contextMock->expects($this->any())->method('getAppState')->willReturn($this->appStateMock);
        $contextMock->expects($this->any())
            ->method('getEventDispatcher')
            ->willReturn($this->eventManagerMock);
        $contextMock->expects($this->any())
            ->method('getCacheManager')
            ->willReturn($cacheInterfaceMock);
        $contextMock->expects($this->any())
            ->method('getActionValidator')
            ->willReturn($actionValidatorMock);

        $this->optionInstanceMock = $this->getMockBuilder(Option::class)
            ->setMethods(['setProduct', 'saveOptions', '__sleep'])
            ->disableOriginalConstructor()
            ->getMock();

        $optionFactory = $this->createPartialMock(
            OptionFactory::class,
            ['create']
        );
        $optionFactory->expects($this->any())->method('create')->willReturn($this->optionInstanceMock);

        $this->resource = $this->getMockBuilder(ProductResourceMOdel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->registry = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->category = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->website = $this->getMockBuilder(Website::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeManager->expects($this->any())
            ->method('getStore')
            ->willReturn($this->store);
        $this->storeManager->expects($this->any())
            ->method('getWebsite')
            ->willReturn($this->website);
        $this->indexerRegistryMock = $this->createPartialMock(
            IndexerRegistry::class,
            ['get']
        );
        $this->categoryRepository = $this->getMockForAbstractClass(CategoryRepositoryInterface::class);

        $this->_catalogProduct = $this->createPartialMock(
            \Magento\Catalog\Helper\Product::class,
            ['isDataForProductCategoryIndexerWasChanged']
        );

        $this->imageCache = $this->getMockBuilder(Cache::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->imageCacheFactory = $this->getMockBuilder(CacheFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->mediaGalleryEntryFactoryMock =
            $this->getMockBuilder(ProductAttributeMediaGalleryEntryInterfaceFactory::class)
                ->setMethods(['create'])
                ->disableOriginalConstructor()
                ->getMock();

        $this->metadataServiceMock = $this->getMockForAbstractClass(ProductAttributeRepositoryInterface::class);
        $this->attributeValueFactory = $this->getMockBuilder(AttributeValueFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->mediaGalleryEntryConverterPoolMock =
            $this->createPartialMock(
                EntryConverterPool::class,
                ['getConverterByMediaType']
            );

        $this->converterMock =
            $this->createMock(
                ImageEntryConverter::class
            );

        $this->mediaGalleryEntryConverterPoolMock->expects($this->any())->method('getConverterByMediaType')
            ->willReturn($this->converterMock);
        $this->productLinkRepositoryMock = $this->getMockBuilder(
            ProductLinkRepositoryInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->extensionAttributesFactory = $this->getMockBuilder(ExtensionAttributesFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filesystemMock = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->collectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->mediaConfig = $this->createMock(\Magento\Catalog\Model\Product\Media\Config::class);
        $this->eavConfig = $this->createMock(Config::class);

        $this->productExtAttributes = $this->getMockBuilder(ProductExtensionInterface::class)
            ->setMethods(['getStockItem'])
            ->getMockForAbstractClass();
        $this->extensionAttributesFactory
            ->expects($this->any())
            ->method('create')
            ->willReturn($this->productExtAttributes);

        $this->filterCustomAttribute = $this->createTestProxy(
            FilterProductCustomAttribute::class
        );

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            \Magento\Catalog\Model\Product::class,
            [
                'context' => $contextMock,
                'catalogProductType' => $this->productTypeInstanceMock,
                'productFlatIndexerProcessor' => $this->productFlatProcessor,
                'extensionFactory' => $this->extensionAttributesFactory,
                'productPriceIndexerProcessor' => $this->productPriceProcessor,
                'catalogProductOptionFactory' => $optionFactory,
                'storeManager' => $this->storeManager,
                'resource' => $this->resource,
                'registry' => $this->registry,
                'moduleManager' => $this->moduleManager,
                'stockItemFactory' => $this->stockItemFactoryMock,
                'dataObjectHelper' => $this->dataObjectHelperMock,
                'indexerRegistry' => $this->indexerRegistryMock,
                'categoryRepository' => $this->categoryRepository,
                'catalogProduct' => $this->_catalogProduct,
                'imageCacheFactory' => $this->imageCacheFactory,
                'mediaGalleryEntryFactory' => $this->mediaGalleryEntryFactoryMock,
                'metadataService' => $this->metadataServiceMock,
                'customAttributeFactory' => $this->attributeValueFactory,
                'mediaGalleryEntryConverterPool' => $this->mediaGalleryEntryConverterPoolMock,
                'linkRepository' => $this->productLinkRepositoryMock,
                'catalogProductMediaConfig' => $this->mediaConfig,
                '_filesystem' => $this->filesystemMock,
                '_collectionFactory' => $this->collectionFactoryMock,
                'data' => ['id' => 1],
                'eavConfig' => $this->eavConfig,
                'filterCustomAttribute' => $this->filterCustomAttribute
            ]
        );
    }

    public function testGetAttributes()
    {
        $productType = $this->getMockBuilder(AbstractType::class)
            ->setMethods(['getSetAttributes'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->productTypeInstanceMock->expects($this->any())->method('factory')->willReturn(
            $productType
        );
        $attribute = $this->getMockBuilder(AbstractAttribute::class)
            ->setMethods(['isInGroup'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $attribute->expects($this->any())->method('isInGroup')->willReturn(true);
        $productType->expects($this->any())->method('getSetAttributes')->willReturn(
            [$attribute]
        );
        $expect = [$attribute];
        $this->assertEquals($expect, $this->model->getAttributes(5));
        $this->assertEquals($expect, $this->model->getAttributes());
    }

    public function testGetStoreIds()
    {
        $expectedStoreIds = [1, 2, 3];
        $websiteIds = ['test'];
        $this->model->setWebsiteIds($websiteIds);
        $this->website->expects($this->once())->method('getStoreIds')->willReturn($expectedStoreIds);
        $this->assertEquals($expectedStoreIds, $this->model->getStoreIds());
    }

    /**
     * @dataProvider getSingleStoreIds
     * @param bool $isObjectNew
     */
    public function testGetStoreSingleSiteModelIds(
        bool $isObjectNew
    ) {
        $websiteIDs = [0 => 2];
        $this->model->setWebsiteIds(
            !$isObjectNew ? $websiteIDs : array_flip($websiteIDs)
        );

        $this->model->isObjectNew($isObjectNew);

        $this->storeManager->expects(
            $this->exactly(
                (int)!$isObjectNew
            )
        )
            ->method('isSingleStoreMode')
            ->willReturn(true);

        $this->website->expects(
            $this->once()
        )->method('getStoreIds')
            ->willReturn($websiteIDs);

        $this->assertEquals($websiteIDs, $this->model->getStoreIds());
    }

    /**
     * @return array
     */
    public function getSingleStoreIds()
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

    public function testGetStoreId()
    {
        $this->model->setStoreId(3);
        $this->assertEquals(3, $this->model->getStoreId());
        $this->model->unsStoreId();
        $this->store->expects($this->once())->method('getId')->willReturn(5);
        $this->assertEquals(5, $this->model->getStoreId());
    }

    public function testGetCategoryCollection()
    {
        $collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resource->expects($this->once())->method('getCategoryCollection')->willReturn($collection);
        $this->assertInstanceOf(Collection::class, $this->model->getCategoryCollection());
    }

    /**
     * @dataProvider getCategoryCollectionCollectionNullDataProvider
     */
    public function testGetCategoryCollectionCollectionNull($initCategoryCollection, $getIdResult, $productIdCached)
    {
        $product = $this->createPartialMock(
            \Magento\Catalog\Model\Product::class,
            [
                '_getResource',
                'setCategoryCollection',
                'getId',
            ]
        );

        $abstractDbMock = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getCategoryCollection',
                ]
            )
            ->getMockForAbstractClass();
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

        $productIdCachedActual = $this->getPropertyValue($product, '_productIdCached', $productIdCached);
        $this->assertEquals($getIdResult, $productIdCachedActual);
        $this->assertEquals($initCategoryCollection, $result);
    }

    /**
     * @return array
     */
    public function getCategoryCollectionCollectionNullDataProvider()
    {
        return [
            [
                '$initCategoryCollection' => null,
                '$getIdResult' => 'getIdResult value',
                '$productIdCached' => 'productIdCached value',
            ],
            [
                '$initCategoryCollection' => 'value',
                '$getIdResult' => 'getIdResult value',
                '$productIdCached' => 'not getIdResult value',
            ],
        ];
    }

    public function testSetCategoryCollection()
    {
        $collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resource->expects($this->once())->method('getCategoryCollection')->willReturn($collection);
        $this->assertSame($this->model->getCategoryCollection(), $this->model->getCategoryCollection());
    }

    public function testGetCategory()
    {
        $this->model->setData('category_ids', [10]);
        $this->category->expects($this->any())->method('getId')->willReturn(10);
        $this->registry->expects($this->any())->method('registry')->willReturn($this->category);
        $this->categoryRepository->expects($this->any())->method('get')->willReturn($this->category);
        $this->assertInstanceOf(Category::class, $this->model->getCategory());
    }

    public function testGetCategoryId()
    {
        $this->model->setData('category_ids', [10]);
        $this->category->expects($this->any())->method('getId')->willReturn(10);

        $this->registry->expects($this->at(0))->method('registry');
        $this->registry->expects($this->at(1))->method('registry')->willReturn($this->category);
        $this->assertFalse($this->model->getCategoryId());
        $this->assertEquals(10, $this->model->getCategoryId());
    }

    public function testGetIdBySku()
    {
        $this->resource->expects($this->once())->method('getIdBySku')->willReturn(5);
        $this->assertEquals(5, $this->model->getIdBySku('someSku'));
    }

    public function testGetCategoryIds()
    {
        $this->model->lockAttribute('category_ids');
        $this->assertEquals([], $this->model->getCategoryIds());
    }

    public function testGetStatusInitial()
    {
        $this->assertEquals(Status::STATUS_ENABLED, $this->model->getStatus());
    }

    public function testGetStatus()
    {
        $this->model->setStatus(null);
        $this->assertEquals(Status::STATUS_ENABLED, $this->model->getStatus());
    }

    public function testIsInStock()
    {
        $this->model->setStatus(Status::STATUS_ENABLED);
        $this->assertTrue($this->model->isInStock());
    }

    public function testIndexerAfterDeleteCommitProduct()
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
     * @dataProvider getProductReindexProvider
     */
    public function testReindex($productChanged, $isScheduled, $productFlatCount, $categoryIndexerCount)
    {
        $this->model->setData('entity_id', 1);
        $this->_catalogProduct->expects($this->once())
            ->method('isDataForProductCategoryIndexerWasChanged')
            ->willReturn($productChanged);
        if ($productChanged) {
            $this->indexerRegistryMock->expects($this->exactly($productFlatCount))
                ->method('get')
                ->with(\Magento\Catalog\Model\Indexer\Product\Category::INDEXER_ID)
                ->willReturn($this->categoryIndexerMock);
            $this->categoryIndexerMock->expects($this->any())
                ->method('isScheduled')
                ->willReturn($isScheduled);
            $this->categoryIndexerMock->expects($this->exactly($categoryIndexerCount))->method('reindexRow');
        }
        $this->productFlatProcessor->expects($this->exactly($productFlatCount))->method('reindexRow');
        $this->model->reindex();
    }

    /**
     * @return array
     */
    public function getProductReindexProvider()
    {
        return [
            'set 1' => [true, false, 1, 1],
            'set 2' => [true, true, 1, 0],
            'set 3' => [false, false, 1, 0]
        ];
    }

    public function testPriceReindexCallback()
    {
        $this->model = $this->objectManagerHelper->getObject(
            \Magento\Catalog\Model\Product::class,
            [
                'catalogProductType' => $this->productTypeInstanceMock,
                'categoryIndexer' => $this->categoryIndexerMock,
                'productFlatIndexerProcessor' => $this->productFlatProcessor,
                'productPriceIndexerProcessor' => $this->productPriceProcessor,
                'catalogProductOption' => $this->optionInstanceMock,
                'resource' => $this->resource,
                'registry' => $this->registry,
                'categoryRepository' => $this->categoryRepository,
                'data' => []
            ]
        );
        $this->productPriceProcessor->expects($this->once())->method('reindexRow');
        $this->assertNull($this->model->priceReindexCallback());
    }

    /**
     * @dataProvider getIdentitiesProvider
     * @param array $expected
     * @param array $origData
     * @param array $data
     * @param bool $isDeleted
     */
    public function testGetIdentities($expected, $origData, $data, $isDeleted = false)
    {
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
        $this->assertEquals($expected, $this->model->getIdentities());
    }

    /**
     * @return array
     */
    public function getIdentitiesProvider()
    {
        $extensionAttributesMock = $this->getMockBuilder(ExtensionAttributesInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStockItem'])->getMockForAbstractClass();
        $stockItemMock = $this->getMockBuilder(StockItemInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $extensionAttributesMock->expects($this->any())->method('getStockItem')->willReturn($stockItemMock);
        $stockItemMock->expects($this->any())->method('getIsInStock')->willReturn(true);

        return [
            'no changes' => [
                ['cat_p_1'],
                ['id' => 1, 'name' => 'value', 'category_ids' => [1]],
                ['id' => 1, 'name' => 'value', 'category_ids' => [1]],
            ],
            'new product' => $this->getNewProductProviderData(),
            'status and category change' => [
                [0 => 'cat_p_1', 1 => 'cat_c_p_1', 2 => 'cat_c_p_2'],
                ['id' => 1, 'name' => 'value', 'category_ids' => [1], 'status' => 2],
                [
                    'id' => 1,
                    'name' => 'value',
                    'category_ids' => [2],
                    'status' => 1,
                    'affected_category_ids' => [1, 2],
                    'is_changed_categories' => true
                ],
            ],
            'status change only' => [
                [0 => 'cat_p_1', 1 => 'cat_c_p_7'],
                ['id' => 1, 'name' => 'value', 'category_ids' => [7], 'status' => 1],
                ['id' => 1, 'name' => 'value', 'category_ids' => [7], 'status' => 2],
            ],
            'status changed, category unassigned' => $this->getStatusAndCategoryChangesData(),
            'no status changes' => [
                [0 => 'cat_p_1'],
                ['id' => 1, 'name' => 'value', 'category_ids' => [1], 'status' => 1],
                ['id' => 1, 'name' => 'value', 'category_ids' => [1], 'status' => 1],
            ],
            'no stock status changes' => [
                [0 => 'cat_p_1'],
                ['id' => 1, 'name' => 'value', 'category_ids' => [1], 'status' => 1],
                [
                    'id' => 1,
                    'name' => 'value',
                    'category_ids' => [1],
                    'status' => 1,
                    'stock_data' => ['is_in_stock' => true],
                    ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY => $extensionAttributesMock,
                ],
            ],
            'no stock status data 1' => [
                [0 => 'cat_p_1'],
                ['id' => 1, 'name' => 'value', 'category_ids' => [1], 'status' => 1],
                [
                    'id' => 1,
                    'name' => 'value',
                    'category_ids' => [1],
                    'status' => 1,
                    ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY => $extensionAttributesMock,
                ],
            ],
            'no stock status data 2' => [
                [0 => 'cat_p_1'],
                ['id' => 1, 'name' => 'value', 'category_ids' => [1], 'status' => 1],
                [
                    'id' => 1,
                    'name' => 'value',
                    'category_ids' => [1],
                    'status' => 1,
                    'stock_data' => ['is_in_stock' => true],
                ],
            ],
            'stock status changes' => $this->getStatusStockProviderData($extensionAttributesMock),
        ];
    }

    /**
     * @return array
     */
    private function getStatusAndCategoryChangesData()
    {
        return [
            [0 => 'cat_p_1', 1 => 'cat_c_p_5'],
            ['id' => 1, 'name' => 'value', 'category_ids' => [5], 'status' => 2],
            [
                'id' => 1,
                'name' => 'value',
                'category_ids' => [],
                'status' => 1,
                'is_changed_categories' => true,
                'affected_category_ids' => [5]
            ],
        ];
    }

    /**
     * @return array
     */
    private function getNewProductProviderData()
    {
        return [
            ['cat_p_1', 'cat_c_p_1'],
            null,
            [
                'id' => 1,
                'name' => 'value',
                'category_ids' => [1],
                'affected_category_ids' => [1],
                'is_changed_categories' => true
            ]
        ];
    }

    /**
     * @param MockObject $extensionAttributesMock
     * @return array
     */
    private function getStatusStockProviderData($extensionAttributesMock)
    {
        return [
            [0 => 'cat_p_1', 1 => 'cat_c_p_1'],
            ['id' => 1, 'name' => 'value', 'category_ids' => [1], 'status' => 1],
            [
                'id' => 1,
                'name' => 'value',
                'category_ids' => [1],
                'status' => 1,
                'stock_data' => ['is_in_stock' => false],
                ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY => $extensionAttributesMock,
            ],
        ];
    }

    /**
     * Test retrieving price Info
     */
    public function testGetPriceInfo()
    {
        $this->productTypeInstanceMock->expects($this->once())
            ->method('getPriceInfo')
            ->with($this->model)
            ->willReturn($this->_priceInfoMock);
        $this->assertEquals($this->model->getPriceInfo(), $this->_priceInfoMock);
    }

    /**
     * Test for set qty
     */
    public function testSetQty()
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
     * Test reload PriceInfo
     */
    public function testReloadPriceInfo()
    {
        $this->productTypeInstanceMock->expects($this->exactly(2))
            ->method('getPriceInfo')
            ->with($this->model)
            ->willReturn($this->_priceInfoMock);
        $this->assertEquals($this->_priceInfoMock, $this->model->getPriceInfo());
        $this->assertEquals($this->_priceInfoMock, $this->model->reloadPriceInfo());
    }

    /**
     * Test for get qty
     */
    public function testGetQty()
    {
        $this->model->setQty(1);
        $this->assertEquals(1, $this->model->getQty());
    }

    /**
     *  Test for `save` method
     */
    public function testSave()
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
     * Image cache generation would not be performed if area was emulated
     */
    public function testSaveIfAreaEmulated()
    {
        $this->appStateMock->expects($this->any())->method('isAreaCodeEmulated')->willReturn(true);
        $this->imageCache->expects($this->never())
            ->method('generate')
            ->with($this->model);
        $this->configureSaveTest();
        $this->model->beforeSave();
        $this->model->afterSave();
    }

    /**
     *  Test for `save` method for duplicated product
     */
    public function testSaveAndDuplicate()
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

    public function testGetIsSalableSimple()
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

    public function testGetIsSalableHasDataIsSaleable()
    {
        $typeInstanceMock = $this->createMock(Simple::class);

        $this->model->setTypeInstance($typeInstanceMock);
        $this->model->setData('is_saleable', true);
        $this->model->setData('is_salable', false);

        self::assertTrue($this->model->getIsSalable());
    }

    /**
     * Configure environment for `testSave` and `testSaveAndDuplicate` methods
     */
    protected function configureSaveTest()
    {
        $productTypeMock = $this->getMockBuilder(Simple::class)
            ->disableOriginalConstructor()
            ->setMethods(['beforeSave', 'save'])->getMock();
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
    public function testFromArray()
    {
        $data = [
            'stock_item' => ['stock-item-data'],
        ];

        $stockItemMock = $this->getMockForAbstractClass(
            AbstractSimpleObject::class,
            [],
            '',
            false,
            true,
            true,
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
        $stockItemMock->expects($this->once())->method('setProduct')->with($this->model);

        $this->assertEquals($this->model, $this->model->fromArray($data));
    }

    protected function prepareCategoryIndexer()
    {
        $this->indexerRegistryMock->expects($this->once())
            ->method('get')
            ->with(\Magento\Catalog\Model\Indexer\Product\Category::INDEXER_ID)
            ->willReturn($this->categoryIndexerMock);
    }

    /**
     *  Test for getProductLinks()
     */
    public function testGetProductLinks()
    {
        $outputRelatedLink = $this->objectManagerHelper->getObject(Link::class);
        $outputRelatedLink->setSku("Simple Product 1");
        $outputRelatedLink->setLinkType("related");
        $outputRelatedLink->setLinkedProductSku("Simple Product 2");
        $outputRelatedLink->setLinkedProductType("simple");
        $outputRelatedLink->setPosition(0);
        $expectedOutput = [$outputRelatedLink];
        $this->productLinkRepositoryMock->expects($this->once())->method('getList')->willReturn($expectedOutput);
        $typeInstance = $this->getMockBuilder(AbstractType::class)
            ->setMethods(['getSku'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $typeInstance->method('getSku')->willReturn('model');
        $this->productTypeInstanceMock->method('factory')->willReturn($typeInstance);
        $links = $this->model->getProductLinks();
        $this->assertEquals($links, $expectedOutput);
    }

    /**
     *  Test for setProductLinks()
     */
    public function testSetProductLinks()
    {
        $link = $this->objectManagerHelper->getObject(Link::class);
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
     * Set up two media attributes: image and small_image
     */
    protected function setupMediaAttributes()
    {
        $productType = $this->getMockBuilder(AbstractType::class)
            ->setMethods(['getSetAttributes'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->productTypeInstanceMock->expects($this->any())->method('factory')->willReturn(
            $productType
        );

        $frontendMock = $this->getMockBuilder(AbstractFrontend::class)
            ->disableOriginalConstructor()
            ->setMethods(['getInputType'])
            ->getMockForAbstractClass();
        $frontendMock->expects($this->any())->method('getInputType')->willReturn('media_image');
        $attributeImage = $this->getMockBuilder(AbstractAttribute::class)
            ->setMethods(['getFrontend', 'getAttributeCode'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $attributeImage->expects($this->any())
            ->method('getFrontend')
            ->willReturn($frontendMock);
        $attributeImage->expects($this->any())->method('getAttributeCode')->willReturn('image');
        $attributeSmallImage = $this->getMockBuilder(AbstractAttribute::class)
            ->setMethods(['getFrontend', 'getAttributeCode'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $attributeSmallImage->expects($this->any())
            ->method('getFrontend')
            ->willReturn($frontendMock);
        $attributeSmallImage->expects($this->any())->method('getAttributeCode')->willReturn('small_image');

        $productType->expects($this->any())->method('getSetAttributes')->willReturn(
            ['image' => $attributeImage, 'small_image' => $attributeSmallImage]
        );

        return [$attributeImage, $attributeSmallImage];
    }

    public function getMediaAttributes()
    {
        $expected = [];
        $mediaAttributes = $this->setupMediaAttributes();
        foreach ($mediaAttributes as $mediaAttribute) {
            $expected[$mediaAttribute->getAttributeCode()] = $mediaAttribute;
        }
        $this->assertEquals($expected, $this->model->getMediaAttributes());
    }

    public function testGetMediaAttributeValues()
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

    public function testGetMediaGalleryEntriesNone()
    {
        $this->assertNull($this->model->getMediaGalleryEntries());
    }

    public function testGetMediaGalleryEntries()
    {
        $this->setupMediaAttributes();
        $this->model->setData('image', 'imageFile.jpg');
        $this->model->setData('small_image', 'smallImageFile.jpg');

        $mediaEntries = [
            'images' => [
                [
                    'value_id' => 1,
                    'file' => 'imageFile.jpg',
                    'media_type' => 'image',
                ],
                [
                    'value_id' => 2,
                    'file' => 'smallImageFile.jpg',
                    'media_type' => 'image',
                ],
            ]
        ];
        $this->model->setData('media_gallery', $mediaEntries);

        $entry1 =
            $this->createMock(
                ProductAttributeMediaGalleryEntryInterface::class
            );
        $entry2 =
            $this->createMock(
                ProductAttributeMediaGalleryEntryInterface::class
            );

        $this->converterMock->expects($this->exactly(2))->method('convertTo')->willReturnOnConsecutiveCalls(
            $entry1,
            $entry2
        );

        $this->assertEquals([$entry1, $entry2], $this->model->getMediaGalleryEntries());
    }

    public function testSetMediaGalleryEntries()
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
            ],
        ];

        $entryMock = $this->getMockBuilder(ProductAttributeMediaGalleryEntryInterface::class)
            ->setMethods(
                [
                    'getId',
                    'getFile',
                    'getLabel',
                    'getPosition',
                    'isDisabled',
                    'types',
                    'getContent',
                    'getMediaType'
                ]
            )
            ->getMockForAbstractClass();

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

    public function testGetMediaGalleryImagesMerging()
    {
        $mediaEntries =
            [
                'images' => [
                    [
                        'value_id' => 1,
                        'file' => 'imageFile.jpg',
                        'media_type' => 'image',
                    ],
                    [
                        'value_id' => 3,
                        'file' => 'imageFile.jpg',
                    ],
                    [
                        'value_id' => 2,
                        'file' => 'smallImageFile.jpg',
                        'media_type' => 'image',
                    ],
                ]
            ];
        $expectedImageDataObject = new DataObject(
            [
                'value_id' => 1,
                'file' => 'imageFile.jpg',
                'media_type' => 'image',
                'url' => 'http://magento.dev/pub/imageFile.jpg',
                'id' => 1,
                'path' => '/var/www/html/pub/imageFile.jpg',
            ]
        );
        $expectedSmallImageDataObject = new DataObject(
            [
                'value_id' => 2,
                'file' => 'smallImageFile.jpg',
                'media_type' => 'image',
                'url' => 'http://magento.dev/pub/smallImageFile.jpg',
                'id' => 2,
                'path' => '/var/www/html/pub/smallImageFile.jpg',
            ]
        );

        $directoryMock = $this->getMockForAbstractClass(ReadInterface::class);
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
                [3, 'not_null_skeep_foreache'],
            ]
        );
        $imagesCollectionMock->expects(self::exactly(2))->method('addItem')
            ->withConsecutive(
                [$expectedImageDataObject],
                [$expectedSmallImageDataObject]
            );
        $this->collectionFactoryMock->method('create')->willReturn($imagesCollectionMock);

        $this->model->getMediaGalleryImages();
    }

    public function testGetCustomAttributes()
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
    public function priceDataProvider()
    {
        return [
            'receive empty array' => [[]],
            'receive null' => [null],
            'receive non-empty array' => [['non-empty', 'array', 'of', 'values']]
        ];
    }

    public function testGetOptions()
    {
        $option1Id = 2;
        $optionMock1 = $this->getMockBuilder(Option::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'setProduct'])
            ->getMock();
        $option2Id = 3;
        $optionMock2 = $this->getMockBuilder(Option::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'setProduct'])
            ->getMock();
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

    public function testGetFinalPrice()
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

    public function testGetFinalPricePreset()
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

    public function testGetTypeId()
    {
        $productType = $this->getMockBuilder(Virtual::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->productTypeInstanceMock->expects($this->exactly(2))->method('factory')->willReturn(
            $productType
        );

        $this->model->getTypeInstance();
        $this->model->setTypeId('typeId');
        $this->model->getTypeInstance();
    }

    public function testGetOptionById()
    {
        $optionId = 100;
        $optionMock = $this->createMock(Option::class);
        $this->model->setOptions([$optionMock]);
        $optionMock->expects($this->once())->method('getId')->willReturn($optionId);
        $this->assertEquals($optionMock, $this->model->getOptionById($optionId));
    }

    public function testGetOptionByIdWithWrongOptionId()
    {
        $optionId = 100;
        $optionMock = $this->createMock(Option::class);
        $this->model->setOptions([$optionMock]);
        $optionMock->expects($this->once())->method('getId')->willReturn(200);
        $this->assertNull($this->model->getOptionById($optionId));
    }

    public function testGetOptionByIdForProductWithoutOptions()
    {
        $this->assertNull($this->model->getOptionById(100));
    }
}
