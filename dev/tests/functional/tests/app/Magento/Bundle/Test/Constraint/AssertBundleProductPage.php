<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Constraint;

use Magento\Catalog\Test\Constraint\AssertProductPage;

/**
 * Class AssertBundleProductPage
 */
class AssertBundleProductPage extends AssertProductPage
{
    /**
     * Verify displayed product price on product page(front-end) equals passed from fixture
     *
     * @return string|null
     */
    protected function verifyPrice()
    {
        $priceData = $this->product->getDataFieldConfig('price')['source']->getPreset();
        $priceBlock = $this->productView->getPriceBlock();
        $priceLow = ($this->product->getPriceView() == 'Price Range')
            ? $priceBlock->getPriceFrom()
            : $priceBlock->getRegularPrice();
        $errors = [];

        if ($priceData['price_from'] != $priceLow) {
            $errors[] = 'Bundle price "From" on product view page is not correct.';
        }
        if ($this->product->getPriceView() == 'Price Range' && $priceData['price_to'] != $priceBlock->getPriceTo()) {
            $errors[] = 'Bundle price "To" on product view page is not correct.';
        }

        return empty($errors) ? null : implode("\n", $errors);
    }
}
