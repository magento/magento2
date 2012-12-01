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
 * @copyright  Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Abstract API2 class for order items rest
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Sales_Model_Api2_Order_Item_Rest extends Mage_Sales_Model_Api2_Order_Item
{
    /**#@+
     * Parameters in request used in model (usually specified in route)
     */
    const PARAM_ORDER_ID = 'id';
    /**#@-*/

    /**
     * Get order items list
     *
     * @return array
     */
    protected function _retrieveCollection()
    {
        $data = array();
        /* @var $item Mage_Sales_Model_Order_Item */
        foreach ($this->_getCollectionForRetrieve() as $item) {
            $itemData = $item->getData();
            $itemData['status'] = $item->getStatus();
            $data[] = $itemData;
        }
        return $data;
    }
    /**
     * Retrieve order items collection
     *
     * @return Mage_Sales_Model_Resource_Order_Item_Collection
     */
    protected function _getCollectionForRetrieve()
    {
        /* @var $order Mage_Sales_Model_Order */
        $order = $this->_loadOrderById(
            $this->getRequest()->getParam(self::PARAM_ORDER_ID)
        );

        /* @var $collection Mage_Sales_Model_Resource_Order_Item_Collection */
        $collection = Mage::getResourceModel('Mage_Sales_Model_Resource_Order_Item_Collection');
        $collection->setOrderFilter($order->getId());
        $this->_applyCollectionModifiers($collection);
        return $collection;
    }

    /**
     * Load order by id
     *
     * @param int $id
     * @throws Mage_Api2_Exception
     * @return Mage_Sales_Model_Order
     */
    protected function _loadOrderById($id)
    {
        /* @var $order Mage_Sales_Model_Order */
        $order = Mage::getModel('Mage_Sales_Model_Order')->load($id);
        if (!$order->getId()) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        return $order;
    }
}
