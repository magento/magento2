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
 * @package     Mage_ProductAlert
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * ProductAlert for back in stock model
 *
 * @method Mage_ProductAlert_Model_Resource_Stock _getResource()
 * @method Mage_ProductAlert_Model_Resource_Stock getResource()
 * @method int getCustomerId()
 * @method Mage_ProductAlert_Model_Stock setCustomerId(int $value)
 * @method int getProductId()
 * @method Mage_ProductAlert_Model_Stock setProductId(int $value)
 * @method int getWebsiteId()
 * @method Mage_ProductAlert_Model_Stock setWebsiteId(int $value)
 * @method string getAddDate()
 * @method Mage_ProductAlert_Model_Stock setAddDate(string $value)
 * @method string getSendDate()
 * @method Mage_ProductAlert_Model_Stock setSendDate(string $value)
 * @method int getSendCount()
 * @method Mage_ProductAlert_Model_Stock setSendCount(int $value)
 * @method int getStatus()
 * @method Mage_ProductAlert_Model_Stock setStatus(int $value)
 *
 * @category    Mage
 * @package     Mage_ProductAlert
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_ProductAlert_Model_Stock extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('Mage_ProductAlert_Model_Resource_Stock');
    }

    public function getCustomerCollection()
    {
        return Mage::getResourceModel('Mage_ProductAlert_Model_Resource_Stock_Customer_Collection');
    }

    public function loadByParam()
    {
        if ($this->getProductId() !== null && $this->getCustomerId() !== null && $this->getWebsiteId() !== null) {
            $this->getResource()->loadByParam($this);
        }
        return $this;
    }

    public function deleteCustomer($customerId, $websiteId = 0)
    {
        $this->getResource()->deleteCustomer($this, $customerId, $websiteId);
        return $this;
    }
}
