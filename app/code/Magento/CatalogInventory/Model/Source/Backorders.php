<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Source;

/**
 * Back orders source class
 * @api
 * @since 100.0.2
 *
 * @deprecated CatalogInventory will be replaced by Multi-Source Inventory (MSI)
 *             see https://github.com/magento-engcom/msi/wiki/Technical-Vision.-Catalog-Inventory
 */
class Backorders implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => \Magento\CatalogInventory\Model\Stock::BACKORDERS_NO, 'label' => __('No Backorders')],
            [
                'value' => \Magento\CatalogInventory\Model\Stock::BACKORDERS_YES_NONOTIFY,
                'label' => __('Allow Qty Below 0')
            ],
            [
                'value' => \Magento\CatalogInventory\Model\Stock::BACKORDERS_YES_NOTIFY,
                'label' => __('Allow Qty Below 0 and Notify Customer')
            ]
        ];
    }
}
