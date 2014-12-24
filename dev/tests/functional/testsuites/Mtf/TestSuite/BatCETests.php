<?php
/**
 * BAT CE
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Mtf\TestSuite;

class BatCETests
{
    public static function suite()
    {
        $suite = new TestSuite('BAT CE');

        // Product
        $suite->addTestSuite('Magento\Bundle\Test\TestCase\BundleFixedTest');
        $suite->addTestSuite('Magento\Catalog\Test\TestCase\Product\CreateTest');
        $suite->addTestSuite('Magento\ConfigurableProduct\Test\TestCase\CreateConfigurableTest');
        $suite->addTestSuite('Magento\ConfigurableProduct\Test\TestCase\CreateWithAttributeTest');
        $suite->addTestSuite('Magento\Catalog\Test\TestCase\Product\CreateSimpleWithCustomOptionsAndCategoryTest');

        // Category
        $suite->addTestSuite('Magento\Catalog\Test\TestCase\Category\CreateTest');

        // Stores
        $suite->addTestSuite('Magento\Store\Test\TestCase\StoreTest');

        return $suite;
    }
}
