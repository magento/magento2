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
namespace Magento\SalesRule\Model\Resource\Coupon;

/**
 * SalesRule Model Resource Coupon_Usage
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Usage extends \Magento\Framework\Model\Resource\Db\AbstractDb
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
     * @return void
     */
    public function updateCustomerCouponTimesUsed($customerId, $couponId)
    {
        $read = $this->_getReadAdapter();
        $select = $read->select();
        $select->from(
            $this->getMainTable(),
            array('times_used')
        )->where(
            'coupon_id = :coupon_id'
        )->where(
            'customer_id = :customer_id'
        );

        $timesUsed = $read->fetchOne($select, array(':coupon_id' => $couponId, ':customer_id' => $customerId));

        if ($timesUsed > 0) {
            $this->_getWriteAdapter()->update(
                $this->getMainTable(),
                array('times_used' => $timesUsed + 1),
                array('coupon_id = ?' => $couponId, 'customer_id = ?' => $customerId)
            );
        } else {
            $this->_getWriteAdapter()->insert(
                $this->getMainTable(),
                array('coupon_id' => $couponId, 'customer_id' => $customerId, 'times_used' => 1)
            );
        }
    }

    /**
     * Load an object by customer_id & coupon_id
     *
     * @param \Magento\Framework\Object $object
     * @param int $customerId
     * @param mixed $couponId
     * @return $this
     */
    public function loadByCustomerCoupon(\Magento\Framework\Object $object, $customerId, $couponId)
    {
        $read = $this->_getReadAdapter();
        if ($read && $couponId && $customerId) {
            $select = $read->select()->from(
                $this->getMainTable()
            )->where(
                'customer_id =:customet_id'
            )->where(
                'coupon_id = :coupon_id'
            );
            $data = $read->fetchRow($select, array(':coupon_id' => $couponId, ':customet_id' => $customerId));
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
