<?php declare(strict_types=1);

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogUrlRewrite\Test\Unit\Observer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\CatalogImportExport\Model\Import\Product as ImportProduct;
use Magento\CatalogUrlRewrite\Model\ObjectRegistry;
use Magento\CatalogUrlRewrite\Model\ObjectRegistryFactory;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Observer\AfterImportDataObserver;
use Magento\CatalogUrlRewrite\Service\V1\StoreViewService;
use Magento\Framework\Event;
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
     * @var string
     */
    private $categoryId = 10;

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
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.TooManyFields)
     * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function setUp(): void
    {
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
            ->setMethods(
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
                ->setMethods(['generate'])
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
            ->setMethods(
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
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager = new ObjectManager($this);
        $this->import = $this->objectManager->getObject(
            AfterImportDataObserver::class,
            [
                'catalogProductFactory' => $this->catalogProductFactory,
                'objectRegistryFactory' => $this->objectRegistryFactory,
                'productUrlPathGenerator' => $this->productUrlPathGenerator,
                'storeViewService' => $this->storeViewService,
                'storeManager'=> $this->storeManager,
                'urlPersist' => $this->urlPersist,
                'urlRewriteFactory' => $this->urlRewriteFactory,
                'urlFinder' => $this->urlFinder,
                'mergeDataProviderFactory' => $mergeDataProviderFactory,
                'categoryCollectionFactory' => $this->categoryCollectionFactory
            ]
        );
    }

    /**
     * Test for afterImportData()
     * Covers afterImportData() + protected methods used inside
     *
     * @covers \Magento\CatalogUrlRewrite\Observer\AfterImportDataObserver::_populateForUrlGeneration
     * @covers \Magento\CatalogUrlRewrite\Observer\AfterImportDataObserver::isGlobalScope
     * @covers \Magento\CatalogUrlRewrite\Observer\AfterImportDataObserver::populateGlobalProduct
     * @covers \Magento\CatalogUrlRewrite\Observer\AfterImportDataObserver::addProductToImport
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
            ->withConsecutive(
                [$this->products[0][ImportProduct::COL_SKU]],
                [$this->products[1][ImportProduct::COL_SKU]]
            )
            ->will($this->onConsecutiveCalls($newSku[0], $newSku[1]));
        $this->importProduct
            ->expects($this->exactly($productsCount))
            ->method('getProductCategories')
            ->withConsecutive(
                [$this->products[0][ImportProduct::COL_SKU]],
                [$this->products[1][ImportProduct::COL_SKU]]
            )->willReturn([]);
        $getProductWebsitesCallsCount = $productsCount*2;
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
        $product
            ->expects($this->exactly($productsCount))
            ->method('setId')
            ->withConsecutive([$newSku[0]['entity_id']], [$newSku[1]['entity_id']]);
        $product
            ->expects($this->any())
            ->method('getId')
            ->willReturnOnConsecutiveCalls(
                $newSku[0]['entity_id'],
                $newSku[0]['entity_id'],
                $newSku[0]['entity_id'],
                $newSku[0]['entity_id'],
                $newSku[1]['entity_id'],
                $newSku[1]['entity_id'],
                $newSku[1]['entity_id']
            );
        $product
            ->expects($this->exactly($productsCount))
            ->method('getSku')
            ->will(
                $this->onConsecutiveCalls(
                    $this->products[0]['sku'],
                    $this->products[1]['sku']
                )
            );
        $product
            ->expects($this->exactly($productsCount))
            ->method('getStoreId')
            ->will(
                $this->onConsecutiveCalls(
                    $this->products[0][ImportProduct::COL_STORE],
                    $this->products[1][ImportProduct::COL_STORE]
                )
            );
        $product
            ->expects($this->exactly($productsCount))
            ->method('setStoreId')
            ->withConsecutive(
                [$this->products[0][ImportProduct::COL_STORE]],
                [$this->products[1][ImportProduct::COL_STORE]]
            );
        $this->catalogProductFactory
            ->expects($this->exactly($productsCount))
            ->method('create')
            ->willReturn($product);

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

        $this->import->execute($this->observer);
    }

    /**
     * Cover canonicalUrlRewriteGenerate().
     */
    public function testCanonicalUrlRewriteGenerateWithUrlPath()
    {
        $productId = 'product_id';
        $requestPath = 'simple-product.html';
        $storeId = 10;
        $product = $this
            ->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productsByStores = [$storeId => $product];
        $products = [
            $productId => $productsByStores,
        ];

        $targetPath = 'catalog/product/view/id/' . $productId;
        $this->setPropertyValue($this->import, 'products', $products);

        $this->productUrlPathGenerator
            ->expects($this->once())
            ->method('getUrlPathWithSuffix')
            ->willReturn($requestPath);
        $this->productUrlPathGenerator
            ->expects($this->once())
            ->method('getUrlPath')
            ->willReturn('urlPath');
        $this->productUrlPathGenerator
            ->expects($this->once())
            ->method('getCanonicalUrlPath')
            ->willReturn($targetPath);
        $this->urlRewrite
            ->expects($this->once())
            ->method('setStoreId')
            ->with($storeId)->willReturnSelf();
        $this->urlRewrite
            ->expects($this->once())
            ->method('setEntityId')
            ->with($productId)->willReturnSelf();
        $this->urlRewrite
            ->expects($this->once())
            ->method('setEntityType')
            ->with(ProductUrlRewriteGenerator::ENTITY_TYPE)->willReturnSelf();
        $this->urlRewrite
            ->expects($this->once())
            ->method('setRequestPath')
            ->with($requestPath)->willReturnSelf();
        $this->urlRewrite
            ->expects($this->once())
            ->method('setTargetPath')
            ->with($targetPath)->willReturnSelf();
        $this->urlRewriteFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->urlRewrite);

        $actualResult = $this->invokeMethod($this->import, 'canonicalUrlRewriteGenerate');
        $this->assertEquals(
            [
                $this->urlRewrite,
            ],
            $actualResult
        );
    }

    /**
     * Cover canonicalUrlRewriteGenerate().
     */
    public function testCanonicalUrlRewriteGenerateWithEmptyUrlPath()
    {
        $productId = 'product_id';
        $storeId = 10;
        $product = $this
            ->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productsByStores = [$storeId => $product];
        $products = [
            $productId => $productsByStores,
        ];

        $this->setPropertyValue($this->import, 'products', $products);

        $this->productUrlPathGenerator
            ->expects($this->once())
            ->method('getUrlPath')
            ->willReturn('');
        $this->urlRewriteFactory
            ->expects($this->never())
            ->method('create');

        $actualResult = $this->invokeMethod($this->import, 'canonicalUrlRewriteGenerate');
        $this->assertEquals([], $actualResult);
    }

    /**
     * Cover categoriesUrlRewriteGenerate().
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCategoriesUrlRewriteGenerate()
    {
        $urlPathWithCategory = 'category/simple-product.html';
        $storeId = 10;
        $productId = 'product_id';
        $canonicalUrlPathWithCategory = 'canonical-path-with-category';
        $product = $this
            ->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productsByStores = [
            $storeId => $product,
        ];
        $products = [
            $productId => $productsByStores,
        ];
        $categoryCache = [
            $productId => [$this->categoryId],
        ];

        $this->setPropertyValue($this->import, 'products', $products);
        $this->setPropertyValue($this->import, 'categoryCache', $categoryCache);
        $this->setPropertyValue($this->import, 'import', $this->importProduct);

        $this->productUrlPathGenerator
            ->expects($this->any())
            ->method('getUrlPathWithSuffix')
            ->willReturn($urlPathWithCategory);
        $this->productUrlPathGenerator
            ->expects($this->any())
            ->method('getCanonicalUrlPath')
            ->willReturn($canonicalUrlPathWithCategory);
        $category = $this->createMock(Category::class);
        $category
            ->expects($this->any())
            ->method('getId')
            ->willReturn($this->categoryId);
        $category
            ->expects($this->any())
            ->method('getAnchorsAbove')
            ->willReturn([]);
        $categoryCollection = $this->getMockBuilder(CategoryCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $categoryCollection->expects($this->once())
            ->method('addIdFilter')
            ->with([$this->categoryId])
            ->willReturnSelf();
        $categoryCollection->expects($this->once())
            ->method('setStoreId')
            ->with($storeId)
            ->willReturnSelf();
        $categoryCollection->expects($this->exactly(3))
            ->method('addAttributeToSelect')
            ->withConsecutive(
                ['name'],
                ['url_key'],
                ['url_path']
            )->willReturnSelf();
        $categoryCollection->expects($this->once())
            ->method('getFirstItem')
            ->willReturn($category);

        $this->categoryCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($categoryCollection);

        $this->urlRewrite
            ->expects($this->any())
            ->method('setStoreId')
            ->with($storeId)->willReturnSelf();
        $this->urlRewrite
            ->expects($this->any())
            ->method('setEntityId')
            ->with($productId)->willReturnSelf();
        $this->urlRewrite
            ->expects($this->any())
            ->method('setEntityType')
            ->with(ProductUrlRewriteGenerator::ENTITY_TYPE)->willReturnSelf();
        $this->urlRewrite
            ->expects($this->any())
            ->method('setRequestPath')
            ->with($urlPathWithCategory)->willReturnSelf();
        $this->urlRewrite
            ->expects($this->any())
            ->method('setTargetPath')
            ->with($canonicalUrlPathWithCategory)->willReturnSelf();
        $this->urlRewrite
            ->expects($this->any())
            ->method('setMetadata')
            ->with(['category_id' => $this->categoryId])->willReturnSelf();
        $this->urlRewriteFactory
            ->expects($this->any())
            ->method('create')
            ->willReturn($this->urlRewrite);

        $actualResult = $this->invokeMethod($this->import, 'categoriesUrlRewriteGenerate');
        $this->assertEquals(
            [
                $this->urlRewrite,
            ],
            $actualResult
        );
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
     * @param AfterImportDataObserver $object
     * @param string $methodName
     * @param array $parameters
     * @return mixed
     */
    protected function invokeMethod($object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
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
