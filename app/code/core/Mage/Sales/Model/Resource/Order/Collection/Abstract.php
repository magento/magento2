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
 * Flat sales order collection
 *
 * @category    Mage
 * @package     Mage_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Sales_Model_Resource_Order_Collection_Abstract extends Mage_Sales_Model_Resource_Collection_Abstract
{
    /**
     * Order object
     *
     * @var Mage_Sales_Model_Order
     */
    protected $_salesOrder   = null;

    /**
     * Order field for setOrderFilter
     *
     * @var string
     */
    protected $_orderField   = 'parent_id';

    /**
     * Set sales order model as parent collection object
     *
     * @param Mage_Sales_Model_Order $order
     * @return Mage_Sales_Model_Resource_Order_Collection_Abstract
     */
    public function setSalesOrder($order)
    {
        $this->_salesOrder = $order;
        if ($this->_eventPrefix && $this->_eventObject) {
            Mage::dispatchEvent($this->_eventPrefix . '_set_sales_order', array(
                'collection' => $this,
                $this->_eventObject => $this,
                'order' => $order
            ));
        }

        return $this;
    }

    /**
     * Retrieve sales order as parent collection object
     *
     * @return Mage_Sales_Model_Order|null
     */
    public function getSalesOrder()
    {
        return $this->_salesOrder;
    }

    /**
     * Add order filter
     *
     * @param int|Mage_Sales_Model_Order $order
     * @return Mage_Sales_Model_Resource_Order_Collection_Abstract
     */
    public function setOrderFilter($order)
    {
        if ($order instanceof Mage_Sales_Model_Order) {
            $this->setSalesOrder($order);
            $orderId = $order->getId();
            if ($orderId) {
                $this->addFieldToFilter($this->_orderField, $orderId);
            } else {
                $this->_totalRecords = 0;
                $this->_setIsLoaded(true);
            }
        } else {
            $this->addFieldToFilter($this->_orderField, $order);
        }
        return $this;
    }
}
