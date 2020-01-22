<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product;

use Magento\Catalog\Model\ProductRepository;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests product copier.
 */
class CopierTest extends TestCase
{
    /**
     * Tests copying of product.
     *
     * Case when url_key is set for store view and has equal value to default store.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple_multistore_with_url_key.php
     * @magentoAppArea adminhtml
     */
    public function testProductCopyWithExistingUrlKey()
    {
        $productSKU = 'simple_100';
        /** @var ProductRepository $productRepository */
        $productRepository = Bootstrap::getObjectManager()->get(ProductRepository::class);
        $copier = Bootstrap::getObjectManager()->get(Copier::class);

        $product = $productRepository->get($productSKU);
        $duplicate = $copier->copy($product);

        $duplicateStoreView = $productRepository->getById($duplicate->getId(), false, Store::DISTRO_STORE_ID);
        $productStoreView = $productRepository->get($productSKU, false, Store::DISTRO_STORE_ID);

        $this->assertNotEquals(
            $duplicateStoreView->getUrlKey(),
            $productStoreView->getUrlKey(),
            'url_key of product duplicate should be different then url_key of the product for the same store view'
        );
    }
}
