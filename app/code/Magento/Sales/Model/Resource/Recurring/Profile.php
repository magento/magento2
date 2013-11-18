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
 * @category    Magento
 * @package     Magento_Sales
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Recurring payment profiles resource model
 *
 * @category    Magento
 * @package     Magento_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Sales\Model\Resource\Recurring;

class Profile extends \Magento\Sales\Model\Resource\AbstractResource
{
    /**
     * Initialize main table and column
     *
     */
    protected function _construct()
    {
        $this->_init('sales_recurring_profile', 'profile_id');

        $this->_serializableFields = array(
            'profile_vendor_info'    => array(null, array()),
            'additional_info' => array(null, array()),

            'order_info' => array(null, array()),
            'order_item_info' => array(null, array()),
            'billing_address_info' => array(null, array()),
            'shipping_address_info' => array(null, array())
        );
    }

    /**
     * Return recurring profile child Orders Ids
     *
     *
     * @param \Magento\Object $object
     * @return array
     */
    public function getChildOrderIds($object)
    {
        $adapter = $this->_getReadAdapter();
        $bind    = array(':profile_id' => $object->getId());
        $select  = $adapter->select()
            ->from(
                array('main_table' => $this->getTable('sales_recurring_profile_order')),
                array('order_id'))
            ->where('profile_id=:profile_id');

        return $adapter->fetchCol($select, $bind);
    }

    /**
     * Add order relation to recurring profile
     *
     * @param int $recurringProfileId
     * @param int $orderId
     * @return \Magento\Sales\Model\Resource\Recurring\Profile
     */
    public function addOrderRelation($recurringProfileId, $orderId)
    {
        $this->_getWriteAdapter()->insert(
            $this->getTable('sales_recurring_profile_order'), array(
                'profile_id' => $recurringProfileId,
                'order_id'   => $orderId
            )
        );
        return $this;
    }
}
