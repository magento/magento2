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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Log Cron Model
 *
 * @category   Mage
 * @package    Mage_Log
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Log_Model_Cron extends Mage_Core_Model_Abstract
{
    const XML_PATH_EMAIL_LOG_CLEAN_TEMPLATE     = 'system/log/error_email_template';
    const XML_PATH_EMAIL_LOG_CLEAN_IDENTITY     = 'system/log/error_email_identity';
    const XML_PATH_EMAIL_LOG_CLEAN_RECIPIENT    = 'system/log/error_email';
    const XML_PATH_LOG_CLEAN_ENABLED            = 'system/log/enabled';

    /**
     * Error messages
     *
     * @var array
     */
    protected $_errors = array();

    /**
     * Send Log Clean Warnings
     *
     * @return Mage_Log_Model_Cron
     */
    protected function _sendLogCleanEmail()
    {
        if (!$this->_errors) {
            return $this;
        }
        if (!Mage::getStoreConfig(self::XML_PATH_EMAIL_LOG_CLEAN_RECIPIENT)) {
            return $this;
        }

        $translate = Mage::getSingleton('Mage_Core_Model_Translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        $emailTemplate = Mage::getModel('Mage_Core_Model_Email_Template');
        /* @var $emailTemplate Mage_Core_Model_Email_Template */
        $emailTemplate->setDesignConfig(array('area' => 'backend', 'store' => Mage::app()->getStore()->getId()))
            ->sendTransactional(
                Mage::getStoreConfig(self::XML_PATH_EMAIL_LOG_CLEAN_TEMPLATE),
                Mage::getStoreConfig(self::XML_PATH_EMAIL_LOG_CLEAN_IDENTITY),
                Mage::getStoreConfig(self::XML_PATH_EMAIL_LOG_CLEAN_RECIPIENT),
                null,
                array('warnings' => join("\n", $this->_errors))
            );

        $translate->setTranslateInline(true);

        return $this;
    }

    /**
     * Clean logs
     *
     * @return Mage_Log_Model_Cron
     */
    public function logClean()
    {
        if (!Mage::getStoreConfigFlag(self::XML_PATH_LOG_CLEAN_ENABLED)) {
            return $this;
        }

        $this->_errors = array();

        try {
            Mage::getModel('Mage_Log_Model_Log')->clean();
        }
        catch (Exception $e) {
            $this->_errors[] = $e->getMessage();
            $this->_errors[] = $e->getTrace();
        }

        $this->_sendLogCleanEmail();

        return $this;
    }
}
