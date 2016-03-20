<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationInterface;

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
         * @var $object \Magento\Quote\Model\Quote
         */
        if ($object->addressCollectionWasSet()) {
            $object->getAddressesCollection()->save();
        }
        if ($object->itemsCollectionWasSet()) {
            $object->getItemsCollection()->save();
        }
        if ($object->paymentsCollectionWasSet()) {
            $object->getPaymentsCollection()->save();
        }
        if ($object->currentPaymentWasSet()) {
            $object->getPayment()->save();
        }
    }
}
