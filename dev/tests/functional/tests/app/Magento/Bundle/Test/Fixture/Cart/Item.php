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

namespace Magento\Bundle\Test\Fixture\Cart;

use Mtf\Fixture\FixtureInterface;
use Magento\Bundle\Test\Fixture\BundleProduct;

/**
 * Class Item
 * Data for verify cart item block on checkout page
 *
 * Data keys:
 *  - product (fixture data for verify)
 */
class Item extends \Magento\Catalog\Test\Fixture\Cart\Item
{
    /**
     * @constructor
     * @param FixtureInterface $product
     */
    public function __construct(FixtureInterface $product)
    {
        parent::__construct($product);

        /** @var BundleProduct $product */
        $bundleSelection = $product->getBundleSelections();
        $checkoutData = $product->getCheckoutData();
        $checkoutBundleOptions = isset($checkoutData['options']['bundle_options'])
            ? $checkoutData['options']['bundle_options']
            : [];

        foreach ($checkoutBundleOptions as $checkoutOptionKey => $checkoutOption) {
            // Find option and value keys
            $attributeKey = null;
            $optionKey = null;
            foreach ($bundleSelection['bundle_options'] as $key => $option) {
                if ($option['title'] == $checkoutOption['title']) {
                    $attributeKey = $key;

                    foreach ($option['assigned_products'] as $valueKey => $value) {
                        if (false !== strpos($value['search_data']['name'], $checkoutOption['value']['name'])) {
                            $optionKey = $valueKey;
                        }
                    }
                }
            }

            // Prepare option data
            $bundleSelectionAttribute = $bundleSelection['products'][$attributeKey];
            $bundleOptions = $bundleSelection['bundle_options'][$attributeKey];
            $value = $bundleSelectionAttribute[$optionKey]->getName();
            $qty = $bundleOptions['assigned_products'][$optionKey]['data']['selection_qty'];
            $price = number_format($bundleSelectionAttribute[$optionKey]->getPrice(), 2);
            $optionData = [
                'title' => $checkoutOption['title'],
                'value' => "{$qty} x {$value} {$price}"
            ];

            $checkoutBundleOptions[$checkoutOptionKey] = $optionData;
        }

        $this->data['options'] += $checkoutBundleOptions;
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
