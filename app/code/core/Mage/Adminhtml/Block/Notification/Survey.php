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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml AdminNotification survey question block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Notification_Survey extends Mage_Adminhtml_Block_Template
{
    /**
     * Check whether survey question can show
     *
     * @return boolean
     */
    public function canShow()
    {
        $adminSession = Mage::getSingleton('Mage_Backend_Model_Auth_Session');
        $seconds = intval(date('s', time()));
        if ($adminSession->getHideSurveyQuestion()
            || !Mage::getSingleton('Mage_Core_Model_Authorization')
                ->isAllowed(Mage_Backend_Model_Acl_Config::ACL_RESOURCE_ALL)
            || Mage_AdminNotification_Model_Survey::isSurveyViewed()
            || !Mage_AdminNotification_Model_Survey::isSurveyUrlValid())
        {
            return false;
        }
        return true;
    }

    /**
     * Return survey url
     *
     * @return string
     */
    public function getSurveyUrl()
    {
        return Mage_AdminNotification_Model_Survey::getSurveyUrl();
    }
}
