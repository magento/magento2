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
 * @package     Mage_Backend
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Backend event observer
 */
class Mage_Backend_Model_Observer
{
    /**
     * Bind locale
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_Backend_Model_Observer
     */
    public function bindLocale($observer)
    {
        $locale = $observer->getEvent()->getLocale();
        if ($locale) {
            $selectedLocale = Mage::getSingleton('Mage_Backend_Model_Session')->getLocale();
            if ($selectedLocale) {
                $locale->setLocaleCode($selectedLocale);
            }
        }
        return $this;
    }

    /**
     * Prepare mass action separated data
     *
     * @return Mage_Backend_Model_Observer
     */
    public function massactionPrepareKey()
    {
        $request = Mage::app()->getFrontController()->getRequest();
        $key = $request->getPost('massaction_prepare_key');
        if ($key) {
            $postData = $request->getPost($key);
            $value = is_array($postData) ? $postData : explode(',', $postData);
            $request->setPost($key, $value ? $value : null);
        }
        return $this;
    }

    /**
     * Clear result of configuration files access level verification in system cache
     *
     * @return Mage_Backend_Model_Observer
     */
    public function clearCacheConfigurationFilesAccessLevelVerification()
    {
        return $this;
    }

    /**
     * Backend will always use base class for translation.
     *
     * @return Mage_Backend_Model_Observer
     */
    public function initializeTranslation()
    {
        return $this;
    }

    /**
     * Set url class name for store 'admin'
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_Backend_Model_Observer
     */
    public function setUrlClassName(Varien_Event_Observer $observer)
    {
        /** @var $storeCollection Mage_Core_Model_Resource_Store_Collection */
        $storeCollection = $observer->getEvent()->getStoreCollection();
        /** @var $store Mage_Core_Model_Store */
        foreach ($storeCollection as $store) {
            if ($store->getId() == 0) {
                $store->setUrlClassName('Mage_Backend_Model_Url');
                break;
            }
        }

        Mage::app()->removeCache(Mage_AdminNotification_Model_System_Message_Security::VERIFICATION_RESULT_CACHE_KEY);
        return $this;
    }
}
