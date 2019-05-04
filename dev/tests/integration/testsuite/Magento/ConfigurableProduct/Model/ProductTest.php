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
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testGetIdentities()
    {
        $productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $confProduct = $productRepository->get('configurable');
        $simple10Product = $productRepository->get('simple_10');
        $simple20Product = $productRepository->get('simple_20');

        $this->assertEmpty(array_diff($confProduct->getIdentities(), $simple10Product->getIdentities()));
        $this->assertEmpty(array_diff($confProduct->getIdentities(), $simple20Product->getIdentities()));
    }
}
