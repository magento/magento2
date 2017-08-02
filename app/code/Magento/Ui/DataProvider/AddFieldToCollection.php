<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\DataProvider;

use Magento\Framework\Data\Collection;

/**
 * Class AddFieldToCollection
 * @since 2.0.0
 */
class AddFieldToCollection implements AddFieldToCollectionInterface
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function addField(Collection $collection, $field, $alias = null)
    {
        $collection->addFieldToSelect($field, $alias);
    }
}
