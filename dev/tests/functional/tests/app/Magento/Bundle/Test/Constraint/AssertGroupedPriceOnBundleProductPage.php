<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Bundle\Test\Constraint;

use Magento\Catalog\Test\Block\Product\View;
use Magento\Catalog\Test\Constraint\AssertProductGroupedPriceOnProductPage;
use Mtf\Fixture\FixtureInterface;

/**
 * Class AssertGroupedPriceOnBundleProductPage
 */
class AssertGroupedPriceOnBundleProductPage extends AssertProductGroupedPriceOnProductPage
{
    /**
     * Get grouped price with fixture product and product page
     *
     * @param View $view
     * @param FixtureInterface $product
     * @return array
     */
    protected function getGroupedPrice(View $view, FixtureInterface $product)
    {
        $groupPrice = [
            'onPage' => [
                'price_regular_price' => $view->getPriceBlock()->getPrice(),
                'price_from' => $view->getPriceBlock()->getPriceFrom(),
            ],
            'fixture' => $product->getDataFieldConfig('price')['source']->getPreset()['price_from'],
        ];

        $groupPrice['onPage'] = isset($groupPrice['onPage']['price_regular_price'])
            ? str_replace('As low as $', '', $groupPrice['onPage']['price_regular_price'])
            : str_replace('$', '', $groupPrice['onPage']['price_from']);

        return $groupPrice;
    }
}
