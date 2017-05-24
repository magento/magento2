<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
class AssertOutOfStockOptionIsAbsentOnProductPage extends AbstractConstraint
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
        $listOptions = $catalogProductView->getConfigurableAttributesBlock()->getPresentOptions();
        $productOptions = [];
        foreach ($listOptions as $option) {
            $productOptions = $catalogProductView->getConfigurableAttributesBlock()->getSelectOptionsData($option);
        }
        $option = $this->isOptionAbsent($outOfStockOption, $productOptions);
        \PHPUnit_Framework_Assert::assertTrue($option, 'Out of stock option is present on product page.');
    }

    /**
     * Check if option is absent on product page.
     *
     * @param string $needle
     * @param array $haystack
     * @return bool
     */
    private function isOptionAbsent($needle, array $haystack)
    {
        foreach ($haystack as $options) {
            foreach ($options as $option) {
                if ($option['title'] === $needle) {
                    return false;
                }
            }
        }
        return true;
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
