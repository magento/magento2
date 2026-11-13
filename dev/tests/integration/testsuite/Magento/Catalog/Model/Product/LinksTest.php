<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductLinkInterface;
use Magento\Catalog\Api\Data\ProductLinkInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductLink\Link;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Saving product with linked products
 */
class LinksTest extends TestCase
{
    /** @var array */
    private $linkTypes = [
        'upsell',
        'crosssell',
        'related',
    ];

    /** @var array */
    private static $defaultDataFixture = [
        [
            'id' => '2',
            'sku' => 'custom-design-simple-product',
            'position' => 1,
        ],
        [
            'id' => '10',
            'sku' => 'simple1',
            'position' => 2,
        ],
    ];

    /** @var array */
    private static $existingProducts = [
        [
            'id' => '10',
            'sku' => 'simple1',
            'position' => 1,
        ],
        [
            'id' => '11',
            'sku' => 'simple2',
            'position' => 2,
        ],
        [
            'id' => '12',
            'sku' => 'simple3',
            'position' => 3,
        ],
    ];

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var ObjectManager */
    private $objectManager;

    /** @var ProductResource */
    private $productResource;

    /** @var ProductLinkInterfaceFactory */
    private $productLinkInterfaceFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $this->productResource = $this->objectManager->create(ProductResource::class);
        $this->productLinkInterfaceFactory = $this->objectManager->create(ProductLinkInterfaceFactory::class);
    }

    /**
     * Test edit and remove simple related, up-sells, cross-sells products in an existing product
     *
     * @magentoDataFixture Magento/Catalog/_files/products.php
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @param array $data
     * @return void
     */
    #[DataProvider('editDeleteRelatedUpSellCrossSellProductsProvider')]
    public function testEditRemoveRelatedUpSellCrossSellProducts(array $data): void
    {
        /** @var ProductInterface|Product $product */
        $product = $this->productRepository->get('simple');
        $this->setCustomProductLinks($product, $this->getProductData($data['defaultLinks']));
        $this->productRepository->save($product);

        $productData = $this->getProductData($data['productLinks']);
        $this->setCustomProductLinks($product, $productData);
        $this->productResource->save($product);

        $product = $this->productRepository->get('simple');
        $expectedLinks = isset($data['expectedProductLinks'])
            ? $this->getProductData($data['expectedProductLinks'])
            : $productData;

        $this->assertEquals(
            $expectedLinks,
            $this->getActualLinks($product),
            "Expected linked products do not match actual linked products!"
        );
    }

    /**
     * Provide test data for testEditDeleteRelatedUpSellCrossSellProducts().
     *
     * @return array
     */
    public static function editDeleteRelatedUpSellCrossSellProductsProvider(): array
    {
        return [
            'update' => [
                'data' => [
                    'defaultLinks' => self::$defaultDataFixture,
                    'productLinks' => self::$existingProducts,
                ],
            ],
            'delete' => [
                'data' => [
                    'defaultLinks' => self::$defaultDataFixture,
                    'productLinks' => []
                ],
            ],
            'same' => [
                'data' => [
                    'defaultLinks' => self::$existingProducts,
                    'productLinks' => self::$existingProducts,
                ],
            ],
            'change_position' => [
                'data' => [
                    'defaultLinks' => self::$existingProducts,
                    'productLinks' => array_replace_recursive(
                        self::$existingProducts,
                        [
                            ['position' => 4],
                            ['position' => 5],
                            ['position' => 6],
                        ]
                    ),
                ],
            ],
            'without_position' => [
                'data' => [
                    'defaultLinks' => self::$defaultDataFixture,
                    'productLinks' => array_replace_recursive(
                        self::$existingProducts,
                        [
                            ['position' => null],
                            ['position' => null],
                            ['position' => null],
                        ]
                    ),
                    'expectedProductLinks' => array_replace_recursive(
                        self::$existingProducts,
                        [
                            ['position' => 1],
                            ['position' => 2],
                            ['position' => 3],
                        ]
                    ),
                ],
            ],
        ];
    }

    /**
     * Saving a product without changing its links must not write to the link tables
     *
     * @magentoDataFixture Magento/Catalog/_files/products.php
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @param string $saveVia
     * @return void
     */
    #[DataProvider('saveViaProvider')]
    public function testSaveWithoutLinkChangesWritesNoLinkRows(string $saveVia): void
    {
        $product = $this->productRepository->get('simple');
        $this->setCustomProductLinks($product, $this->getProductData(self::$existingProducts));
        $this->productRepository->save($product);
        $linkRowsBefore = $this->getLinkRows('simple');

        $product = $this->productRepository->get('simple', false, null, true);
        $product->setName('Simple Product Renamed');
        $writes = $this->getLinkTableWrites(function () use ($product, $saveVia) {
            if ($saveVia === 'repository') {
                $this->productRepository->save($product);
            } else {
                $this->productResource->save($product);
            }
        });

        $this->assertSame([], $writes);
        $this->assertCount(9, $linkRowsBefore);
        $this->assertSame($linkRowsBefore, $this->getLinkRows('simple'));
        $this->assertEquals(
            $this->getProductData(self::$existingProducts),
            $this->getActualLinks($this->productRepository->get('simple', false, null, true))
        );
    }

    /**
     * @return array
     */
    public static function saveViaProvider(): array
    {
        return ['repository' => ['repository'], 'resource model' => ['resource']];
    }

    /**
     * Saving a product with an identical set of links must not write to the link tables
     *
     * @magentoDataFixture Magento/Catalog/_files/products.php
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @return void
     */
    public function testSaveWithSameLinksWritesNoLinkRows(): void
    {
        $product = $this->productRepository->get('simple');
        $this->setCustomProductLinks($product, $this->getProductData(self::$existingProducts));
        $this->productRepository->save($product);
        $linkRowsBefore = $this->getLinkRows('simple');

        $product = $this->productRepository->get('simple', false, null, true);
        $this->setCustomProductLinks($product, $this->getProductData(self::$existingProducts));
        $writes = $this->getLinkTableWrites(function () use ($product) {
            $this->productRepository->save($product);
        });

        $this->assertSame([], $writes);
        $this->assertCount(9, $linkRowsBefore);
        $this->assertSame($linkRowsBefore, $this->getLinkRows('simple'));
        $this->assertEquals(
            $this->getProductData(self::$existingProducts),
            $this->getActualLinks($this->productRepository->get('simple', false, null, true))
        );
    }

    /**
     * Removing and adding links must only delete and insert those links, keeping the unchanged link rows
     *
     * @magentoDataFixture Magento/Catalog/_files/products.php
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @return void
     */
    public function testSaveWithChangedLinksOnlyReplacesChangedRows(): void
    {
        $product = $this->productRepository->get('simple');
        $this->setCustomProductLinks($product, $this->getProductData(self::$defaultDataFixture));
        $this->productRepository->save($product);
        $linkRowsBefore = $this->getLinkRows('simple');

        $product = $this->productRepository->get('simple', false, null, true);
        $this->setCustomProductLinks($product, $this->getProductData(self::$existingProducts));
        $writes = $this->getLinkTableWrites(function () use ($product) {
            $this->productRepository->save($product);
        });
        $linkRowsAfter = $this->getLinkRows('simple');

        // Per link type: product 2 removed, products 11 and 12 added with a position, product 10's position moved to 1
        $this->assertEquals(
            [
                'DELETE catalog_product_link' => 3,
                'INSERT catalog_product_link' => 6,
                'INSERT catalog_product_link_attribute_int' => 9,
            ],
            array_count_values($writes)
        );
        $keptKeys = ['1-10', '4-10', '5-10'];
        $this->assertSame(
            array_intersect_key($linkRowsBefore, array_flip($keptKeys)),
            array_intersect_key($linkRowsAfter, array_flip($keptKeys))
        );
        $this->assertSame(
            ['1-10', '1-11', '1-12', '4-10', '4-11', '4-12', '5-10', '5-11', '5-12'],
            array_keys($linkRowsAfter)
        );
        $this->assertEquals(
            $this->getProductData(self::$existingProducts),
            $this->getActualLinks($this->productRepository->get('simple', false, null, true))
        );
    }

    /**
     * Moving a linked product to another link type must replace that link only
     *
     * @magentoDataFixture Magento/Catalog/_files/products.php
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @return void
     */
    public function testSaveWithLinkMovedToAnotherTypeReplacesOnlyThatLink(): void
    {
        $product = $this->productRepository->get('simple');
        $product->setProductLinks([
            $this->createLink('simple', 'related', 'simple1', 1),
            $this->createLink('simple', 'related', 'simple2', 2),
            $this->createLink('simple', 'upsell', 'simple3', 1),
        ]);
        $this->productRepository->save($product);
        $linkRowsBefore = $this->getLinkRows('simple');

        $product = $this->productRepository->get('simple', false, null, true);
        $product->setProductLinks([
            $this->createLink('simple', 'related', 'simple1', 1),
            $this->createLink('simple', 'upsell', 'simple2', 1),
            $this->createLink('simple', 'upsell', 'simple3', 2),
        ]);
        $this->productRepository->save($product);
        $linkRowsAfter = $this->getLinkRows('simple');

        $this->assertSame(['1-10', '4-11', '4-12'], array_keys($linkRowsAfter));
        $this->assertSame($linkRowsBefore['1-10'], $linkRowsAfter['1-10']);
        $this->assertSame($linkRowsBefore['4-12'], $linkRowsAfter['4-12']);
        $this->assertGreaterThan(max($linkRowsBefore), $linkRowsAfter['4-11']);
        $actual = $this->getActualLinks($this->productRepository->get('simple', false, null, true));
        $this->assertEquals(['simple2' => 1, 'simple3' => 2], array_column($actual['upsell'], 'position', 'sku'));
    }

    /**
     * A link that belongs to another parent SKU must not keep the saved product's existing link
     *
     * @magentoDataFixture Magento/Catalog/_files/products.php
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @return void
     */
    public function testSaveWithLinkForAnotherParentSkuRemovesExistingLink(): void
    {
        $product = $this->productRepository->get('simple');
        $product->setProductLinks([$this->createLink('simple', 'related', 'simple3', 1)]);
        $this->productRepository->save($product);

        $product = $this->productRepository->get('simple', false, null, true);
        $product->setProductLinks([$this->createLink('simple2', 'related', 'simple3', 1)]);
        $this->productRepository->save($product);

        $this->assertSame([], $this->getLinkRows('simple'));
        $this->assertSame(['1-12'], array_keys($this->getLinkRows('simple2')));
    }

    /**
     * Create a product link
     *
     * @param string $sku
     * @param string $linkType
     * @param string $linkedSku
     * @param int $position
     * @return ProductLinkInterface
     */
    private function createLink(string $sku, string $linkType, string $linkedSku, int $position): ProductLinkInterface
    {
        return $this->productLinkInterfaceFactory->create()
            ->setSku($sku)
            ->setLinkType($linkType)
            ->setLinkedProductSku($linkedSku)
            ->setPosition($position);
    }

    /**
     * Run an action and return the writes it made to the product link tables, as "<VERB> <table>"
     *
     * @param callable $action
     * @return string[]
     */
    private function getLinkTableWrites(callable $action): array
    {
        $profiler = $this->productResource->getConnection()->getProfiler();
        $profiler->clear();
        $profiler->setEnabled(true);
        try {
            $action();
        } finally {
            $profiler->setEnabled(false);
        }

        $prefix = substr($this->productResource->getTable('catalog_product_link'), 0, -strlen('catalog_product_link'));
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
     * Get the stored link rows of a product, keyed by link type and linked product
     *
     * @param string $sku
     * @return array
     */
    private function getLinkRows(string $sku): array
    {
        $product = $this->productRepository->get($sku, false, null, true);
        $linkField = $this->objectManager->get(MetadataPool::class)
            ->getMetadata(ProductInterface::class)
            ->getLinkField();
        $connection = $this->productResource->getConnection();
        $select = $connection->select()
            ->from(
                $this->productResource->getTable('catalog_product_link'),
                ['link_type_id', 'linked_product_id', 'link_id']
            )
            ->where('product_id = ?', $product->getData($linkField))
            ->order(['link_type_id', 'linked_product_id']);

        $rows = [];
        foreach ($connection->fetchAll($select) as $row) {
            $rows[$row['link_type_id'] . '-' . $row['linked_product_id']] = (int)$row['link_id'];
        }

        return $rows;
    }

    /**
     * Create an array of products by link type that will be linked
     *
     * @param array $productFixture
     * @return array
     */
    private function getProductData(array $productFixture): array
    {
        $productData = [];
        foreach ($this->linkTypes as $linkType) {
            $productData[$linkType] = [];
            foreach ($productFixture as $data) {
                $productData[$linkType][] = $data;
            }
        }

        return $productData;
    }

    /**
     * Link related, up-sells, cross-sells products received from the array
     *
     * @param ProductInterface|Product $product
     * @param array $productData
     * @return void
     */
    private function setCustomProductLinks(ProductInterface $product, array $productData): void
    {
        $productLinks = [];
        foreach ($productData as $linkType => $links) {
            foreach ($links as $data) {
                /** @var ProductLinkInterface|Link $productLink */
                $productLink = $this->productLinkInterfaceFactory->create();
                $productLink->setSku('simple');
                $productLink->setLinkedProductSku($data['sku']);
                if (isset($data['position'])) {
                    $productLink->setPosition($data['position']);
                }
                $productLink->setLinkType($linkType);
                $productLinks[] = $productLink;
            }
        }
        $product->setProductLinks($productLinks);
    }

    /**
     * Get an array of received related, up-sells, cross-sells products
     *
     * @param ProductInterface|Product $product
     * @return array
     */
    private function getActualLinks(ProductInterface $product): array
    {
        $actualLinks = [];
        foreach ($this->linkTypes as $linkType) {
            $products = [];
            $actualLinks[$linkType] = [];
            switch ($linkType) {
                case 'upsell':
                    $products = $product->getUpSellProducts();
                    break;
                case 'crosssell':
                    $products = $product->getCrossSellProducts();
                    break;
                case 'related':
                    $products = $product->getRelatedProducts();
                    break;
            }
            /** @var ProductInterface|Product $productItem */
            foreach ($products as $productItem) {
                $actualLinks[$linkType][] = [
                    'id' => $productItem->getId(),
                    'sku' => $productItem->getSku(),
                    'position' => $productItem->getPosition(),
                ];
            }
        }

        return $actualLinks;
    }
}
