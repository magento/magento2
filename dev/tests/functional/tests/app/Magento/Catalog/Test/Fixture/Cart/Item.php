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
 
namespace Magento\Catalog\Test\Fixture\Cart;

use Magento\Bundle\Test\Fixture\BundleProduct;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Mtf\Fixture\FixtureInterface;

/**
 * Class Item
 * Data for verify cart item block on checkout page
 *
 * Data keys:
 *  - product (fixture data for verify)
 *
 * @SuppressWarnings(PHPMD.NPathComplexity)
 */
class Item implements FixtureInterface
{
    /**
     * Prepared dataSet data
     *
     * @var array
     */
    protected $data = [];

    /**
     * @constructor
     * @param FixtureInterface $product
     */
    public function __construct(FixtureInterface $product)
    {
        /** @var CatalogProductSimple $product */
        $checkoutData = $product->getCheckoutData();
        $cartItem = isset($checkoutData['cartItem']) ? $checkoutData['cartItem'] : [];
        $customOptions = $product->hasData('custom_options') ? $product->getCustomOptions() : [];
        $checkoutCustomOptions = isset($checkoutData['options']['custom_options'])
            ? $checkoutData['options']['custom_options']
            : [];

        foreach ($checkoutCustomOptions as $key => $checkoutCustomOption) {
            $attribute = str_replace('attribute_key_', '', $checkoutCustomOption['title']);
            $option = str_replace('option_key_', '', $checkoutCustomOption['value']);

            $checkoutCustomOptions[$key] = [
                'title' => isset($customOptions[$attribute]['title'])
                    ? $customOptions[$attribute]['title']
                    : $attribute,
                'value' => isset($customOptions[$attribute]['options'][$option]['title'])
                    ? $customOptions[$attribute]['options'][$option]['title']
                    : $option
            ];
        }

        $cartItem['options'] = isset($cartItem['options'])
            ? $cartItem['options'] + $checkoutCustomOptions
            : $checkoutCustomOptions;
        $cartItem['qty'] = isset($checkoutData['options']['qty'])
                ? $checkoutData['options']['qty']
                : 1;

        $this->data = $cartItem;
    }

    /**
     * Persist fixture
     *
     * @return void
     */
    public function persist()
    {
        //
    }

    /**
     * Return prepared data set
     *
     * @param string $key [optional]
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getData($key = null)
    {
        return $this->data;
    }

    /**
     * Return data set configuration settings
     *
     * @return string
     */
    public function getDataConfig()
    {
        //
    }
}
