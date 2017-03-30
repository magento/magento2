<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogUrlRewrite\Test\Unit\Observer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\CatalogImportExport\Model\Import\Product as ImportProduct;
use Magento\Store\Model\Store;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;

/**
 * Class AfterImportDataObserverTest
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AfterImportDataObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $categoryId = 10;

    /**
     * @var \Magento\UrlRewrite\Model\UrlPersistInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlPersist;

    /**
     * @var \Magento\UrlRewrite\Model\UrlFinderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlFinder;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productUrlRewriteGenerator;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepository;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $importProduct;

    /**
     * @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $observer;

    /**
     * @var \Magento\Framework\Event|\PHPUnit_Framework_MockObject_MockObject
     */
    private $event;

    /**
     * @var \Magento\Catalog\Model\ProductFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $catalogProductFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\ObjectRegistryFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectRegistryFactory;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productUrlPathGenerator;

    /**
     * @var \Magento\CatalogUrlRewrite\Service\V1\StoreViewService|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeViewService;

    /**
     * @var \Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlRewriteFactory;

    /**
     * @var \Magento\UrlRewrite\Service\V1\Data\UrlRewrite|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlRewrite;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\ObjectRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectRegistry;

    /**
     * @var \Magento\CatalogUrlRewrite\Observer\AfterImportDataObserver
     */
    private $import;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $product;

    /** @var \Magento\UrlRewrite\Model\MergeDataProvider|\PHPUnit_Framework_MockObject_MockObject */
    private $mergeDataProvider;

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
    protected function setUp()
    {
        $this->importProduct = $this->getMock(
            \Magento\CatalogImportExport\Model\Import\Product::class,
            [
                'getNewSku',
                'getProductCategories',
                'getProductWebsites',
                'getStoreIdByCode',
                'getCategoryProcessor',
            ],
            [],
            '',
            false
        );
        $this->catalogProductFactory = $this->getMock(
            \Magento\Catalog\Model\ProductFactory::class,
            [
                'create',
            ],
            [],
            '',
            false
        );
        $this->storeManager = $this
            ->getMockBuilder(
                \Magento\Store\Model\StoreManagerInterface::class
            )
            ->disableOriginalConstructor()
            ->setMethods([
                'getWebsite',
            ])
            ->getMockForAbstractClass();
        $this->event = $this->getMock(\Magento\Framework\Event::class, ['getAdapter', 'getBunch'], [], '', false);
        $this->event->expects($this->any())->method('getAdapter')->willReturn($this->importProduct);
        $this->event->expects($this->any())->method('getBunch')->willReturn($this->products);
        $this->observer = $this->getMock(\Magento\Framework\Event\Observer::class, ['getEvent'], [], '', false);
        $this->observer->expects($this->any())->method('getEvent')->willReturn($this->event);
        $this->urlPersist = $this->getMockBuilder(\Magento\UrlRewrite\Model\UrlPersistInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productUrlRewriteGenerator =
            $this->getMockBuilder(\Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator::class)
                ->disableOriginalConstructor()
                ->setMethods(['generate'])
                ->getMock();
        $this->productRepository = $this->getMockBuilder(\Magento\Catalog\Api\ProductRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectRegistryFactory = $this->getMock(
            \Magento\CatalogUrlRewrite\Model\ObjectRegistryFactory::class,
            [],
            [],
            '',
            false
        );
        $this->productUrlPathGenerator = $this->getMock(
            \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator::class,
            [],
            [],
            '',
            false
        );
        $this->storeViewService = $this->getMock(
            \Magento\CatalogUrlRewrite\Service\V1\StoreViewService::class,
            [],
            [],
            '',
            false
        );
        $this->urlRewriteFactory = $this->getMock(
            \Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory::class,
            [
                'create',
            ],
            [],
            '',
            false
        );
        $this->urlFinder = $this
            ->getMockBuilder(\Magento\UrlRewrite\Model\UrlFinderInterface::class)
            ->setMethods([
                'findAllByData',
            ])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->urlRewrite = $this
            ->getMockBuilder(\Magento\UrlRewrite\Service\V1\Data\UrlRewrite::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->product = $this
            ->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectRegistry = $this
            ->getMockBuilder(\Magento\CatalogUrlRewrite\Model\ObjectRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $categoryProcessor = $this->getMock(
            \Magento\CatalogImportExport\Model\Import\Product\CategoryProcessor::class,
            [
                'getCategoryById',
            ],
            [],
            '',
            false
        );
        $category = $this->getMock(
            \Magento\Catalog\Model\Category::class,
            [
                'getId',
            ],
            [],
            '',
            false
        );
        $category
            ->expects($this->any())
            ->method('getId')
            ->willReturn($this->categoryId);
        $categoryProcessor
            ->expects($this->any())
            ->method('getCategoryById')
            ->with($this->categoryId)
            ->willReturn($category);
        $this->importProduct
            ->expects($this->any())
            ->method('getCategoryProcessor')
            ->willReturn($categoryProcessor);
        $mergeDataProviderFactory = $this->getMock(
            \Magento\UrlRewrite\Model\MergeDataProviderFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->mergeDataProvider = new \Magento\UrlRewrite\Model\MergeDataProvider;
        $mergeDataProviderFactory->expects($this->once())->method('create')->willReturn($this->mergeDataProvider);

        $this->objectManager = new ObjectManager($this);
        $this->import = $this->objectManager->getObject(
            \Magento\CatalogUrlRewrite\Observer\AfterImportDataObserver::class,
            [
                'catalogProductFactory' => $this->catalogProductFactory,
                'objectRegistryFactory' => $this->objectRegistryFactory,
                'productUrlPathGenerator' => $this->productUrlPathGenerator,
                'storeViewService' => $this->storeViewService,
                'storeManager'=> $this->storeManager,
                'urlPersist' => $this->urlPersist,
                'urlRewriteFactory' => $this->urlRewriteFactory,
                'urlFinder' => $this->urlFinder,
                'mergeDataProviderFactory' => $mergeDataProviderFactory
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
        $websiteMock = $this->getMock(
            \Magento\Store\Model\Website::class,
            [
                'getStoreIds',
            ],
            [],
            '',
            false
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
            ->will($this->returnValueMap($map));
        $product = $this->getMock(
            \Magento\Catalog\Model\Product::class,
            [
                'getId',
                'setId',
                'getSku',
                'setStoreId',
                'getStoreId',
            ],
            [],
            '',
            false
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
            ->will($this->onConsecutiveCalls(
                $this->products[0]['sku'],
                $this->products[1]['sku']
            ));
        $product
            ->expects($this->exactly($productsCount))
            ->method('getStoreId')
            ->will($this->onConsecutiveCalls(
                $this->products[0][ImportProduct::COL_STORE],
                $this->products[1][ImportProduct::COL_STORE]
            ));
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
            ->will($this->returnValue($requestPath));
        $this->productUrlPathGenerator
            ->expects($this->once())
            ->method('getUrlPath')
            ->will($this->returnValue('urlPath'));
        $this->productUrlPathGenerator
            ->expects($this->once())
            ->method('getCanonicalUrlPath')
            ->will($this->returnValue($targetPath));
        $this->urlRewrite
            ->expects($this->once())
            ->method('setStoreId')
            ->with($storeId)
            ->will($this->returnSelf());
        $this->urlRewrite
            ->expects($this->once())
            ->method('setEntityId')
            ->with($productId)
            ->will($this->returnSelf());
        $this->urlRewrite
            ->expects($this->once())
            ->method('setEntityType')
            ->with(ProductUrlRewriteGenerator::ENTITY_TYPE)
            ->will($this->returnSelf());
        $this->urlRewrite
            ->expects($this->once())
            ->method('setRequestPath')
            ->with($requestPath)
            ->will($this->returnSelf());
        $this->urlRewrite
            ->expects($this->once())
            ->method('setTargetPath')
            ->with($targetPath)
            ->will($this->returnSelf());
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
            ->will($this->returnValue(''));
        $this->urlRewriteFactory
            ->expects($this->never())
            ->method('create');

        $actualResult = $this->invokeMethod($this->import, 'canonicalUrlRewriteGenerate');
        $this->assertEquals([], $actualResult);
    }

    /**
     * Cover categoriesUrlRewriteGenerate().
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
            ->will($this->returnValue($urlPathWithCategory));
        $this->productUrlPathGenerator
            ->expects($this->any())
            ->method('getCanonicalUrlPath')
            ->will($this->returnValue($canonicalUrlPathWithCategory));
        $category = $this->getMock(\Magento\Catalog\Model\Category::class, [], [], '', false);
        $category
            ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($this->categoryId));
        $this->urlRewrite
            ->expects($this->any())
            ->method('setStoreId')
            ->with($storeId)
            ->will($this->returnSelf());
        $this->urlRewrite
            ->expects($this->any())
            ->method('setEntityId')
            ->with($productId)
            ->will($this->returnSelf());
        $this->urlRewrite
            ->expects($this->any())
            ->method('setEntityType')
            ->with(ProductUrlRewriteGenerator::ENTITY_TYPE)
            ->will($this->returnSelf());
        $this->urlRewrite
            ->expects($this->any())
            ->method('setRequestPath')
            ->with($urlPathWithCategory)
            ->will($this->returnSelf());
        $this->urlRewrite
            ->expects($this->any())
            ->method('setTargetPath')
            ->with($canonicalUrlPathWithCategory)
            ->will($this->returnSelf());
        $this->urlRewrite
            ->expects($this->any())
            ->method('setMetadata')
            ->with(['category_id' => $this->categoryId])
            ->will($this->returnSelf());
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
     * @param \Magento\CatalogUrlRewrite\Observer\AfterImportDataObserver $object
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
     * @param \Magento\CatalogUrlRewrite\Observer\AfterImportDataObserver $object
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
        $this->urlRewrite->expects($this->any())->method('setStoreId')->with($storeId)
            ->will($this->returnSelf());
        $this->urlRewrite->expects($this->any())->method('setEntityId')->with($productId)
            ->will($this->returnSelf());
        $this->urlRewrite->expects($this->any())->method('setEntityType')
            ->with(ProductUrlRewriteGenerator::ENTITY_TYPE)->will($this->returnSelf());
        $this->urlRewrite->expects($this->any())->method('setRequestPath')->with($requestPath)
            ->will($this->returnSelf());
        $this->urlRewrite->expects($this->any())->method('setTargetPath')->with($targetPath)
            ->will($this->returnSelf());
        $this->urlRewrite->expects($this->any())->method('setIsAutogenerated')->with(0)
            ->will($this->returnSelf());
        $this->urlRewrite->expects($this->any())->method('setRedirectType')->with($redirectType)
            ->will($this->returnSelf());
        $this->urlRewrite->expects($this->any())->method('setMetadata')->with($metadata)
            ->will($this->returnSelf());
        $this->urlRewriteFactory->expects($this->any())->method('create')->will($this->returnValue($this->urlRewrite));
        $this->urlRewrite->expects($this->once())->method('setDescription')->with($description)
            ->will($this->returnSelf());
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
             * @var \PHPUnit_Framework_MockObject_MockObject
             */
            $url = $this->getMockBuilder(\Magento\UrlRewrite\Service\V1\Data\UrlRewrite::class)
                ->disableOriginalConstructor()->getMock();
            foreach ($urlRewrite as $key => $value) {
                $url->expects($this->any())
                    ->method('get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key))))
                    ->will($this->returnValue($value));
            }
            $rewrites[] = $url;
        }
        return $rewrites;
    }
}
