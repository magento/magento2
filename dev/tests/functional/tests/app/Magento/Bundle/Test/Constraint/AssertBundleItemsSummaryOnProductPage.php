<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Test\Constraint;

use Magento\Bundle\Test\Fixture\BundleProduct;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractAssertForm;

/**
 * Assert that displayed summary for bundle options equals to passed from fixture.
 */
class AssertBundleItemsSummaryOnProductPage extends AbstractAssertForm
{
    /**
     * Assert that selecting bundle option affects Summary section accordingly.
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
        $expectedResult = [];
        $actualResult = [];

        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        $bundleOptions = $product->getData()['bundle_selections']['bundle_options'];
        $bundleViewBlock = $catalogProductView->getBundleViewBlock();
        $configuredPriceBlock = $bundleViewBlock->getBundleSummaryBlock()->getConfiguredPriceBlock();
        foreach ($bundleOptions as $bundleOption) {
            foreach ($bundleOption['assigned_products'] as $assignedProduct) {
                $bundleViewBlock->fillOptionsWithCustomData([
                    [
                        'title' => $bundleOption['title'],
                        'type' => $bundleOption['type'],
                        'frontend_type' => $bundleOption['type'],
                        'value' => [
                            'name' => $assignedProduct['search_data']['name']
                        ]
                    ]
                ]);
                $assignedProductPrice = (double)$assignedProduct['data']['selection_price_value'];
                $assignedProductQty = (double)$assignedProduct['data']['selection_qty'];

                foreach ($bundleViewBlock->getBundleSummaryBlock()->getSummaryItems() as $bundleSummaryItem) {
                    $bundleSummaryItemText = $bundleSummaryItem->getText();
                    if (strpos($bundleSummaryItemText, $assignedProduct['search_data']['name']) !== false) {
                        $optionData = $this->getBundleOptionData($bundleSummaryItemText);
                        $optionData['price'] = (double)$configuredPriceBlock->getPrice();
                        $actualResult[] = $optionData;
                    }
                }

                $expectedResult[] = [
                    'qty' => $assignedProduct['data']['selection_qty'],
                    'name' => $assignedProduct['search_data']['name'],
                    'price' => $assignedProductQty * $assignedProductPrice + (double)$product->getPrice()
                ];
            }
        }

        \PHPUnit_Framework_Assert::assertEquals(
            $expectedResult,
            $actualResult,
            'Bundle Summary Section does not contain correct bundle options data.'
        );
    }

    /**
     * Extract Bundle summary item Qty and Name from row text.
     *
     * @param string $rowItem
     * @return array
     */
    private function getBundleOptionData($rowItem)
    {
        // Row item must be displayed like "1 x Simple Product".
        $rowItem = explode(' x ', $rowItem);
        return [
            'qty' => $rowItem[0],
            'name' => $rowItem[1]
        ];
    }

    /**
     * Return Text if displayed on frontend equals with fixture.
     *
     * @return string
     */
    public function toString()
    {
        return 'Bundle options are displayed correctly in the summary section.';
    }
}
