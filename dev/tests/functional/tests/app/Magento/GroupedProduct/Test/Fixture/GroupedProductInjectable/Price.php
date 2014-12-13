<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\GroupedProduct\Test\Fixture\GroupedProductInjectable;

use Magento\Catalog\Test\Fixture\CatalogProductSimple\Price as ParentPrice;
use Mtf\Fixture\FixtureInterface;

/**
 * Class Price
 *
 * Data keys:
 *  - preset (Price verification preset name)
 *  - value (Price value)
 */
class Price extends ParentPrice implements FixtureInterface
{
    /**
     * Preset for price
     *
     * @return array|null
     */
    public function getPreset()
    {
        $presets = [
            'starting-560' => [
                'compare_price' => [
                    'price_starting' => '560.00',
                ],
            ],
        ];
        if (!isset($presets[$this->currentPreset])) {
            return null;
        }
        return $presets[$this->currentPreset];
    }
}
