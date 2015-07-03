<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Resource\Billing;

/**
 * Billing agreement resource model
 */
class Agreement extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('paypal_billing_agreement', 'agreement_id');
    }

    /**
     * Add order relation to billing agreement
     *
     * @param int $agreementId
     * @param int $orderId
     * @return $this
     */
    public function addOrderRelation($agreementId, $orderId)
    {
        $this->_getWriteAdapter()->insert(
            $this->getTable('paypal_billing_agreement_order'),
            ['agreement_id' => $agreementId, 'order_id' => $orderId]
        );
        return $this;
    }

    /**
     * Add billing agreement filter on orders collection
     *
     * @param \Magento\Sales\Model\Resource\Order\Collection $orderCollection
     * @param string|int|array $agreementIds
     * @return $this
     */
    public function addOrdersFilter(\Magento\Sales\Model\Resource\Order\Collection $orderCollection, $agreementIds)
    {
        $agreementIds = is_array($agreementIds) ? $agreementIds : [$agreementIds];
        $orderCollection->getSelect()->joinInner(
            ['pbao' => $this->getTable('paypal_billing_agreement_order')],
            'main_table.entity_id = pbao.order_id',
            []
        )->where(
            'pbao.agreement_id IN(?)',
            $agreementIds
        );
        return $this;
    }
}
