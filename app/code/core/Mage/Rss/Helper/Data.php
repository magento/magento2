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
 * @package     Mage_Rss
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Rss data helper
 *
 * @category   Mage
 * @package    Mage_Rss
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Rss_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Authenticate customer on frontend
     *
     */
    public function authFrontend()
    {
        $session = Mage::getSingleton('Mage_Rss_Model_Session');
        if ($session->isCustomerLoggedIn()) {
            return;
        }
        list($username, $password) = $this->authValidate();
        $customer = Mage::getModel('Mage_Customer_Model_Customer')->authenticate($username, $password);
        if ($customer && $customer->getId()) {
            Mage::getSingleton('Mage_Rss_Model_Session')->settCustomer($customer);
        } else {
            $this->authFailed();
        }
    }

    /**
     * Authenticate admin and check ACL
     *
     * @param string $path
     */
    public function authAdmin($path)
    {
        $session = Mage::getSingleton('Mage_Rss_Model_Session');
        if ($session->isAdminLoggedIn()) {
            return;
        }
        list($username, $password) = $this->authValidate();
        Mage::getSingleton('Mage_Adminhtml_Model_Url')->setNoSecret(true);
        $adminSession = Mage::getSingleton('Mage_Admin_Model_Session');
        $user = $adminSession->login($username, $password);
        //$user = Mage::getModel('Mage_Admin_Model_User')->login($username, $password);
        if($user && $user->getId() && $user->getIsActive() == '1' && $adminSession->isAllowed($path)){
            $session->setAdmin($user);
        } else {
            $this->authFailed();
        }
    }

    /**
     * Validate Authenticate
     *
     * @param array $headers
     * @return array
     */
    public function authValidate($headers=null)
    {
        $userPass = Mage::helper('Mage_Core_Helper_Http')->authValidate($headers);
        return $userPass;
    }

    /**
     * Send authenticate failed headers
     *
     */
    public function authFailed()
    {
        Mage::helper('Mage_Core_Helper_Http')->authFailed();
    }

    /**
     * Disable using of flat catalog and/or product model to prevent limiting results to single store. Probably won't
     * work inside a controller.
     *
     * @return null
     */
    public function disableFlat()
    {
        /* @var $flatHelper Mage_Catalog_Helper_Product_Flat */
        $flatHelper = Mage::helper('Mage_Catalog_Helper_Product_Flat');
        if ($flatHelper->isEnabled()) {
            /* @var $emulationModel Mage_Core_Model_App_Emulation */
            $emulationModel = Mage::getModel('Mage_Core_Model_App_Emulation');
            // Emulate admin environment to disable using flat model - otherwise we won't get global stats
            // for all stores
            $emulationModel->startEnvironmentEmulation(0, Mage_Core_Model_App_Area::AREA_ADMIN);
        }
    }
}
