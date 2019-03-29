<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\ConfigurableProduct\Model\Plugin\Frontend;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Interception\PluginList;
use PHPUnit\Framework\TestCase;

/**
 * Test configurable fronted product plugin will add children products ids to configurable product identities.
 */
class ProductIdentitiesExtenderTest extends TestCase
{
    /**
     * Check, product identities extender plugin is registered for storefront.
     *
     * @magentoAppArea frontend
     * @return void
     */
    public function testIdentitiesExtenderIsRegistered(): void
    {
        $pluginInfo = Bootstrap::getObjectManager()->get(PluginList::class)
            ->get(\Magento\Catalog\Model\Product::class, []);
        $this->assertSame(ProductIdentitiesExtender::class, $pluginInfo['product_identities_extender']['instance']);
    }

    /**
     * Check plugin will add children ids to configurable product identities on storefront.
     *
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoAppArea frontend
     * @return void
     */
    public function testGetIdentitiesForConfigurableProductOnStorefront(): void
    {
        $productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $configurableProduct = $productRepository->get('configurable');
        $simpleProduct1 = $productRepository->get('simple_10');
        $simpleProduct2 = $productRepository->get('simple_20');
        $expectedIdentities = [
            'cat_p_' . $configurableProduct->getId(),
            'cat_p',
            'cat_p_' . $simpleProduct1->getId(),
            'cat_p_' . $simpleProduct2->getId(),

        ];
        $this->assertEquals($expectedIdentities, $configurableProduct->getIdentities());
    }

    /**
     * Check plugin won't add children ids to configurable product identities in admin area.
     *
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoAppArea adminhtml
     * @return void
     */
    public function testGetIdentitiesForConfigurableProductInAdminArea(): void
    {
        $productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $configurableProduct = $productRepository->get('configurable');
        $expectedIdentities = [
            'cat_p_' . $configurableProduct->getId(),
        ];
        $this->assertEquals($expectedIdentities, $configurableProduct->getIdentities());
    }
}
