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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Flat sales order address resource
 *
 * @category    Mage
 * @package     Mage_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Model_Resource_Order_Address extends Mage_Sales_Model_Resource_Order_Abstract
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix    = 'sales_order_address_resource';

    /**
     * Resource initialization
     *
     */
    protected function _construct()
    {
        $this->_init('sales_flat_order_address', 'entity_id');
    }

    /**
     * Return configuration for all attributes
     *
     * @return array
     */
    public function getAllAttributes()
    {
        $attributes = array(
            'city'       => Mage::helper('Mage_Sales_Helper_Data')->__('City'),
            'company'    => Mage::helper('Mage_Sales_Helper_Data')->__('Company'),
            'country_id' => Mage::helper('Mage_Sales_Helper_Data')->__('Country'),
            'email'      => Mage::helper('Mage_Sales_Helper_Data')->__('Email'),
            'firstname'  => Mage::helper('Mage_Sales_Helper_Data')->__('First Name'),
            'lastname'   => Mage::helper('Mage_Sales_Helper_Data')->__('Last Name'),
            'region_id'  => Mage::helper('Mage_Sales_Helper_Data')->__('State/Province'),
            'street'     => Mage::helper('Mage_Sales_Helper_Data')->__('Street Address'),
            'telephone'  => Mage::helper('Mage_Sales_Helper_Data')->__('Telephone'),
            'postcode'   => Mage::helper('Mage_Sales_Helper_Data')->__('Zip/Postal Code')
        );
        asort($attributes);
        return $attributes;
    }

    /**
     * Update related grid table after object save
     *
     * @param Varien_Object $object
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $resource = parent::_afterSave($object);
        if ($object->hasDataChanges() && $object->getOrder()) {
            $gridList = array(
                'Mage_Sales_Model_Resource_Order' => 'entity_id',
                'Mage_Sales_Model_Resource_Order_Invoice' => 'order_id',
                'Mage_Sales_Model_Resource_Order_Shipment' => 'order_id',
                'Mage_Sales_Model_Resource_Order_Creditmemo' => 'order_id'
            );

            // update grid table after grid update
            foreach ($gridList as $gridResource => $field) {
                Mage::getResourceModel($gridResource)->updateOnRelatedRecordChanged(
                    $field,
                    $object->getParentId()
                );
            }
        }

        return $resource;
    }
}
