<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Constraint;

use Magento\Bundle\Test\Fixture\BundleProduct;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Mtf\Client\Browser;
use Mtf\Constraint\AbstractAssertForm;

/**
 * Class AssertBundleItemsOnProductPage
 * Assert that displayed product bundle items data on product page equals passed from fixture preset
 */
class AssertBundleItemsOnProductPage extends AbstractAssertForm
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that displayed product bundle items data on product page equals passed from fixture preset
     *
     * @param CatalogProductView $catalogProductView
     * @param BundleProduct $product
     * @param Browser $browser
     * @return void
     */
    public function processAssert(
        CatalogProductView $catalogProductView,
        BundleProduct $product,
        Browser $browser
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
     * Prepare bundle options
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
                'type' => $bundleOption['type'],
                'is_require' => $bundleOption['required'],
            ];

            foreach ($bundleOption['assigned_products'] as $productKey => $assignedProduct) {
                $price = isset($assignedProduct['data']['selection_price_value'])
                    ? $assignedProduct['data']['selection_price_value']
                    : $bundleSelections['products'][$optionKey][$productKey]->getPrice();

                if ($product->hasData('group_price')) {
                    $groupedPrice = $product->getGroupPrice();
                    $price -= $price / 100 * reset($groupedPrice)['price'];
                }

                $optionData['options'][$productKey] = [
                    'title' => $assignedProduct['search_data']['name'],
                    'price' => number_format($price, 2),
                ];
            }

            $result[$optionKey] = $optionData;
        }

        return $result;
    }

    /**
     * Return Text if displayed on frontend equals with fixture
     *
     * @return string
     */
    public function toString()
    {
        return 'Bundle options data on product page equals to passed from fixture preset.';
    }
}
