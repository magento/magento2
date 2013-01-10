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
 * @package     Mage_Downloadable
 * @copyright  Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * oAuth authorize controller
 *
 * @category    Mage
 * @package     Mage_Oauth
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Oauth_Adminhtml_Oauth_AuthorizeController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Session name
     *
     * @var string
     */
    protected $_sessionName = 'Mage_Backend_Model_Auth_Session';

    /**
     * Array of actions which can be processed without secret key validation
     *
     * @var array
     */
    protected $_publicActions = array('index', 'simple', 'confirm', 'confirmSimple','reject', 'rejectSimple');

    /**
     * Disable showing of login form
     *
     * @see Mage_Adminhtml_Model_Observer::actionPreDispatchAdmin() method for explanation
     * @return void
     */
    public function preDispatch()
    {
        $this->getRequest()->setParam('forwarded', true);

        // check login data before it set null in Mage_Adminhtml_Model_Observer::actionPreDispatchAdmin
        $loginError = $this->_checkLoginIsEmpty();

        parent::preDispatch();

        // call after parent::preDispatch(); to get session started
        if ($loginError) {
            Mage::getSingleton('Mage_Adminhtml_Model_Session')
                ->addError(Mage::helper('Mage_Adminhtml_Helper_Data')->__('Invalid User Name or Password.'));
            $params = array('_query' => array('oauth_token' => $this->getRequest()->getParam('oauth_token', null)));
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            $this->setFlag('', self::FLAG_NO_POST_DISPATCH, true);
            $params = array('_query' => array('oauth_token' => $this->getRequest()->getParam('oauth_token', null)));
            $this->_redirect('*/*/*', $params);
        }
    }

    /**
     * Index action.
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_initForm();

        $this->_initLayoutMessages($this->_sessionName);
        $this->renderLayout();
    }

    /**
     * Index action with a simple design
     *
     * @return void
     */
    public function simpleAction()
    {
        $this->_initForm(true);
        $this->_initLayoutMessages($this->_sessionName);
        $this->renderLayout();
    }

    /**
     * Init authorize page
     *
     * @param bool $simple
     * @return Mage_Oauth_Adminhtml_Oauth_AuthorizeController
     */
    protected function _initForm($simple = false)
    {
        /** @var $server Mage_Oauth_Model_Server */
        $server = Mage::getModel('Mage_Oauth_Model_Server');
        /** @var $session Mage_Backend_Model_Auth_Session */
        $session = Mage::getSingleton($this->_sessionName);

        $isException = false;
        try {
            $server->checkAuthorizeRequest();
        } catch (Mage_Core_Exception $e) {
            $session->addError($e->getMessage());
        } catch (Mage_Oauth_Exception $e) {
            $isException = true;
            $session->addException($e, $this->__('An error occurred. Your authorization request is invalid.'));
        } catch (Exception $e) {
            $isException = true;
            $session->addException($e, $this->__('An error occurred.'));
        }

        $this->loadLayout();
        $layout = $this->getLayout();
        $logged = $session->isLoggedIn();

        $contentBlock = $layout->getBlock('content');
        if ($logged) {
            $contentBlock->unsetChild('oauth.authorize.form');
            /** @var $block Mage_Oauth_Block_Adminhtml_Oauth_Authorize_Button */
            $block = $contentBlock->getChildBlock('oauth.authorize.button');
        } else {
            $contentBlock->unsetChild('oauth.authorize.button');
            /** @var $block Mage_Oauth_Block_Adminhtml_Oauth_Authorize */
            $block = $contentBlock->getChildBlock('oauth.authorize.form');
        }

        $block->setIsSimple($simple)
            ->setToken($this->getRequest()->getQuery('oauth_token'))
            ->setHasException($isException);
        return $this;
    }

    /**
     * Init confirm page
     *
     * @param bool $simple
     * @return Mage_Oauth_Adminhtml_Oauth_AuthorizeController
     */
    protected function _initConfirmPage($simple = false)
    {
        /** @var $helper Mage_Oauth_Helper_Data */
        $helper = Mage::helper('Mage_Oauth_Helper_Data');

        /** @var $session Mage_Backend_Model_Auth_Session */
        $session = Mage::getSingleton($this->_sessionName);

        /** @var $user Mage_User_Model_User */
        $user = $session->getData('user');
        if (!$user) {
            $session->addError($this->__('Please login to proceed authorization.'));
            $url = $helper->getAuthorizeUrl(Mage_Oauth_Model_Token::USER_TYPE_ADMIN);
            $this->_redirectUrl($url);
            return $this;
        }

        $this->loadLayout();

        /** @var $block Mage_Oauth_Block_Adminhtml_Oauth_Authorize */
        $block = $this->getLayout()->getBlock('oauth.authorize.confirm');
        $block->setIsSimple($simple);

        try {
            /** @var $server Mage_Oauth_Model_Server */
            $server = Mage::getModel('Mage_Oauth_Model_Server');

            $token = $server->authorizeToken($user->getId(), Mage_Oauth_Model_Token::USER_TYPE_ADMIN);

            if (($callback = $helper->getFullCallbackUrl($token))) { //false in case of OOB
                $this->getResponse()->setRedirect($callback . ($simple ? '&simple=1' : ''));
                return $this;
            } else {
                $block->setVerifier($token->getVerifier());
                $session->addSuccess($this->__('Authorization confirmed.'));
            }
        } catch (Mage_Core_Exception $e) {
            $block->setHasException(true);
            $session->addError($e->getMessage());
        } catch (Exception $e) {
            $block->setHasException(true);
            $session->addException($e, $this->__('An error occurred on confirm authorize.'));
        }

        $this->_initLayoutMessages($this->_sessionName);
        $this->renderLayout();

        return $this;
    }

    /**
     * Init reject page
     *
     * @param bool $simple
     * @return Mage_Oauth_AuthorizeController
     */
    protected function _initRejectPage($simple = false)
    {
        /** @var $server Mage_Oauth_Model_Server */
        $server = Mage::getModel('Mage_Oauth_Model_Server');

        /** @var $session Mage_Backend_Model_Auth_Session */
        $session = Mage::getSingleton($this->_sessionName);

        $this->loadLayout();

        /** @var $block Mage_Oauth_Block_Authorize */
        $block = $this->getLayout()->getBlock('oauth.authorize.reject');
        $block->setIsSimple($simple);

        try {
            $token = $server->checkAuthorizeRequest();
            /** @var $helper Mage_Oauth_Helper_Data */
            $helper = Mage::helper('Mage_Oauth_Helper_Data');

            if (($callback = $helper->getFullCallbackUrl($token, true))) {
                $this->_redirectUrl($callback . ($simple ? '&simple=1' : ''));
                return $this;
            } else {
                $session->addNotice($this->__('The application access request is rejected.'));
            }
        } catch (Mage_Core_Exception $e) {
            $session->addError($e->getMessage());
        } catch (Exception $e) {
            $session->addException($e, $this->__('An error occurred on reject authorize.'));
        }

        //display exception
        $this->_initLayoutMessages($this->_sessionName);
        $this->renderLayout();

        return $this;
    }

    /**
     * Check is login data has empty login or pass
     * See Mage_Backend_Model_Auth_Session: there is no any error message if login or password is empty
     *
     * @return boolean
     */
    protected function _checkLoginIsEmpty()
    {
        $error = false;
        $action = $this->getRequest()->getActionName();
        if (($action == 'index' || $action == 'simple') && $this->getRequest()->getPost('login')) {
            $postLogin  = $this->getRequest()->getPost('login');
            $username   = isset($postLogin['username']) ? $postLogin['username'] : '';
            $password   = isset($postLogin['password']) ? $postLogin['password'] : '';
            if (empty($username) || empty($password)) {
                $error = true;
            }
        }
        return $error;
    }

    /**
     * Confirm token authorization action
     */
    public function confirmAction()
    {
        $this->_initConfirmPage();
    }

    /**
     * Confirm token authorization simple page
     */
    public function confirmSimpleAction()
    {
        $this->_initConfirmPage();
    }

    /**
     * Reject token authorization action
     */
    public function rejectAction()
    {
        $this->_initRejectPage();
    }

    /**
     * Reject token authorization simple page
     */
    public function rejectSimpleAction()
    {
        $this->_initRejectPage();
    }
}
