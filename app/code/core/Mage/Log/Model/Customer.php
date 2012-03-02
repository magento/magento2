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
 * @package     Mage_Log
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer log model
 *
 * @method Mage_Log_Model_Resource_Customer _getResource()
 * @method Mage_Log_Model_Resource_Customer getResource()
 * @method int getVisitorId()
 * @method Mage_Log_Model_Customer setVisitorId(int $value)
 * @method int getCustomerId()
 * @method Mage_Log_Model_Customer setCustomerId(int $value)
 * @method string getLoginAt()
 * @method Mage_Log_Model_Customer setLoginAt(string $value)
 * @method string getLogoutAt()
 * @method Mage_Log_Model_Customer setLogoutAt(string $value)
 * @method int getStoreId()
 * @method Mage_Log_Model_Customer setStoreId(int $value)
 *
 * @category    Mage
 * @package     Mage_Log
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Log_Model_Customer extends Mage_Core_Model_Abstract
{
    /**
     * Define resource model
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Mage_Log_Model_Resource_Customer');
    }

    /**
     * Load last log by customer id
     *
     * @param Mage_Customer_Model_Customer|int $customer
     * @return Mage_Log_Model_Customer
     */
    public function loadByCustomer($customer)
    {
        if ($customer instanceof Mage_Customer_Model_Customer) {
            $customer = $customer->getId();
        }

        return $this->load($customer, 'customer_id');
    }

    /**
     * Return last login at in Unix time format
     *
     * @return int
     */
    public function getLoginAtTimestamp()
    {
        $loginAt = $this->getLoginAt();
        if ($loginAt) {
            return Varien_Date::toTimestamp($loginAt);
        }

        return null;
    }
}
