<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product;

use Magento\Catalog\Api\Data\ProductLinkInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductLink\Link;
use Magento\Catalog\Model\ProductRepository;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
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
    private $defaultDataFixture = [
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
    private $existingProducts = [
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

    /** @var ProductRepository $productRepository */
    private $productRepository;

    /** @var ObjectManager */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
    }

    /**
     * Test edit and remove simple related, up-sells, cross-sells products in an existing product
     *
     * @dataProvider editDeleteRelatedUpSellCrossSellProductsProvider
     * @magentoDataFixture Magento/Catalog/_files/products.php
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @param array $data
     * @return void
     */
    public function testEditRemoveRelatedUpSellCrossSellProducts(array $data): void
    {
        /** @var Product $product */
        $product = $this->productRepository->get('simple');
        $this->setCustomProductLinks($product, $this->getProductData($data['defaultLinks']));
        $this->productRepository->save($product);

        $productData = $this->getProductData($data['productLinks']);
        $this->setCustomProductLinks($product, $productData);
        $product->save();

        $product = $this->productRepository->get('simple');
        $expectedLinks = isset($data['expectedProductLinks'])
            ? $this->getProductData($data['expectedProductLinks'])
            : $productData;
        $actualLinks = $this->getActualLinks($product);

        $this->assertEquals(
            $expectedLinks,
            $actualLinks,
            "Expected linked products do not match actual linked products!"
        );
    }

    /**
     * Provide test data for testEditDeleteRelatedUpSellCrossSellProducts().
     *
     * @return array
     */
    public function editDeleteRelatedUpSellCrossSellProductsProvider(): array
    {
        return [
            'update' => [
                'data' => [
                    'defaultLinks' => $this->defaultDataFixture,
                    'productLinks' => $this->existingProducts,
                ],
            ],
            'delete' => [
                'data' => [
                    'defaultLinks' => $this->defaultDataFixture,
                    'productLinks' => []
                ],
            ],
            'same' => [
                'data' => [
                    'defaultLinks' => $this->existingProducts,
                    'productLinks' => $this->existingProducts,
                ],
            ],
            'change_position' => [
                'data' => [
                    'defaultLinks' => $this->existingProducts,
                    'productLinks' => array_replace_recursive(
                        $this->existingProducts,
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
                    'defaultLinks' => $this->defaultDataFixture,
                    'productLinks' => array_replace_recursive(
                        $this->existingProducts,
                        [
                            ['position' => null],
                            ['position' => null],
                            ['position' => null],
                        ]
                    ),
                    'expectedProductLinks' => array_replace_recursive(
                        $this->existingProducts,
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
                $productData[$linkType][$data['id']] = $data;
            }
        }
        return $productData;
    }

    /**
     * Link related, up-sells, cross-sells products received from the array
     *
     * @param Product $product
     * @param array $productData
     */
    private function setCustomProductLinks(Product $product, array $productData): void
    {
        $productLinks = [];
        foreach ($productData as $linkType => $links) {
            foreach ($links as $data) {
                /** @var Link $productLink */
                $productLink = $this->objectManager->create(ProductLinkInterface::class);
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
     * @param Product $product
     * @return array
     */
    private function getActualLinks(Product $product): array
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
            /** @var Product $product */
            foreach ($products as $productItem) {
                $actualLinks[$linkType][$productItem->getId()] = [
                    'id' => $productItem->getId(),
                    'sku' => $productItem->getSku(),
                    'position' => $productItem->getPosition(),
                ];
            }
        }

        return $actualLinks;
    }
}
