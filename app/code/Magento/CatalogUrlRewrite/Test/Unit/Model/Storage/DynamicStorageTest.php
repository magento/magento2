<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
use Magento\UrlRewrite\Model\OptionProvider;
use Magento\Store\Model\ScopeInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class DynamicStorageTest extends TestCase
{
    /**
     * @var DynamicStorage
     */
    private $object;

    /**
     * @var UrlRewriteFactory|MockObject
     */
    private $urlRewriteFactory;

    /**
     * @var DataObjectHelper|MockObject
     */
    private $dataObjectHelper;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connection;

    /**
     * @var Select|MockObject
     */
    private $select;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceConnection;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfig;

    /**
     * @var Product|MockObject
     */
    private $productResource;

    /**
     * @var ProductFactory|MockObject
     */
    private $productFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->urlRewriteFactory = $this->getMockBuilder(UrlRewriteFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataObjectHelper = $this->getMockBuilder(DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->connection = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->select = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resourceConnection = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->connection
            ->method('select')
            ->willReturn($this->select);

        $this->resourceConnection
            ->method('getConnection')
            ->willReturn($this->connection);

        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMock();

        $this->productResource = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->productFactory = $this->getMockBuilder(ProductFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->productFactory
            ->method('create')
            ->willReturn($this->productResource);

        $this->object = new DynamicStorage(
            $this->urlRewriteFactory,
            $this->dataObjectHelper,
            $this->resourceConnection,
            $this->scopeConfig,
            $this->productFactory
        );
    }

    /**
     * @param $data
     * @param $productFromDb
     * @param $categorySuffix
     * @param $categoryFromDb
     * @param $canBeShownInCategory
     * @param $expectedProductRewrite
     * @throws \ReflectionException
     * @dataProvider findProductRewriteByRequestPathDataProvider
     */
    public function testFindProductRewriteByRequestPath(
        $data,
        $productFromDb,
        $categorySuffix,
        $categoryFromDb,
        $canBeShownInCategory,
        $expectedProductRewrite
    ) {
        $this->connection->expects($this->any())
            ->method('fetchRow')
            ->will($this->onConsecutiveCalls($productFromDb, $categoryFromDb));

        $scopeConfigMap = [
            [
                CategoryUrlPathGenerator::XML_PATH_CATEGORY_URL_SUFFIX,
                ScopeInterface::SCOPE_STORE,
                $data['store_id'],
                $categorySuffix
            ]
        ];

        $this->scopeConfig
            ->method('getValue')
            ->willReturnMap($scopeConfigMap);

        $this->productResource
            ->method('canBeShowInCategory')
            ->willReturn($canBeShownInCategory);

        $method = new ReflectionMethod($this->object, 'findProductRewriteByRequestPath');
        $method->setAccessible(true);

        $this->assertSame($expectedProductRewrite, $method->invoke($this->object, $data));
    }

    /**
     * @return array
     */
    public function findProductRewriteByRequestPathDataProvider(): array
    {
        return [
            [
                // Non-existing product
                [
                    'request_path' => 'test.html',
                    'store_id' => 1
                ],
                false,
                null,
                null,
                null,
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
                null,
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
        ];
    }
}
