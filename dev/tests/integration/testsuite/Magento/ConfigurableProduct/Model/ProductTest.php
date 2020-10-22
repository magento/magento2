<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Model;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Api\ProductRepositoryInterface;

class ProductTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Check that no children identities are added to the parent product in frontend area
     *
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoAppArea frontend
     * @return void
     */
    public function testGetIdentitiesForConfigurableProductOnStorefront(): void
    {
        $productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $configurableProduct = $productRepository->get('configurable');
        $expectedIdentities = [
            'cat_p_' . $configurableProduct->getId(),
            'cat_p'
        ];
        $this->assertEquals($expectedIdentities, $configurableProduct->getIdentities());
    }

    /**
     * Check that no children identities are added to the parent product in frontend area
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
