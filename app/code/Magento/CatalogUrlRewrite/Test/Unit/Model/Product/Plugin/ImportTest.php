<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogUrlRewrite\Test\Unit\Model\Product\Plugin;

use Magento\ImportExport\Model\Import as ImportExport;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\CatalogImportExport\Model\Import\Product as ImportProduct;
use Magento\Store\Model\Store;
use Magento\Framework\App\Resource;
use \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Model\OptionProvider;

/**
 * Class ImportTest
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ImportTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var string
     */
    protected $categoryId = 10;

    /**
     * @var \Magento\UrlRewrite\Model\UrlPersistInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlPersist;

    /**
     * @var \Magento\UrlRewrite\Model\UrlFinderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlFinder;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productUrlRewriteGenerator;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productRepository;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $importProduct;

    /**
     * @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $observer;

    /**
     * @var \Magento\Framework\Event|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $event;

    /**
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $adapter;

    /**
     * @var \Magento\Catalog\Model\ProductFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $catalogProductFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connection;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\ObjectRegistryFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectRegistryFactory;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productUrlPathGenerator;

    /**
     * @var \Magento\CatalogUrlRewrite\Service\V1\StoreViewService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeViewService;

    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavConfig;

    /**
     * @var \Magento\Framework\App\Resource|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $select;

    /**
     * @var \Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlRewriteFactory;

    /**
     * @var \Magento\UrlRewrite\Service\V1\Data\UrlRewrite|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlRewrite;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\ObjectRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectRegistry;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\Product\Plugin\Import|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $importMock;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\Product\Plugin\Import
     */
    protected $import;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $product;

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
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.TooManyFields)
     * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function setUp()
    {
        $this->importProduct = $this->getMock(
            '\Magento\CatalogImportExport\Model\Import\Product',
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
            '\Magento\Catalog\Model\ProductFactory',
            [
                'create',
            ],
            [],
            '',
            false
        );
        $this->storeManager = $this
            ->getMockBuilder(
                '\Magento\Store\Model\StoreManagerInterface'
            )
            ->disableOriginalConstructor()
            ->setMethods([
                'getWebsite',
            ])
            ->getMockForAbstractClass();
        $this->adapter = $this->getMock(
            'Magento\Framework\DB\Adapter\Pdo\Mysql',
            [],
            [],
            '',
            false
        );
        $this->event = $this->getMock('\Magento\Framework\Event', ['getAdapter', 'getBunch'], [], '', false);
        $this->event->expects($this->any())->method('getAdapter')->willReturn($this->importProduct);
        $this->event->expects($this->any())->method('getBunch')->willReturn($this->products);
        $this->observer = $this->getMock('\Magento\Framework\Event\Observer', ['getEvent'], [], '', false);
        $this->observer->expects($this->any())->method('getEvent')->willReturn($this->event);
        $this->urlPersist = $this->getMockBuilder('\Magento\UrlRewrite\Model\UrlPersistInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->productUrlRewriteGenerator =
            $this->getMockBuilder('\Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator')
                ->disableOriginalConstructor()
                ->setMethods(['generate'])
                ->getMock();
        $this->productRepository = $this->getMockBuilder('\Magento\Catalog\Api\ProductRepositoryInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->eavConfig = $this->getMock(
            '\Magento\Eav\Model\Config',
            [
                'getAttribute',
            ],
            [],
            '',
            false
        );
        $attribute = $this->getMockBuilder('\Magento\Eav\Model\Entity\Attribute\AbstractAttribute')
            ->setMethods([
                'getBackendTable',
            ])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $beTable = 'backend table';
        $attribute->expects($this->any())
            ->method('getBackendTable')
            ->willReturn($beTable);
        $this->eavConfig->expects($this->any())
            ->method('getAttribute')
            ->with(
                \Magento\Catalog\Model\Product::ENTITY,
                \Magento\CatalogUrlRewrite\Model\Product\Plugin\Import::URL_KEY_ATTRIBUTE_CODE
            )
            ->willReturn($attribute);

        $this->resource = $this->getMock(
            '\Magento\Framework\App\Resource',
            [],
            [],
            '',
            false
        );
        $this->connection = $this->getMockBuilder('\Magento\Framework\DB\Adapter\AdapterInterface')
            ->disableOriginalConstructor()
            ->setMethods([
                'quoteInto',
                'select',
                'fetchAll',
            ])
            ->getMockForAbstractClass();
        $this->resource
            ->expects($this->any())
            ->method('getConnection')
            ->with(Resource::DEFAULT_READ_RESOURCE)
            ->willReturn($this->connection);
        $this->select = $this->getMock(
            '\Magento\Framework\DB\Select',
            [
                'from',
                'where',
            ],
            [],
            '',
            false
        );
        $this->connection
            ->expects($this->any())
            ->method('select')
            ->willReturn($this->select);
        $this->objectRegistryFactory = $this->getMock(
            '\Magento\CatalogUrlRewrite\Model\ObjectRegistryFactory',
            [],
            [],
            '',
            false
        );
        $this->productUrlPathGenerator = $this->getMock(
            '\Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator',
            [],
            [],
            '',
            false
        );
        $this->storeViewService = $this->getMock(
            '\Magento\CatalogUrlRewrite\Service\V1\StoreViewService',
            [],
            [],
            '',
            false
        );
        $this->urlRewriteFactory = $this->getMock(
            '\Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory',
            [
                'create',
            ],
            [],
            '',
            false
        );
        $this->urlFinder = $this
            ->getMockBuilder('\Magento\UrlRewrite\Model\UrlFinderInterface')
            ->setMethods([
                'findAllByData',
            ])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->urlRewrite = $this
            ->getMockBuilder('Magento\UrlRewrite\Service\V1\Data\UrlRewrite')
            ->disableOriginalConstructor()
            ->getMock();

        $this->product = $this
            ->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectRegistry = $this
            ->getMockBuilder('\Magento\CatalogUrlRewrite\Model\ObjectRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $categoryProcessor = $this->getMock(
            '\Magento\CatalogImportExport\Model\Import\Product\CategoryProcessor',
            [
                'getCategoryById',
            ],
            [],
            '',
            false
        );
        $category = $this->getMock(
            'Magento\Catalog\Model\Category',
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

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->import = $this->objectManagerHelper->getObject(
            '\Magento\CatalogUrlRewrite\Model\Product\Plugin\Import',
            [
                'catalogProductFactory' => $this->catalogProductFactory,
                'eavConfig' => $this->eavConfig,
                'objectRegistryFactory' => $this->objectRegistryFactory,
                'productUrlPathGenerator' => $this->productUrlPathGenerator,
                'resource' => $this->resource,
                'storeViewService' => $this->storeViewService,
                'storeManager'=> $this->storeManager,
                'urlPersist' => $this->urlPersist,
                'urlRewriteFactory' => $this->urlRewriteFactory,
                'urlFinder' => $this->urlFinder,
            ]
        );
    }

    /**
     * Test for afterImportData()
     * Covers afterImportData() + protected methods used inside except related to generateUrls() ones.
     * generateUrls will be covered separately.
     *
     * @covers \Magento\CatalogUrlRewrite\Model\Product\Plugin\Import::afterImportData
     * @covers \Magento\CatalogUrlRewrite\Model\Product\Plugin\Import::_populateForUrlGeneration
     * @covers \Magento\CatalogUrlRewrite\Model\Product\Plugin\Import::isGlobalScope
     * @covers \Magento\CatalogUrlRewrite\Model\Product\Plugin\Import::populateGlobalProduct
     * @covers \Magento\CatalogUrlRewrite\Model\Product\Plugin\Import::addProductToImport
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testAfterImportData()
    {
        $newSku = ['entity_id' => 'value'];
        $websiteId = 'websiteId value';
        $productsCount = count($this->products);
        $websiteMock = $this->getMock(
            '\Magento\Store\Model\Website',
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
            ->willReturn($newSku);
        $this->importProduct
            ->expects($this->exactly($productsCount))
            ->method('getProductCategories')
            ->withConsecutive(
                [$this->products[0][ImportProduct::COL_SKU]],
                [$this->products[1][ImportProduct::COL_SKU]]
            );
        $getProductWebsitesCallsCount = $productsCount*2;
        $this->importProduct
            ->expects($this->exactly($getProductWebsitesCallsCount))
            ->method('getProductWebsites')
            ->willReturn([
                $newSku['entity_id'] => $websiteId,
            ]);
        $map = [
            [$this->products[0][ImportProduct::COL_STORE], $this->products[0][ImportProduct::COL_STORE]],
            [$this->products[1][ImportProduct::COL_STORE], $this->products[1][ImportProduct::COL_STORE]]
        ];
        $this->importProduct
            ->expects($this->exactly(1))
            ->method('getStoreIdByCode')
            ->will($this->returnValueMap($map));
        $product = $this->getMock(
            '\Magento\Catalog\Model\Product',
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
            ->with($newSku['entity_id']);
        $product
            ->expects($this->any())
            ->method('getId')
            ->willReturn($newSku['entity_id']);
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
            ->expects($this->once())
            ->method('setStoreId')
            ->with($this->products[1][ImportProduct::COL_STORE]);
        $this->catalogProductFactory
            ->expects($this->exactly($productsCount))
            ->method('create')
            ->willReturn($product);
        $this->connection
            ->expects($this->exactly(4))
            ->method('quoteInto')
            ->withConsecutive(
                [
                    '(store_id = ?',
                    $storeIds[0],
                ],
                [
                    ' AND entity_id = ?)',
                    $newSku['entity_id'],
                ]
            );

        $productUrls = [
            'url 1',
            'url 2',
        ];

        $importMock = $this->getImportMock([
            'generateUrls',
            'canonicalUrlRewriteGenerate',
            'categoriesUrlRewriteGenerate',
            'currentUrlRewritesRegenerate',
            'cleanOverriddenUrlKey',
        ]);
        $importMock
            ->expects($this->once())
            ->method('generateUrls')
            ->willReturn($productUrls);
        $this->urlPersist
            ->expects($this->once())
            ->method('replace')
            ->with($productUrls);

        $importMock->afterImportData($this->observer);
    }

    /**
     * Cover generateUrls().
     */
    public function testGenerateUrls()
    {
        $importMock = $this->getImportMock([
            'canonicalUrlRewriteGenerate',
            'categoriesUrlRewriteGenerate',
            'currentUrlRewritesRegenerate',
            'cleanOverriddenUrlKey',
        ]);

        $importMock
            ->expects($this->once())
            ->method('cleanOverriddenUrlKey');

        $urlRewriteMethods = [
            'canonicalUrlRewriteGenerate',
            'categoriesUrlRewriteGenerate',
            'currentUrlRewritesRegenerate',
        ];
        $urlRewriteMock = $this->getMock(
            '\Magento\UrlRewrite\Service\V1\Data\UrlRewrite',
            [
                'getTargetPath',
                'getStoreId',
            ],
            [],
            '',
            false
        );
        $targetPath = 'test.html';
        $urlRewriteMock
            ->expects($this->exactly(3))
            ->method('getTargetPath')
            ->willReturn($targetPath);
        $storeId = 11;
        $urlRewriteMock
            ->expects($this->exactly(3))
            ->method('getStoreId')
            ->willReturn($storeId);
        $resultKey = $targetPath . '-' . $storeId;
        $expectedResult = array_fill_keys([$resultKey, $resultKey, $resultKey], $urlRewriteMock);

        $urls = [$urlRewriteMock];

        foreach ($urlRewriteMethods as $method) {
            $importMock
                ->expects($this->once())
                ->method($method)
                ->willReturn($urls);
        }

        $actualResult = $importMock->generateUrls();
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * Cover cleanOverriddenUrlKey().
     */
    public function testCleanOverriddenUrlKey()
    {
        $urlKeyAttributeBackendTable = 'table value';
        $urlKeyAttributeId = 'id value';
        $entityStoresToCheckOverridden = [1,2,3];
        $this->import->urlKeyAttributeBackendTable = $urlKeyAttributeBackendTable;
        $this->import->urlKeyAttributeId = $urlKeyAttributeId;
        $this->setPropertyValue($this->import, 'entityStoresToCheckOverridden', $entityStoresToCheckOverridden);
        $this->select
            ->expects($this->once())
            ->method('from')
            ->with(
                $urlKeyAttributeBackendTable,
                ['store_id', 'entity_id']
            )
            ->will($this->returnSelf());
        $this->select
            ->expects($this->exactly(2))
            ->method('where')
            ->withConsecutive(
                [
                    'attribute_id = ?',
                    $urlKeyAttributeId,
                ],
                [
                    implode(' OR ', $entityStoresToCheckOverridden)
                ]
            )
            ->will($this->returnSelf());

        $entityIdVal = 'entity id value';
        $storeIdVal = 'store id value';
        $entityStore = [
            'entity_id' => $entityIdVal,
            'store_id' => $storeIdVal,
        ];
        $entityStoresToClean = [$entityStore];
        $products = [
            $entityIdVal => [
                $storeIdVal => 'value',
            ]
        ];
        $this->setPropertyValue($this->import, 'products', $products);
        $this->connection
            ->expects($this->once())
            ->method('fetchAll')
            ->willReturn($entityStoresToClean);

        $actualResult = $this->invokeMethod($this->import, 'cleanOverriddenUrlKey');
        $this->assertEquals($this->import, $actualResult);
    }

    /**
     * Cover cleanOverriddenUrlKey() method with empty entityStoresToCheckOverridden property.
     */
    public function testCleanOverriddenUrlKeyEmptyEntityStoresToCheckOverridden()
    {
        $this->setPropertyValue($this->import, 'entityStoresToCheckOverridden', null);
        $this->select
            ->expects($this->never())
            ->method('from');
        $this->select
            ->expects($this->never())
            ->method('where');

        $actualResult = $this->invokeMethod($this->import, 'cleanOverriddenUrlKey');
        $this->assertEquals($this->import, $actualResult);
    }

    /**
     * Test for clearProductUrls()
     */
    public function testClearProductUrls()
    {
        $this->import->clearProductUrls($this->observer);
    }

    /**
     * Cover canonicalUrlRewriteGenerate().
     */
    public function testCanonicalUrlRewriteGenerate()
    {
        $productId = 'product_id';
        $requestPath = 'simple-product.html';
        $storeId = 10;
        $product = $this
            ->getMockBuilder('Magento\Catalog\Model\Product')
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
     * Cover categoriesUrlRewriteGenerate().
     */
    public function testCategoriesUrlRewriteGenerate()
    {
        $urlPathWithCategory = 'category/simple-product.html';
        $storeId = 10;
        $productId = 'product_id';
        $canonicalUrlPathWithCategory = 'canonical-path-with-category';
        $product = $this
            ->getMockBuilder('Magento\Catalog\Model\Product')
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
        $category = $this->getMock('Magento\Catalog\Model\Category', [], [], '', false);
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
     * Cover currentUrlRewritesRegenerate().
     */
    public function testCurrentUrlRewritesRegenerateEmptyProduct()
    {
        $productId = 'entity id value';
        $storeId = 'store id value';
        $products = [
            $productId => [
                $storeId => $this->product,
            ]
        ];
        $this->setPropertyValue($this->import, 'products', $products);
        $this->urlFinder->expects($this->once())->method('findAllByData')
            ->will($this->returnValue($this->currentUrlRewritesRegeneratorGetCurrentRewritesMocks([
                [
                    UrlRewrite::IS_AUTOGENERATED => 1,
                ]
            ])));

        $actualResult = $this->import->currentUrlRewritesRegenerate();
        $this->assertEquals(
            [],
            $actualResult
        );
    }

    /**
     * Cover currentUrlRewritesRegenerate().
     */
    public function testCurrentUrlRewritesRegenerateIsAutogeneratedWithoutSaveRewriteHistory()
    {
        $productId = 'entity id value';
        $storeId = 'store id value';
        $products = [
            $productId => [
                $storeId => $this->product,
            ]
        ];
        $this->setPropertyValue($this->import, 'products', $products);
        $this->urlFinder->expects($this->once())->method('findAllByData')
            ->will($this->returnValue($this->currentUrlRewritesRegeneratorGetCurrentRewritesMocks([
                [
                    UrlRewrite::IS_AUTOGENERATED => 1,
                    UrlRewrite::ENTITY_ID => $productId,
                    UrlRewrite::STORE_ID => $storeId,
                ]
            ])));
        $this->product->expects($this->once())->method('getData')->with('save_rewrites_history')
            ->will($this->returnValue(false));

        $actualResult = $this->import->currentUrlRewritesRegenerate();
        $this->assertEquals(
            [],
            $actualResult
        );
    }

    /**
     * Cover currentUrlRewritesRegenerate().
     */
    public function testCurrentUrlRewritesRegenerateSkipGenerationForAutogenerated()
    {
        $productId = 'entity id value';
        $storeId = 'store id value';
        $products = [
            $productId => [
                $storeId => $this->product,
            ]
        ];
        $this->setPropertyValue($this->import, 'products', $products);
        $this->urlFinder->expects($this->once())->method('findAllByData')
            ->will($this->returnValue($this->currentUrlRewritesRegeneratorGetCurrentRewritesMocks([
                [
                    UrlRewrite::IS_AUTOGENERATED => 1,
                    UrlRewrite::REQUEST_PATH => 'same-path',
                    UrlRewrite::ENTITY_ID => $productId,
                    UrlRewrite::STORE_ID => $storeId,
                ],
            ])));
        $this->product->expects($this->once())->method('getData')->with('save_rewrites_history')
            ->will($this->returnValue(true));
        $this->productUrlPathGenerator->expects($this->once())->method('getUrlPathWithSuffix')
            ->will($this->returnValue('same-path'));

        $actualResult = $this->import->currentUrlRewritesRegenerate();
        $this->assertEquals(
            [],
            $actualResult
        );
    }

    /**
     * Cover currentUrlRewritesRegenerate().
     */
    public function testCurrentUrlRewritesRegenerateIsAutogeneratedWithoutCategory()
    {
        $productId = 'entity id value';
        $storeId = 'store id value';
        $products = [
            $productId => [
                $storeId => $this->product,
            ]
        ];
        $this->setPropertyValue($this->import, 'products', $products);
        $requestPath = 'autogenerated.html';
        $targetPath = 'some-path.html';
        $description = 'description';
        $this->urlFinder->expects($this->once())->method('findAllByData')
            ->will($this->returnValue($this->currentUrlRewritesRegeneratorGetCurrentRewritesMocks([
                [
                    UrlRewrite::REQUEST_PATH => $requestPath,
                    UrlRewrite::TARGET_PATH => 'custom-target-path',
                    UrlRewrite::STORE_ID => $storeId,
                    UrlRewrite::IS_AUTOGENERATED => 1,
                    UrlRewrite::METADATA => [],
                    UrlRewrite::DESCRIPTION => $description,
                    UrlRewrite::ENTITY_ID => $productId,
                    UrlRewrite::STORE_ID => $storeId,
                ],
            ])));
        $this->product->expects($this->any())->method('getId')->will($this->returnValue($productId));
        $this->product->expects($this->once())->method('getData')->with('save_rewrites_history')
            ->will($this->returnValue(true));
        $this->productUrlPathGenerator->expects($this->once())->method('getUrlPathWithSuffix')
            ->will($this->returnValue($targetPath));

        $this->currentUrlRewritesRegeneratorPrepareUrlRewriteMock(
            $storeId,
            $productId,
            $requestPath,
            $targetPath,
            OptionProvider::PERMANENT,
            [],
            $description
        );

        $actualResult = $this->import->currentUrlRewritesRegenerate();
        $this->assertEquals(
            [[$this->urlRewrite]],
            $actualResult
        );
    }

    /**
     * Cover currentUrlRewritesRegenerate().
     */
    public function testCurrentUrlRewritesRegenerateIsAutogeneratedWithCategory()
    {
        $productId = 'entity id value';
        $storeId = 'store id value';
        $products = [
            $productId => [
                $storeId => $this->product,
            ]
        ];
        $this->setPropertyValue($this->import, 'products', $products);
        $this->setPropertyValue($this->import, 'import', $this->importProduct);
        $requestPath = 'autogenerated.html';
        $targetPath = 'simple-product.html';
        $metadata = ['category_id' => $this->categoryId, 'some_another_data' => 1];
        $description = 'description';
        $this->urlFinder->expects($this->once())->method('findAllByData')
            ->will($this->returnValue($this->currentUrlRewritesRegeneratorGetCurrentRewritesMocks([
                [
                    UrlRewrite::REQUEST_PATH => $requestPath,
                    UrlRewrite::TARGET_PATH => 'some-path.html',
                    UrlRewrite::STORE_ID => $storeId,
                    UrlRewrite::IS_AUTOGENERATED => 1,
                    UrlRewrite::METADATA => $metadata,
                    UrlRewrite::DESCRIPTION => $description,
                    UrlRewrite::ENTITY_ID => $productId,
                    UrlRewrite::STORE_ID => $storeId,
                ],
            ])));
        $this->product->expects($this->any())->method('getId')->will($this->returnValue($productId));
        $this->product->expects($this->once())->method('getData')->with('save_rewrites_history')
            ->will($this->returnValue(true));
        $this->productUrlPathGenerator->expects($this->once())->method('getUrlPathWithSuffix')
            ->will($this->returnValue($targetPath));
        $this->currentUrlRewritesRegeneratorPrepareUrlRewriteMock(
            $storeId,
            $productId,
            $requestPath,
            $targetPath,
            OptionProvider::PERMANENT,
            $metadata,
            $description
        );

        $actualResult = $this->import->currentUrlRewritesRegenerate();
        $this->assertEquals(
            [[$this->urlRewrite]],
            $actualResult
        );
    }

    /**
     * Cover currentUrlRewritesRegenerate().
     */
    public function testCurrentUrlRewritesRegenerateSkipGenerationForCustom()
    {
        $productId = 'entity id value';
        $storeId = 'store id value';
        $products = [
            $productId => [
                $storeId => 'value',
            ]
        ];
        $this->setPropertyValue($this->import, 'products', $products);
        $this->urlFinder->expects($this->once())->method('findAllByData')
            ->will($this->returnValue($this->currentUrlRewritesRegeneratorGetCurrentRewritesMocks([
                [
                    UrlRewrite::IS_AUTOGENERATED => 0,
                    UrlRewrite::REQUEST_PATH => 'same-path',
                    UrlRewrite::REDIRECT_TYPE => 1,
                    UrlRewrite::ENTITY_ID => $productId,
                    UrlRewrite::STORE_ID => $storeId,
                ],
            ])));
        $this->productUrlPathGenerator->expects($this->once())->method('getUrlPathWithSuffix')
            ->will($this->returnValue('same-path'));

        $actualResult = $this->import->currentUrlRewritesRegenerate();
        $this->assertEquals(
            [],
            $actualResult
        );
    }

    /**
     * Cover currentUrlRewritesRegenerate().
     */
    public function testCurrentUrlRewritesRegenerateForCustomWithoutTargetPathGeneration()
    {
        $productId = 'entity id value';
        $storeId = 'store id value';
        $products = [
            $productId => [
                $storeId => 'value',
            ]
        ];
        $this->setPropertyValue($this->import, 'products', $products);
        $requestPath = 'generate-for-custom-without-redirect-type.html';
        $targetPath = 'custom-target-path.html';
        $description = 'description';
        $this->urlFinder->expects($this->once())->method('findAllByData')
            ->will($this->returnValue($this->currentUrlRewritesRegeneratorGetCurrentRewritesMocks([
                [
                    UrlRewrite::REQUEST_PATH => $requestPath,
                    UrlRewrite::TARGET_PATH => $targetPath,
                    UrlRewrite::REDIRECT_TYPE => 0,
                    UrlRewrite::IS_AUTOGENERATED => 0,
                    UrlRewrite::DESCRIPTION => $description,
                    UrlRewrite::METADATA => [],
                    UrlRewrite::ENTITY_ID => $productId,
                    UrlRewrite::STORE_ID => $storeId,
                ],
            ])));
        $this->productUrlPathGenerator->expects($this->never())->method('getUrlPathWithSuffix');
        $this->product->expects($this->any())->method('getId')->will($this->returnValue($productId));
        $this->currentUrlRewritesRegeneratorPrepareUrlRewriteMock(
            $storeId,
            $productId,
            $requestPath,
            $targetPath,
            0,
            [],
            $description
        );

        $actualResult = $this->import->currentUrlRewritesRegenerate();
        $this->assertEquals(
            [[$this->urlRewrite]],
            $actualResult
        );
    }

    /**
     * Cover currentUrlRewritesRegenerate().
     *
     * @cover \Magento\CatalogUrlRewrite\Model\Product\Plugin\Import::generateForCustom
     */
    public function testCurrentUrlRewritesRegenerateForCustomWithTargetPathGeneration()
    {
        $productId = 'entity id value';
        $storeId = 'store id value';
        $products = [
            $productId => [
                $storeId => 'value',
            ]
        ];
        $this->setPropertyValue($this->import, 'products', $products);
        $requestPath = 'generate-for-custom-without-redirect-type.html';
        $targetPath = 'generated-target-path.html';
        $description = 'description';
        $this->urlFinder->expects($this->once())->method('findAllByData')
            ->will($this->returnValue($this->currentUrlRewritesRegeneratorGetCurrentRewritesMocks([
                [
                    UrlRewrite::REQUEST_PATH => $requestPath,
                    UrlRewrite::TARGET_PATH => 'custom-target-path.html',
                    UrlRewrite::REDIRECT_TYPE => 'code',
                    UrlRewrite::IS_AUTOGENERATED => 0,
                    UrlRewrite::DESCRIPTION => $description,
                    UrlRewrite::METADATA => [],
                    UrlRewrite::ENTITY_ID => $productId,
                    UrlRewrite::STORE_ID => $storeId,
                ],
            ])));
        $this->productUrlPathGenerator->expects($this->any())->method('getUrlPathWithSuffix')
            ->will($this->returnValue($targetPath));
        $this->product->expects($this->any())->method('getId')->will($this->returnValue($productId));
        $this->currentUrlRewritesRegeneratorPrepareUrlRewriteMock(
            $storeId,
            $productId,
            $requestPath,
            $targetPath,
            'code',
            [],
            $description
        );

        $actualResult = $this->import->currentUrlRewritesRegenerate();
        $this->assertEquals(
            [[$this->urlRewrite]],
            $actualResult
        );
    }

    /**
     * Set property for an object.
     *
     * @param object $object
     * @param string $property
     * @param mixed $value
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
     * Invoke any method of an object.
     *
     * @param $object
     * @param $methodName
     * @param array $parameters
     * @return mixed
     */
    protected function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * Get mock of Import class instance with defined methods and called constructor.
     */
    protected function getImportMock($methods = [])
    {
        return $this->getMock(
            '\Magento\CatalogUrlRewrite\Model\Product\Plugin\Import',
            $methods,
            [
                $this->catalogProductFactory,
                $this->eavConfig,
                $this->objectRegistryFactory,
                $this->productUrlPathGenerator,
                $this->resource,
                $this->storeViewService,
                $this->storeManager,
                $this->urlPersist,
                $this->urlRewriteFactory,
                $this->urlFinder,
            ],
            ''
        );
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
            $url = $this->getMockBuilder('Magento\UrlRewrite\Service\V1\Data\UrlRewrite')
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
