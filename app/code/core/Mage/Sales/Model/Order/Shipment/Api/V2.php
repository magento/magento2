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
 * Sales order shippment API V2
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Model_Order_Shipment_Api_V2 extends Mage_Sales_Model_Order_Shipment_Api
{
    /**
     * Retrive shipments by filters
     *
     * @param array $filters
     * @return array
     */
    public function items($filters = null)
    {
        //TODO: add full name logic
        $collection = Mage::getResourceModel('Mage_Sales_Model_Resource_Order_Shipment_Collection')
            ->addAttributeToSelect('increment_id')
            ->addAttributeToSelect('created_at')
            ->addAttributeToSelect('total_qty')
            ->joinAttribute('shipping_firstname', 'order_address/firstname', 'shipping_address_id', null, 'left')
            ->joinAttribute('shipping_lastname', 'order_address/lastname', 'shipping_address_id', null, 'left')
            ->joinAttribute('order_increment_id', 'order/increment_id', 'order_id', null, 'left')
            ->joinAttribute('order_created_at', 'order/created_at', 'order_id', null, 'left');

        $preparedFilters = array();
        if (isset($filters->filter)) {
            foreach ($filters->filter as $_filter) {
                $preparedFilters[$_filter->key] = $_filter->value;
            }
        }
        if (isset($filters->complex_filter)) {
            foreach ($filters->complex_filter as $_filter) {
                $_value = $_filter->value;
                $preparedFilters[$_filter->key] = array(
                    $_value->key => $_value->value
                );
            }
        }

        if (!empty($preparedFilters)) {
            try {
                foreach ($preparedFilters as $field => $value) {
                    if (isset($this->_attributesMap['shipment'][$field])) {
                        $field = $this->_attributesMap['shipment'][$field];
                    }

                    $collection->addFieldToFilter($field, $value);
                }
            } catch (Mage_Core_Exception $e) {
                $this->_fault('filters_invalid', $e->getMessage());
            }
        }

        $result = array();

        foreach ($collection as $shipment) {
            $result[] = $this->_getAttributes($shipment, 'shipment');
        }

        return $result;
    }

    protected function _prepareItemQtyData($data)
    {
        $_data = array();
        foreach ($data as $item) {
            if (isset($item->order_item_id) && isset($item->qty)) {
                $_data[$item->order_item_id] = $item->qty;
            }
        }
        return $_data;
    }

    /**
     * Create new shipment for order
     *
     * @param string $orderIncrementId
     * @param array $itemsQty
     * @param string $comment
     * @param booleam $email
     * @param boolean $includeComment
     * @return string
     */
    public function create($orderIncrementId, $itemsQty = array(), $comment = null, $email = false, $includeComment = false)
    {
        $order = Mage::getModel('Mage_Sales_Model_Order')->loadByIncrementId($orderIncrementId);
        $itemsQty = $this->_prepareItemQtyData($itemsQty);
        /**
          * Check order existing
          */
        if (!$order->getId()) {
             $this->_fault('order_not_exists');
        }

        /**
         * Check shipment create availability
         */
        if (!$order->canShip()) {
             $this->_fault('data_invalid', Mage::helper('Mage_Sales_Helper_Data')->__('Cannot do shipment for order.'));
        }

         /* @var $shipment Mage_Sales_Model_Order_Shipment */
        $shipment = $order->prepareShipment($itemsQty);
        if ($shipment) {
            $shipment->register();
            $shipment->addComment($comment, $email && $includeComment);
            if ($email) {
                $shipment->setEmailSent(true);
            }
            $shipment->getOrder()->setIsInProcess(true);
            try {
                $transactionSave = Mage::getModel('Mage_Core_Model_Resource_Transaction')
                    ->addObject($shipment)
                    ->addObject($shipment->getOrder())
                    ->save();
                $shipment->sendEmail($email, ($includeComment ? $comment : ''));
            } catch (Mage_Core_Exception $e) {
                $this->_fault('data_invalid', $e->getMessage());
            }
            return $shipment->getIncrementId();
        }
        return null;
    }

    /**
     * Retrieve allowed shipping carriers for specified order
     *
     * @param string $orderIncrementId
     * @return array
     */
    public function getCarriers($orderIncrementId)
    {
        $order = Mage::getModel('Mage_Sales_Model_Order')->loadByIncrementId($orderIncrementId);

        /**
          * Check order existing
          */
        if (!$order->getId()) {
            $this->_fault('order_not_exists');
        }
        $carriers = array();
        foreach ($this->_getCarriers($order) as $key => $value) {
            $carriers[] = array('key' => $key, 'value' => $value);
        }

        return $carriers;
    }
}
