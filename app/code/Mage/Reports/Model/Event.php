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
 * @package     Mage_Reports
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Events model
 *
 * @method Mage_Reports_Model_Resource_Event _getResource()
 * @method Mage_Reports_Model_Resource_Event getResource()
 * @method string getLoggedAt()
 * @method Mage_Reports_Model_Event setLoggedAt(string $value)
 * @method int getEventTypeId()
 * @method Mage_Reports_Model_Event setEventTypeId(int $value)
 * @method int getObjectId()
 * @method Mage_Reports_Model_Event setObjectId(int $value)
 * @method int getSubjectId()
 * @method Mage_Reports_Model_Event setSubjectId(int $value)
 * @method int getSubtype()
 * @method Mage_Reports_Model_Event setSubtype(int $value)
 * @method int getStoreId()
 * @method Mage_Reports_Model_Event setStoreId(int $value)
 *
 * @category    Mage
 * @package     Mage_Reports
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Reports_Model_Event extends Mage_Core_Model_Abstract
{
    const EVENT_PRODUCT_VIEW    = 1;
    const EVENT_PRODUCT_SEND    = 2;
    const EVENT_PRODUCT_COMPARE = 3;
    const EVENT_PRODUCT_TO_CART = 4;
    const EVENT_PRODUCT_TO_WISHLIST = 5;
    const EVENT_WISHLIST_SHARE  = 6;

    /**
     * Initialize resource
     *
     */
    protected function _construct()
    {
        $this->_init('Mage_Reports_Model_Resource_Event');
    }

    /**
     * Before Event save process
     *
     * @return Mage_Reports_Model_Event
     */
    protected function _beforeSave()
    {
        $this->setLoggedAt(Mage::getModel('Mage_Core_Model_Date')->gmtDate());
        return parent::_beforeSave();
    }

    /**
     * Update customer type after customer login
     *
     * @param int $visitorId
     * @param int $customerId
     * @param array $types
     * @return Mage_Reports_Model_Event
     */
    public function updateCustomerType($visitorId, $customerId, $types = null)
    {
        if (is_null($types)) {
            $types = array();
            foreach (Mage::getModel('Mage_Reports_Model_Event_Type')->getCollection() as $eventType) {
                if ($eventType->getCustomerLogin()) {
                    $types[$eventType->getId()] = $eventType->getId();
                }
            }
        }
        $this->getResource()->updateCustomerType($this, $visitorId, $customerId, $types);
        return $this;
    }

    /**
     * Clean events (visitors)
     *
     * @return Mage_Reports_Model_Event
     */
    public function clean()
    {
        $this->getResource()->clean($this);
        return $this;
    }
}
