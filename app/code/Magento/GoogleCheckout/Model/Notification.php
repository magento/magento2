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
 * @category    Magento
 * @package     Magento_GoogleCheckout
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Google Checkout notification model
 *
 * @method \Magento\GoogleCheckout\Model\Resource\Notification _getResource()
 * @method \Magento\GoogleCheckout\Model\Resource\Notification getResource()
 * @method string getSerialNumber()
 * @method \Magento\GoogleCheckout\Model\Notification setSerialNumber(string $value)
 * @method string getStartedAt()
 * @method \Magento\GoogleCheckout\Model\Notification setStartedAt(string $value)
 * @method int getStatus()
 * @method \Magento\GoogleCheckout\Model\Notification setStatus(int $value)
 *
 * @category    Magento
 * @package     Magento_GoogleCheckout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\GoogleCheckout\Model;

class Notification extends \Magento\Core\Model\AbstractModel
{
    const TIMEOUT_LIMIT = 3600;
    const STATUS_INPROCESS = 0;
    const STATUS_PROCESSED = 1;

    /**
     * Initialize model
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Magento\GoogleCheckout\Model\Resource\Notification');
    }

    /**
     * Assign previously saved notification data to model
     *
     * @return \Magento\GoogleCheckout\Model\Notification
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
     * @return \Magento\GoogleCheckout\Model\Notification
     */
    public function startProcess()
    {
        $this->getResource()->startProcess($this->getSerialNumber());
        return $this;
    }

    /**
     * Update process of current notification
     *
     * @return \Magento\GoogleCheckout\Model\Notification
     */
    public function updateProcess()
    {
        $this->getResource()->updateProcess($this->getSerialNumber());
        return $this;
    }

    /**
     * Stop process of current notification
     *
     * @return \Magento\GoogleCheckout\Model\Notification
     */
    public function stopProcess()
    {
        $this->getResource()->stopProcess($this->getSerialNumber());
        return $this;
    }
}
