<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\Downloadable\Test\Fixture\DownloadableProductInjectable;

/**
 * Class GroupPriceOptions
 *
 * Data keys:
 *  - preset (Price options preset name)
 *  - products (comma separated sku identifiers)
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
