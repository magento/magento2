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
 * @package     Mage_GoogleShopping
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * GoogleShopping Admin Items Controller
 *
 * @category   Mage
 * @package    Mage_GoogleShopping
 * @name       Mage_GoogleShopping_Adminhtml_Googleshopping_ItemsController
 * @author     Magento Core Team <core@magentocommerce.com>
*/
class Mage_GoogleShopping_Adminhtml_Googleshopping_ItemsController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Initialize general settings for action
     *
     * @return  Mage_GoogleShopping_Adminhtml_Googleshopping_ItemsController
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('catalog/googleshopping/items')
            ->_addBreadcrumb(Mage::helper('Mage_Adminhtml_Helper_Data')->__('Catalog'), Mage::helper('Mage_Adminhtml_Helper_Data')->__('Catalog'))
            ->_addBreadcrumb(Mage::helper('Mage_Adminhtml_Helper_Data')->__('Google Content'), Mage::helper('Mage_Adminhtml_Helper_Data')->__('Google Content'));
        return $this;
    }

    /**
     * Manage Items page with two item grids: Magento products and Google Content items
     */
    public function indexAction()
    {
        $this->_title($this->__('Catalog'))
             ->_title($this->__('Google Content'))
             ->_title($this->__('Manage Items'));

        if (0 === (int)$this->getRequest()->getParam('store')) {
            $this->_redirect('*/*/', array('store' => Mage::app()->getAnyStoreView()->getId(), '_current' => true));
            return;
        }
        $contentBlock = $this->getLayout()
            ->createBlock('Mage_GoogleShopping_Block_Adminhtml_Items')->setStore($this->_getStore());

        if ($this->getRequest()->getParam('captcha_token') && $this->getRequest()->getParam('captcha_url')) {
            $contentBlock->setGcontentCaptchaToken(
                Mage::helper('Mage_Core_Helper_Data')->urlDecode($this->getRequest()->getParam('captcha_token'))
            );
            $contentBlock->setGcontentCaptchaUrl(
                Mage::helper('Mage_Core_Helper_Data')->urlDecode($this->getRequest()->getParam('captcha_url'))
            );
        }

        if (!$this->_getConfig()->isValidDefaultCurrencyCode($this->_getStore()->getId())) {
            $_countryInfo = $this->_getConfig()->getTargetCountryInfo($this->_getStore()->getId());
            $this->_getSession()->addNotice(
                Mage::helper('Mage_GoogleShopping_Helper_Data')->__("The store's currency should be set to %s for %s in system configuration. Otherwise item prices won't be correct in Google Content.", $_countryInfo['currency_name'], $_countryInfo['name'])
            );
        }

        $this->_initAction()
            ->_addBreadcrumb(Mage::helper('Mage_GoogleShopping_Helper_Data')->__('Items'), Mage::helper('Mage_GoogleShopping_Helper_Data')->__('Items'))
            ->_addContent($contentBlock)
            ->renderLayout();
    }

    /**
     * Grid with Google Content items
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()
                ->createBlock('Mage_GoogleShopping_Block_Adminhtml_Items_Item')
                ->setIndex($this->getRequest()->getParam('index'))
                ->toHtml()
           );
    }

    /**
     * Retrieve synchronization process mutex
     *
     * @return Mage_GoogleShopping_Model_Flag
     */
    protected function _getFlag()
    {
        return Mage::getSingleton('Mage_GoogleShopping_Model_Flag')->loadSelf();
    }

    /**
     * Add (export) several products to Google Content
     */
    public function massAddAction()
    {
        $flag = $this->_getFlag();
        if ($flag->isLocked()) {
            return;
        }

        session_write_close();
        ignore_user_abort(true);
        set_time_limit(0);

        $storeId = $this->_getStore()->getId();
        $productIds = $this->getRequest()->getParam('product', null);
        $notifier = Mage::getModel('Mage_AdminNotification_Model_Inbox');

        try {
            $flag->lock();
            Mage::getModel('Mage_GoogleShopping_Model_MassOperations')
                ->setFlag($flag)
                ->addProducts($productIds, $storeId);
        } catch (Zend_Gdata_App_CaptchaRequiredException $e) {
            // Google requires CAPTCHA for login
            $this->_getSession()->addError(Mage::helper('Mage_GoogleShopping_Helper_Data')->__($e->getMessage()));
            $flag->unlock();
            $this->_redirectToCaptcha($e);
            return;
        } catch (Exception $e) {
            $flag->unlock();
            $notifier->addMajor(
                Mage::helper('Mage_GoogleShopping_Helper_Data')->__('An error has occured while adding products to google shopping account.'),
                $e->getMessage()
            );
            Mage::logException($e);
            return;
        }

        $flag->unlock();
    }

    /**
     * Delete products from Google Content
     */
    public function massDeleteAction()
    {
        $flag = $this->_getFlag();
        if ($flag->isLocked()) {
            return;
        }

        session_write_close();
        ignore_user_abort(true);
        set_time_limit(0);

        $itemIds = $this->getRequest()->getParam('item');

        try {
            $flag->lock();
            Mage::getModel('Mage_GoogleShopping_Model_MassOperations')
                ->setFlag($flag)
                ->deleteItems($itemIds);
        } catch (Zend_Gdata_App_CaptchaRequiredException $e) {
            // Google requires CAPTCHA for login
            $this->_getSession()->addError(Mage::helper('Mage_GoogleShopping_Helper_Data')->__($e->getMessage()));
            $flag->unlock();
            $this->_redirectToCaptcha($e);
            return;
        } catch (Exception $e) {
            $flag->unlock();
            Mage::getModel('Mage_AdminNotification_Model_Inbox')->addMajor(
                Mage::helper('Mage_GoogleShopping_Helper_Data')->__('An error has occured while deleting products from google shopping account.'),
                Mage::helper('Mage_GoogleShopping_Helper_Data')->__('One or more products were not deleted from google shopping account. Refer to the log file for details.')
            );
            Mage::logException($e);
            return;
        }

        $flag->unlock();
    }

    /**
     * Update items statistics and remove the items which are not available in Google Content
     */
    public function refreshAction()
    {
        $flag = $this->_getFlag();
        if ($flag->isLocked()) {
            return;
        }

        session_write_close();
        ignore_user_abort(true);
        set_time_limit(0);

        $itemIds = $this->getRequest()->getParam('item');

        try {
            $flag->lock();
            Mage::getModel('Mage_GoogleShopping_Model_MassOperations')
                ->setFlag($flag)
                ->synchronizeItems($itemIds);
        } catch (Zend_Gdata_App_CaptchaRequiredException $e) {
            // Google requires CAPTCHA for login
            $this->_getSession()->addError(Mage::helper('Mage_GoogleShopping_Helper_Data')->__($e->getMessage()));
            $flag->unlock();
            $this->_redirectToCaptcha($e);
            return;
        } catch (Exception $e) {
            $flag->unlock();
            Mage::getModel('Mage_AdminNotification_Model_Inbox')->addMajor(
                Mage::helper('Mage_GoogleShopping_Helper_Data')->__('An error has occured while deleting products from google shopping account.'),
                Mage::helper('Mage_GoogleShopping_Helper_Data')->__('One or more products were not deleted from google shopping account. Refer to the log file for details.')
            );
            Mage::logException($e);
            return;
        }

        $flag->unlock();
    }

    /**
     * Confirm CAPTCHA
     */
    public function confirmCaptchaAction()
    {

        $storeId = $this->_getStore()->getId();
        try {
            Mage::getModel('Mage_GoogleShopping_Model_Service')->getClient(
                $storeId,
                Mage::helper('Mage_Core_Helper_Data')->urlDecode($this->getRequest()->getParam('captcha_token')),
                $this->getRequest()->getParam('user_confirm')
            );
            $this->_getSession()->addSuccess($this->__('Captcha has been confirmed.'));

        } catch (Zend_Gdata_App_CaptchaRequiredException $e) {
            $this->_getSession()->addError($this->__('Captcha confirmation error: %s', $e->getMessage()));
            $this->_redirectToCaptcha($e);
            return;
        } catch (Zend_Gdata_App_Exception $e) {
            $this->_getSession()->addError(
                Mage::helper('Mage_GoogleShopping_Helper_Data')->parseGdataExceptionMessage($e->getMessage())
            );
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError($this->__('Captcha confirmation error.'));
        }

        $this->_redirect('*/*/index', array('store'=>$storeId));
    }

    /**
     * Retrieve background process status
     *
     * @return Zend_Controller_Response_Abstract
     */
    public function statusAction()
    {
        if ($this->getRequest()->isAjax()) {
            $this->getResponse()->setHeader('Content-Type', 'application/json');
            $params = array(
                'is_running' => $this->_getFlag()->isLocked()
            );
            return $this->getResponse()->setBody(Mage::helper('Mage_Core_Helper_Data')->jsonEncode($params));
        }
    }

    /**
     * Redirect user to Google Captcha challenge
     *
     * @param Zend_Gdata_App_CaptchaRequiredException $e
     */
    protected function _redirectToCaptcha($e)
    {
        $redirectUrl = $this->getUrl(
            '*/*/index',
            array(
                'store' => $this->_getStore()->getId(),
                'captcha_token' => Mage::helper('Mage_Core_Helper_Data')->urlEncode($e->getCaptchaToken()),
                'captcha_url' => Mage::helper('Mage_Core_Helper_Data')->urlEncode($e->getCaptchaUrl())
            )
        );
        if ($this->getRequest()->isAjax()) {
            $this->getResponse()->setHeader('Content-Type', 'application/json')
                ->setBody(Mage::helper('Mage_Core_Helper_Data')->jsonEncode(array('redirect' => $redirectUrl)));
        } else {
            $this->_redirect($redirectUrl);
        }
    }

    /**
     * Get store object, basing on request
     *
     * @return Mage_Core_Model_Store
     * @throws Mage_Core_Exception
     */
    public function _getStore()
    {
        $store = Mage::app()->getStore((int)$this->getRequest()->getParam('store', 0));
        if ((!$store) || 0 == $store->getId()) {
            Mage::throwException($this->__('Unable to select a Store View.'));
        }
        return $store;
    }

    /**
     * Get Google Shopping config model
     *
     * @return Mage_GoogleShopping_Model_Config
     */
    protected function _getConfig()
    {
        return Mage::getSingleton('Mage_GoogleShopping_Model_Config');
    }

    /**
     * Check access to this controller
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('Mage_Backend_Model_Auth_Session')->isAllowed('catalog/googleshopping/items');
    }
}
