<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Ui\DataProvider\Product;

use Magento\Framework\Data\Collection;
use Magento\Ui\DataProvider\AddFieldToCollectionInterface;

/**
 * Add is_in_stock field to collection
 */
class AddIsInStockFieldToCollection implements AddFieldToCollectionInterface
{
    /**
     * @inheritdoc
     */
    public function addField(Collection $collection, $field, $alias = null)
    {
        $collection->joinField(
            'quantity_and_stock_status',
            'cataloginventory_stock_item',
            'is_in_stock',
            'product_id=entity_id',
            '{{table}}.stock_id=1',
            'left'
        );
    }
}
