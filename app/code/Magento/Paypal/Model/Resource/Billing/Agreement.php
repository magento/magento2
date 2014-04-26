<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
            array('agreement_id' => $agreementId, 'order_id' => $orderId)
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
        $agreementIds = is_array($agreementIds) ? $agreementIds : array($agreementIds);
        $orderCollection->getSelect()->joinInner(
            array('pbao' => $this->getTable('paypal_billing_agreement_order')),
            'main_table.entity_id = pbao.order_id',
            array()
        )->where(
            'pbao.agreement_id IN(?)',
            $agreementIds
        );
        return $this;
    }
}
