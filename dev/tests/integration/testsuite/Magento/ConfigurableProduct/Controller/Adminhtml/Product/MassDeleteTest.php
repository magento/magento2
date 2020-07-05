<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Controller\Adminhtml\Product;

use Magento\Catalog\Controller\Adminhtml\Product\MassDeleteTest as CatalogMassDeleteTest;

/**
 * Test for mass configurable product deleting.
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class MassDeleteTest extends CatalogMassDeleteTest
{
    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_product_with_one_simple.php
     *
     * @return void
     */
    public function testDeleteConfigurableProductViaMassAction(): void
    {
        $product = $this->productRepository->get('configurable');
        $this->dispatchMassDeleteAction([$product->getId()]);
        $this->assertSuccessfulDeleteProducts(1);
    }
}
