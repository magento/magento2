<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Model\Product\Type;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Catalog\Test\Fixture\Virtual as VirtualProductFixture;
use Magento\CatalogInventory\Model\Configuration;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\GroupedProduct\Test\Fixture\Product as GroupedProductFixture;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;

class GroupedTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ReinitableConfigInterface
     */
    private $reinitableConfig;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $_productType;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_productType = $this->objectManager->get(\Magento\Catalog\Model\Product\Type::class);
        $this->reinitableConfig = $this->objectManager->get(ReinitableConfigInterface::class);
    }

    protected function tearDown(): void
    {
        $this->dropConfigValue(Configuration::XML_PATH_SHOW_OUT_OF_STOCK);
    }

    public function testFactory()
    {
        $product = new \Magento\Framework\DataObject();
        $product->setTypeId(Grouped::TYPE_CODE);
        $type = $this->_productType->factory($product);
        $this->assertInstanceOf(Grouped::class, $type);
    }

    #[
        AppArea('frontend'),
        DataFixture(ProductFixture::class, ['sku' => 'simple', 'name' => 'Simple Product', 'price' => 10], 'p1'),
        DataFixture(
            VirtualProductFixture::class,
            ['sku' => 'virtual-product', 'name' => 'Virtual Product', 'price' => 10],
            'p2'
        ),
        DataFixture(
            GroupedProductFixture::class,
            ['sku' => 'gr1', 'product_links' => ['$p1$', ['sku' => '$p2.sku$', 'qty' => 2]]],
            'gr1'
        ),
    ]
    public function testGetAssociatedProducts()
    {
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);

        /** @var Product $product */
        $product = $productRepository->get('gr1');
        $type = $product->getTypeInstance();
        $this->assertInstanceOf(Grouped::class, $type);

        $associatedProducts = $type->getAssociatedProducts($product);
        $this->assertCount(2, $associatedProducts);

        $this->assertProductInfo($associatedProducts[0]);
        $this->assertProductInfo($associatedProducts[1]);
    }

    /**
     * @param Product $product
     */
    private function assertProductInfo($product)
    {
        $data = [
            'simple' => [
                'sku' => 'simple',
                'name' => 'Simple Product',
                'price' => '10.000000',
                'qty' => '1',
                'position' => '1'
            ],
            'virtual-product' => [
                'sku' => 'virtual-product',
                'name' => 'Virtual Product',
                'price' => '10.000000',
                'qty' => '2',
                'position' => '2'
            ]
        ];
        $productId = $product->getSku();
        $this->assertEquals($data[$productId]['sku'], $product->getSku());
        $this->assertEquals($data[$productId]['name'], $product->getName());
        $this->assertEquals($data[$productId]['price'], $product->getPrice());
        $this->assertEquals($data[$productId]['qty'], $product->getQty());
        $this->assertEquals($data[$productId]['position'], $product->getPosition());
    }

    /**
     * @magentoDataFixture Magento/GroupedProduct/_files/product_grouped.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     */
    public function testPrepareProduct()
    {
        $buyRequest = $this->objectManager->create(
            \Magento\Framework\DataObject::class,
            ['data' => ['value' => ['qty' => 2]]]
        );
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get('grouped-product');

        /** @var Grouped $type */
        $type = $this->objectManager->get(Grouped::class);

        $processModes = [
            Grouped::PROCESS_MODE_FULL,
            Grouped::PROCESS_MODE_LITE
        ];
        $expectedData = [
            Grouped::PROCESS_MODE_FULL => [
                1  => '{"super_product_config":{"product_type":"grouped","product_id":"'
                    . $product->getId() . '"}}',
                21 => '{"super_product_config":{"product_type":"grouped","product_id":"'
                    . $product->getId() . '"}}',
            ],
            Grouped::PROCESS_MODE_LITE => [
                $product->getId() => '{"value":{"qty":2}}',
            ]
        ];

        foreach ($processModes as $processMode) {
            $products = $type->processConfiguration($buyRequest, $product, $processMode);
            foreach ($products as $item) {
                $productId = $item->getId();
                $this->assertEquals(
                    $expectedData[$processMode][$productId],
                    $item->getCustomOptions()['info_buyRequest']->getValue(),
                    "Wrong info_buyRequest data for product with id: $productId"
                );
            }
        }
    }

    /**
     * Test adding grouped product to cart when one of subproducts is out of stock.
     *
     * @magentoDataFixture Magento/GroupedProduct/_files/product_grouped_with_out_of_stock.php
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @dataProvider outOfStockSubProductDataProvider
     * @param bool $outOfStockShown
     * @param array $data
     * @param array $expected
     */
    public function testOutOfStockSubProduct(bool $outOfStockShown, array $data, array $expected)
    {
        $this->changeConfigValue(Configuration::XML_PATH_SHOW_OUT_OF_STOCK, $outOfStockShown);
        $buyRequest = new \Magento\Framework\DataObject($data);
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        /** @var Product $product */
        $product = $productRepository->get('grouped-product');
        /** @var Grouped $groupedProduct */
        $groupedProduct = $this->objectManager->get(Grouped::class);
        $actual = $groupedProduct->prepareForCartAdvanced($buyRequest, $product, Grouped::PROCESS_MODE_FULL);
        self::assertEquals(
            count($expected),
            count($actual)
        );
        /** @var Product $product */
        foreach ($actual as $product) {
            $sku = $product->getSku();
            self::assertEquals(
                $expected[$sku],
                $product->getCartQty(),
                "Failed asserting that Product Cart Quantity matches expected"
            );
        }
    }

    /**
     * Data provider for testOutOfStockSubProduct.
     *
     * @return array
     */
    public static function outOfStockSubProductDataProvider()
    {
        return [
            'Out of stock product are shown #1' => [
                true,
                [
                    'product' => 3,
                    'qty' => 1,
                    'super_group' => [
                        1 => 4,
                        21 => 5,
                    ],
                ],
                [
                    'virtual-product' => 5,
                    'simple' => 4
                ],
            ],
            'Out of stock product are shown #2' => [
                true,
                [
                    'product' => 3,
                    'qty' => 1,
                    'super_group' => [
                        1 => 0,
                    ],
                ],
                [
                    'virtual-product' => 2.5, // This is a default quantity.
                ],
            ],
            'Out of stock product are hidden #1' => [
                false,
                [
                    'product' => 3,
                    'qty' => 1,
                    'super_group' => [
                        1 => 4,
                        21 => 5,
                    ],
                ],
                [
                    'virtual-product' => 5,
                    'simple' => 4,
                ],
            ],
            'Out of stock product are hidden #2' => [
                false,
                [
                    'product' => 3,
                    'qty' => 1,
                    'super_group' => [
                        1 => 0,
                    ],
                ],
                [
                    'virtual-product' => 2.5, // This is a default quantity.
                ],
            ],
        ];
    }

    /**
     * Write config value to database.
     *
     * @param string $path
     * @param string $value
     * @param string $scope
     * @param int $scopeId
     */
    private function changeConfigValue(string $path, string $value, string $scope = 'default', int $scopeId = 0)
    {
        $configValue = $this->objectManager->create(Value::class);
        $configValue->setPath($path)
            ->setValue($value)
            ->setScope($scope)
            ->setScopeId($scopeId)
            ->save();
        $this->reinitConfig();
    }

    /**
     * Delete config value from database.
     *
     * @param string $path
     */
    private function dropConfigValue(string $path)
    {
        $configValue = $this->objectManager->create(Value::class);
        try {
            $configValue->load($path, 'path');
            $configValue->delete();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            // do nothing
        }
        $this->reinitConfig();
    }

    /**
     * Reinit config.
     */
    private function reinitConfig()
    {
        $this->reinitableConfig->reinit();
    }
}
