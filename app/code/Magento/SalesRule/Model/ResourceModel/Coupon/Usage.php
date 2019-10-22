<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\ResourceModel\Coupon;

use Magento\Framework\Exception\LocalizedException;
use Magento\SalesRule\Exception\CouponUsageExceeded;
use Magento\SalesRule\Model\Coupon;

/**
 * SalesRule Model Resource Coupon_Usage
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Usage extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
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
     * @param int $couponId
     * @param int $customerId
     * @return int
     * @throws LocalizedException
     */
    public function getUsagePerCustomer(int $couponId, int $customerId): int {
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

        return (int)$connection->fetchOne($select, [':coupon_id' => $couponId, ':customer_id' => $customerId]);
    }

    /**
     * Increment times_used counter
     *
     * @param mixed $couponId
     * @param int $customerId
     * @param int $timesUsed
     * @return void
     * @throws LocalizedException
     */
    public function updateUsagePerCustomer(int $couponId, int $customerId, int $timesUsed): void
    {
        $this->getConnection()->insertOnDuplicate(
            $this->getMainTable(),
            [
                'coupon_id' => $couponId,
                'customer_id' => $customerId,
                'times_used' => $timesUsed
            ],
            ['times_used']
        );
    }

    /**
     * @deprecated
     * Increment times_used counter
     *
     * @param int $customerId
     * @param mixed $couponId
     * @param bool $increment
     * @return void
     * @throws LocalizedException
     */
    public function updateCustomerCouponTimesUsed($customerId, $couponId, $increment = true): void
    {
        $timesUsed = $this->getUsagePerCustomer($couponId, $customerId);
        if ($timesUsed === 0 && $increment) {
            $timesUsed = 1;
        }
        if ($timesUsed) {
            $this->updateUsagePerCustomer($couponId, $customerId, $timesUsed);
        }
    }

    /**
     * Load an object by customer_id & coupon_id
     *
     * @param \Magento\Framework\DataObject $object
     * @param int $customerId
     * @param mixed $couponId
     * @return $this
     * @throws LocalizedException
     */
    public function loadByCustomerCoupon(\Magento\Framework\DataObject $object, $customerId, $couponId)
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
        if ($object instanceof \Magento\Framework\Model\AbstractModel) {
            $this->_afterLoad($object);
        }
        return $this;
    }
}
