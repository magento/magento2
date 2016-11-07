<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\Product;

use Magento\Config\Test\Fixture\ConfigData;
use Magento\Customer\Test\Page\CustomerAccountIndex;

/**
 * Preconditions:
 * 1. All product types are created.
 * 2. Customer created.
 *
 * Steps:
 * 1. Login to frontend.
 * 2. Add to Compare Product $products.
 * 3. Navigate to My Account page.
 * 4. Click "Clear All" icon under the left menu tabs.
 * 5. Perform assertions.
 *
 * @group Compare_Products
 * @ZephyrId MAGETWO-25961
 */
class ClearAllCompareProductsTest extends AbstractCompareProductsTest
{
    /* tags */
    const MVP = 'yes';
    /* end tags */

    /**
     * Test creation for clear all compare products.
     *
     * @param string $products
     * @param ConfigData $config
     * @param CustomerAccountIndex $customerAccountIndex
     * @return void
     */
    public function test($products, ConfigData $config, CustomerAccountIndex $customerAccountIndex)
    {
        // Preconditions
        $config->persist();
        $products = $this->createProducts($products);

        //Steps
        $this->cmsIndex->open();
        $this->loginCustomer();
        $this->addProducts($products);
        $this->cmsIndex->getLinksBlock()->openLink("My Account");
        $customerAccountIndex->getCompareProductsBlock()->clickClearAll();
    }
}
