<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\Product;

use Magento\Catalog\Test\Constraint\AssertProductCompareSuccessAddMessage;
use Magento\Catalog\Test\Page\Product\CatalogProductCompare;

/**
 * Preconditions:
 * 1. All product types are created.
 * 2. Customer created.
 *
 * Steps:
 * 1. Navigate to front-end.
 * 1.1 If present data for customer, login as test customer.
 * 2. Open product page of test product(s) and click "Add to Compare" button.
 * 3. Assert success message is present on page.
 * 4. Navigate to compare page(click "compare product" link at the top of the page).
 * 5. Perform all asserts.
 *
 * @group Compare_Products
 * @ZephyrId MAGETWO-25843
 */
class AddCompareProductsTest extends AbstractCompareProductsTest
{
    /* tags */
    const MVP = 'yes';
    const TO_MAINTAIN = 'yes';
    /* end tags */

    /**
     * Catalog product compare page.
     *
     * @var CatalogProductCompare
     */
    protected $catalogProductCompare;

    /**
     * Test creation for adding compare products.
     *
     * @param string $products
     * @param string $isCustomerLoggedIn
     * @param AssertProductCompareSuccessAddMessage $assertProductCompareSuccessAddMessage
     * @param CatalogProductCompare $catalogProductCompare
     * @return array
     */
    public function test(
        $products,
        $isCustomerLoggedIn,
        AssertProductCompareSuccessAddMessage $assertProductCompareSuccessAddMessage,
        CatalogProductCompare $catalogProductCompare
    ) {
        //Steps
        $this->catalogProductCompare = $catalogProductCompare;
        $this->cmsIndex->open();
        if ($isCustomerLoggedIn == 'Yes') {
            $this->loginCustomer();
        }
        $this->products = $this->createProducts($products);
        $this->addProducts($this->products, $assertProductCompareSuccessAddMessage);
        $this->cmsIndex->getLinksBlock()->openLink("Compare Products");

        return ['products' => $this->products];
    }

    /**
     * Clear data after test.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->cmsIndex->open();
        $this->cmsIndex->getLinksBlock()->openLink("Compare Products");
        for ($i = 1; $i <= count($this->products); $i++) {
            $this->catalogProductCompare->getCompareProductsBlock()->removeProduct();
        }
    }
}
