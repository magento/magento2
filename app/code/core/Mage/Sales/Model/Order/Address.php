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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Sales order address model
 *
 * @method Mage_Sales_Model_Resource_Order_Address _getResource()
 * @method Mage_Sales_Model_Resource_Order_Address getResource()
 * @method int getParentId()
 * @method Mage_Sales_Model_Order_Address setParentId(int $value)
 * @method int getCustomerAddressId()
 * @method Mage_Sales_Model_Order_Address setCustomerAddressId(int $value)
 * @method int getQuoteAddressId()
 * @method Mage_Sales_Model_Order_Address setQuoteAddressId(int $value)
 * @method Mage_Sales_Model_Order_Address setRegionId(int $value)
 * @method int getCustomerId()
 * @method Mage_Sales_Model_Order_Address setCustomerId(int $value)
 * @method string getFax()
 * @method Mage_Sales_Model_Order_Address setFax(string $value)
 * @method Mage_Sales_Model_Order_Address setRegion(string $value)
 * @method string getPostcode()
 * @method Mage_Sales_Model_Order_Address setPostcode(string $value)
 * @method string getLastname()
 * @method Mage_Sales_Model_Order_Address setLastname(string $value)
 * @method string getCity()
 * @method Mage_Sales_Model_Order_Address setCity(string $value)
 * @method string getEmail()
 * @method Mage_Sales_Model_Order_Address setEmail(string $value)
 * @method string getTelephone()
 * @method Mage_Sales_Model_Order_Address setTelephone(string $value)
 * @method string getCountryId()
 * @method Mage_Sales_Model_Order_Address setCountryId(string $value)
 * @method string getFirstname()
 * @method Mage_Sales_Model_Order_Address setFirstname(string $value)
 * @method string getAddressType()
 * @method Mage_Sales_Model_Order_Address setAddressType(string $value)
 * @method string getPrefix()
 * @method Mage_Sales_Model_Order_Address setPrefix(string $value)
 * @method string getMiddlename()
 * @method Mage_Sales_Model_Order_Address setMiddlename(string $value)
 * @method string getSuffix()
 * @method Mage_Sales_Model_Order_Address setSuffix(string $value)
 * @method string getCompany()
 * @method Mage_Sales_Model_Order_Address setCompany(string $value)
 *
 * @category    Mage
 * @package     Mage_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Model_Order_Address extends Mage_Customer_Model_Address_Abstract
{
    protected $_order;

    protected $_eventPrefix = 'sales_order_address';
    protected $_eventObject = 'address';

    /**
     * Initialize resource
     */
    protected function _construct()
    {
        $this->_init('Mage_Sales_Model_Resource_Order_Address');
    }

    /**
     * Set order
     *
     * @return Mage_Sales_Model_Order_Address
     */
    public function setOrder(Mage_Sales_Model_Order $order)
    {
        $this->_order = $order;
        return $this;
    }

    /**
     * Get order
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if (!$this->_order) {
            $this->_order = Mage::getModel('Mage_Sales_Model_Order')->load($this->getParentId());
        }
        return $this->_order;
    }

    /**
     * Before object save manipulations
     *
     * @return Mage_Sales_Model_Order_Address
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();

        if (!$this->getParentId() && $this->getOrder()) {
            $this->setParentId($this->getOrder()->getId());
        }

        // Init customer address id if customer address is assigned
        if ($this->getCustomerAddress()) {
            $this->setCustomerAddressId($this->getCustomerAddress()->getId());
        }

        return $this;
    }
}
