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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Index admin controller
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Render specified template
     *
     * @param string $tplName
     * @param array $data parameters required by template
     */
    protected function _outTemplate($tplName, $data = array())
    {
        $this->_initLayoutMessages('Mage_Adminhtml_Model_Session');
        $block = $this->getLayout()->createBlock('Mage_Adminhtml_Block_Template')->setTemplate("$tplName.phtml");
        foreach ($data as $index => $value) {
            $block->assign($index, $value);
        }
        $html = $block->toHtml();
        Mage::getSingleton('Mage_Core_Model_Translate_Inline')->processResponseBody($html);
        $this->getResponse()->setBody($html);
    }

    /**
     * Admin area entry point
     * Always redirects to the startup page url
     */
    public function indexAction()
    {
        $session = Mage::getSingleton('Mage_Admin_Model_Session');
        $url = $session->getUser()->getStartupPageUrl();
        if ($session->isFirstPageAfterLogin()) {
            // retain the "first page after login" value in session (before redirect)
            $session->setIsFirstPageAfterLogin(true);
        }
        $this->_redirect($url);
    }

    /**
     * Administrator login action
     */
    public function loginAction()
    {
        if (Mage::getSingleton('Mage_Admin_Model_Session')->isLoggedIn()) {
            $this->_redirect('*');
            return;
        }
        $loginData = $this->getRequest()->getParam('login');
        $data = array();

        if(is_array($loginData) && array_key_exists('username', $loginData)) {
            $data['username'] = $loginData['username'];
        } else {
            $data['username'] = null;
        }
        $this->_outTemplate('admin/login', $data);
    }

    /**
     * Administrator logout action
     */
    public function logoutAction()
    {
        /** @var $adminSession Mage_Admin_Model_Session */
        $adminSession = Mage::getSingleton('Mage_Admin_Model_Session');
        $adminSession->unsetAll();
        $adminSession->getCookie()->delete($adminSession->getSessionName());
        $adminSession->addSuccess(Mage::helper('Mage_Adminhtml_Helper_Data')->__('You have logged out.'));

        $this->_redirect('*');
    }

    /**
     * Global Search Action
     */
    public function globalSearchAction()
    {
        $searchModules = Mage::getConfig()->getNode("adminhtml/global_search");
        $items = array();

        if (!Mage::getSingleton('Mage_Admin_Model_Session')->isAllowed('admin/global_search')) {
            $items[] = array(
                'id' => 'error',
                'type' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Error'),
                'name' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Access Denied'),
                'description' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('You have not enough permissions to use this functionality.')
            );
            $totalCount = 1;
        } else {
            if (empty($searchModules)) {
                $items[] = array(
                    'id' => 'error',
                    'type' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Error'),
                    'name' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('No search modules were registered'),
                    'description' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Please make sure that all global admin search modules are installed and activated.')
                );
                $totalCount = 1;
            } else {
                $start = $this->getRequest()->getParam('start', 1);
                $limit = $this->getRequest()->getParam('limit', 10);
                $query = $this->getRequest()->getParam('query', '');
                foreach ($searchModules->children() as $searchConfig) {

                    if ($searchConfig->acl && !Mage::getSingleton('Mage_Admin_Model_Session')->isAllowed($searchConfig->acl)){
                        continue;
                    }

                    $className = $searchConfig->getClassName();

                    if (empty($className)) {
                        continue;
                    }
                    $searchInstance = new $className();
                    $results = $searchInstance->setStart($start)
                        ->setLimit($limit)
                        ->setQuery($query)
                        ->load()
                        ->getResults();
                    $items = array_merge_recursive($items, $results);
                }
                $totalCount = sizeof($items);
            }
        }

        $block = $this->getLayout()->createBlock('Mage_Adminhtml_Block_Template')
            ->setTemplate('system/autocomplete.phtml')
            ->assign('items', $items);

        $this->getResponse()->setBody($block->toHtml());
    }

    /**
     * Change locale action
     */
    public function changeLocaleAction()
    {
        $locale = $this->getRequest()->getParam('locale');
        if ($locale) {
            Mage::getSingleton('Mage_Adminhtml_Model_Session')->setLocale($locale);
        }
        $this->_redirectReferer();
    }

    /**
     * Denied JSON action
     */
    public function deniedJsonAction()
    {
        $this->getResponse()->setBody($this->_getDeniedJson());
    }

    /**
     * Retrieve response for deniedJsonAction()
     */
    protected function _getDeniedJson()
    {
        return Mage::helper('Mage_Core_Helper_Data')->jsonEncode(array(
            'ajaxExpired' => 1,
            'ajaxRedirect' => $this->getUrl('*/index/login')
        ));
    }

    /**
     * Denied IFrame action
     */
    public function deniedIframeAction()
    {
        $this->getResponse()->setBody($this->_getDeniedIframe());
    }

    /**
     * Retrieve response for deniedIframeAction()
     */
    protected function _getDeniedIframe()
    {
        return '<script type="text/javascript">parent.window.location = \''
            . $this->getUrl('*/index/login') . '\';</script>';
    }

    /**
     * Forgot administrator password action
     */
    public function forgotpasswordAction()
    {
        $email = (string) $this->getRequest()->getParam('email');
        $params = $this->getRequest()->getParams();

        if (!empty($email) && !empty($params)) {
            // Validate received data to be an email address
            if (!Zend_Validate::is($email, 'EmailAddress')) {
                $this->_getSession()->addError($this->__('Invalid email address.'));
                $this->_outTemplate('forgotpassword');
                return;
            }
            $collection = Mage::getResourceModel('Mage_Admin_Model_Resource_User_Collection');
            /** @var $collection Mage_Admin_Model_Resource_User_Collection */
            $collection->addFieldToFilter('email', $email);
            $collection->load(false);

            if ($collection->getSize() > 0) {
                foreach ($collection as $item) {
                    $user = Mage::getModel('Mage_Admin_Model_User')->load($item->getId());
                    if ($user->getId()) {
                        $newResetPasswordLinkToken = Mage::helper('Mage_Admin_Helper_Data')->generateResetPasswordLinkToken();
                        $user->changeResetPasswordLinkToken($newResetPasswordLinkToken);
                        $user->save();
                        $user->sendPasswordResetConfirmationEmail();
                    }
                    break;
                }
            }
            $this->_getSession()
                ->addSuccess(Mage::helper('Mage_Adminhtml_Helper_Data')->__('If there is an account associated with %s you will receive an email with a link to reset your password.', Mage::helper('Mage_Adminhtml_Helper_Data')->escapeHtml($email)));
            $this->_redirect('*/*/login');
            return;
        } elseif (!empty($params)) {
            $this->_getSession()->addError(Mage::helper('Mage_Adminhtml_Helper_Data')->__('The email address is empty.'));
        }

        $data = array(
            'email' => $email
        );
        $this->_outTemplate('admin/forgotpassword', $data);
    }

    /**
     * Display reset forgotten password form
     *
     * User is redirected on this action when he clicks on the corresponding link in password reset confirmation email
     */
    public function resetPasswordAction()
    {
        $resetPasswordLinkToken = (string) $this->getRequest()->getQuery('token');
        $userId = (int) $this->getRequest()->getQuery('id');
        try {
            $this->_validateResetPasswordLinkToken($userId, $resetPasswordLinkToken);
            $data = array(
                'userId' => $userId,
                'resetPasswordLinkToken' => $resetPasswordLinkToken
            );
            $this->_outTemplate('resetforgottenpassword', $data);
        } catch (Exception $exception) {
            $this->_getSession()->addError(Mage::helper('Mage_Adminhtml_Helper_Data')->__('Your password reset link has expired.'));
            $this->_redirect('*/*/forgotpassword', array('_nosecret' => true));
        }
    }

    /**
     * Reset forgotten password
     *
     * Used to handle data recieved from reset forgotten password form
     */
    public function resetPasswordPostAction()
    {
        $resetPasswordLinkToken = (string) $this->getRequest()->getQuery('token');
        $userId = (int) $this->getRequest()->getQuery('id');
        $password = (string) $this->getRequest()->getPost('password');
        $passwordConfirmation = (string) $this->getRequest()->getPost('confirmation');

        try {
            $this->_validateResetPasswordLinkToken($userId, $resetPasswordLinkToken);
        } catch (Exception $exception) {
            $this->_getSession()->addError(Mage::helper('Mage_Adminhtml_Helper_Data')->__('Your password reset link has expired.'));
            $this->_redirect('*/*/');
            return;
        }

        $errorMessages = array();
        if (iconv_strlen($password) <= 0) {
            array_push($errorMessages, Mage::helper('Mage_Adminhtml_Helper_Data')->__('New password field cannot be empty.'));
        }
        /** @var $user Mage_Admin_Model_User */
        $user = Mage::getModel('Mage_Admin_Model_User')->load($userId);

        $user->setNewPassword($password);
        $user->setPasswordConfirmation($passwordConfirmation);
        $validationErrorMessages = $user->validate();
        if (is_array($validationErrorMessages)) {
            $errorMessages = array_merge($errorMessages, $validationErrorMessages);
        }

        if (!empty($errorMessages)) {
            foreach ($errorMessages as $errorMessage) {
                $this->_getSession()->addError($errorMessage);
            }
            $data = array(
                'userId' => $userId,
                'resetPasswordLinkToken' => $resetPasswordLinkToken
            );
            $this->_outTemplate('resetforgottenpassword', $data);
            return;
        }

        try {
            // Empty current reset password token i.e. invalidate it
            $user->setRpToken(null);
            $user->setRpTokenCreatedAt(null);
            $user->setPasswordConfirmation(null);
            $user->save();
            $this->_getSession()->addSuccess(Mage::helper('Mage_Adminhtml_Helper_Data')->__('Your password has been updated.'));
            $this->_redirect('*/*/login');
        } catch (Exception $exception) {
            $this->_getSession()->addError($exception->getMessage());
            $data = array(
                'userId' => $userId,
                'resetPasswordLinkToken' => $resetPasswordLinkToken
            );
            $this->_outTemplate('resetforgottenpassword', $data);
            return;
        }
    }

    /**
     * Check if password reset token is valid
     *
     * @param int $userId
     * @param string $resetPasswordLinkToken
     * @throws Mage_Core_Exception
     */
    protected function _validateResetPasswordLinkToken($userId, $resetPasswordLinkToken)
    {
        if (!is_int($userId)
            || !is_string($resetPasswordLinkToken)
            || empty($resetPasswordLinkToken)
            || empty($userId)
            || $userId < 0
        ) {
            throw Mage::exception('Mage_Core', Mage::helper('Mage_Adminhtml_Helper_Data')->__('Invalid password reset token.'));
        }

        /** @var $user Mage_Admin_Model_User */
        $user = Mage::getModel('Mage_Admin_Model_User')->load($userId);
        if (!$user || !$user->getId()) {
            throw Mage::exception('Mage_Core', Mage::helper('Mage_Adminhtml_Helper_Data')->__('Wrong account specified.'));
        }

        $userToken = $user->getRpToken();
        if (strcmp($userToken, $resetPasswordLinkToken) != 0 || $user->isResetPasswordLinkTokenExpired()) {
            throw Mage::exception('Mage_Core', Mage::helper('Mage_Adminhtml_Helper_Data')->__('Your password reset link has expired.'));
        }
    }

    /**
     * Check if user has permissions to access this controller
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return true;
    }
}
