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
 * @package     Magento_Customer
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Customer\Controller;
use Magento\App\RequestInterface;

/**
 * Customer account controller
 */
class Account extends \Magento\App\Action\Action
{
    /**
     * List of actions that are allowed for not authorized users
     *
     * @var array
     */
    protected $_openActions = array(
        'create',
        'login',
        'logoutsuccess',
        'forgotpassword',
        'forgotpasswordpost',
        'resetpassword',
        'resetpasswordpost',
        'confirm',
        'confirmation',
        'createpassword',
        'createpost',
        'loginpost'
    );

    /**
     * Core registry
     *
     * @var \Magento\Core\Model\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Core\Model\UrlFactory
     */
    protected $_urlFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \Magento\Customer\Model\FormFactory
     */
    protected $_formFactory;

    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $_addressFactory;

    /**
     * Magento string lib
     *
     * @var \Magento\Stdlib\String
     */
    protected $string;

    /**
     * @var \Magento\Core\App\Action\FormKeyValidator
     */
    protected $_formKeyValidator;

    /**
     * @var \Magento\Escaper
     */
    protected $escaper;

    /**
     * @param \Magento\App\Action\Context $context
     * @param \Magento\Core\Model\Registry $coreRegistry
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Core\Model\UrlFactory $urlFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Model\FormFactory $formFactory
     * @param \Magento\Customer\Model\AddressFactory $addressFactory
     * @param \Magento\Stdlib\String $string
     * @param \Magento\Core\App\Action\FormKeyValidator $formKeyValidator
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Escaper $escaper
     */
    public function __construct(
        \Magento\App\Action\Context $context,
        \Magento\Core\Model\Registry $coreRegistry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Core\Model\UrlFactory $urlFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\FormFactory $formFactory,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Stdlib\String $string,
        \Magento\Core\App\Action\FormKeyValidator $formKeyValidator,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Escaper $escaper
    ) {
        $this->_storeManager = $storeManager;
        $this->_coreRegistry = $coreRegistry;
        $this->_customerSession = $customerSession;
        $this->_urlFactory = $urlFactory;
        $this->_customerFactory = $customerFactory;
        $this->_formFactory = $formFactory;
        $this->_addressFactory = $addressFactory;
        $this->string = $string;
        $this->_formKeyValidator = $formKeyValidator;
        $this->escaper = $escaper;
        parent::__construct($context);
    }

    /**
     * Retrieve customer session model object
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getSession()
    {
        return $this->_customerSession;
    }

    /**
     * Get list of actions that are allowed for not authorized users
     *
     * @return array
     */
    protected function _getAllowedActions()
    {
        return $this->_openActions;
    }

    /**
     * Dispatch request
     *
     * @param RequestInterface $request
     * @return \Magento\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->_objectManager->get('Magento\App\State')->isInstalled()) {
            parent::dispatch($request);
        }

        if (!$this->getRequest()->isDispatched()) {
            parent::dispatch($request);
        }

        $action = $this->getRequest()->getActionName();
        $pattern = '/^(' . implode('|', $this->_getAllowedActions()) . ')$/i';

        if (!preg_match($pattern, $action)) {
            if (!$this->_getSession()->authenticate($this)) {
                $this->_actionFlag->set('', 'no-dispatch', true);
            }
        } else {
            $this->_getSession()->setNoReferer(true);
        }
        $result = parent::dispatch($request);
        $this->_getSession()->unsNoReferer(false);
        return $result;
    }

    /**
     * Default customer account page
     */
    public function indexAction()
    {
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->getLayout()->getBlock('head')->setTitle(__('My Account'));
        $this->_view->renderLayout();
    }

