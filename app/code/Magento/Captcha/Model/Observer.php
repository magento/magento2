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
 * @category    Magento
 * @package     Magento_Captcha
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Captcha Observer
 *
 * @category    Magento
 * @package     Magento_Captcha
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Captcha\Model;

class Observer
{
    /**
     * CAPTCHA helper
     *
     * @var \Magento\Captcha\Helper\Data
     */
    protected $_helper;

    /**
     * URL manager
     *
     * @var \Magento\Core\Model\Url
     */
    protected $_urlManager;

    /**
     * @var \Magento\Filesystem
     */
    protected $_filesystem;

    /**
     * Customer data
     *
     * @var \Magento\Customer\Helper\Data
     */
    protected $_customerData;

    /**
     * Core data
     *
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData;

    /**
     * @var \Magento\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Core\Model\StoreManager
     */
    protected $_storeManager;

    /**
     * @var \Magento\Checkout\Model\Type\Onepage
     */
    protected $_typeOnepage;

    /**
     * @var \Magento\Core\Model\Session\AbstractSession
     */
    protected $_session;

    /**
     * @var \Magento\Captcha\Model\Resource\LogFactory
     */
    protected $_resLogFactory;

    /**
     * @param \Magento\Captcha\Model\Resource\LogFactory $resLogFactory
     * @param \Magento\Core\Model\Session\AbstractSession $session
     * @param \Magento\Checkout\Model\Type\Onepage $typeOnepage
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Customer\Helper\Data $customerData
     * @param \Magento\Captcha\Helper\Data $helper
     * @param \Magento\Core\Model\Url $urlManager
     * @param \Magento\Filesystem $filesystem
     * @param \Magento\App\RequestInterface $request
     * @param \Magento\Core\Model\StoreManager $storeManager
     */
    public function __construct(
        \Magento\Captcha\Model\Resource\LogFactory $resLogFactory,
        \Magento\Core\Model\Session\AbstractSession $session,
        \Magento\Checkout\Model\Type\Onepage $typeOnepage,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Customer\Helper\Data $customerData,
        \Magento\Captcha\Helper\Data $helper,
        \Magento\Core\Model\Url $urlManager,
        \Magento\Filesystem $filesystem,
        \Magento\App\RequestInterface $request,
        \Magento\Core\Model\StoreManager $storeManager
    ) {
        $this->_resLogFactory = $resLogFactory;
        $this->_session = $session;
        $this->_typeOnepage = $typeOnepage;
        $this->_coreData = $coreData;
        $this->_customerData = $customerData;
        $this->_helper = $helper;
        $this->_urlManager = $urlManager;
        $this->_filesystem = $filesystem;
        $this->_request = $request;
        $this->_storeManager = $storeManager;
    }

    /**
     * Check Captcha On Forgot Password Page
     *
     * @param \Magento\Event\Observer $observer
     * @return \Magento\Captcha\Model\Observer
     */
    public function checkForgotpassword($observer)
    {
        $formId = 'user_forgotpassword';
        $captchaModel = $this->_helper->getCaptcha($formId);
        if ($captchaModel->isRequired()) {
            $controller = $observer->getControllerAction();
            if (!$captchaModel->isCorrect($this->_getCaptchaString($controller->getRequest(), $formId))) {
                $this->_session->addError(__('Incorrect CAPTCHA'));
                $controller->setFlag('', \Magento\Core\Controller\Varien\Action::FLAG_NO_DISPATCH, true);
                $controller->getResponse()->setRedirect($this->_urlManager->getUrl('*/*/forgotpassword'));
            }
        }
        return $this;
    }

    /**
     * Check CAPTCHA on Contact Us page
     *
     * @param \Magento\Event\Observer $observer
     */
    public function checkContactUsForm($observer)
    {
        $formId = 'contact_us';
        $captcha = $this->_helper->getCaptcha($formId);
        if ($captcha->isRequired()) {
            $controller = $observer->getControllerAction();
            if (!$captcha->isCorrect($this->_getCaptchaString($controller->getRequest(), $formId))) {
                $this->_session->addError(__('Incorrect CAPTCHA.'));
                $controller->setFlag('', \Magento\Core\Controller\Varien\Action::FLAG_NO_DISPATCH, true);
                $controller->getResponse()->setRedirect($this->_urlManager->getUrl('contacts/index/index'));
            }
        }
    }

