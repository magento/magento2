<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Test\Fixture\DownloadableProduct;

/**
 * Group price options fixture for downloadable product
 */
class GroupPriceOptions extends \Magento\Catalog\Test\Fixture\CatalogProductSimple\GroupPriceOptions
{
    /**
     * Get preset array
     *
     * @param string $name
     * @return mixed|null
     */
    protected function getPreset($name)
    {
        $presets = [
            'default' => [
                [
                    'price' => 20,
                    'website' => 'All Websites [USD]',
                    'customer_group' => 'NOT LOGGED IN',
                ],
            ],
            'downloadable_with_tax' => [
                [
                    'price' => 20.00,
                    'website' => 'All Websites [USD]',
                    'customer_group' => 'General',
                ],
            ],
        ];
        if (!isset($presets[$name])) {
            return null;
        }
        return $presets[$name];
    }
}
