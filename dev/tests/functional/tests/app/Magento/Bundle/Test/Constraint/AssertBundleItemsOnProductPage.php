<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Bundle\Test\Constraint;

use Mtf\Client\Browser;
use Mtf\Constraint\AbstractAssertForm;
use Magento\Bundle\Test\Fixture\BundleProduct;
use Magento\Catalog\Test\Page\Product\CatalogProductView;

/**
 * Class AssertBundleItemsOnProductPage
 * Assert that displayed product bundle items data on product page equals passed from fixture preset
 */
class AssertBundleItemsOnProductPage extends AbstractAssertForm
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

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

                $optionData['options'][$productKey] = [
                    'title' => $assignedProduct['search_data']['name'],
                    'price' => number_format($price, 2)
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
