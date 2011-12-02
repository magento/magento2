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
 * @package     Mage_GoogleCheckout
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Google Checkout notification model
 *
 * @method Mage_GoogleCheckout_Model_Resource_Notification _getResource()
 * @method Mage_GoogleCheckout_Model_Resource_Notification getResource()
 * @method string getSerialNumber()
 * @method Mage_GoogleCheckout_Model_Notification setSerialNumber(string $value)
 * @method string getStartedAt()
 * @method Mage_GoogleCheckout_Model_Notification setStartedAt(string $value)
 * @method int getStatus()
 * @method Mage_GoogleCheckout_Model_Notification setStatus(int $value)
 *
 * @category    Mage
 * @package     Mage_GoogleCheckout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_GoogleCheckout_Model_Notification extends Mage_Core_Model_Abstract
{
    const TIMEOUT_LIMIT = 3600;
    const STATUS_INPROCESS = 0;
    const STATUS_PROCESSED = 1;

    /**
     * Intialize model
     */
    function _construct()
    {
        $this->_init('Mage_GoogleCheckout_Model_Resource_Notification');
    }

    /**
     * Assign previously saved notification data to model
     *
     * @return Mage_GoogleCheckout_Model_Notification
     */
    public function loadNotificationData()
    {
        $data = $this->getResource()->getNotificationData($this->getSerialNumber());
        if (is_array($data)) {
            $this->addData($data);
        }
        return $this;
    }

    /**
     * Check if current notification is already processed
     *
     * @return bool
     */
    public function isProcessed()
    {
        return $this->getStatus() == self::STATUS_PROCESSED;
    }

    /**
     * Check if current notification is time out
     *
     * @return bool
     */
    public function isTimeout()
    {
        $startedTime = strtotime($this->getStartedAt());
        $currentTime = time();

        if ($currentTime - $startedTime > self::TIMEOUT_LIMIT) {
            return true;
        }
        return false;
    }

    /**
     * Start process of current notification
     *
     * @return Mage_GoogleCheckout_Model_Notification
     */
    public function startProcess()
    {
        $this->getResource()->startProcess($this->getSerialNumber());
        return $this;
    }

    /**
     * Update process of current notification
     *
     * @return Mage_GoogleCheckout_Model_Notification
     */
    public function updateProcess()
    {
        $this->getResource()->updateProcess($this->getSerialNumber());
        return $this;
    }

    /**
     * Stop process of current notification
     *
     * @return Mage_GoogleCheckout_Model_Notification
     */
    public function stopProcess()
    {
        $this->getResource()->stopProcess($this->getSerialNumber());
        return $this;
    }
}