    /**
     * Check Captcha On User Login Page
     *
     * @param \Magento\Event\Observer $observer
     * @return \Magento\Captcha\Model\Observer
     */
    public function checkUserLogin($observer)
    {
        $formId = 'user_login';
        $captchaModel = $this->_helper->getCaptcha($formId);
        $controller = $observer->getControllerAction();
        $loginParams = $controller->getRequest()->getPost('login');
        $login = array_key_exists('username', $loginParams) ? $loginParams['username'] : null;
        if ($captchaModel->isRequired($login)) {
            $word = $this->_getCaptchaString($controller->getRequest(), $formId);
            if (!$captchaModel->isCorrect($word)) {
                $this->_session->addError(__('Incorrect CAPTCHA'));
                $controller->setFlag('', \Magento\Core\Controller\Varien\Action::FLAG_NO_DISPATCH, true);
                $this->_session->setUsername($login);
                $beforeUrl = $this->_session->getBeforeAuthUrl();
                $url =  $beforeUrl ? $beforeUrl : $this->_customerData->getLoginUrl();
                $controller->getResponse()->setRedirect($url);
            }
        }
        $captchaModel->logAttempt($login);
        return $this;
    }

    /**
     * Check Captcha On Register User Page
     *
     * @param \Magento\Event\Observer $observer
     * @return \Magento\Captcha\Model\Observer
     */
    public function checkUserCreate($observer)
    {
        $formId = 'user_create';
        $captchaModel = $this->_helper->getCaptcha($formId);
        if ($captchaModel->isRequired()) {
            $controller = $observer->getControllerAction();
            if (!$captchaModel->isCorrect($this->_getCaptchaString($controller->getRequest(), $formId))) {
                $this->_session->addError(__('Incorrect CAPTCHA'));
                $controller->setFlag('', \Magento\Core\Controller\Varien\Action::FLAG_NO_DISPATCH, true);
                $this->_session->setCustomerFormData($controller->getRequest()->getPost());
                $controller->getResponse()->setRedirect($this->_urlManager->getUrl('*/*/create'));
            }
        }
        return $this;
    }

    /**
     * Check Captcha On Checkout as Guest Page
     *
     * @param \Magento\Event\Observer $observer
     * @return \Magento\Captcha\Model\Observer
     */
    public function checkGuestCheckout($observer)
    {
        $formId = 'guest_checkout';
        $captchaModel = $this->_helper->getCaptcha($formId);
        $checkoutMethod = $this->_typeOnepage->getQuote()->getCheckoutMethod();
        if ($checkoutMethod == \Magento\Checkout\Model\Type\Onepage::METHOD_GUEST) {
            if ($captchaModel->isRequired()) {
                $controller = $observer->getControllerAction();
                if (!$captchaModel->isCorrect($this->_getCaptchaString($controller->getRequest(), $formId))) {
                    $controller->setFlag('', \Magento\Core\Controller\Varien\Action::FLAG_NO_DISPATCH, true);
                    $result = array('error' => 1, 'message' => __('Incorrect CAPTCHA'));
                    $controller->getResponse()->setBody($this->_coreData->jsonEncode($result));
                }
            }
        }
        return $this;
    }

    /**
     * Check Captcha On Checkout Register Page
     *
     * @param \Magento\Event\Observer $observer
     * @return \Magento\Captcha\Model\Observer
     */
    public function checkRegisterCheckout($observer)
    {
        $formId = 'register_during_checkout';
        $captchaModel = $this->_helper->getCaptcha($formId);
        $checkoutMethod = $this->_typeOnepage->getQuote()->getCheckoutMethod();
        if ($checkoutMethod == \Magento\Checkout\Model\Type\Onepage::METHOD_REGISTER) {
            if ($captchaModel->isRequired()) {
                $controller = $observer->getControllerAction();
                if (!$captchaModel->isCorrect($this->_getCaptchaString($controller->getRequest(), $formId))) {
                    $controller->setFlag('', \Magento\Core\Controller\Varien\Action::FLAG_NO_DISPATCH, true);
                    $result = array('error' => 1, 'message' => __('Incorrect CAPTCHA'));
                    $controller->getResponse()->setBody($this->_coreData->jsonEncode($result));
                }
            }
        }
        return $this;
    }

