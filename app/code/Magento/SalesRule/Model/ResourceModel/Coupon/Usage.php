<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\ResourceModel\Coupon;

use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * SalesRule Model Resource Coupon_Usage
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Usage extends AbstractDb
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('salesrule_coupon_usage', 'coupon_id');
    }

    /**
     * Increment times_used counter
     *
     * @param int $customerId
     * @param mixed $couponId
     * @param bool $increment
     * @return void
     */
    public function updateCustomerCouponTimesUsed($customerId, $couponId, $increment = true): void
    {
        $connection = $this->getConnection();
        $select = $connection->select();
        $select->from(
            $this->getMainTable(),
            ['times_used']
        )->where(
            'coupon_id = :coupon_id'
        )->where(
            'customer_id = :customer_id'
        );

        $timesUsed = $connection->fetchOne($select, [':coupon_id' => $couponId, ':customer_id' => $customerId]);

        if ($timesUsed !== false) {
            $this->getConnection()->update(
                $this->getMainTable(),
                ['times_used' => $timesUsed + ($increment ? 1 : -1)],
                ['coupon_id = ?' => $couponId, 'customer_id = ?' => $customerId]
            );
        } elseif ($increment) {
            $this->getConnection()->insert(
                $this->getMainTable(),
                ['coupon_id' => $couponId, 'customer_id' => $customerId, 'times_used' => 1]
            );
        }
    }

    /**
     * Load an object by customer_id & coupon_id
     *
     * @param DataObject $object
     * @param int $customerId
     * @param mixed $couponId
     * @return $this
     */
    public function loadByCustomerCoupon(DataObject $object, $customerId, $couponId)
    {
        $connection = $this->getConnection();
        if ($connection && $couponId && $customerId) {
            $select = $connection->select()->from(
                $this->getMainTable()
            )->where(
                'customer_id =:customer_id'
            )->where(
                'coupon_id = :coupon_id'
            );
            $data = $connection->fetchRow($select, [':coupon_id' => $couponId, ':customer_id' => $customerId]);
            if ($data) {
                $object->setData($data);
            }
        }
        if ($object instanceof AbstractModel) {
            $this->_afterLoad($object);
        }
        return $this;
    }
}
