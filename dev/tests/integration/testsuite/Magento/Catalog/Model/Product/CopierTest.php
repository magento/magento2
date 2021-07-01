<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests product copier.
 * @magentoAppArea adminhtml
 */
class CopierTest extends TestCase
{
    /**
     * @var Copier
     */
    private $copier;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->copier = $objectManager->get(Copier::class);
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $this->productFactory = $objectManager->get(ProductFactory::class);
    }

    /**
     * Tests copying of product.
     *
     * Case when url_key is set for store view and has equal value to default store.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple_multistore_with_url_key.php
     *
     * @return void
     */
    public function testProductCopyWithExistingUrlKey(): void
    {
        $productSKU = 'simple_100';
        $product = $this->productRepository->get($productSKU);
        $newProduct = $this->productFactory->create();
        $duplicate = $this->copier->copy($product, $newProduct);

        $duplicateStoreView = $this->productRepository->getById($duplicate->getId(), false, Store::DISTRO_STORE_ID);
        $productStoreView = $this->productRepository->get($productSKU, false, Store::DISTRO_STORE_ID);

        $this->assertNotEquals(
            $duplicateStoreView->getUrlKey(),
            $productStoreView->getUrlKey(),
            'url_key of product duplicate should be different then url_key of the product for the same store view'
        );
    }

    /**
     * Tests copying of product when url_key exists.
     *
     * @magentoDataFixture Magento/CatalogUrlRewrite/_files/product_simple.php
     * @magentoDbIsolation disabled
     *
     * @return void
     */
    public function testProductCopyWithExistingUrlKeyPermanentType(): void
    {
        $product = $this->productRepository->get('simple');
        $duplicate = $this->copier->copy($product, $this->productFactory->create());

        $data = [
            'url_key' => 'new-url-key',
            'url_key_create_redirect' => $duplicate->getUrlKey(),
            'save_rewrites_history' => true,
        ];
        $duplicate->addData($data);
        $this->productRepository->save($duplicate);

        $secondDuplicate = $this->copier->copy($product, $this->productFactory->create());

        $this->assertNotNull($secondDuplicate->getId());
    }
}
