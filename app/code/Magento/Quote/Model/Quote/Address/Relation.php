<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\Address;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationInterface;

/**
 * Class \Magento\Quote\Model\Quote\Address\Relation
 *
 */
class Relation implements RelationInterface
{
    /**
     * Process object relations
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return void
     */
    public function processRelation(\Magento\Framework\Model\AbstractModel $object)
    {
        /**
         * @var $object \Magento\Quote\Model\Quote\Address
         */
        if ($object->itemsCollectionWasSet()) {
            $object->getItemsCollection()->save();
        }
        if ($object->shippingRatesCollectionWasSet()) {
            $object->getShippingRatesCollection()->save();
        }
    }
}
