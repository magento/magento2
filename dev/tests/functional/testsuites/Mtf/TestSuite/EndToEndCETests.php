<?php
/**
 * End-to-end scenarios without 3-rd party solutions for CE
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Mtf\TestSuite;

class EndToEndCETests
{
    public static function suite()
    {
        $suite = new TestSuite('End-to-end Scenarios without 3-rd Party Solutions for CE');

        // Products
        // Simple
        $suite->addTestSuite('Magento\Catalog\Test\TestCase\Product\CreateProductTest');
        $suite->addTestSuite('Magento\Catalog\Test\TestCase\Product\EditSimpleProductTest');
        $suite->addTestSuite('Magento\Catalog\Test\TestCase\Product\CreateSimpleWithCategoryTest');
        $suite->addTestSuite('Magento\Catalog\Test\TestCase\Product\UnassignCategoryTest');
        // Grouped
        $suite->addTestSuite('Magento\GroupedProduct\Test\TestCase\CreateGroupedTest');
        // Virtual
        $suite->addTestSuite('Magento\Catalog\Test\TestCase\Product\CreateVirtualTest');
        // Configurable
        $suite->addTestSuite('Magento\ConfigurableProduct\Test\TestCase\EditConfigurableTest');
        // Downloadable
        $suite->addTestSuite('Magento\Downloadable\Test\TestCase\Create\LinksPurchasedSeparatelyTest');
        // Bundle
        $suite->addTestSuite('Magento\Bundle\Test\TestCase\BundleDynamicTest');
        $suite->addTestSuite('Magento\Bundle\Test\TestCase\EditBundleTest');

        $suite->addTestSuite('Magento\Catalog\Test\TestCase\Product\UpsellTest');
        $suite->addTestSuite('Magento\Catalog\Test\TestCase\Product\CrosssellTest');
        $suite->addTestSuite('Magento\Catalog\Test\TestCase\Product\RelatedProductTest');

        // Product search
        $suite->addTestSuite('Magento\CatalogSearch\Test\TestCase\AdvancedSearchTest');

        // Url rewrites
        $suite->addTestSuite('Magento\Urlrewrite\Test\TestCase\ProductTest');
        $suite->addTestSuite('Magento\Urlrewrite\Test\TestCase\CategoryTest');

        // Customer
        $suite->addTestSuite('Magento\Customer\Test\TestCase\BackendCustomerCreateTest');
        $suite->addTestSuite('Magento\Customer\Test\TestCase\CreateOnFrontendTest');

        // Review
        $suite->addTestSuite('Magento\Review\Test\TestCase\ReviewTest');

        // Tax
        $suite->addTestSuite('Magento\Tax\Test\TestCase\TaxRuleTest');

        // Assign products to a category
        $suite->addTestSuite('Magento\Catalog\Test\TestCase\Category\AssignProductTest');

        return $suite;
    }
}
