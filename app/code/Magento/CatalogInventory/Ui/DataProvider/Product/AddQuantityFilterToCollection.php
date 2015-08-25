<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Ui\DataProvider\Product;

use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\Data\Collection;
use Magento\Ui\DataProvider\AddFilterToCollectionInterface;

/**
 * Class AddQuantityFilterToCollection
 */
class AddQuantityFilterToCollection implements AddFilterToCollectionInterface
{
    /**
     * {@inheritdoc}
     */
    public function addFilter(Collection $collection, $field, $condition = null)
    {
        if (isset($condition['from'])) {
            $collection->getSelect()->where(
                AbstractCollection::ATTRIBUTE_TABLE_ALIAS_PREFIX . 'qty.qty >= ?',
                (float)$condition['from']
            );
        }
        if ($condition['to']) {
            $collection->getSelect()->where(
                AbstractCollection::ATTRIBUTE_TABLE_ALIAS_PREFIX . 'qty.qty <= ?',
                (float)$condition['to']
            );
        }
    }
}
