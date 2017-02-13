<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventoryConfigurableProduct\Test\Constraint;

use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct;

/**
 * Assert that out of stock configurable option is not displayed on product page.
 */
class AssertProductOptionsOnProductPage extends AbstractConstraint
{
    /**
     * Assert that out of stock configurable option is not displayed on product page on frontend.
     *
     * @param BrowserInterface $browser
     * @param CatalogProductView $catalogProductView
     * @param ConfigurableProduct $product
     * @param string $outOfStockOption
     * @return void
     */
    public function processAssert(
        BrowserInterface $browser,
        CatalogProductView $catalogProductView,
        ConfigurableProduct $product,
        $outOfStockOption
    ) {
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        $listOptions = $catalogProductView->getConfigurableAttributesBlock()->getListOptions();
        $productOptions = [];
        foreach ($listOptions as $option) {
            $productOptions = $catalogProductView->getConfigurableAttributesBlock()->getDropdownData($option);
        }
        $option = $this->searchForOption($outOfStockOption, $productOptions);
        \PHPUnit_Framework_Assert::assertNull($option, 'Out of stock option is present on product page.');
    }

    /**
     * Search for option.
     *
     * @param string $needle
     * @param array $haystack
     * @return int|null|string
     */
    private function searchForOption($needle, $haystack) {
        foreach ($haystack as $options) {
            foreach ($options as $key => $option) {
                if ($option['title'] === $needle) {
                    return $key;
                }
            }
        }
        return null;
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return "Out of stock configurable option is absent on product page on frontend.";
    }
}
