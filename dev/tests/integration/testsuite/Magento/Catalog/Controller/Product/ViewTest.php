<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\Registry;
use Magento\TestFramework\Response;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Checks product visibility on storefront
 *
 * @magentoDbIsolation enabled
 */
class ViewTest extends AbstractController
{
    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var Registry */
    private $registry;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->productRepository = $this->_objectManager->create(ProductRepositoryInterface::class);
        $this->registry = $this->_objectManager->get(Registry::class);
    }

    /**
     * @magentoDataFixture Magento/Catalog/controllers/_files/products.php
     * @magentoConfigFixture current_store catalog/seo/product_canonical_tag 1
     */
    public function testViewActionWithCanonicalTag()
    {
        $this->markTestSkipped(
            'MAGETWO-40724: Canonical url from tests sometimes does not equal canonical url from action'
        );
        $this->dispatch('catalog/product/view/id/1/');

        $this->assertContains(
            '<link  rel="canonical" href="http://localhost/index.php/catalog/product/view/_ignore_category/1/id/1/" />',
            $this->getResponse()->getBody()
        );
    }

    /**
     * @magentoDataFixture Magento/Quote/_files/is_not_salable_product.php
     * @return void
     */
    public function testDisabledProductInvisibility(): void
    {
        $product = $this->productRepository->get('simple-99');
        $this->dispatch(sprintf('catalog/product/view/id/%s/', $product->getId()));

        $this->assert404NotFound();
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     * @dataProvider productVisibilityDataProvider
     * @param int $visibility
     * @return void
     */
    public function testProductVisibility(int $visibility): void
    {
        $product = $this->updateProductVisibility('simple2', $visibility);
        $this->dispatch(sprintf('catalog/product/view/id/%s/', $product->getId()));

        $this->assertProductIsVisible($product);
    }

    /**
     * @return array
     */
    public function productVisibilityDataProvider(): array
    {
        return [
            'catalog_search' => [Visibility::VISIBILITY_BOTH],
            'search' => [Visibility::VISIBILITY_IN_SEARCH],
            'catalog' => [Visibility::VISIBILITY_IN_CATALOG],
        ];
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/simple_products_not_visible_individually.php
     */
    public function testProductNotVisibleIndividually(): void
    {
        $product = $this->updateProductVisibility('simple_not_visible_1', Visibility::VISIBILITY_NOT_VISIBLE);
        $this->dispatch(sprintf('catalog/product/view/id/%s/', $product->getId()));

        $this->assert404NotFound();
    }

    /**
     * @inheritdoc
     */
    public function assert404NotFound()
    {
        parent::assert404NotFound();

        $this->assertNull($this->registry->registry('current_product'));
    }

    /**
     * Assert that product is available in storefront
     *
     * @param ProductInterface $product
     * @return void
     */
    private function assertProductIsVisible(ProductInterface $product): void
    {
        $this->assertEquals(
            Response::STATUS_CODE_200,
            $this->getResponse()->getHttpResponseCode(),
            'Wrong response code is returned'
        );
        $this->assertEquals(
            $product->getSku(),
            $this->registry->registry('current_product')->getSku(),
            'Wrong product is registered'
        );
    }

    /**
     * Update product visibility
     *
     * @param string $sku
     * @param int $visibility
     * @return ProductInterface
     */
    private function updateProductVisibility(string $sku, int $visibility): ProductInterface
    {
        $product = $this->productRepository->get($sku);
        $product->setVisibility($visibility);

        return $this->productRepository->save($product);
    }
}
