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

use Magento\Catalog\Test\Constraint\AssertProductPage;

/**
 * Class AssertBundleProductPage
 */
class AssertBundleProductPage extends AssertProductPage
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Error messages
     *
     * @var array
     */
    protected $errorsMessages = [
        'name' => '- product name on product view page is not correct.',
        'sku' => '- product sku on product view page is not correct.',
        'price_from' => '- bundle product price from on product view page is not correct.',
        'price_to' => '- bundle product price to on product view page is not correct.',
        'short_description' => '- product short description on product view page is not correct.',
        'description' => '- product description on product view page is not correct.'
    ];

    /**
     * Prepare Price data
     *
     * @param array $price
     * @return array
     */
    protected function preparePrice($price)
    {
        $priceData = $this->product->getDataFieldConfig('price')['source']->getPreset();
        $priceView = $this->product->getPriceView();
        if ($priceView === null || $priceView == 'Price Range') {
            if (isset($price['price_from']) && isset($price['price_to'])) {
                return [
                    ['price_from' => $price['price_from'], 'price_to' => $price['price_to']],
                    [
                        'price_from' => number_format($priceData['price_from'], 2),
                        'price_to' => number_format($priceData['price_to'], 2)
                    ]
                ];
            }
            return [
                ['price_regular_price' => $price['price_regular_price']],
                ['price_regular_price' => number_format($priceData['price_from'], 2)]
            ];
        } else {
            return [
                ['price_from' => $price['price_regular_price']],
                [
                    'price_from' => is_numeric($priceData['price_from'])
                            ? number_format($priceData['price_from'], 2)
                            : $priceData['price_from']
                ]
            ];
        }
    }
}
