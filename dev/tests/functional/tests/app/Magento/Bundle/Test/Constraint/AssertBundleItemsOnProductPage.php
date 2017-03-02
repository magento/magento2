<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Constraint;

use Magento\Bundle\Test\Fixture\BundleProduct;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractAssertForm;

/**
 * Assert that displayed product bundle items data on product page equals passed from fixture
 */
class AssertBundleItemsOnProductPage extends AbstractAssertForm
{
    /**
     * Assert that displayed product bundle items data on product page equals passed from fixture.
     *
     * @param CatalogProductView $catalogProductView
     * @param BundleProduct $product
     * @param BrowserInterface $browser
     * @return void
     */
    public function processAssert(
        CatalogProductView $catalogProductView,
        BundleProduct $product,
        BrowserInterface $browser
    ) {
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');

        $productOptions = $this->prepareBundleOptions($product);
        $productOptions = $this->sortDataByPath($productOptions, '::title');
        foreach ($productOptions as $key => $productOption) {
            $productOptions[$key] = $this->sortDataByPath($productOption, 'options::title');
        }
        $formOptions = $catalogProductView->getViewBlock()->getOptions($product)['bundle_options'];
        $formOptions = $this->sortDataByPath($formOptions, '::title');
        foreach ($formOptions as $key => $formOption) {
            $formOptions[$key] = $this->sortDataByPath($formOption, 'options::title');
        }

        $error = $this->verifyData($productOptions, $formOptions);
        \PHPUnit_Framework_Assert::assertEmpty($error, $error);
    }

    /**
     * Prepare bundle options.
     *
     * @param BundleProduct $product
     * @return array
     */
    protected function prepareBundleOptions(BundleProduct $product)
    {
        $bundleSelections = $product->getBundleSelections();
        $bundleOptions = isset($bundleSelections['bundle_options']) ? $bundleSelections['bundle_options'] : [];
        $result = [];

        foreach ($bundleOptions as $optionKey => $bundleOption) {
            $optionData = [
                'title' => $bundleOption['title'],
                'type' => $bundleOption['frontend_type'],
                'is_require' => $bundleOption['required'],
            ];

            $key = 0;
            foreach ($bundleOption['assigned_products'] as $productKey => $assignedProduct) {
                if ($this->isInStock($product, $key++)) {
                    $price = isset($assignedProduct['data']['selection_price_value'])
                        ? $assignedProduct['data']['selection_price_value']
                        : $bundleSelections['products'][$optionKey][$productKey]->getPrice();

                    $optionData['options'][$productKey] = [
                        'title' => $assignedProduct['search_data']['name'],
                        'price' => number_format($price, 2),
                    ];
                }
            }

            $result[$optionKey] = $optionData;
        }

        return $result;
    }

    /**
     * Check product attribute 'is_in_stock'.
     *
     * @param BundleProduct $product
     * @param int $key
     * @return bool
     */
    private function isInStock(BundleProduct $product, $key)
    {
        $assignedProducts = $product->getBundleSelections()['products'][0];
        $status = $assignedProducts[$key]->getData()['quantity_and_stock_status']['is_in_stock'];

        if ($status == 'In Stock') {
            return true;
        }
        return false;
    }

    /**
     * Return Text if displayed on frontend equals with fixture.
     *
     * @return string
     */
    public function toString()
    {
        return 'Bundle options data on product page equals to passed from fixture dataset.';
    }
}
