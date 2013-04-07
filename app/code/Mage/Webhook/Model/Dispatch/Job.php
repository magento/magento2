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
 * @package     Mage_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webhook_Model_Dispatch_Job extends Mage_Core_Model_Abstract implements Mage_Webhook_Model_Job_Interface
{
    const READY_TO_SEND         = 0;
    const SUCCESS               = 1;
    const FAILED                = 2;
    const FAILED_NOT_SUBSCRIBED = 3;
    const RETRY                 = 4;

    public function _construct()
    {
        parent::_construct();
        $this->_init('Mage_Webhook_Model_Resource_Dispatch_Job');
    }

    public function getEvent()
    {
        return Mage::getModel('Mage_Webhook_Model_Event')->load($this->getEventId());
    }

    public function getSubscriber()
    {
        return Mage::getModel('Mage_Webhook_Model_Subscriber')->load($this->getSubscriberId());
    }

    public function getStatus()
    {
        return $this->getData('status');
    }

    public function setStatus($status)
    {
        return $this->setData('status', $status);
    }

    /**
     * Prepare data to be saved to database
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
        if ($this->isObjectNew()) {
            $this->setCreatedAt(Varien_Date::formatDate(true));
        } elseif ($this->getId() && !$this->hasData('updated_at')) {
            $this->setUpdatedAt($this->_getResource()->formatDate(true));
        }
        return $this;
    }
}
