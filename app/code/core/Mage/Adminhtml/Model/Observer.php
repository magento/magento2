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
 * Installation event observer
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Model_Observer
{

    public function bindLocale($observer)
    {
        if ($locale=$observer->getEvent()->getLocale()) {
            if ($choosedLocale = Mage::getSingleton('Mage_Adminhtml_Model_Session')->getLocale()) {
                $locale->setLocaleCode($choosedLocale);
            }
        }
        return $this;
    }

    /**
     * Prepare massaction separated data
     *
     * @return Mage_Adminhtml_Model_Observer
     */
    public function massactionPrepareKey()
    {
        $request = Mage::app()->getFrontController()->getRequest();
        if ($key = $request->getPost('massaction_prepare_key')) {
            $postData = $request->getPost($key);
            $value = is_array($postData) ? $postData : explode(',', $postData);
            $request->setPost($key, $value ? $value : null);
        }
        return $this;
    }

    /**
     * Clear result of configuration files access level verification in system cache
     *
     * @return Mage_Adminhtml_Model_Observer
     */
    public function clearCacheConfigurationFilesAccessLevelVerification()
    {
        Mage::app()->removeCache(Mage_Adminhtml_Block_Notification_Security::VERIFICATION_RESULT_CACHE_KEY);
        return $this;
    }

    /**
     * Handler for controller_action_predispatch event
     *
     * @param Varien_Event_Observer $observer
     */
    public function actionPreDispatchAdmin($observer)
    {
        $request = Mage::app()->getRequest();
        /** @var $controller Mage_Core_Controller_Varien_Action */
        $controller = $observer->getEvent()->getControllerAction();
        /** @var $session Mage_Admin_Model_Session */
        $session = Mage::getSingleton('Mage_Admin_Model_Session');
        /** @var $user Mage_Admin_Model_User */
        $user = $session->getUser();

        $requestedActionName = $request->getActionName();
        $openActions = array(
            'forgotpassword',
            'resetpassword',
            'resetpasswordpost',
            'logout',
            'refresh' // captcha refresh
        );
        if (in_array($requestedActionName, $openActions)) {
            $request->setDispatched(true);
        } else {
            if ($user) {
                $user->reload();
            }
            if (!$session->isLoggedIn()) {
                $isRedirectNeeded = false;
                if ($request->getPost('login')) {
                    $this->_performLogin($controller, $isRedirectNeeded);
                }
                if (!$isRedirectNeeded && !$request->getParam('forwarded')) {
                    if ($request->getParam('isIframe')) {
                        $request->setParam('forwarded', true)
                            ->setControllerName('index')
                            ->setActionName('deniedIframe')
                            ->setDispatched(false);
                    } else if ($request->getParam('isAjax')) {
                        $request->setParam('forwarded', true)
                            ->setControllerName('index')
                            ->setActionName('deniedJson')
                            ->setDispatched(false);
                    } else {
                        $request->setParam('forwarded', true)
                            ->setRouteName('adminhtml')
                            ->setControllerName('index')
                            ->setActionName('login')
                            ->setDispatched(false);
                    }
                }
            }
        }

        $session->refreshAcl();
    }

    /**
     * Performs login, if user submitted login form
     *
     * @param Mage_Core_Controller_Varien_Action $controller
     * @param bool $isRedirectNeeded
     * @return bool
     */
    protected function _performLogin($controller, &$isRedirectNeeded)
    {
        $isRedirectNeeded = false;
        /** @var $session Mage_Admin_Model_Session */
        $session = Mage::getSingleton('Mage_Admin_Model_Session');
        $request = $controller->getRequest();

        $postLogin  = $request->getPost('login');
        $username   = isset($postLogin['username']) ? $postLogin['username'] : '';
        $password   = isset($postLogin['password']) ? $postLogin['password'] : '';
        $request->setPost('login', null);
        $result = $session->login($username, $password);
        if ($result) {
            $this->_redirectIfNeededAfterLogin($controller);
        } else if (!$request->getParam('messageSent')) {
            Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError(
                Mage::helper('Mage_Adminhtml_Helper_Data')->__('Invalid User Name or Password.')
            );
            $request->setParam('messageSent', true);
        }
        return $result;
    }

    /**
     * Checks, whether Magento requires redirection after successful admin login, and redirects user, if needed
     *
     * @param Mage_Core_Controller_Varien_Action $controller
     * @return bool
     */
    protected function _redirectIfNeededAfterLogin($controller)
    {
        $requestUri = $this->_getRequestUri($controller->getRequest());
        if (!$requestUri) {
            return false;
        }
        Mage::app()->getResponse()->setRedirect($requestUri);
        $controller->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
        return true;
    }

    /**
     * Checks, whether secret key is required for admin access or request uri is explicitly set, and returns
     * an appropriate url for redirection or null, if none
     *
     * @param Mage_Core_Controller_Request_Http $request
     * @return string|null
     */
    protected function _getRequestUri($request)
    {
        /** @var $urlModel Mage_Adminhtml_Model_Url */
        $urlModel = Mage::getSingleton('Mage_Adminhtml_Model_Url');
        if ($urlModel->useSecretKey()) {
            return $urlModel->getUrl('*/*/*', array('_current' => true));
        } elseif ($request) {
            return $request->getRequestUri();
        } else {
            return null;
        }
    }
}
