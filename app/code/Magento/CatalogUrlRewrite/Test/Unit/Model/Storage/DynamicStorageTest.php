<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Model\Storage;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Catalog\Model\ResourceModel\ProductFactory;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\Storage\DynamicStorage;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\UrlRewrite\Model\OptionProvider;
use Magento\Store\Model\ScopeInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite as UrlRewriteData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DynamicStorageTest extends TestCase
{
    /**
     * @var DynamicStorage
     */
    private $object;

    /**
     * @var UrlRewriteFactory|MockObject
     */
    private $urlRewriteFactoryMock;

    /**
     * @var DataObjectHelper|MockObject
     */
    private $dataObjectHelperMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @var Select|MockObject
     */
    private $selectMock;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var Product|MockObject
     */
    private $productResourceMock;

    /**
     * @var ProductFactory|MockObject
     */
    private $productFactoryMock;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $requestPath;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->urlRewriteFactoryMock = $this->getMockBuilder(UrlRewriteFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataObjectHelperMock = $this->getMockBuilder(DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resourceConnectionMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->connectionMock
            ->method('select')
            ->willReturn($this->selectMock);

        $this->resourceConnectionMock
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMock();

        $this->productResourceMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->productFactoryMock = $this->getMockBuilder(ProductFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->productFactoryMock
            ->method('create')
            ->willReturn($this->productResourceMock);

        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $objects = [
            [
                LoggerInterface::class,
                $this->createMock(LoggerInterface::class)
            ]
        ];
        $objectManager->prepareObjectManager($objects);

        $this->object = new DynamicStorage(
            $this->urlRewriteFactoryMock,
            $this->dataObjectHelperMock,
            $this->resourceConnectionMock,
            $this->scopeConfigMock,
            $this->productFactoryMock,
            $this->logger
        );
    }

    /**
     * @param array $data
     * @param array|false $productFromDb
     * @param string $categorySuffix
     * @param array|false $categoryFromDb
     * @param bool $canBeShownInCategory
     * @param array|null $expectedProductRewrite
     * @throws \ReflectionException
     */
    #[DataProvider('findProductRewriteByRequestPathDataProvider')]
    public function testFindProductRewriteByRequestPath(
        array $data,
        $productFromDb,
        string $categorySuffix,
        $categoryFromDb,
        bool $canBeShownInCategory,
        ?array $expectedProductRewrite
    ): void {
        $this->fetchDataMock($productFromDb, $categoryFromDb);

        $scopeConfigMap = [
            [
                CategoryUrlPathGenerator::XML_PATH_CATEGORY_URL_SUFFIX,
                ScopeInterface::SCOPE_STORE,
                $data['store_id'],
                $categorySuffix
            ]
        ];

        $this->scopeConfigMock
            ->method('getValue')
            ->willReturnMap($scopeConfigMap);

        $this->productResourceMock
            ->method('canBeShowInCategory')
            ->willReturn($canBeShownInCategory);

        $method = new ReflectionMethod($this->object, 'findProductRewriteByRequestPath');
        $this->assertSame($expectedProductRewrite, $method->invoke($this->object, $data));
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function findProductRewriteByRequestPathDataProvider(): array
    {
        return [
            [
                // Non-existing product
                [
                    'request_path' => 'test.html',
                    'store_id' => 1
                ],
                false,
                '',
                null,
                true,
                null
            ],
            [
                // Non-existing category
                [
                    'request_path' => 'a/test.html',
                    'store_id' => 1
                ],
                [
                    'entity_type' => 'product',
                    'entity_id' => '1',
                    'request_path' => 'test.html',
                    'target_path' => 'catalog/product/view/id/1',
                    'redirect_type' => '0',
                ],
                '.html',
                false,
                true,
                null
            ],
            [
                // Existing category
                [
                    'request_path' => 'shop/test.html',
                    'store_id' => 1
                ],
                [
                    'entity_type' => 'product',
                    'entity_id' => '1',
                    'request_path' => 'test.html',
                    'target_path' => 'catalog/product/view/id/1',
                    'redirect_type' => '0',
                ],
                '.html',
                [
                    'entity_type' => 'category',
                    'entity_id' => '3',
                    'request_path' => 'shop.html',
                    'target_path' => 'catalog/category/view/id/3',
                    'redirect_type' => '0',
                ],
                true,
                [
                    'entity_type' => 'product',
                    'entity_id' => '1',
                    'request_path' => 'shop/test.html',
                    'target_path' => 'catalog/product/view/id/1/category/3',
                    'redirect_type' => '0',
                ]
            ],
            [
                // Existing category, but can't be shown in category
                [
                    'request_path' => 'shop/test.html',
                    'store_id' => 1
                ],
                [
                    'entity_type' => 'product',
                    'entity_id' => '1',
                    'request_path' => 'test.html',
                    'target_path' => 'catalog/product/view/id/1',
                    'redirect_type' => '0',
                ],
                '.html',
                [
                    'entity_type' => 'category',
                    'entity_id' => '3',
                    'request_path' => 'shop.html',
                    'target_path' => 'catalog/category/view/id/3',
                    'redirect_type' => '0',
                ],
                false,
                null
            ],
            [
                // Existing category, with product 301 redirect type
                [
                    'request_path' => 'shop/test.html',
                    'store_id' => 1
                ],
                [
                    'entity_type' => 'product',
                    'entity_id' => '1',
                    'request_path' => 'test.html',
                    'target_path' => 'test-new.html',
                    'redirect_type' => OptionProvider::PERMANENT,
                ],
                '.html',
                [
                    'entity_type' => 'category',
                    'entity_id' => '3',
                    'request_path' => 'shop.html',
                    'target_path' => 'catalog/category/view/id/3',
                    'redirect_type' => '0',
                ],
                true,
                [
                    'entity_type' => 'product',
                    'entity_id' => '1',
                    'request_path' => 'shop/test.html',
                    'target_path' => 'shop/test-new.html',
                    'redirect_type' => OptionProvider::PERMANENT,
                ]
            ],
            [
                // Existing category, with category 301 redirect type
                [
                    'request_path' => 'shop/test.html',
                    'store_id' => 1
                ],
                [
                    'entity_type' => 'product',
                    'entity_id' => '1',
                    'request_path' => 'test.html',
                    'target_path' => 'catalog/product/view/id/1',
                    'redirect_type' => '0',
                ],
                '.html',
                [
                    'entity_type' => 'category',
                    'entity_id' => '3',
                    'request_path' => 'shop.html',
                    'target_path' => 'shop-new.html',
                    'redirect_type' => OptionProvider::PERMANENT,
                ],
                true,
                [
                    'entity_type' => 'product',
                    'entity_id' => '1',
                    'request_path' => 'shop/test.html',
                    'target_path' => 'shop-new/test.html',
                    'redirect_type' => OptionProvider::PERMANENT,
                ]
            ],
            [
                // Category has product url key at the beginning of its url key
                [
                    'request_path' => 'test-category/test-sub-category/test',
                    'store_id' => 1
                ],
                [
                    'entity_type' => 'product',
                    'entity_id' => '1',
                    'request_path' => 'test',
                    'target_path' => 'catalog/product/view/id/1',
                    'redirect_type' => '0',
                ],
                '',
                [
                    'entity_type' => 'category',
                    'entity_id' => '38',
                    'request_path' => 'test-category/test-sub-category',
                    'target_path' => 'catalog/category/view/id/38',
                    'redirect_type' => '0',
                ],
                true,
                [
                    'entity_type' => 'product',
                    'entity_id' => '1',
                    'request_path' => 'test-category/test-sub-category/test',
                    'target_path' => 'catalog/product/view/id/1/category/38',
                    'redirect_type' => '0',
                ]
            ],
        ];
    }

    /**
     * @param array|false $productFromDb
     * @param array|false $categoryFromDb
     *
     * @return void
     */
    private function fetchDataMock($productFromDb, $categoryFromDb): void
    {
        $selectMock = $this->selectMock;
        $this->selectMock->expects($this->any())
            ->method('where')
            ->willReturnCallback(function ($string, $value) use ($selectMock) {
                if ($string == 'url_rewrite.request_path IN (?)') {
                    $this->requestPath = array_shift($value);
                }
                return $selectMock;
            });
        $this->connectionMock->expects($this->any())
            ->method('fetchRow')
            ->willReturnCallback(function () use ($productFromDb, $categoryFromDb) {
                switch (true) {
                    case $productFromDb && $productFromDb['request_path'] == $this->requestPath:
                        return $productFromDb;
                    case $categoryFromDb && $categoryFromDb['request_path'] == $this->requestPath:
                        return $categoryFromDb;
                    default:
                        return false;
                }
            })
        ;
    }

    /**
     * Test doFindOneByData with REQUEST_PATH routes to findProductRewriteByRequestPath
     */
    public function testDoFindOneByDataWithRequestPath(): void
    {
        $data = [
            UrlRewrite::REQUEST_PATH => 'test.html',
            UrlRewrite::STORE_ID => 1
        ];

        $productFromDb = [
            'entity_type' => 'product',
            'entity_id' => '1',
            'request_path' => 'test.html',
            'target_path' => 'catalog/product/view/id/1',
            'redirect_type' => '0',
        ];

        $this->fetchDataMock($productFromDb, false);

        $this->scopeConfigMock
            ->method('getValue')
            ->willReturn('.html');

        $method = new ReflectionMethod($this->object, 'doFindOneByData');
        $method->setAccessible(true);

        $result = $method->invoke($this->object, $data);
        $this->assertNotNull($result);
        $this->assertEquals('test.html', $result['request_path']);
    }

    /**
     * Test doFindOneByData with TARGET_PATH containing category context
     */
    public function testDoFindOneByDataWithTargetPathAndCategory(): void
    {
        $data = [
            UrlRewrite::TARGET_PATH => 'catalog/product/view/id/1/category/3',
            UrlRewrite::STORE_ID => 1
        ];

        $productFromDb = [
            'entity_type' => 'product',
            'entity_id' => '1',
            'request_path' => 'test.html',
            'target_path' => 'catalog/product/view/id/1',
            'redirect_type' => '0',
        ];

        $categoryFromDb = [
            'entity_type' => 'category',
            'entity_id' => '3',
            'request_path' => 'shop.html',
            'target_path' => 'catalog/category/view/id/3',
            'redirect_type' => '0',
        ];

        $this->setupFetchRowForTargetPath($productFromDb, $categoryFromDb);

        $this->scopeConfigMock
            ->method('getValue')
            ->willReturn('.html');

        $method = new ReflectionMethod($this->object, 'doFindOneByData');
        $method->setAccessible(true);

        $result = $method->invoke($this->object, $data);
        $this->assertNotNull($result);
        $this->assertEquals('shop/test.html', $result['request_path']);
    }

    /**
     * Test doFindOneByData with TARGET_PATH that doesn't match product pattern
     */
    public function testDoFindOneByDataWithNonProductTargetPath(): void
    {
        $data = [
            UrlRewrite::TARGET_PATH => 'cms/page/view/id/1',
            UrlRewrite::STORE_ID => 1
        ];

        $this->connectionMock->expects($this->any())
            ->method('fetchRow')
            ->willReturn(false);

        $this->connectionMock->expects($this->any())
            ->method('fetchAll')
            ->willReturn([]);

        $method = new ReflectionMethod($this->object, 'doFindOneByData');
        $method->setAccessible(true);

        $result = $method->invoke($this->object, $data);
        $this->assertNull($result);
    }

    public function testDoFindOneByDataReturnsNullWhenFilterEmpty(): void
    {
        $data = [
            'entity_type' => 'product',
            'store_id' => 1
        ];

        $this->connectionMock->method('fetchAll')
            ->willReturn([]);

        $method = new ReflectionMethod($this->object, 'doFindOneByData');
        $method->setAccessible(true);

        $result = $method->invoke($this->object, $data);
        $this->assertNull($result);
    }

    /**
     * Test doFindOneByData with ENTITY_TYPE filter
     */
    public function testDoFindOneByDataWithEntityTypeFilter(): void
    {
        $data = [
            UrlRewrite::ENTITY_TYPE => 'product',
            UrlRewrite::ENTITY_ID => '1',
            UrlRewrite::STORE_ID => 1
        ];

        $productFromDb = [
            'entity_type' => 'product',
            'entity_id' => '1',
            'request_path' => 'test.html',
            'target_path' => 'catalog/product/view/id/1',
            'redirect_type' => '0',
        ];

        $this->connectionMock->expects($this->any())
            ->method('fetchAll')
            ->willReturn([$productFromDb]);

        $method = new ReflectionMethod($this->object, 'doFindOneByData');
        $method->setAccessible(true);

        $result = $method->invoke($this->object, $data);
        $this->assertNotNull($result);
        $this->assertEquals('1', $result['entity_id']);
    }

    /**
     * Test doFindAllByData merges remaining products
     */
    public function testDoFindAllByDataWithRemainingProducts(): void
    {
        $data = [
            UrlRewrite::ENTITY_TYPE => 'product',
            UrlRewrite::ENTITY_ID => ['1', '2', '3'],
            UrlRewrite::STORE_ID => 1
        ];

        $productFromDb1 = [
            'entity_type' => 'product',
            'entity_id' => '1',
            'request_path' => 'test1.html',
            'target_path' => 'catalog/product/view/id/1',
            'redirect_type' => '0',
        ];

        $productFromDb2 = [
            'entity_type' => 'product',
            'entity_id' => '2',
            'request_path' => 'test2.html',
            'target_path' => 'catalog/product/view/id/2',
            'redirect_type' => '0',
        ];

        // First call returns only product 1, second call returns product 2 and 3
        $this->connectionMock->expects($this->any())
            ->method('fetchAll')
            ->willReturnOnConsecutiveCalls(
                [$productFromDb1],
                [$productFromDb2]
            );

        $method = new ReflectionMethod($this->object, 'doFindAllByData');
        $method->setAccessible(true);

        $result = $method->invoke($this->object, $data);
        $this->assertCount(2, $result);
    }

    /**
     * Test findProductRewriteByTargetPath with valid pattern
     */
    public function testFindProductRewriteByTargetPathWithValidPattern(): void
    {
        $data = [
            UrlRewrite::TARGET_PATH => 'catalog/product/view/id/5/category/10',
            UrlRewrite::STORE_ID => 2
        ];

        $productFromDb = [
            'entity_type' => 'product',
            'entity_id' => '5',
            'request_path' => 'product.html',
            'target_path' => 'catalog/product/view/id/5',
            'redirect_type' => '0',
        ];

        $categoryFromDb = [
            'entity_type' => 'category',
            'entity_id' => '10',
            'request_path' => 'category.html',
            'target_path' => 'catalog/category/view/id/10',
            'redirect_type' => '0',
        ];

        $this->setupFetchRowForTargetPath($productFromDb, $categoryFromDb);

        $this->scopeConfigMock
            ->method('getValue')
            ->willReturn('.html');

        $method = new ReflectionMethod($this->object, 'findProductRewriteByTargetPath');
        $method->setAccessible(true);

        $result = $method->invoke($this->object, $data);
        $this->assertNotNull($result);
        $this->assertEquals('category/product.html', $result['request_path']);
        $this->assertEquals('catalog/product/view/id/5/category/10', $result['target_path']);
    }

    /**
     * Test findProductRewriteByTargetPath with invalid pattern returns null
     */
    public function testFindProductRewriteByTargetPathWithInvalidPattern(): void
    {
        $data = [
            UrlRewrite::TARGET_PATH => 'catalog/category/view/id/5',
            UrlRewrite::STORE_ID => 2
        ];

        $method = new ReflectionMethod($this->object, 'findProductRewriteByTargetPath');
        $method->setAccessible(true);

        $result = $method->invoke($this->object, $data);
        $this->assertNull($result);
    }

    /**
     * Test findProductRewriteByTargetPath when product not found
     */
    public function testFindProductRewriteByTargetPathProductNotFound(): void
    {
        $data = [
            UrlRewrite::TARGET_PATH => 'catalog/product/view/id/999/category/10',
            UrlRewrite::STORE_ID => 2
        ];

        $this->connectionMock->expects($this->any())
            ->method('fetchRow')
            ->willReturn(false);

        $method = new ReflectionMethod($this->object, 'findProductRewriteByTargetPath');
        $method->setAccessible(true);

        $result = $method->invoke($this->object, $data);
        $this->assertNull($result);
    }

    /**
     * Test findProductRewritesByFilter with category_id in metadata
     */
    public function testFindProductRewritesByFilterWithCategoryMetadata(): void
    {
        $data = [
            UrlRewrite::ENTITY_TYPE => 'product',
            UrlRewrite::ENTITY_ID => '1',
            UrlRewrite::STORE_ID => 1,
            UrlRewrite::METADATA => ['category_id' => '3']
        ];

        $productFromDb = [
            'entity_type' => 'product',
            'entity_id' => '1',
            'request_path' => 'test.html',
            'target_path' => 'catalog/product/view/id/1',
            'redirect_type' => '0',
        ];

        $categoryFromDb = [
            'entity_type' => 'category',
            'entity_id' => '3',
            'request_path' => 'shop.html',
            'target_path' => 'catalog/category/view/id/3',
            'redirect_type' => '0',
        ];

        $this->connectionMock->expects($this->any())
            ->method('fetchAll')
            ->willReturn([$productFromDb]);

        $this->connectionMock->expects($this->any())
            ->method('fetchRow')
            ->willReturn($categoryFromDb);

        $this->scopeConfigMock
            ->method('getValue')
            ->willReturn('.html');

        $method = new ReflectionMethod($this->object, 'findProductRewritesByFilter');
        $method->setAccessible(true);

        $result = $method->invoke($this->object, $data);
        $this->assertCount(1, $result);
        $this->assertEquals('shop/test.html', $result[0]['request_path']);
    }

    /**
     * Test findProductRewritesByFilter without metadata
     */
    public function testFindProductRewritesByFilterWithoutMetadata(): void
    {
        $data = [
            UrlRewrite::ENTITY_TYPE => 'product',
            UrlRewrite::ENTITY_ID => '1',
            UrlRewrite::STORE_ID => 1
        ];

        $productFromDb = [
            'entity_type' => 'product',
            'entity_id' => '1',
            'request_path' => 'test.html',
            'target_path' => 'catalog/product/view/id/1',
            'redirect_type' => '0',
        ];

        $this->connectionMock->expects($this->any())
            ->method('fetchAll')
            ->willReturn([$productFromDb]);

        $method = new ReflectionMethod($this->object, 'findProductRewritesByFilter');
        $method->setAccessible(true);

        $result = $method->invoke($this->object, $data);
        $this->assertCount(1, $result);
        $this->assertEquals('test.html', $result[0]['request_path']);
    }

    /**
     * Test findProductRewritesByFilter returns empty when no entity type
     */
    public function testFindProductRewritesByFilterEmptyWithoutEntityType(): void
    {
        $data = [
            UrlRewrite::STORE_ID => 1
        ];

        $method = new ReflectionMethod($this->object, 'findProductRewritesByFilter');
        $method->setAccessible(true);

        $result = $method->invoke($this->object, $data);
        $this->assertEmpty($result);
    }

    /**
     * Test prepareSelect with category_id in metadata
     */
    public function testPrepareSelectWithCategoryMetadata(): void
    {
        $data = [
            UrlRewrite::ENTITY_TYPE => 'product',
            UrlRewrite::STORE_ID => 1,
            UrlRewrite::METADATA => ['category_id' => '5']
        ];

        $this->selectMock->expects($this->atLeastOnce())
            ->method('where')
            ->willReturnCallback(function ($condition, $value = null) {
                // Verify that category_id condition is applied
                if ($condition === 'relation.category_id = ?') {
                    $this->assertEquals('5', $value);
                }
                return $this->selectMock;
            });

        $method = new ReflectionMethod($this->object, 'prepareSelect');
        $method->setAccessible(true);

        $result = $method->invoke($this->object, $data);
        $this->assertInstanceOf(Select::class, $result);
    }

    /**
     * Test prepareSelect without metadata sets category_id IS NULL
     */
    public function testPrepareSelectWithoutMetadata(): void
    {
        $data = [
            UrlRewrite::ENTITY_TYPE => 'product',
            UrlRewrite::STORE_ID => 1
        ];

        $whereConditions = [];
        $this->selectMock->expects($this->atLeastOnce())
            ->method('where')
            ->willReturnCallback(function ($condition) use (&$whereConditions) {
                $whereConditions[] = $condition;
                return $this->selectMock;
            });

        $method = new ReflectionMethod($this->object, 'prepareSelect');
        $method->setAccessible(true);

        $method->invoke($this->object, $data);
        $this->assertContains('relation.category_id IS NULL', $whereConditions);
    }

    /**
     * Helper method to setup fetchRow mock for target path lookups
     *
     * @param array $productFromDb
     * @param array $categoryFromDb
     */
    private function setupFetchRowForTargetPath(array $productFromDb, array $categoryFromDb): void
    {
        $callCount = 0;
        $this->connectionMock->expects($this->any())
            ->method('fetchRow')
            ->willReturnCallback(function () use (&$callCount, $productFromDb, $categoryFromDb) {
                $callCount++;
                // First call returns product, second call returns category
                if ($callCount === 1) {
                    return $productFromDb;
                }
                return $categoryFromDb;
            });
    }
}
