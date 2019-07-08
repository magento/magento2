<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model;

use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\Api\Data\ImageContentInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Product Test
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 *
 */
class ProductTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
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
     * @var \Magento\Framework\Module\ModuleManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $moduleManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockItemFactoryMock;

    /**
     * @var \Magento\Framework\Indexer\IndexerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $categoryIndexerMock;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Processor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productFlatProcessor;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productPriceProcessor;

    /**
     * @var Product\Type|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productTypeInstanceMock;

    /**
     * @var Product\Option|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionInstanceMock;

    /**
     * @var \Magento\Framework\Pricing\PriceInfo\Base|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_priceInfoMock;

    /**
     * @var \Magento\Catalog\Model\FilterProductCustomAttribute|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterCustomAttribute;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    private $store;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resource;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $registry;

    /**
     * @var \Magento\Catalog\Model\Category|\PHPUnit_Framework_MockObject_MockObject
     */
    private $category;

    /**
     * @var \Magento\Store\Model\Website|\PHPUnit_Framework_MockObject_MockObject
     */
    private $website;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexerRegistryMock;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryRepository;

    /**
     * @var \Magento\Catalog\Helper\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $_catalogProduct;

    /**
     * @var \Magento\Catalog\Model\Product\Image\Cache|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $imageCache;

    /**
     * @var \Magento\Catalog\Model\Product\Image\CacheFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $imageCacheFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $mediaGalleryEntryFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataObjectHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataServiceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeValueFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $mediaGalleryEntryConverterPoolMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $converterMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $mediaConfig;

    /**
     * @var \Magento\Framework\App\State|\PHPUnit_Framework_MockObject_MockObject
     */
    private $appStateMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $extensionAttributes;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $extensionAttributesFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystemMock;

    /**
     * @var \Magento\Framework\Data\CollectionFactory
     */
    private $collectionFactoryMock;

    /**
     * @var ProductExtensionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productExtAttributes;

    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eavConfig;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->categoryIndexerMock = $this->getMockForAbstractClass(\Magento\Framework\Indexer\IndexerInterface::class);

        $this->moduleManager = $this->createPartialMock(
            \Magento\Framework\Module\ModuleManagerInterface::class,
            ['isEnabled']
        );
        $this->extensionAttributes = $this->getMockBuilder(\Magento\Framework\Api\ExtensionAttributesInterface::class)
            ->setMethods(['getWebsiteIds', 'setWebsiteIds'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->stockItemFactoryMock = $this->createPartialMock(
            \Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory::class,
            ['create']
        );
        $this->dataObjectHelperMock = $this->getMockBuilder(\Magento\Framework\Api\DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productFlatProcessor = $this->createMock(
            \Magento\Catalog\Model\Indexer\Product\Flat\Processor::class
        );

        $this->_priceInfoMock = $this->createMock(\Magento\Framework\Pricing\PriceInfo\Base::class);
        $this->productTypeInstanceMock = $this->createMock(\Magento\Catalog\Model\Product\Type::class);
        $this->productPriceProcessor = $this->createMock(
            \Magento\Catalog\Model\Indexer\Product\Price\Processor::class
        );

        $this->appStateMock = $this->createPartialMock(
            \Magento\Framework\App\State::class,
            ['getAreaCode', 'isAreaCodeEmulated']
        );
        $this->appStateMock->expects($this->any())
            ->method('getAreaCode')
            ->will($this->returnValue(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE));

        $this->eventManagerMock = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $actionValidatorMock = $this->createMock(
            \Magento\Framework\Model\ActionValidator\RemoveAction::class
        );
        $actionValidatorMock->expects($this->any())->method('isAllowed')->will($this->returnValue(true));
        $cacheInterfaceMock = $this->createMock(\Magento\Framework\App\CacheInterface::class);

        $contextMock = $this->createPartialMock(
            \Magento\Framework\Model\Context::class,
            ['getEventDispatcher', 'getCacheManager', 'getAppState', 'getActionValidator'],
            [],
            '',
            false
        );
        $contextMock->expects($this->any())->method('getAppState')->will($this->returnValue($this->appStateMock));
        $contextMock->expects($this->any())
            ->method('getEventDispatcher')
            ->will($this->returnValue($this->eventManagerMock));
        $contextMock->expects($this->any())
            ->method('getCacheManager')
            ->will($this->returnValue($cacheInterfaceMock));
        $contextMock->expects($this->any())
            ->method('getActionValidator')
            ->will($this->returnValue($actionValidatorMock));

        $this->optionInstanceMock = $this->getMockBuilder(\Magento\Catalog\Model\Product\Option::class)
            ->setMethods(['setProduct', 'saveOptions', '__wakeup', '__sleep'])
            ->disableOriginalConstructor()->getMock();

        $optionFactory = $this->createPartialMock(
            \Magento\Catalog\Model\Product\OptionFactory::class,
            ['create']
        );
        $optionFactory->expects($this->any())->method('create')->willReturn($this->optionInstanceMock);

        $this->resource = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->registry = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->category = $this->getMockBuilder(\Magento\Catalog\Model\Category::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->store = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->website = $this->getMockBuilder(\Magento\Store\Model\Website::class)
            ->disableOriginalConstructor()
            ->getMock();

        $storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $storeManager->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($this->store));
        $storeManager->expects($this->any())
            ->method('getWebsite')
            ->will($this->returnValue($this->website));
        $this->indexerRegistryMock = $this->createPartialMock(
            \Magento\Framework\Indexer\IndexerRegistry::class,
            ['get']
        );
        $this->categoryRepository = $this->createMock(\Magento\Catalog\Api\CategoryRepositoryInterface::class);

        $this->_catalogProduct = $this->createPartialMock(
            \Magento\Catalog\Helper\Product::class,
            ['isDataForProductCategoryIndexerWasChanged']
        );

        $this->imageCache = $this->getMockBuilder(\Magento\Catalog\Model\Product\Image\Cache::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->imageCacheFactory = $this->getMockBuilder(\Magento\Catalog\Model\Product\Image\CacheFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->mediaGalleryEntryFactoryMock =
            $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterfaceFactory::class)
                ->setMethods(['create'])
                ->disableOriginalConstructor()
                ->getMock();

        $this->metadataServiceMock = $this->createMock(\Magento\Catalog\Api\ProductAttributeRepositoryInterface::class);
        $this->attributeValueFactory = $this->getMockBuilder(\Magento\Framework\Api\AttributeValueFactory::class)
            ->disableOriginalConstructor()->getMock();

        $this->mediaGalleryEntryConverterPoolMock =
            $this->createPartialMock(
                \Magento\Catalog\Model\Product\Attribute\Backend\Media\EntryConverterPool::class,
                ['getConverterByMediaType']
            );

        $this->converterMock =
            $this->createMock(
                \Magento\Catalog\Model\Product\Attribute\Backend\Media\ImageEntryConverter::class
            );

        $this->mediaGalleryEntryConverterPoolMock->expects($this->any())->method('getConverterByMediaType')
            ->willReturn($this->converterMock);
        $this->productLinkRepositoryMock = $this->getMockBuilder(
            \Magento\Catalog\Api\ProductLinkRepositoryInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->extensionAttributesFactory = $this->getMockBuilder(ExtensionAttributesFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filesystemMock = $this->getMockBuilder(\Magento\Framework\Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->collectionFactoryMock = $this->getMockBuilder(\Magento\Framework\Data\CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->mediaConfig = $this->createMock(\Magento\Catalog\Model\Product\Media\Config::class);
        $this->eavConfig = $this->createMock(\Magento\Eav\Model\Config::class);

        $this->productExtAttributes = $this->getMockBuilder(ProductExtensionInterface::class)
            ->setMethods(['getStockItem'])
            ->getMockForAbstractClass();
        $this->extensionAttributesFactory
            ->expects($this->any())
            ->method('create')
            ->willReturn($this->productExtAttributes);

        $this->filterCustomAttribute = $this->createTestProxy(
            \Magento\Catalog\Model\FilterProductCustomAttribute::class
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
                'storeManager' => $storeManager,
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
        $productType = $this->getMockBuilder(\Magento\Catalog\Model\Product\Type\AbstractType::class)
            ->setMethods(['getSetAttributes'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->productTypeInstanceMock->expects($this->any())->method('factory')->will(
            $this->returnValue($productType)
        );
        $attribute = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class)
            ->setMethods(['__wakeup', 'isInGroup'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $attribute->expects($this->any())->method('isInGroup')->will($this->returnValue(true));
        $productType->expects($this->any())->method('getSetAttributes')->will(
            $this->returnValue([$attribute])
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
        $this->website->expects($this->once())->method('getStoreIds')->will($this->returnValue($expectedStoreIds));
        $this->assertEquals($expectedStoreIds, $this->model->getStoreIds());
    }

    public function testGetStoreId()
    {
        $this->model->setStoreId(3);
        $this->assertEquals(3, $this->model->getStoreId());
        $this->model->unsStoreId();
        $this->store->expects($this->once())->method('getId')->will($this->returnValue(5));
        $this->assertEquals(5, $this->model->getStoreId());
    }

    public function testGetCategoryCollection()
    {
        $collection = $this->getMockBuilder(\Magento\Framework\Data\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resource->expects($this->once())->method('getCategoryCollection')->will($this->returnValue($collection));
        $this->assertInstanceOf(\Magento\Framework\Data\Collection::class, $this->model->getCategoryCollection());
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

        $abstractDbMock = $this->getMockBuilder(\Magento\Framework\Model\ResourceModel\Db\AbstractDb::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                'getCategoryCollection',
                ]
            )
            ->getMockForAbstractClass();
        $getCategoryCollectionMock = $this->createMock(
            \Magento\Framework\Data\Collection::class
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
        $collection = $this->getMockBuilder(\Magento\Framework\Data\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resource->expects($this->once())->method('getCategoryCollection')->will($this->returnValue($collection));
        $this->assertSame($this->model->getCategoryCollection(), $this->model->getCategoryCollection());
    }

    public function testGetCategory()
    {
        $this->model->setData('category_ids', [10]);
        $this->category->expects($this->any())->method('getId')->will($this->returnValue(10));
        $this->registry->expects($this->any())->method('registry')->will($this->returnValue($this->category));
        $this->categoryRepository->expects($this->any())->method('get')->will($this->returnValue($this->category));
        $this->assertInstanceOf(\Magento\Catalog\Model\Category::class, $this->model->getCategory());
    }

    public function testGetCategoryId()
    {
        $this->model->setData('category_ids', [10]);
        $this->category->expects($this->any())->method('getId')->will($this->returnValue(10));

        $this->registry->expects($this->at(0))->method('registry');
        $this->registry->expects($this->at(1))->method('registry')->will($this->returnValue($this->category));
        $this->assertFalse($this->model->getCategoryId());
        $this->assertEquals(10, $this->model->getCategoryId());
    }

    public function testGetIdBySku()
    {
        $this->resource->expects($this->once())->method('getIdBySku')->will($this->returnValue(5));
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
                ->will($this->returnValue($this->categoryIndexerMock));
            $this->categoryIndexerMock->expects($this->any())
                ->method('isScheduled')
                ->will($this->returnValue($isScheduled));
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
        $extensionAttributesMock = $this->getMockBuilder(\Magento\Framework\Api\ExtensionAttributesInterface::class)
            ->disableOriginalConstructor()->setMethods(['getStockItem'])->getMock();
        $stockItemMock = $this->getMockBuilder(\Magento\CatalogInventory\Api\Data\StockItemInterface::class)
            ->disableOriginalConstructor()->getMock();
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
     * @param \PHPUnit_Framework_MockObject_MockObject $extensionAttributesMock
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
            ->with($this->equalTo($this->model))
            ->will($this->returnValue($this->_priceInfoMock));
        $this->assertEquals($this->model->getPriceInfo(), $this->_priceInfoMock);
    }

    /**
     * Test for set qty
     */
    public function testSetQty()
    {
        $this->productTypeInstanceMock->expects($this->exactly(2))
            ->method('getPriceInfo')
            ->with($this->equalTo($this->model))
            ->will($this->returnValue($this->_priceInfoMock));

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
            ->with($this->equalTo($this->model))
            ->will($this->returnValue($this->_priceInfoMock));
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
        $collection = $this->createMock(\Magento\Framework\Data\Collection::class);
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
        $collection = $this->createMock(\Magento\Framework\Data\Collection::class);
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
            $this->createPartialMock(\Magento\Catalog\Model\Product\Type\Simple::class, ['isSalable']);
        $typeInstanceMock
            ->expects($this->atLeastOnce())
            ->method('isSalable')
            ->willReturn(true);

        $this->model->setTypeInstance($typeInstanceMock);

        self::assertTrue($this->model->getIsSalable());
    }

    public function testGetIsSalableHasDataIsSaleable()
    {
        $typeInstanceMock = $this->createMock(\Magento\Catalog\Model\Product\Type\Simple::class);

        $this->model->setTypeInstance($typeInstanceMock);
        $this->model->setData('is_saleable', true);
        $this->model->setData('is_salable', false);

        self::assertTrue($this->model->getIsSalable());
    }

    /**
     * Configure environment for `testSave` and `testSaveAndDuplicate` methods
     *
     * @return array
     */
    protected function configureSaveTest()
    {
        $productTypeMock = $this->getMockBuilder(\Magento\Catalog\Model\Product\Type\Simple::class)
            ->disableOriginalConstructor()->setMethods(['beforeSave', 'save'])->getMock();
        $productTypeMock->expects($this->once())->method('beforeSave')->will($this->returnSelf());
        $productTypeMock->expects($this->once())->method('save')->will($this->returnSelf());

        $this->productTypeInstanceMock->expects($this->once())->method('factory')->with($this->model)
            ->will($this->returnValue($productTypeMock));

        $this->model->getResource()->expects($this->any())->method('addCommitCallback')->will($this->returnSelf());
        $this->model->getResource()->expects($this->any())->method('commit')->will($this->returnSelf());
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
            \Magento\Framework\Api\AbstractSimpleObject::class,
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
            ->will($this->returnValue(true));
        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray')
            ->with($stockItemMock, $data['stock_item'], \Magento\CatalogInventory\Api\Data\StockItemInterface::class)
            ->will($this->returnSelf());
        $this->stockItemFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($stockItemMock));
        $stockItemMock->expects($this->once())->method('setProduct')->with($this->model);

        $this->assertEquals($this->model, $this->model->fromArray($data));
    }

    protected function prepareCategoryIndexer()
    {
        $this->indexerRegistryMock->expects($this->once())
            ->method('get')
            ->with(\Magento\Catalog\Model\Indexer\Product\Category::INDEXER_ID)
            ->will($this->returnValue($this->categoryIndexerMock));
    }

    /**
     *  Test for getProductLinks()
     */
    public function testGetProductLinks()
    {
        $outputRelatedLink = $this->objectManagerHelper->getObject(\Magento\Catalog\Model\ProductLink\Link::class);
        $outputRelatedLink->setSku("Simple Product 1");
        $outputRelatedLink->setLinkType("related");
        $outputRelatedLink->setLinkedProductSku("Simple Product 2");
        $outputRelatedLink->setLinkedProductType("simple");
        $outputRelatedLink->setPosition(0);
        $expectedOutput = [$outputRelatedLink];
        $this->productLinkRepositoryMock->expects($this->once())->method('getList')->willReturn($expectedOutput);
        $links = $this->model->getProductLinks();
        $this->assertEquals($links, $expectedOutput);
    }

    /**
     *  Test for setProductLinks()
     */
    public function testSetProductLinks()
    {
        $link = $this->objectManagerHelper->getObject(\Magento\Catalog\Model\ProductLink\Link::class);
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
        $productType = $this->getMockBuilder(\Magento\Catalog\Model\Product\Type\AbstractType::class)
            ->setMethods(['getSetAttributes'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->productTypeInstanceMock->expects($this->any())->method('factory')->will(
            $this->returnValue($productType)
        );

        $frontendMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend::class)
            ->disableOriginalConstructor()
            ->setMethods(['getInputType'])
            ->getMockForAbstractClass();
        $frontendMock->expects($this->any())->method('getInputType')->willReturn('media_image');
        $attributeImage = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class)
            ->setMethods(['__wakeup', 'getFrontend', 'getAttributeCode'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $attributeImage->expects($this->any())
            ->method('getFrontend')
            ->willReturn($frontendMock);
        $attributeImage->expects($this->any())->method('getAttributeCode')->willReturn('image');
        $attributeSmallImage = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class)
            ->setMethods(['__wakeup', 'getFrontend', 'getAttributeCode'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $attributeSmallImage->expects($this->any())
            ->method('getFrontend')
            ->willReturn($frontendMock);
        $attributeSmallImage->expects($this->any())->method('getAttributeCode')->willReturn('small_image');

        $productType->expects($this->any())->method('getSetAttributes')->will(
            $this->returnValue(['image' => $attributeImage, 'small_image' => $attributeSmallImage])
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
                \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface::class
            );
        $entry2 =
            $this->createMock(
                \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface::class
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

        $entryMock = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface::class)
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
            'images' =>
                [
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
        $expectedImageDataObject = new \Magento\Framework\DataObject(
            [
            'value_id' => 1,
            'file' => 'imageFile.jpg',
            'media_type' => 'image',
            'url' => 'http://magento.dev/pub/imageFile.jpg',
            'id' => 1,
            'path' => '/var/www/html/pub/imageFile.jpg',
            ]
        );
        $expectedSmallImageDataObject = new \Magento\Framework\DataObject(
            [
            'value_id' => 2,
            'file' => 'smallImageFile.jpg',
            'media_type' => 'image',
            'url' => 'http://magento.dev/pub/smallImageFile.jpg',
            'id' => 2,
            'path' => '/var/www/html/pub/smallImageFile.jpg',
            ]
        );

        $directoryMock = $this->createMock(\Magento\Framework\Filesystem\Directory\ReadInterface::class);
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
        $imagesCollectionMock = $this->createMock(\Magento\Framework\Data\Collection::class);
        $imagesCollectionMock->method('count')->willReturn(0);
        $imagesCollectionMock->method('getItemById')->willReturnMap(
            [
                [1, null],
                [2, null],
                [3, 'not_null_skeep_foreache'],
            ]
        );
        $imagesCollectionMock->expects(self::exactly(2))->method('addItem')->withConsecutive(
            $expectedImageDataObject,
            $expectedSmallImageDataObject
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
        $attributeValue = new \Magento\Framework\Api\AttributeValue();
        $attributeValue2 = new \Magento\Framework\Api\AttributeValue();
        $this->attributeValueFactory->expects($this->exactly(2))->method('create')
            ->willReturnOnConsecutiveCalls($attributeValue, $attributeValue2);
        $this->assertEquals(1, count($this->model->getCustomAttributes()));
        $this->assertNotNull($this->model->getCustomAttribute($customAttributeCode));
        $this->assertEquals(
            $initialCustomAttributeValue,
            $this->model->getCustomAttribute($customAttributeCode)->getValue()
        );

        //Change the attribute value, should reflect in getCustomAttribute
        $this->model->setCustomAttribute($customAttributeCode, $newCustomAttributeValue);
        $this->assertEquals(1, count($this->model->getCustomAttributes()));
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
        $optionMock1 = $this->getMockBuilder(\Magento\Catalog\Model\Product\Option::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'setProduct'])
            ->getMock();
        $option2Id = 3;
        $optionMock2 = $this->getMockBuilder(\Magento\Catalog\Model\Product\Option::class)
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
            \Magento\Catalog\Model\Product\Type\Price::class,
            ['getFinalPrice']
        );

        $productTypePriceMock->expects($this->any())
            ->method('getFinalPrice')
            ->with($qty, $this->model)
            ->will($this->returnValue($finalPrice));

        $this->productTypeInstanceMock->expects($this->any())
            ->method('priceFactory')
            ->with($this->model->getTypeId())
            ->will($this->returnValue($productTypePriceMock));

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
            \Magento\Catalog\Model\Product\Type\Price::class,
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
        $productType = $this->getMockBuilder(\Magento\Catalog\Model\Product\Type\Virtual::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->productTypeInstanceMock->expects($this->exactly(2))->method('factory')->will(
            $this->returnValue($productType)
        );

        $this->model->getTypeInstance();
        $this->model->setTypeId('typeId');
        $this->model->getTypeInstance();
    }

    public function testGetOptionById()
    {
        $optionId = 100;
        $optionMock = $this->createMock(\Magento\Catalog\Model\Product\Option::class);
        $this->model->setOptions([$optionMock]);
        $optionMock->expects($this->once())->method('getId')->willReturn($optionId);
        $this->assertEquals($optionMock, $this->model->getOptionById($optionId));
    }

    public function testGetOptionByIdWithWrongOptionId()
    {
        $optionId = 100;
        $optionMock = $this->createMock(\Magento\Catalog\Model\Product\Option::class);
        $this->model->setOptions([$optionMock]);
        $optionMock->expects($this->once())->method('getId')->willReturn(200);
        $this->assertNull($this->model->getOptionById($optionId));
    }

    public function testGetOptionByIdForProductWithoutOptions()
    {
        $this->assertNull($this->model->getOptionById(100));
    }
}
