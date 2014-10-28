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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Captcha\Model;

/**
 * Captcha Observer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
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
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlManager;

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
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Checkout\Model\Type\Onepage
     */
    protected $_typeOnepage;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_session;

    /**
     * @var \Magento\Captcha\Model\Resource\LogFactory
     */
    protected $_resLogFactory;

    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $_actionFlag;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    /**
     * @param Resource\LogFactory $resLogFactory
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Checkout\Model\Type\Onepage $typeOnepage
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Customer\Helper\Data $customerData
     * @param \Magento\Captcha\Helper\Data $helper
     * @param \Magento\Framework\UrlInterface $urlManager
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\ActionFlag $actionFlag
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Resource\LogFactory $resLogFactory,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Checkout\Model\Type\Onepage $typeOnepage,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Customer\Helper\Data $customerData,
        \Magento\Captcha\Helper\Data $helper,
        \Magento\Framework\UrlInterface $urlManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Response\RedirectInterface $redirect
    ) {
        $this->_resLogFactory = $resLogFactory;
        $this->_session = $session;
        $this->_typeOnepage = $typeOnepage;
        $this->_coreData = $coreData;
        $this->_customerData = $customerData;
        $this->_helper = $helper;
        $this->_urlManager = $urlManager;
        $this->_request = $request;
        $this->_actionFlag = $actionFlag;
        $this->messageManager = $messageManager;
        $this->redirect = $redirect;
    }

    /**
     * Check Captcha On Forgot Password Page
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function checkForgotpassword($observer)
    {
        $formId = 'user_forgotpassword';
        $captchaModel = $this->_helper->getCaptcha($formId);
        if ($captchaModel->isRequired()) {
            /** @var \Magento\Framework\App\Action\Action $controller */
            $controller = $observer->getControllerAction();
            if (!$captchaModel->isCorrect($this->_getCaptchaString($controller->getRequest(), $formId))) {
                $this->messageManager->addError(__('Incorrect CAPTCHA'));
                $this->_actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                $this->redirect->redirect($controller->getResponse(), '*/*/forgotpassword');
            }
        }
        return $this;
    }

    /**
     * Check CAPTCHA on Contact Us page
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function checkContactUsForm($observer)
    {
        $formId = 'contact_us';
        $captcha = $this->_helper->getCaptcha($formId);
        if ($captcha->isRequired()) {
            /** @var \Magento\Framework\App\Action\Action $controller */
            $controller = $observer->getControllerAction();
            if (!$captcha->isCorrect($this->_getCaptchaString($controller->getRequest(), $formId))) {
                $this->messageManager->addError(__('Incorrect CAPTCHA.'));
                $this->_actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                $this->redirect->redirect($controller->getResponse(), 'contact/index/index');
            }
        }
    }

    /**
     * Check Captcha On User Login Page
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
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
                $this->messageManager->addError(__('Incorrect CAPTCHA'));
                $this->_actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                $this->_session->setUsername($login);
                $beforeUrl = $this->_session->getBeforeAuthUrl();
                $url = $beforeUrl ? $beforeUrl : $this->_customerData->getLoginUrl();
                $controller->getResponse()->setRedirect($url);
            }
        }
        $captchaModel->logAttempt($login);
        return $this;
    }

    /**
     * Check Captcha On Register User Page
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function checkUserCreate($observer)
    {
        $formId = 'user_create';
        $captchaModel = $this->_helper->getCaptcha($formId);
        if ($captchaModel->isRequired()) {
            /** @var \Magento\Framework\App\Action\Action $controller */
            $controller = $observer->getControllerAction();
            if (!$captchaModel->isCorrect($this->_getCaptchaString($controller->getRequest(), $formId))) {
                $this->messageManager->addError(__('Incorrect CAPTCHA'));
                $this->_actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                $this->_session->setCustomerFormData($controller->getRequest()->getPost());
                $url = $this->_urlManager->getUrl('*/*/create', array('_nosecret' => true));
                $controller->getResponse()->setRedirect($this->redirect->error($url));
            }
        }
        return $this;
    }

    /**
     * Check Captcha On Checkout as Guest Page
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
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
                    $this->_actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                    $result = array('error' => 1, 'message' => __('Incorrect CAPTCHA'));
                    $controller->getResponse()->representJson($this->_coreData->jsonEncode($result));
                }
            }
        }
        return $this;
    }

    /**
     * Check Captcha On Checkout Register Page
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
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
                    $this->_actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                    $result = array('error' => 1, 'message' => __('Incorrect CAPTCHA'));
                    $controller->getResponse()->representJson($this->_coreData->jsonEncode($result));
                }
            }
        }
        return $this;
    }

    /**
     * Check Captcha On User Login Backend Page
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Backend\Model\Auth\Plugin\Exception
     * @return $this
     */
    public function checkUserLoginBackend($observer)
    {
        $formId = 'backend_login';
        $captchaModel = $this->_helper->getCaptcha($formId);
        $login = $observer->getEvent()->getUsername();
        if ($captchaModel->isRequired($login)) {
            if (!$captchaModel->isCorrect($this->_getCaptchaString($this->_request, $formId))) {
                $captchaModel->logAttempt($login);
                throw new \Magento\Backend\Model\Auth\Plugin\Exception(__('Incorrect CAPTCHA.'));
            }
        }
        $captchaModel->logAttempt($login);
        return $this;
    }

    /**
     * Check Captcha On User Login Backend Page
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function checkUserForgotPasswordBackend($observer)
    {
        $formId = 'backend_forgotpassword';
        $captchaModel = $this->_helper->getCaptcha($formId);
        $controller = $observer->getControllerAction();
        $email = (string)$observer->getControllerAction()->getRequest()->getParam('email');
        $params = $observer->getControllerAction()->getRequest()->getParams();

        if (!empty($email) && !empty($params)) {
            if ($captchaModel->isRequired()) {
                if (!$captchaModel->isCorrect($this->_getCaptchaString($controller->getRequest(), $formId))) {
                    $this->_session->setEmail((string)$controller->getRequest()->getPost('email'));
                    $this->_actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                    $this->messageManager->addError(__('Incorrect CAPTCHA'));
                    $controller->getResponse()->setRedirect(
                        $controller->getUrl('*/*/forgotpassword', array('_nosecret' => true))
                    );
                }
            }
        }
        return $this;
    }

    /**
     * Reset Attempts For Frontend
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magento\Captcha\Model\Observer
     */
    public function resetAttemptForFrontend($observer)
    {
        return $this->_getResourceModel()->deleteUserAttempts($observer->getModel()->getEmail());
    }

    /**
     * Reset Attempts For Backend
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magento\Captcha\Model\Observer
     */
    public function resetAttemptForBackend($observer)
    {
        return $this->_getResourceModel()->deleteUserAttempts($observer->getUser()->getUsername());
    }

    /**
     * Get Captcha String
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param string $formId
     * @return string
     */
    protected function _getCaptchaString(\Magento\Framework\App\RequestInterface $request, $formId)
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
