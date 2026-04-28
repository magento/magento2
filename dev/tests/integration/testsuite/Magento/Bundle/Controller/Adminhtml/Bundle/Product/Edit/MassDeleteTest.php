<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Controller\Adminhtml\Bundle\Product\Edit;

use Magento\Catalog\Controller\Adminhtml\Product\MassDeleteTest as CatalogMassDeleteTest;

/**
 * Test for mass bundle product deleting.
 *
 * @see \Magento\Bundle\Controller\Adminhtml\Bundle\Product\Edit\MassDelete
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class MassDeleteTest extends CatalogMassDeleteTest
{
    /**
     * @magentoDataFixture Magento/Bundle/_files/bundle_product_checkbox_required_option.php
     *
     * @return void
     */
    public function testDeleteBundleProductViaMassAction(): void
    {
        $product = $this->productRepository->get('bundle-product-checkbox-required-option');
        $this->dispatchMassDeleteAction([$product->getId()]);
        $this->assertSuccessfulDeleteProducts(1);
    }
}
