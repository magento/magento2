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
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Adminhtml AdminNotification toolbar
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Notification_Toolbar extends Mage_Adminhtml_Block_Template
{

    /**
     * Retrieve helper
     *
     * @return Mage_AdminNotification_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('Mage_AdminNotification_Helper_Data');
    }

    /**
     * Check is show toolbar
     *
     * @return bool
     */
    public function isShow()
    {
        if (!$this->isOutputEnabled('Mage_AdminNotification')) {
            return false;
        }
        if ($this->getRequest()->getControllerName() == 'notification') {
            return false;
        }
        if ($this->getCriticalCount() == 0 && $this->getMajorCount() == 0 && $this->getMinorCount() == 0
            && $this->getNoticeCount() == 0
        ) {
            return false;
        }

        return true;
    }

    /**
     * Retrieve count of critical errors
     *
     * @return int
     */
    public function getCriticalCount()
    {
        return $this->_getHelper()
            ->getUnreadNoticeCount(Mage_AdminNotification_Model_Inbox::SEVERITY_CRITICAL);
    }

    /**
     * Retrieve count of major errors
     *
     * @return int
     */
    public function getMajorCount()
    {
        return $this->_getHelper()
            ->getUnreadNoticeCount(Mage_AdminNotification_Model_Inbox::SEVERITY_MAJOR);
    }

    /**
     * Retrieve count of minor errors
     *
     * @return int
     */
    public function getMinorCount()
    {
        return $this->_getHelper()
            ->getUnreadNoticeCount(Mage_AdminNotification_Model_Inbox::SEVERITY_MINOR);
    }

    /**
     * Retrieve count of notices
     *
     * @return int
     */
    public function getNoticeCount()
    {
        return $this->_getHelper()
            ->getUnreadNoticeCount(Mage_AdminNotification_Model_Inbox::SEVERITY_NOTICE);
    }

    /**
     * Retrieve Notices Inbox URL
     *
     * @return string
     */
    public function getNoticesInboxUrl()
    {
        return $this->getUrl('adminhtml/notification');
    }

    /**
     * Retrieve last notice Title
     *
     * @return string
     */
    public function getLatestNotice()
    {
        return  $this->_getHelper()
            ->getLatestNotice()->getTitle();
    }

    /**
     * Retrieve Last Notice URL
     *
     * @return string
     */
    public function getLatestNoticeUrl()
    {
        return $this->_getHelper()->getLatestNotice()->getUrl();
    }

    /**
     * Check is Message Window Available
     *
     * @return bool
     */
    public function isMessageWindowAvailable()
    {
        $block = $this->getLayout()->getBlock('notification_window');
        if ($block) {
            return $block->canShow();
        }
        return false;
    }
}
