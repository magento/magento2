<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Bundle\Model\Plugin\Frontend;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Interception\PluginList;
use PHPUnit\Framework\TestCase;

/**
 * Test bundle fronted product plugin adds children products ids to bundle product identities.
 */
class ProductTest extends TestCase
{
    /**
     * Check, product plugin is registered for storefront.
     *
     * @magentoAppArea frontend
     * @return void
     */
    public function testProductIsRegistered(): void
    {
        $pluginInfo = Bootstrap::getObjectManager()->get(PluginList::class)
            ->get(\Magento\Catalog\Model\Product::class, []);
        $this->assertSame(Product::class, $pluginInfo['bundle']['instance']);
    }

    /**
     * Check plugin will add children ids to bundle product identities on storefront.
     *
     * @magentoDataFixture Magento/Bundle/_files/product.php
     * @magentoAppArea frontend
     * @return void
     */
    public function testGetIdentitiesForBundleProductOnStorefront(): void
    {
        $productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $bundleProduct = $productRepository->get('bundle-product');
        $simpleProduct = $productRepository->get('simple');
        $expectedIdentities = [
            'cat_p_' . $bundleProduct->getId(),
            'cat_p',
            'cat_p_' . $simpleProduct->getId(),

        ];
        $this->assertEquals($expectedIdentities, $bundleProduct->getIdentities());
    }

    /**
     * Check plugin won't add children ids to bundle product identities in admin area.
     *
     * @magentoDataFixture Magento/Bundle/_files/product.php
     * @magentoAppArea adminhtml
     * @return void
     */
    public function testGetIdentitiesForBundleProductInAdminArea(): void
    {
        $productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $bundleProduct = $productRepository->get('bundle-product');
        $expectedIdentities = [
            'cat_p_' . $bundleProduct->getId(),
        ];
        $this->assertEquals($expectedIdentities, $bundleProduct->getIdentities());
    }
}