    /**
     * Check Captcha On User Login Backend Page
     *
     * @param \Magento\Event\Observer $observer
     * @throws \Magento\Backend\Model\Auth\Plugin\Exception
     * @return \Magento\Captcha\Model\Observer
     */
    public function checkUserLoginBackend($observer)
    {
        $formId = 'backend_login';
        $captchaModel = $this->_helper->getCaptcha($formId);
        $login = $observer->getEvent()->getUsername();
        if ($captchaModel->isRequired($login)) {
            if (!$captchaModel->isCorrect($this->_getCaptchaString($this->_request, $formId))) {
                $captchaModel->logAttempt($login);
                throw new \Magento\Backend\Model\Auth\Plugin\Exception(
                    __('Incorrect CAPTCHA.')
                );
            }
        }
        $captchaModel->logAttempt($login);
        return $this;
    }

    /**
     * Check Captcha On User Login Backend Page
     *
     * @param \Magento\Event\Observer $observer
     * @return \Magento\Captcha\Model\Observer
     */
    public function checkUserForgotPasswordBackend($observer)
    {
        $formId = 'backend_forgotpassword';
        $captchaModel = $this->_helper->getCaptcha($formId);
        $controller = $observer->getControllerAction();
        $email = (string) $observer->getControllerAction()->getRequest()->getParam('email');
        $params = $observer->getControllerAction()->getRequest()->getParams();

        if (!empty($email) && !empty($params)) {
            if ($captchaModel->isRequired()) {
                if (!$captchaModel->isCorrect($this->_getCaptchaString($controller->getRequest(), $formId))) {
                    $this->_session->setEmail((string) $controller->getRequest()->getPost('email'));
                    $controller->setFlag('', \Magento\Core\Controller\Varien\Action::FLAG_NO_DISPATCH, true);
                    $this->_session->addError(__('Incorrect CAPTCHA'));
                    $controller->getResponse()
                        ->setRedirect($controller->getUrl('*/*/forgotpassword', array('_nosecret' => true)));
                }
            }
        }
        return $this;
    }

    /**
     * Reset Attempts For Frontend
     *
     * @param \Magento\Event\Observer $observer
     * @return \Magento\Captcha\Model\Observer
     */
    public function resetAttemptForFrontend($observer)
    {
        return $this->_getResourceModel()->deleteUserAttempts(
            $observer->getModel()->getEmail()
        );
    }

    /**
     * Reset Attempts For Backend
     *
     * @param \Magento\Event\Observer $observer
     * @return \Magento\Captcha\Model\Observer
     */
    public function resetAttemptForBackend($observer)
    {
        return $this->_getResourceModel()->deleteUserAttempts(
            $observer->getUser()->getUsername()
        );
    }

    /**
     * Delete Unnecessary logged attempts
     *
     * @return \Magento\Captcha\Model\Observer
     */
    public function deleteOldAttempts()
    {
        $this->_getResourceModel()->deleteOldAttempts();
        return $this;
    }

    /**
     * Delete Expired Captcha Images
     *
     * @return \Magento\Captcha\Model\Observer
     */
    public function deleteExpiredImages()
    {
        foreach ($this->_storeManager->getWebsites(true) as $website) {
            $expire = time() - $this->_helper->getConfigNode('timeout', $website->getDefaultStore()) * 60;
            $imageDirectory = $this->_helper->getImgDir($website);
            foreach ($this->_filesystem->getNestedKeys($imageDirectory) as $filePath) {
                if ($this->_filesystem->isFile($filePath)
                    && pathinfo($filePath, PATHINFO_EXTENSION) == 'png'
                    && $this->_filesystem->getMTime($filePath) < $expire) {
                    $this->_filesystem->delete($filePath);
                }
            }
        }
        return $this;
    }

    /**
     * Get Captcha String
     *
     * @param \Magento\App\RequestInterface $request
     * @param string $formId
     * @return string
     */
    protected function _getCaptchaString(\Magento\App\RequestInterface $request, $formId)
    {
        $captchaParams = $request->getPost(\Magento\Captcha\Helper\Data::INPUT_NAME_FIELD_VALUE);
        return isset($captchaParams[$formId]) ? $captchaParams[$formId] : '';
    }

    /**
     * Get resource model
     *
     * @return \Magento\Captcha\Model\Resource\Log
     */
    protected function _getResourceModel()
    {
        return $this->_resLogFactory->create();
    }
}
