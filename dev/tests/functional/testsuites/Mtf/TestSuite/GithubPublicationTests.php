<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mtf\TestSuite;

/**
 * Test suite prepared for Github Publication
 */
class GithubPublicationTests
{
    /**
     * List of tests used for Github Publication
     *
     * @return TestSuite
     */
    public static function suite()
    {
        $suite = new TestSuite('Github Publication');

        // New customer creation in backend (MAGETWO-12516)
        $suite->addTestSuite('Magento\Customer\Test\TestCase\BackendCustomerCreateTest');

        // Using USPS/UPS/FedEx/DHL(EU)/DHL(US) online shipping carrier on checkout as a registered customer
        // (MAGETWO-12444, MAGETWO-12848, MAGETWO-12849, MAGETWO-12850, MAGETWO-12851)
        $suite->addTestSuite('Magento\Checkout\Test\TestCase\ShippingCarrierTest');

        // Adding temporary redirect for product (MAGETWO-12409)
        $suite->addTestSuite('Magento\UrlRewrite\Test\TestCase\ProductTest');

        // Creating offline order for registered/new customer in admin (MAGETWO-12395, MAGETWO-12520)
        $suite->addTestSuite('Magento\Sales\Test\TestCase\CreateOrderTest');

        // Creating customer account (MAGETWO-12394)
        $suite->addTestSuite('Magento\Customer\Test\TestCase\CreateOnFrontendTest');

        // Creating Grouped product and assign it to the category (MAGETWO-13610)
        $suite->addTestSuite('Magento\GroupedProduct\Test\TestCase\CreateGroupedTest');

        // Creating Virtual product with required fields only and assign it to the category (MAGETWO-13593)
        $suite->addTestSuite('Magento\Catalog\Test\TestCase\Product\CreateVirtualTest');

        // Creating Downloadable product with required fields only and assign it to the category (MAGETWO-13595)
        $suite->addTestSuite('Magento\Downloadable\Test\TestCase\Create\LinksPurchasedSeparatelyTest');

        // Using ACL Role with full GWS Scope (without using Secret Key to URLs) (MAGETWO-12375, MAGETWO-12385)
        $suite->addTestSuite('Magento\User\Test\TestCase\UserWithRestrictedRoleTest');

        // Creating simple product with creating new category  (required fields only) (MAGETWO-13345)
        $suite->addTestSuite('Magento\Catalog\Test\TestCase\Product\CreateSimpleWithCategoryTest');

        // Checkout products with special prices (MAGETWO-12429)
        $suite->addTestSuite('Magento\Checkout\Test\TestCase\ProductAdvancedPricingTest');

        // Using layered navigation to filter product list (MAGETWO-12419)
        $suite->addTestSuite('Magento\Catalog\Test\TestCase\Layer\FilterProductListTest');

        // Using quick search to find the product (MAGETWO-12420)
        $suite->addTestSuite('Magento\CatalogSearch\Test\TestCase\SearchTest');

        // Product Up-selling (MAGETWO-12391)
        $suite->addTestSuite('Magento\Catalog\Test\TestCase\Product\UpsellTest');

        // Product Cross-selling (MAGETWO-12390)
        $suite->addTestSuite('Magento\Catalog\Test\TestCase\Product\CrosssellTest');

        return $suite;
    }
}
