<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Constraint;

use Magento\Catalog\Test\Constraint\AssertProductPage;

class AssertConfigurableProductOutOfStockPage extends AssertProductPage
{
    /**
     * Verifies that all relevant product data will be shown for an out of stock configurable product.
     *
     * @return array
     */
    protected function verify()
    {
        $errors = parent::verify();

        return array_filter($errors);
    }

    /**
     * Verify displayed product price on product page (front-end) equals passed from fixture
     *
     * @return string|null
     */
    protected function verifyPrice()
    {
        $priceBlock = $this->productView->getPriceBlock();
        $fixturePrice = $this->getLowestConfigurablePrice();

        if ($fixturePrice === null) {
            if ($priceBlock->isVisible()) {
                return "Price block for '{$this->product->getName()}' product' is visible.";
            }
        } else {
            if (!$priceBlock->isVisible()) {
                return "Price block for '{$this->product->getName()}' product' is not visible.";
            }

            $formPrice = $priceBlock->isOldPriceVisible() ? $priceBlock->getOldPrice() : $priceBlock->getPrice();

            if ($fixturePrice != $formPrice) {
                return "Displayed product price on product page (front-end) not equals passed from fixture. "
                    . "Actual: {$formPrice}, expected: {$fixturePrice}.";
            }
        }

        return null;
    }

    /**
     * Returns lowest possible price of configurable product.
     *
     * @return string
     */
    protected function getLowestConfigurablePrice()
    {
        $price = null;
        $priceDataConfig = $this->product->getDataFieldConfig('price');
        if (isset($priceDataConfig['source'])) {
            $priceData = $priceDataConfig['source']->getPriceData();
            if (isset($priceData['price_from'])) {
                $price = $priceData['price_from'];
            }
        }

        if (null === $price) {
            $configurableOptions = $this->product->getConfigurableAttributesData();
            $products = $this->product->getDataFieldConfig('configurable_attributes_data')['source']->getProducts();
            foreach ($configurableOptions['matrix'] as $key => $option) {
                if ($products[$key]->getQuantityAndStockStatus()['is_in_stock'] !== 'Out of Stock') {
                    $price = $price === null ? $option['price'] : $price;
                    if ($price > $option['price']) {
                        $price = $option['price'];
                    }
                }
            }
        }
        return $price;
    }
}
