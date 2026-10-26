<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\GroupedProduct\Model\Product\Type;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Catalog\Test\Fixture\Virtual as VirtualProductFixture;
use Magento\CatalogInventory\Model\Configuration;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\GroupedProduct\Model\ResourceModel\Product\Link;
use Magento\GroupedProduct\Test\Fixture\Product as GroupedProductFixture;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use PHPUnit\Framework\Attributes\DataProvider;

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
    public function testSaveWithChangedQtyKeepsLinkRowsAndUpdatesQty(): void
    {
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $linkIdsBefore = $this->getGroupedLinkIds('gr1');

        $product = $productRepository->get('gr1', false, null, true);
        $links = $product->getProductLinks();
        foreach ($links as $link) {
            if ($link->getLinkedProductSku() === 'virtual-product') {
                $link->getExtensionAttributes()->setQty(5);
            }
        }
        $product->setProductLinks($links);
        $productRepository->save($product);

        $product = $productRepository->get('gr1', false, null, true);
        $qtyBySku = [];
        foreach ($product->getTypeInstance()->getAssociatedProducts($product) as $associatedProduct) {
            $qtyBySku[$associatedProduct->getSku()] = (float)$associatedProduct->getQty();
        }

        $this->assertEquals(['simple' => 1.0, 'virtual-product' => 5.0], $qtyBySku);
        $this->assertCount(2, $linkIdsBefore);
        $this->assertSame($linkIdsBefore, $this->getGroupedLinkIds('gr1'));
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
            ['sku' => 'gr1', 'product_links' => [['sku' => '$p1.sku$', 'qty' => 3], ['sku' => '$p2.sku$', 'qty' => 2]]],
            'gr1'
        ),
    ]
    public function testSaveWithOneChildRemovedKeepsTheOtherChild(): void
    {
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $linkIdsBefore = $this->getGroupedLinkIds('gr1');

        $product = $productRepository->get('gr1', false, null, true);
        $links = array_filter(
            $product->getProductLinks(),
            fn ($link) => $link->getLinkedProductSku() === 'simple'
        );
        $product->setProductLinks(array_values($links));
        $productRepository->save($product);

        $product = $productRepository->get('gr1', false, null, true);
        $qtyBySku = [];
        foreach ($product->getTypeInstance()->getAssociatedProducts($product) as $associatedProduct) {
            $qtyBySku[$associatedProduct->getSku()] = (float)$associatedProduct->getQty();
        }

        $this->assertEquals(['simple' => 3.0], $qtyBySku);
        $this->assertCount(2, $linkIdsBefore);
        $this->assertSame(array_slice($linkIdsBefore, 0, 1, true), $this->getGroupedLinkIds('gr1'));
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
            [
                'sku' => 'gr1',
                'product_links' => [['sku' => '$p1.sku$', 'qty' => 3], ['sku' => '$p2.sku$', 'qty' => 2.5]],
            ],
            'gr1'
        ),
    ]
    public function testSaveWithoutChangesWritesNoLinkRows(): void
    {
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);

        $product = $productRepository->get('gr1', false, null, true);
        $product->setName('Grouped Product Renamed');
        $writesWithoutLinks = $this->getLinkTableWrites(function () use ($productRepository, $product) {
            $productRepository->save($product);
        });

        $product = $productRepository->get('gr1', false, null, true);
        $product->setProductLinks($product->getProductLinks());
        $writesWithSameLinks = $this->getLinkTableWrites(function () use ($productRepository, $product) {
            $productRepository->save($product);
        });

        $this->assertSame([], $writesWithoutLinks);
        $this->assertSame([], $writesWithSameLinks);
        $this->assertEquals(['simple' => 3.0, 'virtual-product' => 2.5], $this->getStoredQtyBySku('gr1'));
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
            ['sku' => 'gr1', 'product_links' => [['sku' => '$p1.sku$', 'qty' => 3], ['sku' => '$p2.sku$', 'qty' => 2]]],
            'gr1'
        ),
    ]
    public function testSaveWithQtyOmittedDeletesStoredQty(): void
    {
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $product = $productRepository->get('gr1', false, null, true);
        $links = $product->getProductLinks();
        foreach ($links as $link) {
            if ($link->getLinkedProductSku() === 'virtual-product') {
                $link->getExtensionAttributes()->setQty(null);
            }
        }
        $product->setProductLinks($links);
        $writes = $this->getLinkTableWrites(function () use ($productRepository, $product) {
            $productRepository->save($product);
        });

        $this->assertSame(['DELETE catalog_product_link_attribute_decimal'], $writes);
        $this->assertEquals(['simple' => 3.0], $this->getStoredQtyBySku('gr1'));
    }

    /**
     * Get the stored grouped qty of each linked product
     *
     * @param string $sku
     * @return array
     */
    private function getStoredQtyBySku(string $sku): array
    {
        $product = $this->objectManager->create(ProductRepositoryInterface::class)->get($sku, false, null, true);
        $linkField = $this->objectManager->get(MetadataPool::class)
            ->getMetadata(ProductInterface::class)
            ->getLinkField();
        $resource = $this->objectManager->get(ResourceConnection::class);
        $connection = $resource->getConnection();
        $select = $connection->select()
            ->from(['l' => $resource->getTableName('catalog_product_link')], [])
            ->join(
                ['p' => $resource->getTableName('catalog_product_entity')],
                'p.entity_id = l.linked_product_id',
                ['sku']
            )
            ->join(
                ['a' => $resource->getTableName('catalog_product_link_attribute')],
                "a.link_type_id = l.link_type_id AND a.product_link_attribute_code = 'qty'",
                []
            )
            ->join(
                ['v' => $resource->getTableName('catalog_product_link_attribute_decimal')],
                'v.link_id = l.link_id AND v.product_link_attribute_id = a.product_link_attribute_id',
                ['value']
            )
            ->where('l.product_id = ?', $product->getData($linkField))
            ->where('l.link_type_id = ?', Link::LINK_TYPE_GROUPED)
            ->order('p.sku');

        return array_map('floatval', $connection->fetchPairs($select));
    }

    /**
     * Run an action and return the writes it made to the product link tables, as "<VERB> <table>"
     *
     * @param callable $action
     * @return string[]
     */
    private function getLinkTableWrites(callable $action): array
    {
        $resource = $this->objectManager->get(ResourceConnection::class);
        $profiler = $resource->getConnection()->getProfiler();
        $profiler->clear();
        $profiler->setEnabled(true);
        try {
            $action();
        } finally {
            $profiler->setEnabled(false);
        }

        $prefix = substr($resource->getTableName('catalog_product_link'), 0, -strlen('catalog_product_link'));
        $pattern = '/^\s*(INSERT|UPDATE|DELETE)\s+(?:IGNORE\s+)?(?:INTO\s+|FROM\s+)?`?'
            . preg_quote($prefix, '/') . '(catalog_product_link\w*)`?/i';
        $writes = [];
        foreach ($profiler->getQueryProfiles() ?: [] as $queryProfile) {
            if (preg_match($pattern, $queryProfile->getQuery(), $match)) {
                $writes[] = strtoupper($match[1]) . ' ' . $match[2];
            }
        }
        $profiler->clear();

        return $writes;
    }

    /**
     * Get the stored grouped link ids of a product, keyed by linked product id
     *
     * @param string $sku
     * @return array
     */
    private function getGroupedLinkIds(string $sku): array
    {
        $product = $this->objectManager->create(ProductRepositoryInterface::class)->get($sku, false, null, true);
        $linkField = $this->objectManager->get(MetadataPool::class)
            ->getMetadata(ProductInterface::class)
            ->getLinkField();
        $resource = $this->objectManager->get(ResourceConnection::class);
        $connection = $resource->getConnection();
        $select = $connection->select()
            ->from($resource->getTableName('catalog_product_link'), ['linked_product_id', 'link_id'])
            ->where('product_id = ?', $product->getData($linkField))
            ->where('link_type_id = ?', Link::LINK_TYPE_GROUPED)
            ->order('linked_product_id');

        return array_map('intval', $connection->fetchPairs($select));
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
     * @param bool $outOfStockShown
     * @param array $data
     * @param array $expected
     */
    #[DataProvider('outOfStockSubProductDataProvider')]
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
