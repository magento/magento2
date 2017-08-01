<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Ui\DataProvider\Product;

use Magento\Framework\Data\Collection;
use Magento\Ui\DataProvider\AddFieldToCollectionInterface;

/**
 * Class AddQuantityFieldToCollection
 * @since 2.0.0
 */
class AddQuantityFieldToCollection implements AddFieldToCollectionInterface
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function addField(Collection $collection, $field, $alias = null)
    {
        $collection->joinField(
            'qty',
            'cataloginventory_stock_item',
            'qty',
            'product_id=entity_id',
            '{{table}}.stock_id=1',
            'left'
        );
    }
}
