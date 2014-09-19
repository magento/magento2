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

use Mtf\Fixture\FixtureInterface;
use Magento\Catalog\Test\Block\Product\View;
use Magento\Catalog\Test\Constraint\AssertProductGroupedPriceOnProductPage;

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
            'fixture' => $product->getDataFieldConfig('price')['source']->getPreset()['price_from']
        ];

        $groupPrice['onPage'] = isset($groupPrice['onPage']['price_regular_price'])
            ? str_replace('As low as $', '', $groupPrice['onPage']['price_regular_price'])
            : str_replace('$', '', $groupPrice['onPage']['price_from']);

        return $groupPrice;
    }
}
