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
 * @package     Mage_Customer
 * @copyright  Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * API2 class for customer address
 *
 * @category   Mage
 * @package    Mage_Customer
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Customer_Model_Api2_Customer_Address extends Mage_Api2_Model_Resource
{
    /**
     * Resource specific method to retrieve attributes' codes. May be overriden in child.
     *
     * @return array
     */
    protected function _getResourceAttributes()
    {
        return $this->getEavAttributes(Mage_Api2_Model_Auth_User_Admin::USER_TYPE != $this->getUserType());
    }

    /**
     * Get customer address resource validator instance
     *
     * @return Mage_Customer_Model_Api2_Customer_Address_Validator
     */
    protected function _getValidator()
    {
        return Mage::getModel('Mage_Customer_Model_Api2_Customer_Address_Validator',
            array('options' => array('resource' => $this)));
    }

    /**
     * Is specified address a default billing address?
     *
     * @param Mage_Customer_Model_Address $address
     * @return bool
     */
    protected function _isDefaultBillingAddress(Mage_Customer_Model_Address $address)
    {
        return $address->getCustomer()->getDefaultBilling() == $address->getId();
    }

    /**
     * Is specified address a default shipping address?
     *
     * @param Mage_Customer_Model_Address $address
     * @return bool
     */
    protected function _isDefaultShippingAddress(Mage_Customer_Model_Address $address)
    {
        return $address->getCustomer()->getDefaultShipping() == $address->getId();
    }

    /**
     * Get region id by name or code
     * If id is not found then return passed $region
     *
     * @param string $region
     * @param string $countryId
     * @return int|string
     */
    protected function _getRegionIdByNameOrCode($region, $countryId)
    {
        /** @var $collection Mage_Directory_Model_Resource_Region_Collection */
        $collection = Mage::getResourceModel('Mage_Directory_Model_Resource_Region_Collection');

        $collection->getSelect()
            ->reset() // to avoid locale usage
            ->from(array('main_table' => $collection->getMainTable()), 'region_id');

        $collection->addCountryFilter($countryId)
            ->addFieldToFilter(array('default_name', 'code'), array($region, $region));

        $id = $collection->getResource()->getReadConnection()->fetchOne($collection->getSelect());

        return $id ? (int)$id : $region;
    }

    /**
     * Load customer address by id
     *
     * @param int $id
     * @return Mage_Customer_Model_Address
     */
    protected function _loadCustomerAddressById($id)
    {
        /* @var $address Mage_Customer_Model_Address */
        $address = Mage::getModel('Mage_Customer_Model_Address')->load($id);

        if (!$address->getId()) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        $address->addData($this->_getDefaultAddressesInfo($address));

        return $address;
    }

    /**
     * Load customer by id
     *
     * @param int $id
     * @throws Mage_Api2_Exception
     * @return Mage_Customer_Model_Customer
     */
    protected function _loadCustomerById($id)
    {
        /* @var $customer Mage_Customer_Model_Customer */
        $customer = Mage::getModel('Mage_Customer_Model_Customer')->load($id);
        if (!$customer->getId()) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        return $customer;
    }
}
