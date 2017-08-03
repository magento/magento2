<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationInterface;

/**
 * Class \Magento\Quote\Model\Quote\Relation
 *
 * @since 2.0.0
 */
class Relation implements RelationInterface
{
    /**
     * Process object relations
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return void
     * @since 2.0.0
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
