<?php
/**
 * Copyright © Magento, Inc. All rights reserved. 
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data;

use Magento\Framework\Data\Collection\AbstractDb;

/**
 * Represents specific condition, that can be applied on product collection.
 * This condition can be some join statement, some filter, some derived query, etc...
 */
interface CollectionModifierInterface
{
    /**
     * Apply condition to collection
     * Each condition can be represented as collection filter or collection join
     * Each condition will be applied each time in place, where this condition will be called
     *
     * @param AbstractDb $abstractCollection
     * @return void
     */
    public function apply(AbstractDb $abstractCollection);
}
