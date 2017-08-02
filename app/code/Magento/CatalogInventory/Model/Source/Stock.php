<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * CatalogInventory Stock source model
 * @api
 * @since 2.0.0
 */
class Stock extends AbstractSource
{
    /**
     * Retrieve option array
     *
     * @return array
     * @since 2.0.0
     */
    public function getAllOptions()
    {
        return [
            ['value' => \Magento\CatalogInventory\Model\Stock::STOCK_IN_STOCK, 'label' => __('In Stock')],
            ['value' => \Magento\CatalogInventory\Model\Stock::STOCK_OUT_OF_STOCK, 'label' => __('Out of Stock')]
        ];
    }
}
