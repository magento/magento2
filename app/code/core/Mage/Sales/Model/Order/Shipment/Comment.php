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
 * Enter description here ...
 *
 * @method Mage_Sales_Model_Resource_Order_Shipment_Comment _getResource()
 * @method Mage_Sales_Model_Resource_Order_Shipment_Comment getResource()
 * @method int getParentId()
 * @method Mage_Sales_Model_Order_Shipment_Comment setParentId(int $value)
 * @method int getIsCustomerNotified()
 * @method Mage_Sales_Model_Order_Shipment_Comment setIsCustomerNotified(int $value)
 * @method int getIsVisibleOnFront()
 * @method Mage_Sales_Model_Order_Shipment_Comment setIsVisibleOnFront(int $value)
 * @method string getComment()
 * @method Mage_Sales_Model_Order_Shipment_Comment setComment(string $value)
 * @method string getCreatedAt()
 * @method Mage_Sales_Model_Order_Shipment_Comment setCreatedAt(string $value)
 *
 * @category    Mage
 * @package     Mage_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Model_Order_Shipment_Comment extends Mage_Sales_Model_Abstract
{
    /**
     * Shipment instance
     *
     * @var Mage_Sales_Model_Order_Shipment
     */
    protected $_shipment;

    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('Mage_Sales_Model_Resource_Order_Shipment_Comment');
    }

    /**
     * Declare Shipment instance
     *
     * @param   Mage_Sales_Model_Order_Shipment $shipment
     * @return  Mage_Sales_Model_Order_Shipment_Comment
     */
    public function setShipment(Mage_Sales_Model_Order_Shipment $shipment)
    {
        $this->_shipment = $shipment;
        return $this;
    }

    /**
     * Retrieve Shipment instance
     *
     * @return Mage_Sales_Model_Order_Shipment
     */
    public function getShipment()
    {
        return $this->_shipment;
    }

    /**
     * Get store object
     *
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        if ($this->getShipment()) {
            return $this->getShipment()->getStore();
        }
        return Mage::app()->getStore();
    }

    /**
     * Before object save
     *
     * @return Mage_Sales_Model_Order_Shipment_Comment
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();

        if (!$this->getParentId() && $this->getShipment()) {
            $this->setParentId($this->getShipment()->getId());
        }

        return $this;
    }
}
