<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Assert that displayed tier price on product page equals passed from fixture for specified customer.
 */
class AssertProductTierPriceOnProductPageWithCustomer extends AbstractConstraint
{
    /**
     * Assertion that tier prices are displayed correctly for specified customer
     *
     * @param BrowserInterface $browser
     * @param CatalogProductView $catalogProductView
     * @param FixtureInterface $product
     * @param Customer $customer
     * @return void
     */
    public function processAssert(
        BrowserInterface $browser,
        CatalogProductView $catalogProductView,
        FixtureInterface $product,
        Customer $customer
    ) {
        $customer->persist();
        $this->loginCustomer($customer);

        $productTierPriceAssert = $this->objectManager->get(
            \Magento\Catalog\Test\Constraint\AssertProductTierPriceOnProductPage::class
        );
        $productTierPriceAssert->processAssert($browser, $catalogProductView, $product);
    }

    /**
     * Login customer
     *
     * @param Customer $customer
     *
     * @return void
     */
    protected function loginCustomer($customer)
    {
        $this->objectManager->create(
            \Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep::class,
            ['customer' => $customer]
        )->run();
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Tier price is displayed on the product page for specific customer.';
    }
}
