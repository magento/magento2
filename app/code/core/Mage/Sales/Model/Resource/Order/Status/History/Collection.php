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
 * @category    Mage
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Flat sales order status history collection
 *
 * @category    Mage
 * @package     Mage_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Model_Resource_Order_Status_History_Collection
    extends Mage_Sales_Model_Resource_Order_Collection_Abstract
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix    = 'sales_order_status_history_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject    = 'order_status_history_collection';

    /**
     * Model initialization
     *
     */
    protected function _construct()
    {
        $this->_init('Mage_Sales_Model_Order_Status_History', 'Mage_Sales_Model_Resource_Order_Status_History');
    }

    /**
     * Get history object collection for specified instance (order, shipment, invoice or credit memo)
     * Parameter instance may be one of the following types: Mage_Sales_Model_Order,
     * Mage_Sales_Model_Order_Creditmemo, Mage_Sales_Model_Order_Invoice, Mage_Sales_Model_Order_Shipment
     *
     * @param mixed $instance
     * @param string $historyEntityName
     *
     * @return Mage_Sales_Model_Order_Status_History|null
     */
    public function getUnnotifiedForInstance($instance, $historyEntityName=Mage_Sales_Model_Order::HISTORY_ENTITY_NAME)
    {
        if(!$instance instanceof Mage_Sales_Model_Order) {
            $instance = $instance->getOrder();
        }
        $this->setOrderFilter($instance)->setOrder('created_at', 'desc')
            ->addFieldToFilter('entity_name', $historyEntityName)
            ->addFieldToFilter('is_customer_notified', 0)->setPageSize(1);
        foreach($this as $historyItem) {
            return $historyItem;
        }
        return null;
    }

}
