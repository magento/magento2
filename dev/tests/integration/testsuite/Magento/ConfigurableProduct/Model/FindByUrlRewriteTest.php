<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollection;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollectionFactory;
use Magento\UrlRewrite\Model\UrlRewrite as UrlRewriteItem;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use PHPUnit\Framework\TestCase;

/**
 * Test cases related to check that URL rewrite has created or not.
 */
class FindByUrlRewriteTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManger;

    /**
     * @var ProductResource
     */
    private $productResource;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var UrlRewriteCollectionFactory
     */
    private $urlRewriteCollectionFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManger = Bootstrap::getObjectManager();
        $this->productResource = $this->objectManger->get(ProductResource::class);
        $this->productRepository = $this->objectManger->get(ProductRepositoryInterface::class);
        $this->urlRewriteCollectionFactory = $this->objectManger->get(UrlRewriteCollectionFactory::class);
        parent::setUp();
    }

    /**
     * Assert that product is available by URL rewrite with different visibility.
     *
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_product_with_two_child_products.php
     * @dataProvider visibilityWithExpectedResultDataProvider
     * @magentoDbIsolation enabled
     *
     * @param array $productsData
     * @return void
     */
    public function testCheckIsUrlRewriteForChildrenProductsHasCreated(array $productsData): void
    {
        $this->checkConfigurableUrlRewriteWasCreated();
        $this->updateProductsVisibility($productsData);
        $productIdsBySkus = $this->getProductIdsBySkus($productsData);
        $urlRewritesCollection = $this->getUrlRewritesCollectionByProductIds($productIdsBySkus);
        $expectedCount = 0;
        foreach ($productsData as $productData) {
            $productId = $productIdsBySkus[$productData['sku']];
            /** @var UrlRewriteItem $urlRewrite */
            $urlRewrite = $urlRewritesCollection->getItemByColumnValue(
                UrlRewrite::TARGET_PATH,
                "catalog/product/view/id/{$productId}"
            );
            if ($productData['url_rewrite_created']) {
                $this->assertNotNull($urlRewrite);
                $this->assertEquals($productId, $urlRewrite->getEntityId());
                $this->assertEquals('product', $urlRewrite->getEntityType());
                $expectedCount++;
            } else {
                $this->assertNull($urlRewrite);
            }
        }
        $this->assertCount($expectedCount, $urlRewritesCollection);
    }

    /**
     * Return products visibility, expected result and other product additional data.
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * @return array
     */
    public function visibilityWithExpectedResultDataProvider(): array
    {
        return [
            'visibility_for_both_product_only_catalog' => [
                [
                    [
                        'sku' => 'Simple option 1',
                        'visibility' => Visibility::VISIBILITY_IN_CATALOG,
                        'url_rewrite_created' => true,
                    ],
                    [
                        'sku' => 'Simple option 2',
                        'visibility' => Visibility::VISIBILITY_IN_CATALOG,
                        'url_rewrite_created' => true,
                    ],
                ],
            ],
            'visibility_for_both_product_catalog_search' => [
                [
                    [
                        'sku' => 'Simple option 1',
                        'visibility' => Visibility::VISIBILITY_BOTH,
                        'url_rewrite_created' => true,
                    ],
                    [
                        'sku' => 'Simple option 2',
                        'visibility' => Visibility::VISIBILITY_BOTH,
                        'url_rewrite_created' => true,
                    ],
                ],
            ],
            'visibility_for_both_product_only_search' => [
                [
                    [
                        'sku' => 'Simple option 1',
                        'visibility' => Visibility::VISIBILITY_IN_SEARCH,
                        'url_rewrite_created' => true,
                    ],
                    [
                        'sku' => 'Simple option 2',
                        'visibility' => Visibility::VISIBILITY_IN_SEARCH,
                        'url_rewrite_created' => true,
                    ],
                ],
            ],
            'visibility_for_both_product_not_visible_individuality' => [
                [
                    [
                        'sku' => 'Simple option 1',
                        'visibility' => Visibility::VISIBILITY_NOT_VISIBLE,
                        'url_rewrite_created' => false,
                    ],
                    [
                        'sku' => 'Simple option 2',
                        'visibility' => Visibility::VISIBILITY_NOT_VISIBLE,
                        'url_rewrite_created' => false,
                    ],
                ],
            ],
            'visibility_for_one_product_only_catalog' => [
                [
                    [
                        'sku' => 'Simple option 1',
                        'visibility' => Visibility::VISIBILITY_IN_CATALOG,
                        'url_rewrite_created' => true,
                    ],
                    [
                        'sku' => 'Simple option 2',
                        'visibility' => Visibility::VISIBILITY_NOT_VISIBLE,
                        'url_rewrite_created' => false,
                    ],
                ],
            ],
            'visibility_for_one_product_catalog_search' => [
                [
                    [
                        'sku' => 'Simple option 1',
                        'visibility' => Visibility::VISIBILITY_BOTH,
                        'url_rewrite_created' => true,
                    ],
                    [
                        'sku' => 'Simple option 2',
                        'visibility' => Visibility::VISIBILITY_NOT_VISIBLE,
                        'url_rewrite_created' => false,
                    ],
                ],
            ],
            'visibility_for_one_product_only_search' => [
                [
                    [
                        'sku' => 'Simple option 1',
                        'visibility' => Visibility::VISIBILITY_IN_SEARCH,
                        'url_rewrite_created' => true,
                    ],
                    [
                        'sku' => 'Simple option 2',
                        'visibility' => Visibility::VISIBILITY_NOT_VISIBLE,
                        'url_rewrite_created' => false,
                    ],
                ],
            ],
        ];
    }

    /**
     * Update products visibility.
     *
     * @param array $productsData
     * @return void
     */
    private function updateProductsVisibility(array $productsData): void
    {
        foreach ($productsData as $productData) {
            $product = $this->productRepository->get($productData['sku']);
            $product->setVisibility($productData['visibility']);
            $this->productRepository->save($product);
        }
    }

    /**
     * Get URL rewrite collection by product ids.
     *
     * @param int[] $productIds
     * @param string $storeCode
     * @return UrlRewriteCollection
     */
    private function getUrlRewritesCollectionByProductIds(
        array $productIds,
        string $storeCode = 'default'
    ): UrlRewriteCollection {
        $collection = $this->urlRewriteCollectionFactory->create();
        $collection->addStoreFilter($storeCode);
        $collection->addFieldToFilter(UrlRewrite::ENTITY_TYPE, ['eq' => 'product']);
        $collection->addFieldToFilter(UrlRewrite::ENTITY_ID, ['in' => $productIds]);

        return $collection;
    }

    /**
     * Check that configurable url rewrite was created.
     *
     * @return void
     */
    private function checkConfigurableUrlRewriteWasCreated(): void
    {
        $configurableProduct = $this->productRepository->get('Configurable product');
        $configurableUrlRewrite = $this->getUrlRewritesCollectionByProductIds([$configurableProduct->getId()])
            ->getFirstItem();
        $this->assertEquals(
            $configurableUrlRewrite->getTargetPath(),
            "catalog/product/view/id/{$configurableProduct->getId()}"
        );
    }

    /**
     * Load all product ids by skus.
     *
     * @param array $productsData
     * @return array
     */
    private function getProductIdsBySkus(array $productsData): array
    {
        $skus = array_column($productsData, 'sku');

        return $this->productResource->getProductsIdsBySkus($skus);
    }
}
