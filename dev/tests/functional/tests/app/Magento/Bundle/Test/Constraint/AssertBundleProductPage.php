<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Constraint;

use Magento\Catalog\Test\Constraint\AssertProductPage;

/**
 * Check displayed product price on product page(front-end).
 */
class AssertBundleProductPage extends AssertProductPage
{
    /**
     * Verify displayed product price on product page(front-end) equals passed from fixture.
     *
     * @return string|null
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function verifyPrice()
    {
        $priceData = $this->product->getDataFieldConfig('price')['source']->getPriceData();
        $priceView = $this->product->getPriceView();
        $priceBlock = $this->productView->getPriceBlock();

        if ($this->product->hasData('special_price')) {
            $priceLow = $priceBlock->getPrice();
        } else {
            $priceLow = ($priceView == 'Price Range') ? $priceBlock->getPriceFrom() : $priceBlock->getPrice();
        }

        $errors = [];

        if ($priceData['price_from'] != $priceLow) {
            $errors[] = 'Bundle price "From" on product view page is not correct.';
        }
        if ($priceView == 'Price Range' && $priceData['price_to'] != $priceBlock->getPriceTo()) {
            $errors[] = 'Bundle price "To" on product view page is not correct.';
        }

        return empty($errors) ? null : implode("\n", $errors);
    }
}
