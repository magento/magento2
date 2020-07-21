<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator.
 *
 * @magentoAppArea adminhtml
 */
class ProductUrlRewriteGeneratorTest extends TestCase
{
    /**
     * @var ProductUrlRewriteGenerator
     */
    private $model;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        $this->model = $objectManager->get(ProductUrlRewriteGenerator::class);
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
    }

    /**
     * Test generate url rewrite with specific category url key
     *
     * @magentoDataFixture Magento/CatalogUrlRewrite/_files/product_with_category.php
     * @magentoConfigFixture default/catalog/seo/generate_category_product_rewrites 1
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     *
     * @return void
     */
    public function testGenerateWithSpecificCategoryUrlKey(): void
    {
        $product = $this->productRepository->get('p002');
        $product->setStoreId(0);

        $result = $this->getGeneratedUrls($product);

        $this->assertTrue(in_array('p002.html', $result));
        $this->assertTrue(in_array('cat-1/p002.html', $result));
        $this->assertTrue(in_array('cat-1-2/p002.html', $result));
    }

    /**
     * Test generate url rewrite for product not visible individually
     *
     * @magentoDataFixture Magento/Catalog/_files/simple_products_not_visible_individually.php
     *
     * @return void
     */
    public function testGenerateForProductNotVisibleIndividually(): void
    {
        $product = $this->productRepository->get('simple_not_visible_1');
        $result = $this->getGeneratedUrls($product);

        $this->assertTrue(in_array('simple-product-not-visible-1.html', $result));
    }

    /**
     * Returns prepared urls by product
     *
     * @param ProductInterface $product
     * @return array
     */
    private function getGeneratedUrls(ProductInterface $product): array
    {
        $urls = $this->model->generate($product);

        return array_map(
            function (UrlRewrite $url) {
                return $url->getRequestPath();
            },
            $urls
        );
    }
}
