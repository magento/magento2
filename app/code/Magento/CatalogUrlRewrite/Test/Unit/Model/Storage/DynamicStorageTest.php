<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Model\Storage;

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
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;
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
     * @dataProvider findProductRewriteByRequestPathDataProvider
     * @param array $data
     * @param array|false $productFromDb
     * @param string $categorySuffix
     * @param array|false $categoryFromDb
     * @param bool $canBeShownInCategory
     * @param array|null $expectedProductRewrite
     * @throws \ReflectionException
     */
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
        $method->setAccessible(true);

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
}
