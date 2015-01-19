<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Test\Constraint;

use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\GroupedProduct\Test\Fixture\GroupedProductInjectable;
use Mtf\Client\Browser;
use Mtf\Constraint\AbstractAssertForm;

/**
 * Class AssertGroupedProductsDefaultQty
 * Assert that default qty for sub products in grouped product displays according to dataset on product page
 */
class AssertGroupedProductsDefaultQty extends AbstractAssertForm
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that default qty for sub products in grouped product displays according to dataset on product page
     *
     * @param CatalogProductView $groupedProductView
     * @param GroupedProductInjectable $product
     * @param Browser $browser
     * @return void
     */
    public function processAssert(
        CatalogProductView $groupedProductView,
        GroupedProductInjectable $product,
        Browser $browser
    ) {
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        $associatedProducts = $product->getAssociated();
        $fixtureQtyData = [];
        $pageOptions = $groupedProductView->getViewBlock()->getOptions($product);
        $pageQtyData = [];

        foreach ($associatedProducts['assigned_products'] as $productData) {
            $fixtureQtyData[] = [
                'name' => $productData['name'],
                'qty' => $productData['qty'],
            ];
        }
        foreach ($pageOptions['grouped_options'] as $productData) {
            $pageQtyData[] = [
                'name' => $productData['name'],
                'qty' => $productData['qty'],
            ];
        }
        $fixtureQtyData = $this->sortDataByPath($fixtureQtyData, '::name');
        $pageQtyData = $this->sortDataByPath($pageQtyData, '::name');

        $error = $this->verifyData($fixtureQtyData, $pageQtyData);
        \PHPUnit_Framework_Assert::assertEmpty($error, $error);
    }

    /**
     * Text of Visible in grouped assert for default qty for sub products
     *
     * @return string
     */
    public function toString()
    {
        return 'Default qty for sub products in grouped product displays according to dataset on product page.';
    }
}
