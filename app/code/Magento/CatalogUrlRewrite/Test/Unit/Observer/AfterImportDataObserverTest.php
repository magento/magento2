<?php declare(strict_types=1);

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogUrlRewrite\Test\Unit\Observer;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Eav\Model\ResourceModel\AttributeValue;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogImportExport\Model\Import\Product as ImportProduct;
use Magento\CatalogUrlRewrite\Model\ObjectRegistry;
use Magento\CatalogUrlRewrite\Model\ObjectRegistryFactory;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Observer\AfterImportDataObserver;
use Magento\CatalogUrlRewrite\Service\V1\StoreViewService;
use Magento\Framework\Event;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use Magento\UrlRewrite\Model\MergeDataProvider;
use Magento\UrlRewrite\Model\MergeDataProviderFactory;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AfterImportDataObserverTest extends TestCase
{
    /**
     * @var UrlPersistInterface|MockObject
     */
    private $urlPersist;

    /**
     * @var UrlFinderInterface|MockObject
     */
    protected $urlFinder;

    /**
     * @var ProductUrlRewriteGenerator|MockObject
     */
    private $productUrlRewriteGenerator;

    /**
     * @var ProductRepositoryInterface|MockObject
     */
    private $productRepository;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product|MockObject
     */
    protected $importProduct;

    /**
     * @var Observer|MockObject
     */
    private $observer;

    /**
     * @var Event|MockObject
     */
    private $event;

    /**
     * @var ProductFactory|MockObject
     */
    private $catalogProductFactory;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var ObjectRegistryFactory|MockObject
     */
    private $objectRegistryFactory;

    /**
     * @var ProductUrlPathGenerator|MockObject
     */
    private $productUrlPathGenerator;

    /**
     * @var StoreViewService|MockObject
     */
    private $storeViewService;

    /**
     * @var UrlRewriteFactory|MockObject
     */
    private $urlRewriteFactory;

    /**
     * @var UrlRewrite|MockObject
     */
    private $urlRewrite;

    /**
     * @var ObjectRegistry|MockObject
     */
    private $objectRegistry;

    /**
     * @var AfterImportDataObserver
     */
    private $import;

    /**
     * @var \Magento\Catalog\Model\Product|MockObject
     */
    private $product;

    /**
     * @var MergeDataProvider|MockObject
     */
    private $mergeDataProvider;

    /**
     * @var CategoryCollectionFactory|MockObject
     */
    private $categoryCollectionFactory;

    /**
     * @var AttributeValue|MockObject
     */
    private $attributeValue;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfig;

    /**
     * @var CollectionFactory|MockObject
     */
    private $collectionFactory;

    /**
     * Test products returned by getBunch method of event object.
     *
     * @var array
     */
    protected $products = [
        [
            'sku' => 'sku',
            'url_key' => 'value1',
            ImportProduct::COL_STORE => Store::DEFAULT_STORE_ID,
        ],
        [
            'sku' => 'sku3',
            'url_key' => 'value3',
            ImportProduct::COL_STORE => 'not global',
        ],
    ];

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var ImportProduct\SkuStorage|MockObject
     */
    private ImportProduct\SkuStorage|MockObject $skuStorageMock;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.TooManyFields)
     * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function setUp(): void
    {
        $this->skuStorageMock = $this->createMock(ImportProduct\SkuStorage::class);
        $this->importProduct = $this->createPartialMock(
            \Magento\CatalogImportExport\Model\Import\Product::class,
            [
                'getNewSku',
                'getProductCategories',
                'getProductWebsites',
                'getStoreIdByCode',
                'getCategoryProcessor',
            ]
        );
        $this->catalogProductFactory = $this->createPartialMock(
            ProductFactory::class,
            [
                'create',
            ]
        );
        $this->storeManager = $this
            ->getMockBuilder(
                StoreManagerInterface::class
            )
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'getWebsite',
                ]
            )
            ->getMockForAbstractClass();
        $this->event = $this->getMockBuilder(Event::class)
            ->addMethods(['getAdapter', 'getBunch'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->event->expects($this->any())->method('getAdapter')->willReturn($this->importProduct);
        $this->event->expects($this->any())->method('getBunch')->willReturn($this->products);
        $this->observer = $this->createPartialMock(Observer::class, ['getEvent']);
        $this->observer->expects($this->any())->method('getEvent')->willReturn($this->event);
        $this->urlPersist = $this->getMockBuilder(UrlPersistInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->productUrlRewriteGenerator =
            $this->getMockBuilder(ProductUrlRewriteGenerator::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['generate'])
                ->getMock();
        $this->productRepository = $this->getMockBuilder(ProductRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->objectRegistryFactory = $this->createMock(ObjectRegistryFactory::class);
        $this->productUrlPathGenerator = $this->createMock(
            ProductUrlPathGenerator::class
        );
        $this->storeViewService = $this->createMock(StoreViewService::class);
        $this->urlRewriteFactory = $this->createPartialMock(
            UrlRewriteFactory::class,
            [
                'create',
            ]
        );
        $this->urlFinder = $this
            ->getMockBuilder(UrlFinderInterface::class)
            ->onlyMethods(
                [
                    'findAllByData',
                ]
            )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->urlRewrite = $this
            ->getMockBuilder(UrlRewrite::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->product = $this
            ->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectRegistry = $this
            ->getMockBuilder(ObjectRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mergeDataProviderFactory = $this->createPartialMock(
            MergeDataProviderFactory::class,
            ['create']
        );
        $this->mergeDataProvider = new MergeDataProvider();
        $mergeDataProviderFactory->expects($this->once())->method('create')->willReturn($this->mergeDataProvider);
        $this->categoryCollectionFactory = $this->getMockBuilder(CategoryCollectionFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeValue = $this->getMockBuilder(AttributeValue::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->collectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->import = new AfterImportDataObserver(
            $this->catalogProductFactory,
            $this->objectRegistryFactory,
            $this->productUrlPathGenerator,
            $this->storeViewService,
            $this->storeManager,
            $this->urlPersist,
            $this->urlRewriteFactory,
            $this->urlFinder,
            $mergeDataProviderFactory,
            $this->categoryCollectionFactory,
            $this->scopeConfig,
            $this->collectionFactory,
            $this->attributeValue,
            $this->skuStorageMock
        );
    }

    /**
     * Test for afterImportData()
     * Covers afterImportData() + protected methods used inside
     *
     * @covers \Magento\CatalogUrlRewrite\Observer\AfterImportDataObserver
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testAfterImportData()
    {
        $newSku = [['entity_id' => 'value'], ['entity_id' => 'value3']];
        $websiteId = 'websiteId value';
        $productsCount = count($this->products);
        $websiteMock = $this->createPartialMock(
            Website::class,
            [
                'getStoreIds',
            ]
        );
        $storeIds = [1, Store::DEFAULT_STORE_ID];
        $websiteMock
            ->expects($this->once())
            ->method('getStoreIds')
            ->willReturn($storeIds);
        $this->storeManager
            ->expects($this->once())
            ->method('getWebsite')
            ->with($websiteId)
            ->willReturn($websiteMock);
        $this->importProduct
            ->expects($this->exactly($productsCount))
            ->method('getNewSku')
            ->willReturnCallback(fn($param) => match ([$param]) {
                [$this->products[0][ImportProduct::COL_SKU]] => $newSku[0],
                [$this->products[1][ImportProduct::COL_SKU]] => $newSku[1]
            });

        $this->importProduct
            ->expects($this->exactly($productsCount))
            ->method('getProductCategories')
            ->willReturnCallback(fn($param) => match ([$param]) {
                [$this->products[0][ImportProduct::COL_SKU]] => [],
                [$this->products[1][ImportProduct::COL_SKU]] => []
            });
        $getProductWebsitesCallsCount = $productsCount * 2;
        $this->importProduct
            ->expects($this->exactly($getProductWebsitesCallsCount))
            ->method('getProductWebsites')
            ->willReturnOnConsecutiveCalls(
                [$newSku[0]['entity_id'] => $websiteId],
                [$newSku[0]['entity_id'] => $websiteId],
                [$newSku[1]['entity_id'] => $websiteId],
                [$newSku[1]['entity_id'] => $websiteId]
            );
        $map = [
            [$this->products[0][ImportProduct::COL_STORE], $this->products[0][ImportProduct::COL_STORE]],
            [$this->products[1][ImportProduct::COL_STORE], $this->products[1][ImportProduct::COL_STORE]]
        ];
        $this->importProduct
            ->expects($this->exactly(1))
            ->method('getStoreIdByCode')
            ->willReturnMap($map);
        $mockProducts = [];
        foreach ($this->products as $productsKey => $productsValue) {
            $product = $this->createPartialMock(
                \Magento\Catalog\Model\Product::class,
                [
                    'getId',
                    'setId',
                    'getSku',
                    'setStoreId',
                    'getStoreId',
                ]
            );
            $product->expects($this->any())
                ->method('setId')
                ->with($newSku[$productsKey]['entity_id']);
            $product->expects($this->any())
                ->method('getId')
                ->willReturn($newSku[$productsKey]['entity_id']);
            $product->expects($this->any())
                ->method('getSku')
                ->willReturn($productsValue['sku']);
            $product->expects($this->any())->method('getStoreId')
                ->willReturn($productsValue[ImportProduct::COL_STORE]);
            $product->expects($this->any())
                ->method('setStoreId')
                ->with($productsValue[ImportProduct::COL_STORE]);
            $mockProducts[] = $product;
        }
        $this->catalogProductFactory
            ->expects($this->exactly($productsCount))
            ->method('create')
            ->willReturnOnConsecutiveCalls(...$mockProducts);
        $this->urlFinder->expects($this->any())->method('findAllByData')->willReturn([]);
        $this->productUrlPathGenerator->expects($this->any())->method('getUrlPathWithSuffix')
            ->willReturn('urlPathWithSuffix');
        $this->productUrlPathGenerator->expects($this->any())->method('getUrlPath')
            ->willReturn('urlPath');
        $this->productUrlPathGenerator->expects($this->any())->method('getCanonicalUrlPath')
            ->willReturn('canonicalUrlPath');
        $this->urlRewrite->expects($this->any())->method('setStoreId')->willReturnSelf();
        $this->urlRewrite->expects($this->any())->method('setEntityId')->willReturnSelf();
        $this->urlRewrite->expects($this->any())->method('setEntityType')->willReturnSelf();
        $this->urlRewrite->expects($this->any())->method('setRequestPath')->willReturnSelf();
        $this->urlRewrite->expects($this->any())->method('setTargetPath')->willReturnSelf();
        $this->urlRewrite->expects($this->any())->method('getTargetPath')->willReturn('targetPath');
        $this->urlRewrite->expects($this->any())->method('getRequestPath')->willReturn('requestPath');
        $this->urlRewrite->expects($this->any())->method('getStoreId')
            ->willReturnOnConsecutiveCalls(0, 'not global');
        $this->urlRewriteFactory->expects($this->any())->method('create')->willReturn($this->urlRewrite);
        $productUrls = [
            'requestPath_0' => $this->urlRewrite,
            'requestPath_not global' => $this->urlRewrite
        ];
        $this->urlPersist
            ->expects($this->once())
            ->method('replace')
            ->with($productUrls);
        $this->attributeValue->expects($this->once())
            ->method('getValuesMultiple')
            ->with(ProductInterface::class, [0], [ProductAttributeInterface::CODE_SEO_FIELD_URL_KEY], [1]);
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('catalog/seo/generate_category_product_rewrites')
            ->willReturn(true);
        $this->import->execute($this->observer);
    }

    /**
     * @param AfterImportDataObserver $object
     * @param string $property
     * @param mixed $value
     * @return void
     */
    protected function setPropertyValue($object, $property, $value)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $reflectionProperty = $reflection->getProperty($property);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);
    }

    /**
     * @param mixed $storeId
     * @param mixed $productId
     * @param mixed $requestPath
     * @param mixed $targetPath
     * @param mixed $redirectType
     * @param mixed $metadata
     * @param mixed $description
     */
    protected function currentUrlRewritesRegeneratorPrepareUrlRewriteMock(
        $storeId,
        $productId,
        $requestPath,
        $targetPath,
        $redirectType,
        $metadata,
        $description
    ) {
        $this->urlRewrite->expects($this->any())->method('setStoreId')->with($storeId)->willReturnSelf();
        $this->urlRewrite->expects($this->any())->method('setEntityId')->with($productId)->willReturnSelf();
        $this->urlRewrite->expects($this->any())->method('setEntityType')
            ->with(ProductUrlRewriteGenerator::ENTITY_TYPE)->willReturnSelf();
        $this->urlRewrite->expects($this->any())->method('setRequestPath')->with($requestPath)->willReturnSelf();
        $this->urlRewrite->expects($this->any())->method('setTargetPath')->with($targetPath)->willReturnSelf();
        $this->urlRewrite->expects($this->any())->method('setIsAutogenerated')->with(0)->willReturnSelf();
        $this->urlRewrite->expects($this->any())->method('setRedirectType')->with($redirectType)->willReturnSelf();
        $this->urlRewrite->expects($this->any())->method('setMetadata')->with($metadata)->willReturnSelf();
        $this->urlRewriteFactory->expects($this->any())->method('create')->willReturn($this->urlRewrite);
        $this->urlRewrite->expects($this->once())->method('setDescription')->with($description)->willReturnSelf();
    }

    /**
     * @param array $currentRewrites
     * @return array
     */
    protected function currentUrlRewritesRegeneratorGetCurrentRewritesMocks($currentRewrites)
    {
        $rewrites = [];
        foreach ($currentRewrites as $urlRewrite) {
            /**
             * @var MockObject
             */
            $url = $this->getMockBuilder(UrlRewrite::class)
                ->disableOriginalConstructor()
                ->getMock();
            foreach ($urlRewrite as $key => $value) {
                $url->expects($this->any())
                    ->method('get' . str_replace('_', '', ucwords($key, '_')))
                    ->willReturn($value);
            }
            $rewrites[] = $url;
        }
        return $rewrites;
    }
}
