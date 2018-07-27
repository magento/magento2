<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\ResourceModel\Billing;

/**
 * Billing agreement resource model
 */
class Agreement extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
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
        $this->getConnection()->insert(
            $this->getTable('paypal_billing_agreement_order'),
            ['agreement_id' => $agreementId, 'order_id' => $orderId]
        );
        return $this;
    }

    /**
     * Add billing agreement filter on orders collection
     *
     * @param \Magento\Framework\Data\Collection\AbstractDb $orderCollection
     * @param string|int|array $agreementIds
     * @return $this
     */
    public function addOrdersFilter(\Magento\Framework\Data\Collection\AbstractDb $orderCollection, $agreementIds)
    {
        $agreementIds = is_array($agreementIds) ? $agreementIds : [$agreementIds];
        $orderIds = $this->getConnection()->fetchCol(
            $this->getConnection()->select()
                ->from(['pbao' => $this->getTable('paypal_billing_agreement_order')], ['order_id'])
                ->where(
                    'pbao.agreement_id IN(?)',
                    $agreementIds
                )
        );
        $orderCollection->getSelect()
            ->where('main_table.entity_id IN (?)', $orderIds);
        return $this;
    }
}