    /**
     * Customer login form page
     */
    public function loginAction()
    {
        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }
        $this->getResponse()->setHeader('Login-Required', 'true');
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }

    /**
     * Login post action
     */
    public function loginPostAction()
    {
        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }

        if ($this->getRequest()->isPost()) {
            $login = $this->getRequest()->getPost('login');
            if (!empty($login['username']) && !empty($login['password'])) {
                try {
                    $this->_getSession()->login($login['username'], $login['password']);
                    if ($this->_getSession()->getCustomer()->getIsJustConfirmed()) {
                        $this->_welcomeCustomer($this->_getSession()->getCustomer(), true);
                    }
                } catch (\Magento\Core\Exception $e) {
                    switch ($e->getCode()) {
                        case \Magento\Customer\Model\Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                            $value = $this->_objectManager->get('Magento\Customer\Helper\Data')
                                ->getEmailConfirmationUrl($login['username']);
                            $message = __('This account is not confirmed.'
                                    . ' <a href="%1">Click here</a> to resend confirmation email.', $value);
                            break;
                        case \Magento\Customer\Model\Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                            $message = $e->getMessage();
                            break;
                        default:
                            $message = $e->getMessage();
                            break;
                    }
                    $this->messageManager->addError($message);
                    $this->_getSession()->setUsername($login['username']);
                } catch (\Exception $e) {
                    // $this->_objectManager->get('Magento\Logger')->logException($e); // PA DSS violation: this exception log can disclose customer password
                }
            } else {
                $this->messageManager->addError(__('Login and password are required.'));
            }
        }

        $this->_loginPostRedirect();
    }

    /**
     * Define target URL and redirect customer after logging in
     */
    protected function _loginPostRedirect()
    {
        $lastCustomerId = $this->_getSession()->getLastCustomerId();
        if (isset($lastCustomerId) && $this->_getSession()->isLoggedIn() && $lastCustomerId != $this->_getSession()->getId()) {
            $this->_getSession()->unsBeforeAuthUrl()
                ->setLastCustomerId($this->_getSession()->getId());
        }
        if (!$this->_getSession()->getBeforeAuthUrl()
            || $this->_getSession()->getBeforeAuthUrl() == $this->_storeManager->getStore()->getBaseUrl()
        ) {
            // Set default URL to redirect customer to
            $this->_getSession()->setBeforeAuthUrl($this->_objectManager->get('Magento\Customer\Helper\Data')->getAccountUrl());
            // Redirect customer to the last page visited after logging in
            if ($this->_getSession()->isLoggedIn()) {
                if (!$this->_objectManager->get('Magento\Core\Model\Store\Config')->getConfigFlag(
                    \Magento\Customer\Helper\Data::XML_PATH_CUSTOMER_STARTUP_REDIRECT_TO_DASHBOARD
                )) {
                    $referer = $this->getRequest()->getParam(\Magento\Customer\Helper\Data::REFERER_QUERY_PARAM_NAME);
                    if ($referer) {
                        $referer = $this->_objectManager->get('Magento\Core\Helper\Data')->urlDecode($referer);
                        if ($this->_url->isInternal($referer)) {
                            $this->_getSession()->setBeforeAuthUrl($referer);
                        }
                    }
                } elseif ($this->_getSession()->getAfterAuthUrl()) {
                    $this->_getSession()->setBeforeAuthUrl($this->_getSession()->getAfterAuthUrl(true));
                }
            } else {
                $this->_getSession()->setBeforeAuthUrl($this->_objectManager->get('Magento\Customer\Helper\Data')->getLoginUrl());
            }
        } elseif ($this->_getSession()->getBeforeAuthUrl() == $this->_objectManager->get('Magento\Customer\Helper\Data')->getLogoutUrl()) {
            $this->_getSession()->setBeforeAuthUrl($this->_objectManager->get('Magento\Customer\Helper\Data')->getDashboardUrl());
        } else {
            if (!$this->_getSession()->getAfterAuthUrl()) {
                $this->_getSession()->setAfterAuthUrl($this->_getSession()->getBeforeAuthUrl());
            }
            if ($this->_getSession()->isLoggedIn()) {
                $this->_getSession()->setBeforeAuthUrl($this->_getSession()->getAfterAuthUrl(true));
            }
        }
        $this->getResponse()->setRedirect($this->_getSession()->getBeforeAuthUrl(true));
    }

    /**
     * Customer logout action
     */
    public function logoutAction()
    {
        $lastCustomerId = $this->_getSession()->getId();
        $this->_getSession()->logout()
            ->setBeforeAuthUrl($this->_redirect->getRefererUrl())
            ->setLastCustomerId($lastCustomerId);

        $this->_redirect('*/*/logoutSuccess');
    }

    /**
     * Logout success page
     */
    public function logoutSuccessAction()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }

    /**
     * Customer register form page
     */
    public function createAction()
    {
        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*');
            return;
        }

        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }

    /**
     * Create customer account action
     */
    public function createPostAction()
    {
        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }

        if (!$this->getRequest()->isPost()) {
            $url = $this->_createUrl()->getUrl('*/*/create', array('_secure' => true));
            $this->getResponse()->setRedirect($this->_redirect->error($url));
            return;
        }

        try {
            $customer = $this->_extractCustomer();
            $address = $this->_extractAddress($customer);
            $this->_validateCustomer($customer, $address);

            $customer->save()->setOrigData();
            $this->_eventManager->dispatch('customer_register_success',
                array('account_controller' => $this, 'customer' => $customer)
            );

            $newLinkToken = $this->_objectManager->get('Magento\Customer\Helper\Data')
                ->generateResetPasswordLinkToken();
            $customer->changeResetPasswordLinkToken($newLinkToken);

            if ($customer->isConfirmationRequired()) {
                $customer->sendNewAccountEmail(
                    'confirmation',
                    $this->_getSession()->getBeforeAuthUrl(),
                    $this->_storeManager->getStore()->getId()
                );
                $email = $this->_objectManager->get('Magento\Customer\Helper\Data')->getEmailConfirmationUrl($customer->getEmail());
                $this->messageManager->addSuccess(
                    __('Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%1">click here</a>.', $email)
                );
                $url = $this->_createUrl()->getUrl('*/*/index', array('_secure' => true));
                $this->getResponse()->setRedirect($this->_redirect->success($url));
            } else {
                $this->_getSession()->setCustomerAsLoggedIn($customer);
                $url = $this->_welcomeCustomer($customer);
                $this->getResponse()->setRedirect($this->_redirect->success($url));
            }
            return;
        } catch (\Magento\Core\Exception $e) {
            if ($e->getCode() === \Magento\Customer\Model\Customer::EXCEPTION_EMAIL_EXISTS) {
                $url = $this->_createUrl()->getUrl('customer/account/forgotpassword');
                $message = __('There is already an account with this email address. If you are sure that it is your email address, <a href="%1">click here</a> to get your password and access your account.', $url);
            } else {
                $message = $e->getMessage();
            }
            $this->messageManager->addError($message);
        } catch (\Magento\Validator\ValidatorException $e) {
            foreach ($e->getMessages() as $messages) {
                foreach ($messages as $message) {
                    $this->messageManager->addError($this->escaper->escapeHtml($message));
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Cannot save the customer.'));
        }

        $this->_getSession()->setCustomerFormData($this->getRequest()->getPost());
        $defaultUrl = $this->_createUrl()->getUrl('*/*/create', array('_secure' => true));
        $this->getResponse()->setRedirect($this->_redirect->error($defaultUrl));
    }

    /**
     * Do validation of customer and its address using validate methods in models
     *
     * @param \Magento\Customer\Model\Customer $customer
     * @param \Magento\Customer\Model\Address|null $address
     * @throws \Magento\Validator\ValidatorException
     */
    protected function _validateCustomer($customer, $address = null)
    {
        $errors = array();
        if ($address) {
            $addressErrors = $address->validate();
            if (is_array($addressErrors)) {
                $errors = array_merge($errors, $addressErrors);
            }
        }
        $customerErrors = $customer->validate();
        if (is_array($customerErrors)) {
            $errors = array_merge($errors, $customerErrors);
        }
        if (count($errors) > 0) {
            throw new \Magento\Validator\ValidatorException(array($errors));
        }
        // Empty password confirmation data (it is needed only for validation purposes and is not meant to be stored)
        $customer->setConfirmation(null);
    }

    /**
     * Add address to customer during create account
     *
     * @param \Magento\Customer\Model\Customer $customer
     * @return \Magento\Customer\Model\Address|null
     */
    protected function _extractAddress($customer)
    {
        if (!$this->getRequest()->getPost('create_address')) {
            return null;
        }
        /* @var \Magento\Customer\Model\Address $address */
        $address = $this->_createAddress();
        /* @var \Magento\Customer\Model\Form $addressForm */
        $addressForm = $this->_createForm();
        $addressForm->setFormCode('customer_register_address')
            ->setEntity($address);

        $addressData = $addressForm->extractData($this->getRequest(), 'address', false);
        $address->setId(null)
            ->setIsDefaultBilling($this->getRequest()->getParam('default_billing', false))
            ->setIsDefaultShipping($this->getRequest()->getParam('default_shipping', false));
        $addressForm->compactData($addressData);
        $customer->addAddress($address);
        return $address;
    }

    /**
     * Extract customer entity from request
     *
     * @return \Magento\Customer\Model\Customer
     */
    protected function _extractCustomer()
    {
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = $this->_coreRegistry->registry('current_customer');
        if (!$customer) {
            $customer = $this->_createCustomer()->setId(null);
        }
        /* @var \Magento\Customer\Model\Form $customerForm */
        $customerForm = $this->_createForm();
        $customerForm->setFormCode('customer_account_create')
            ->setEntity($customer);

        $customerData = $customerForm->extractData($this->getRequest());
        // Initialize customer group id
        $customer->getGroupId();
        $customerForm->compactData($customerData);
        $customer->setPassword($this->getRequest()->getPost('password'));
        $customer->setConfirmation($this->getRequest()->getPost('confirmation'));
        if ($this->getRequest()->getParam('is_subscribed', false)) {
            $customer->setIsSubscribed(1);
        }
        return $customer;
    }

    /**
     * Add welcome message and send new account email.
     * Returns success URL
     *
     * @param \Magento\Customer\Model\Customer $customer
     * @param bool $isJustConfirmed
     * @return string
     */
    protected function _welcomeCustomer(\Magento\Customer\Model\Customer $customer, $isJustConfirmed = false)
    {
        $this->messageManager->addSuccess(
            __('Thank you for registering with %1.', $this->_storeManager->getStore()->getFrontendName())
        );
        if ($this->_isVatValidationEnabled()) {
            // Show corresponding VAT message to customer
            $configAddressType = $this->_objectManager->get('Magento\Customer\Helper\Address')->getTaxCalculationAddressType();
            $editAddersUrl = $this->_createUrl()->getUrl('customer/address/edit');
            switch ($configAddressType) {
                case \Magento\Customer\Model\Address\AbstractAddress::TYPE_SHIPPING:
                    $userPrompt = __('If you are a registered VAT customer, please click <a href="%1">here</a> to enter you shipping address for proper VAT calculation', $editAddersUrl);
                    break;
                default:
                    $userPrompt = __('If you are a registered VAT customer, please click <a href="%1">here</a> to enter you billing address for proper VAT calculation', $editAddersUrl);
                    break;
            }
            $this->messageManager->addSuccess($userPrompt);
        }

        $customer->sendNewAccountEmail(
            $isJustConfirmed ? 'confirmed' : 'registered',
            '',
            $this->_storeManager->getStore()->getId()
        );

        $successUrl = $this->_createUrl()->getUrl('*/*/index', array('_secure' => true));
        if (!$this->_objectManager->get('Magento\Core\Model\Store\Config')->getConfigFlag(\Magento\Customer\Helper\Data::XML_PATH_CUSTOMER_STARTUP_REDIRECT_TO_DASHBOARD)
            && $this->_getSession()->getBeforeAuthUrl()
        ) {
            $successUrl = $this->_getSession()->getBeforeAuthUrl(true);
        }
        return $successUrl;
    }

    /**
     * load customer by id (try/catch in case if it throws exceptions)
     *
     * @param $customerId
     * @return \Magento\Customer\Model\Customer
     * @throws \Exception
     */
    protected function _loadCustomerById($customerId)
    {
        try {
            /** @var \Magento\Customer\Model\Customer $customer */
            $customer = $this->_createCustomer()->load($customerId);
            if ((!$customer) || (!$customer->getId())) {
                throw new \Exception('Failed to load customer by id.');
            }
        } catch (\Exception $e) {
            throw new \Exception(__('Wrong customer account specified.'));
        }
        return $customer;
    }

    /**
     * @param \Magento\Customer\Model\Customer $customer
     * @throws \Exception
     */
    protected function _activateCustomer($customer)
    {
        try {
            $customer->setConfirmation(null);
            $customer->save();
        } catch (\Exception $e) {
            throw new \Exception(__('Failed to confirm customer account.'));
        }
    }

    /**
     * @param \Magento\Customer\Model\Customer $customer
     * @param mixed $key
     * @return bool|null
     * @throws \Exception
     */
    protected function _checkCustomerActive($customer, $key)
    {
        $backUrl = $this->getRequest()->getParam('back_url', false);

        // check if it is inactive
        if ($customer->getConfirmation()) {
            if ($customer->getConfirmation() !== $key) {
                throw new \Exception(__('Wrong confirmation key.'));
            }
            $this->_activateCustomer($customer);

            // log in and send greeting email, then die happy
            $this->_getSession()->setCustomerAsLoggedIn($customer);
            $successUrl = $this->_welcomeCustomer($customer, true);
            $url = $backUrl ? $backUrl : $successUrl;
            $this->getResponse()->setRedirect($this->_redirect->success($url));
            return true;
        }
    }

    /**
     * Confirm customer account by id and confirmation key
     */
    public function confirmAction()
    {
        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }
        try {
            $customerId = $this->getRequest()->getParam('id', false);
            $key     = $this->getRequest()->getParam('key', false);
            if (empty($customerId) || empty($key)) {
                throw new \Exception(__('Bad request.'));
            }

            $customer = $this->_loadCustomerById($customerId);
            if (true === $this->_checkCustomerActive($customer, $key)) {
                return;
            }

            // die happy
            $url = $this->_createUrl()->getUrl('*/*/index', array('_secure' => true));
            $this->getResponse()->setRedirect($this->_redirect->success($url));
            return;
        } catch (\Exception $e) {
            // die unhappy
            $this->messageManager->addError($e->getMessage());
            $url = $this->_createUrl()->getUrl('*/*/index', array('_secure' => true));
            $this->getResponse()->setRedirect($this->_redirect->error($url));
            return;
        }
    }

    /**
     * @param \Magento\Customer\Model\Customer $customer
     * @param string $email
     */
    protected function _confirmByEmail($customer, $email)
    {
        try {
            $customer->setWebsiteId($this->_storeManager->getStore()->getWebsiteId())->loadByEmail($email);
            if (!$customer->getId()) {
                throw new \Exception('');
            }
            if ($customer->getConfirmation()) {
                $customer->sendNewAccountEmail('confirmation', '', $this->_storeManager->getStore()->getId());
                $this->messageManager->addSuccess(__('Please, check your email for confirmation key.'));
            } else {
                $this->messageManager->addSuccess(__('This email does not require confirmation.'));
            }
            $this->_getSession()->setUsername($email);
            $url = $this->_createUrl()->getUrl('*/*/index', array('_secure' => true));
            $this->getResponse()->setRedirect($this->_redirect->success($url));
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Wrong email.'));
            $url = $this->_createUrl()->getUrl('*/*/*', array('email' => $email, '_secure' => true));
            $this->getResponse()->setRedirect($this->_redirect->error($url));
        }
    }

    /**
     * Send confirmation link to specified email
     */
    public function confirmationAction()
    {
        $customer = $this->_createCustomer();
        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }

        // try to confirm by email
        $email = $this->getRequest()->getPost('email');
        if ($email) {
            $this->_confirmByEmail($customer, $email);
            return;
        }

        // output form
        $this->_view->loadLayout();

        $this->_view->getLayout()->getBlock('accountConfirmation')
            ->setEmail($this->getRequest()->getParam('email', $email));

        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }

    /**
     * Forgot customer password page
     */
    public function forgotPasswordAction()
    {
        $this->_view->loadLayout();

        $this->_view->getLayout()->getBlock('forgotPassword')->setEmailValue(
            $this->_getSession()->getForgottenEmail()
        );
        $this->_getSession()->unsForgottenEmail();

        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }

    /**
     * Forgot customer password action
     */
    public function forgotPasswordPostAction()
    {
        $email = (string)$this->getRequest()->getPost('email');
        if ($email) {
            if (!\Zend_Validate::is($email, 'EmailAddress')) {
                $this->_getSession()->setForgottenEmail($email);
                $this->messageManager->addError(__('Please correct the email address.'));
                $this->_redirect('*/*/forgotpassword');
                return;
            }

            /** @var $customer \Magento\Customer\Model\Customer */
            $customer = $this->_createCustomer()
                ->setWebsiteId($this->_storeManager->getStore()->getWebsiteId())
                ->loadByEmail($email);

            if ($customer->getId()) {
                try {
                    $newPasswordToken = $this->_objectManager->get('Magento\Customer\Helper\Data')
                        ->generateResetPasswordLinkToken();
                    $customer->changeResetPasswordLinkToken($newPasswordToken);
                    $customer->sendPasswordResetConfirmationEmail();
                } catch (\Exception $exception) {
                    $this->messageManager->addError($exception->getMessage());
                    $this->_redirect('*/*/forgotpassword');
                    return;
                }
            }
            $email = $this->_objectManager->get('Magento\Escaper')->escapeHtml($email);
            $this->messageManager->addSuccess(
                __('If there is an account associated with %1 you will receive an email with a link to reset your password.', $email)
            );
            $this->_redirect('*/*/');
            return;
        } else {
            $this->messageManager->addError(__('Please enter your email.'));
            $this->_redirect('*/*/forgotpassword');
            return;
        }
    }

    /**
     * Display reset forgotten password form
     *
     * User is redirected on this action when he clicks on the corresponding link in password reset confirmation email
     *
     */
    public function resetPasswordAction()
    {
        $this->_forward('createPassword');
    }

    /**
     * Resetting password handler
     */
    public function createPasswordAction()
    {
        $resetPasswordToken = (string)$this->getRequest()->getParam('token');
        $customerId = (int)$this->getRequest()->getParam('id');
        try {
            $this->_validateResetPasswordLinkToken($customerId, $resetPasswordToken);
            $this->_view->loadLayout();
            // Pass received parameters to the reset forgotten password form
            $this->_view->getLayout()->getBlock('resetPassword')
                ->setCustomerId($customerId)
                ->setResetPasswordLinkToken($resetPasswordToken);
            $this->_view->renderLayout();
        } catch (\Exception $exception) {
            $this->messageManager->addError(
                __('Your password reset link has expired.')
            );
            $this->_redirect('*/*/forgotpassword');
        }
    }

    /**
     * Reset forgotten password
     *
     * Used to handle data received from reset forgotten password form
     *
     */
    public function resetPasswordPostAction()
    {
        $resetPasswordToken = (string)$this->getRequest()->getQuery('token');
        $customerId = (int)$this->getRequest()->getQuery('id');
        $password = (string)$this->getRequest()->getPost('password');
        $passwordConfirmation = (string)$this->getRequest()->getPost('confirmation');

        try {
            $this->_validateResetPasswordLinkToken($customerId, $resetPasswordToken);
        } catch (\Exception $exception) {
            $this->messageManager->addError(
                __('Your password reset link has expired.')
            );
            $this->_redirect('*/*/');
            return;
        }

        $errorMessages = array();
        if (iconv_strlen($password) <= 0) {
            $errorMessages[] = __('New password field cannot be empty.');
        }
        /** @var $customer \Magento\Customer\Model\Customer */
        $customer = $this->_createCustomer()->load($customerId);

        $customer->setPassword($password);
        $customer->setConfirmation($passwordConfirmation);
        $validationErrors = $customer->validate();
        if (is_array($validationErrors)) {
            $errorMessages = array_merge($errorMessages, $validationErrors);
        }

        if (!empty($errorMessages)) {
            $this->_getSession()->setCustomerFormData($this->getRequest()->getPost());
            foreach ($errorMessages as $errorMessage) {
                $this->messageManager->addError($errorMessage);
            }
            $this->_redirect('*/*/createpassword', array(
                'id' => $customerId,
                'token' => $resetPasswordToken
            ));
            return;
        }

        try {
            // Empty current reset password token i.e. invalidate it
            $customer->setRpToken(null);
            $customer->setRpTokenCreatedAt(null);
            $customer->setConfirmation(null);
            $customer->save();
            $this->messageManager->addSuccess(
                __('Your password has been updated.')
            );
            $this->_redirect('*/*/login');
        } catch (\Exception $exception) {
            $this->messageManager->addException($exception, __('Cannot save a new password.'));
            $this->_redirect('*/*/createpassword', array(
                'id' => $customerId,
                'token' => $resetPasswordToken
            ));
            return;
        }
    }

    /**
     * Check if password reset token is valid
     *
     * @param int $customerId
     * @param string $resetPasswordLinkToken
     * @throws \Magento\Core\Exception
     */
    protected function _validateResetPasswordLinkToken($customerId, $resetPasswordLinkToken)
    {
        if (!is_int($customerId)
            || !is_string($resetPasswordLinkToken)
            || empty($resetPasswordLinkToken)
            || empty($customerId)
            || $customerId < 0
        ) {
            throw new \Magento\Core\Exception(__('Invalid password reset token.'));
        }

        /** @var $customer \Magento\Customer\Model\Customer */
        $customer = $this->_createCustomer()->load($customerId);
        if (!$customer || !$customer->getId()) {
            throw new \Magento\Core\Exception(__('Wrong customer account specified.'));
        }

        $customerToken = $customer->getRpToken();
        if (strcmp($customerToken, $resetPasswordLinkToken) !== 0 || $customer->isResetPasswordLinkTokenExpired()) {
            throw new \Magento\Core\Exception(__('Your password reset link has expired.'));
        }
    }

    /**
     * Forgot customer account information page
     */
    public function editAction()
    {
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();

        $block = $this->_view->getLayout()->getBlock('customer_edit');
        if ($block) {
            $block->setRefererUrl($this->_redirect->getRefererUrl());
        }
        $data = $this->_getSession()->getCustomerFormData(true);
        $customer = $this->_getSession()->getCustomer();
        if (!empty($data)) {
            $customer->addData($data);
        }
        if ($this->getRequest()->getParam('changepass') == 1) {
            $customer->setChangePassword(1);
        }

        $this->_view->getLayout()->getBlock('head')->setTitle(__('Account Information'));
        $this->_view->getLayout()->getBlock('messages')->setEscapeMessageFlag(true);
        $this->_view->renderLayout();
    }

    /**
     * Change customer password action
     */
    public function editPostAction()
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            $this->_redirect('*/*/edit');
            return;
        }

        if ($this->getRequest()->isPost()) {
            /** @var $customer \Magento\Customer\Model\Customer */
            $customer = $this->_getSession()->getCustomer();

            /** @var $customerForm \Magento\Customer\Model\Form */
            $customerForm = $this->_createForm();
            $customerForm->setFormCode('customer_account_edit')
                ->setEntity($customer);

            $customerData = $customerForm->extractData($this->getRequest());

            $customerForm->compactData($customerData);
            $errors = array();

            // If password change was requested then add it to common validation scheme
            if ($this->getRequest()->getParam('change_password')) {
                $currPass   = $this->getRequest()->getPost('current_password');
                $newPass    = $this->getRequest()->getPost('password');
                $confPass   = $this->getRequest()->getPost('confirmation');

                $oldPass = $this->_getSession()->getCustomer()->getPasswordHash();
                if ($this->string->strpos($oldPass, ':')) {
                    list(, $salt) = explode(':', $oldPass);
                } else {
                    $salt = false;
                }

                if ($customer->hashPassword($currPass, $salt) == $oldPass) {
                    if (strlen($newPass)) {
                        /**
                         * Set entered password and its confirmation - they
                         * will be validated later to match each other and be of right length
                         */
                        $customer->setPassword($newPass);
                        $customer->setConfirmation($confPass);
                    } else {
                        $errors[] = __('New password field cannot be empty.');
                    }
                } else {
                    $errors[] = __('Invalid current password');
                }
            }

            // Validate account and compose list of errors if any
            $customerErrors = $customer->validate();
            if (is_array($customerErrors)) {
                $errors = array_merge($errors, $customerErrors);
            }

            if (!empty($errors)) {
                $this->_getSession()->setCustomerFormData($this->getRequest()->getPost());
                foreach ($errors as $message) {
                    $this->messageManager->addError($message);
                }
                $this->_redirect('*/*/edit');
                return;
            }

            try {
                $customer->setConfirmation(null);
                $customer->save();

                $customer->sendPasswordResetNotificationEmail('reset_frontend');

                $this->_getSession()->setCustomer($customer);
                $this->messageManager->addSuccess(__('The account information has been saved.'));

                $this->_redirect('customer/account');
                return;
            } catch (\Magento\Core\Exception $e) {
                $this->_getSession()->setCustomerFormData($this->getRequest()->getPost());
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->_getSession()->setCustomerFormData($this->getRequest()->getPost());
                $this->messageManager->addException($e, __('Cannot save the customer.'));
            }
        }

        $this->_redirect('*/*/edit');
    }

    /**
     * Check whether VAT ID validation is enabled
     *
     * @param \Magento\Core\Model\Store|string|int $store
     * @return bool
     */
    protected function _isVatValidationEnabled($store = null)
    {
        return $this->_objectManager->get('Magento\Customer\Helper\Address')->isVatValidationEnabled($store);
    }

    /**
     * @return \Magento\Core\Model\Url
     */
    protected function _createUrl()
    {
        return $this->_urlFactory->create();
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    protected function _createCustomer()
    {
        return $this->_customerFactory->create();
    }

    /**
     * @return \Magento\Customer\Model\Form
     */
    protected function _createForm()
    {
        return $this->_formFactory->create();
    }

    /**
     * @return \Magento\Customer\Model\Address
     */
    protected function _createAddress()
    {
        return $this->_addressFactory->create();
    }
}
