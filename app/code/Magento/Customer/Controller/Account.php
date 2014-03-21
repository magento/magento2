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
namespace Magento\Customer\Controller;

use Magento\App\RequestInterface;
use Magento\Customer\Service\V1\CustomerAccountServiceInterface;
use Magento\Customer\Service\V1\CustomerGroupServiceInterface;
use Magento\Customer\Service\V1\Data\Customer;
use Magento\Customer\Service\V1\Data\CustomerDetails;
use Magento\Exception\AuthenticationException;
use Magento\Exception\InputException;
use Magento\Exception\NoSuchEntityException;
use Magento\Exception\StateException;

/**
 * Customer account controller
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Account extends \Magento\App\Action\Action
{
    /**
     * List of actions that are allowed for not authorized users
     *
     * @var string[]
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

    /** @var \Magento\Customer\Model\Session */
    protected $_session;

    /** @var \Magento\Customer\Helper\Address */
    protected $_addressHelper;

    /** @var \Magento\Customer\Helper\Data */
    protected $_customerHelperData;

    /** @var \Magento\UrlFactory */
    protected $_urlFactory;

    /** @var \Magento\Customer\Model\Metadata\FormFactory */
    protected $_formFactory;

    /** @var \Magento\Stdlib\String */
    protected $string;

    /** @var \Magento\Core\App\Action\FormKeyValidator */
    protected $_formKeyValidator;

    /** @var \Magento\Newsletter\Model\SubscriberFactory */
    protected $_subscriberFactory;

    /** @var \Magento\Core\Model\StoreManagerInterface */
    protected $_storeManager;

    /** @var \Magento\Core\Model\Store\Config */
    protected $_storeConfig;

    /** @var \Magento\Core\Helper\Data */
    protected $coreHelperData;

    /** @var \Magento\Escaper */
    protected $escaper;

    /** @var \Magento\App\State */
    protected $appState;

    /** @var CustomerGroupServiceInterface */
    protected $_groupService;

    /** @var CustomerAccountServiceInterface  */
    protected $_customerAccountService;

    /** @var \Magento\Customer\Service\V1\Data\RegionBuilder */
    protected $_regionBuilder;

    /** @var \Magento\Customer\Service\V1\Data\AddressBuilder */
    protected $_addressBuilder;

    /** @var \Magento\Customer\Service\V1\Data\CustomerBuilder */
    protected $_customerBuilder;

    /** @var \Magento\Customer\Service\V1\Data\CustomerDetailsBuilder */
    protected $_customerDetailsBuilder;

    /**
     * @param \Magento\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Helper\Address $addressHelper
     * @param \Magento\Customer\Helper\Data $customerHelperData
     * @param \Magento\UrlFactory $urlFactory
     * @param \Magento\Customer\Model\Metadata\FormFactory $formFactory
     * @param \Magento\Stdlib\String $string
     * @param \Magento\Core\App\Action\FormKeyValidator $formKeyValidator
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Core\Model\Store\Config $storeConfig
     * @param \Magento\Core\Helper\Data $coreHelperData
     * @param \Magento\Escaper $escaper
     * @param \Magento\App\State $appState
     * @param \Magento\Customer\Service\V1\CustomerGroupServiceInterface $customerGroupService
     * @param \Magento\Customer\Service\V1\CustomerAccountServiceInterface $customerAccountService
     * @param \Magento\Customer\Service\V1\Data\RegionBuilder $regionBuilder
     * @param \Magento\Customer\Service\V1\Data\AddressBuilder $addressBuilder
     * @param \Magento\Customer\Service\V1\Data\CustomerBuilder $customerBuilder
     * @param \Magento\Customer\Service\V1\Data\CustomerDetailsBuilder $customerDetailsBuilder
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Helper\Address $addressHelper,
        \Magento\Customer\Helper\Data $customerHelperData,
        \Magento\UrlFactory $urlFactory,
        \Magento\Customer\Model\Metadata\FormFactory $formFactory,
        \Magento\Stdlib\String $string,
        \Magento\Core\App\Action\FormKeyValidator $formKeyValidator,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Core\Model\Store\Config $storeConfig,
        \Magento\Core\Helper\Data $coreHelperData,
        \Magento\Escaper $escaper,
        \Magento\App\State $appState,
        CustomerGroupServiceInterface $customerGroupService,
        CustomerAccountServiceInterface $customerAccountService,
        \Magento\Customer\Service\V1\Data\RegionBuilder $regionBuilder,
        \Magento\Customer\Service\V1\Data\AddressBuilder $addressBuilder,
        \Magento\Customer\Service\V1\Data\CustomerBuilder $customerBuilder,
        \Magento\Customer\Service\V1\Data\CustomerDetailsBuilder $customerDetailsBuilder
    ) {
        $this->_session = $customerSession;
        $this->_addressHelper = $addressHelper;
        $this->_customerHelperData = $customerHelperData;
        $this->_urlFactory = $urlFactory;
        $this->_formFactory = $formFactory;
        $this->string = $string;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_subscriberFactory = $subscriberFactory;
        $this->_storeManager = $storeManager;
        $this->_storeConfig = $storeConfig;
        $this->coreHelperData = $coreHelperData;
        $this->escaper = $escaper;
        $this->appState = $appState;
        $this->_groupService = $customerGroupService;
        $this->_customerAccountService = $customerAccountService;
        $this->_regionBuilder = $regionBuilder;
        $this->_addressBuilder = $addressBuilder;
        $this->_customerBuilder = $customerBuilder;
        $this->_customerDetailsBuilder = $customerDetailsBuilder;
        parent::__construct($context);
    }

    /**
     * Retrieve customer session model object
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getSession()
    {
        return $this->_session;
    }

    /**
     * Get list of actions that are allowed for not authorized users
     *
     * @return string[]
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
        if (!$this->appState->isInstalled()) {
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
     *
     * @return void
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
     *
     * @return void
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
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function loginPostAction()
    {
        if ($this->_getSession()->isLoggedIn() || !$this->_formKeyValidator->validate($this->getRequest())) {
            $this->_redirect('*/*/');
            return;
        }

        if ($this->getRequest()->isPost()) {
            $login = $this->getRequest()->getPost('login');
            if (!empty($login['username']) && !empty($login['password'])) {
                try {
                    $customer = $this->_customerAccountService->authenticate($login['username'], $login['password']);
                    $this->_getSession()->setCustomerDataAsLoggedIn($customer);
                    $this->_getSession()->regenerateId();
                } catch (AuthenticationException $e) {
                    switch ($e->getCode()) {
                        case AuthenticationException::EMAIL_NOT_CONFIRMED:
                            $value = $this->_customerHelperData->getEmailConfirmationUrl($login['username']);
                            $message = __(
                                'This account is not confirmed.' .
                                ' <a href="%1">Click here</a> to resend confirmation email.',
                                $value
                            );
                            break;
                        case AuthenticationException::INVALID_EMAIL_OR_PASSWORD:
                        default:
                            $message = __('Invalid login or password.');
                            break;
                    }
                    $this->messageManager->addError($message);
                    $this->_getSession()->setUsername($login['username']);
                } catch (\Exception $e) {
                    // PA DSS violation: this exception log can disclose customer password
                    // $this->_objectManager->get('Magento\Logger')->logException($e);
                    $this->messageManager->addError(__('There was an error validating the login and password.'));
                }
            } else {
                $this->messageManager->addError(__('Login and password are required.'));
            }
        }

        $this->_loginPostRedirect();
    }

    /**
     * Define target URL and redirect customer after logging in
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _loginPostRedirect()
    {
        $lastCustomerId = $this->_getSession()->getLastCustomerId();
        if (isset(
            $lastCustomerId
        ) && $this->_getSession()->isLoggedIn() && $lastCustomerId != $this->_getSession()->getId()
        ) {
            $this->_getSession()->unsBeforeAuthUrl()->setLastCustomerId($this->_getSession()->getId());
        }
        if (!$this->_getSession()->getBeforeAuthUrl() ||
            $this->_getSession()->getBeforeAuthUrl() == $this->_storeManager->getStore()->getBaseUrl()
        ) {
            // Set default URL to redirect customer to
            $this->_getSession()->setBeforeAuthUrl($this->_customerHelperData->getAccountUrl());
            // Redirect customer to the last page visited after logging in
            if ($this->_getSession()->isLoggedIn()) {
                if (!$this->_storeConfig->getConfigFlag(
                    \Magento\Customer\Helper\Data::XML_PATH_CUSTOMER_STARTUP_REDIRECT_TO_DASHBOARD
                )
                ) {
                    $referer = $this->getRequest()->getParam(\Magento\Customer\Helper\Data::REFERER_QUERY_PARAM_NAME);
                    if ($referer) {
                        $referer = $this->coreHelperData->urlDecode($referer);
                        if ($this->_url->isOwnOriginUrl()) {
                            $this->_getSession()->setBeforeAuthUrl($referer);
                        }
                    }
                } elseif ($this->_getSession()->getAfterAuthUrl()) {
                    $this->_getSession()->setBeforeAuthUrl($this->_getSession()->getAfterAuthUrl(true));
                }
            } else {
                $this->_getSession()->setBeforeAuthUrl($this->_customerHelperData->getLoginUrl());
            }
        } elseif ($this->_getSession()->getBeforeAuthUrl() == $this->_customerHelperData->getLogoutUrl()) {
            $this->_getSession()->setBeforeAuthUrl($this->_customerHelperData->getDashboardUrl());
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
     *
     * @return void
     */
    public function logoutAction()
    {
        $lastCustomerId = $this->_getSession()->getId();
        $this->_getSession()->logout()->setBeforeAuthUrl(
            $this->_redirect->getRefererUrl()
        )->setLastCustomerId(
            $lastCustomerId
        );

        $this->_redirect('*/*/logoutSuccess');
    }

    /**
     * Logout success page
     *
     * @return void
     */
    public function logoutSuccessAction()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }

    /**
     * Customer register form page
     *
     * @return void
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
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
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
            $customer = $this->_extractCustomer('customer_account_create');
            $address = $this->_extractAddress();
            $addresses = is_null($address) ? array() : array($address);
            $password = $this->getRequest()->getParam('password');
            $redirectUrl = $this->_getSession()->getBeforeAuthUrl();
            $customerDetails = $this->_customerDetailsBuilder->setCustomer(
                $customer
            )->setAddresses(
                $addresses
            )->create();
            $customer = $this->_customerAccountService->createAccount($customerDetails, $password, $redirectUrl);

            if ($this->getRequest()->getParam('is_subscribed', false)) {
                $this->_subscriberFactory->create()->updateSubscription($customer->getId(), true);
            }

            $this->_eventManager->dispatch(
                'customer_register_success',
                array('account_controller' => $this, 'customer' => $customer)
            );

            $confirmationStatus = $this->_customerAccountService->getConfirmationStatus($customer->getId());
            if ($confirmationStatus === CustomerAccountServiceInterface::ACCOUNT_CONFIRMATION_REQUIRED) {
                $email = $this->_customerHelperData->getEmailConfirmationUrl($customer->getEmail());
                // @codingStandardsIgnoreStart
                $this->messageManager->addSuccess(
                    __(
                        'Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%1">click here</a>.',
                        $email
                    )
                );
                // @codingStandardsIgnoreEnd
                $url = $this->_createUrl()->getUrl('*/*/index', array('_secure' => true));
                $this->getResponse()->setRedirect($this->_redirect->success($url));
            } else {
                $this->_getSession()->setCustomerDataAsLoggedIn($customer);
                $url = $this->_welcomeCustomer($customer);
                $this->getResponse()->setRedirect($this->_redirect->success($url));
            }
            return;
        } catch (StateException $e) {
            $url = $this->_createUrl()->getUrl('customer/account/forgotpassword');
            // @codingStandardsIgnoreStart
            $message = __(
                'There is already an account with this email address. If you are sure that it is your email address, <a href="%1">click here</a> to get your password and access your account.',
                $url
            );
            // @codingStandardsIgnoreEnd
            $this->messageManager->addError($message);
        } catch (InputException $e) {
            foreach ($e->getErrors() as $error) {
                $message = InputException::translateError($error);
                $this->messageManager->addError($this->escaper->escapeHtml($message));
            }
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Cannot save the customer.'));
        }

        $this->_getSession()->setCustomerFormData($this->getRequest()->getPost());
        $defaultUrl = $this->_createUrl()->getUrl('*/*/create', array('_secure' => true));
        $this->getResponse()->setRedirect($this->_redirect->error($defaultUrl));
    }

    /**
     * Add address to customer during create account
     *
     * @return \Magento\Customer\Service\V1\Data\Address|null
     */
    protected function _extractAddress()
    {
        if (!$this->getRequest()->getPost('create_address')) {
            return null;
        }

        $addressForm = $this->_createForm('customer_address', 'customer_register_address');
        $allowedAttributes = $addressForm->getAllowedAttributes();

        $addressData = array();

        foreach ($allowedAttributes as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            $value = $this->getRequest()->getParam($attributeCode);
            if (is_null($value)) {
                continue;
            }
            switch ($attributeCode) {
                case 'region_id':
                    $this->_regionBuilder->setRegionId($value);
                    break;
                case 'region':
                    $this->_regionBuilder->setRegion($value);
                    break;
                default:
                    $addressData[$attributeCode] = $value;
            }
        }
        $this->_addressBuilder->populateWithArray($addressData);
        $this->_addressBuilder->setRegion($this->_regionBuilder->create());

        $this->_addressBuilder->setDefaultBilling(
            $this->getRequest()->getParam('default_billing', false)
        )->setDefaultShipping(
            $this->getRequest()->getParam('default_shipping', false)
        );
        return $this->_addressBuilder->create();
    }

    /**
     * Extract customer entity from request
     *
     * @param string $formCode
     * @return Customer
     */
    protected function _extractCustomer($formCode)
    {
        $customerForm = $this->_createForm('customer', $formCode);
        $allowedAttributes = $customerForm->getAllowedAttributes();
        $isGroupIdEmpty = true;
        $customerData = array();
        foreach ($allowedAttributes as $attribute) {
            // confirmation in request param is the repeated password, not a confirmation code.
            if ($attribute === 'confirmation') {
                continue;
            }
            $attributeCode = $attribute->getAttributeCode();
            if ($attributeCode == 'group_id') {
                $isGroupIdEmpty = false;
            }
            $customerData[$attributeCode] = $this->getRequest()->getParam($attributeCode);
        }
        $this->_customerBuilder->populateWithArray($customerData);
        $store = $this->_storeManager->getStore();
        if ($isGroupIdEmpty) {
            $this->_customerBuilder->setGroupId($this->_groupService->getDefaultGroup($store->getId())->getId());
        }

        $this->_customerBuilder->setWebsiteId($store->getWebsiteId());
        $this->_customerBuilder->setStoreId($store->getId());

        return $this->_customerBuilder->create();
    }

    /**
     * Adds welcome message and returns success URL
     *
     * @return string
     */
    protected function _welcomeCustomer()
    {
        $this->_addWelcomeMessage();

        $successUrl = $this->_createUrl()->getUrl('*/*/index', array('_secure' => true));
        if (!$this->_storeConfig->getConfigFlag(
            \Magento\Customer\Helper\Data::XML_PATH_CUSTOMER_STARTUP_REDIRECT_TO_DASHBOARD
        ) && $this->_getSession()->getBeforeAuthUrl()
        ) {
            $successUrl = $this->_getSession()->getBeforeAuthUrl(true);
        }
        return $successUrl;
    }

    /**
     * Adds a welcome message to the session
     *
     * @return void
     */
    protected function _addWelcomeMessage()
    {
        $this->messageManager->addSuccess(
            __('Thank you for registering with %1.', $this->_storeManager->getStore()->getFrontendName())
        );
        if ($this->_isVatValidationEnabled()) {
            // Show corresponding VAT message to customer
            $configAddressType = $this->_addressHelper->getTaxCalculationAddressType();
            $editAddersUrl = $this->_createUrl()->getUrl('customer/address/edit');
            switch ($configAddressType) {
                case \Magento\Customer\Helper\Address::TYPE_SHIPPING:
                    // @codingStandardsIgnoreStart
                    $userPrompt = __(
                        'If you are a registered VAT customer, please click <a href="%1">here</a> to enter you shipping address for proper VAT calculation',
                        $editAddersUrl
                    );
                    // @codingStandardsIgnoreEnd
                    break;
                default:
                    // @codingStandardsIgnoreStart
                    $userPrompt = __(
                        'If you are a registered VAT customer, please click <a href="%1">here</a> to enter you billing address for proper VAT calculation',
                        $editAddersUrl
                    );
                    // @codingStandardsIgnoreEnd
                    break;
            }
            $this->messageManager->addSuccess($userPrompt);
        }
    }

    /**
     * Load customer by id (try/catch in case if it throws exceptions)
     *
     * @param int $customerId
     * @return \Magento\Customer\Service\V1\Data\Customer
     * @throws \Exception
     */
    protected function _loadCustomerById($customerId)
    {
        try {
            /** @var \Magento\Customer\Service\V1\Data\Customer $customer */
            $customer = $this->_customerAccountService->getCustomer($customerId);
            return $customer;
        } catch (NoSuchEntityException $e) {
            throw new \Exception(__('Wrong customer account specified.'));
        }
    }

    /**
     * Confirm customer account by id and confirmation key
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function confirmAction()
    {
        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }
        try {
            $customerId = $this->getRequest()->getParam('id', false);
            $key = $this->getRequest()->getParam('key', false);
            $backUrl = $this->getRequest()->getParam('back_url', false);
            if (empty($customerId) || empty($key)) {
                throw new \Exception(__('Bad request.'));
            }

            $customer = $this->_customerAccountService->activateCustomer($customerId, $key);

            // log in and send greeting email, then die happy
            $this->_getSession()->setCustomerDataAsLoggedIn($customer);
            $successUrl = $this->_welcomeCustomer();
            $this->getResponse()->setRedirect($this->_redirect->success($backUrl ? $backUrl : $successUrl));
            return;
        } catch (StateException $e) {
            switch ($e->getCode()) {
                case StateException::INVALID_STATE:
                    return;
                case StateException::INPUT_MISMATCH:
                case StateException::EXPIRED:
                    $this->messageManager->addException($e, __('This confirmation key is invalid or has expired.'));
                    break;
                default:
                    $this->messageManager->addException($e, __('There was an error confirming the account.'));
                    break;
            }
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addException($e, __('There was an error confirming the account.'));
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('There was an error confirming the account'));
        }
        // die unhappy
        $url = $this->_createUrl()->getUrl('*/*/index', array('_secure' => true));
        $this->getResponse()->setRedirect($this->_redirect->error($url));
        return;
    }

    /**
     * Send confirmation link to specified email
     *
     * @return void
     */
    public function confirmationAction()
    {
        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }

        // try to confirm by email
        $email = $this->getRequest()->getPost('email');
        if ($email) {
            try {
                $this->_customerAccountService->resendConfirmation(
                    $email,
                    $this->_storeManager->getStore()->getWebsiteId()
                );
                $this->messageManager->addSuccess(__('Please, check your email for confirmation key.'));
            } catch (StateException $e) {
                $this->messageManager->addSuccess(__('This email does not require confirmation.'));
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Wrong email.'));
                $this->getResponse()->setRedirect(
                    $this->_createUrl()->getUrl('*/*/*', array('email' => $email, '_secure' => true))
                );
                return;
            }
            $this->_getSession()->setUsername($email);
            $this->getResponse()->setRedirect($this->_createUrl()->getUrl('*/*/index', array('_secure' => true)));
            return;
        }

        // output form
        $this->_view->loadLayout();

        $this->_view->getLayout()->getBlock(
            'accountConfirmation'
        )->setEmail(
            $this->getRequest()->getParam('email', $email)
        );

        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }

    /**
     * Forgot customer password page
     *
     * @return void
     */
    public function forgotPasswordAction()
    {
        $this->_view->loadLayout();

        $this->_view->getLayout()->getBlock(
            'forgotPassword'
        )->setEmailValue(
            $this->_getSession()->getForgottenEmail()
        );
        $this->_getSession()->unsForgottenEmail();

        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }

    /**
     * Forgot customer password action
     *
     * @return void
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

            try {
                $this->_customerAccountService->initiatePasswordReset(
                    $email,
                    $this->_storeManager->getStore()->getWebsiteId(),
                    CustomerAccountServiceInterface::EMAIL_RESET
                );
            } catch (NoSuchEntityException $e) {
                // Do nothing, we don't want anyone to use this action to determine which email accounts are registered.
            } catch (\Exception $exception) {
                $this->messageManager->addException($exception, __('Unable to send password reset email.'));
                $this->_redirect('*/*/forgotpassword');
                return;
            }
            $email = $this->escaper->escapeHtml($email);
            // @codingStandardsIgnoreStart
            $this->messageManager->addSuccess(
                __(
                    'If there is an account associated with %1 you will receive an email with a link to reset your password.',
                    $email
                )
            );
            // @codingStandardsIgnoreEnd
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
     * @return void
     */
    public function resetPasswordAction()
    {
        $this->_forward('createPassword');
    }

    /**
     * Resetting password handler
     *
     * @return void
     */
    public function createPasswordAction()
    {
        $resetPasswordToken = (string)$this->getRequest()->getParam('token');
        $customerId = (int)$this->getRequest()->getParam('id');
        try {
            $this->_customerAccountService->validateResetPasswordLinkToken($customerId, $resetPasswordToken);
            $this->_view->loadLayout();
            // Pass received parameters to the reset forgotten password form
            $this->_view->getLayout()->getBlock(
                'resetPassword'
            )->setCustomerId(
                $customerId
            )->setResetPasswordLinkToken(
                $resetPasswordToken
            );
            $this->_view->renderLayout();
        } catch (\Exception $exception) {
            $this->messageManager->addError(__('Your password reset link has expired.'));
            $this->_redirect('*/*/forgotpassword');
        }
    }

    /**
     * Reset forgotten password
     *
     * Used to handle data received from reset forgotten password form
     *
     * @return void
     */
    public function resetPasswordPostAction()
    {
        $resetPasswordToken = (string)$this->getRequest()->getQuery('token');
        $customerId = (int)$this->getRequest()->getQuery('id');
        $password = (string)$this->getRequest()->getPost('password');
        $passwordConfirmation = (string)$this->getRequest()->getPost('confirmation');

        if ($password !== $passwordConfirmation) {
            $this->messageManager->addError(__("New Password and Confirm New Password values didn't match."));
            return;
        }
        if (iconv_strlen($password) <= 0) {
            $this->messageManager->addError(__('New password field cannot be empty.'));
            $this->_redirect('*/*/createpassword', array('id' => $customerId, 'token' => $resetPasswordToken));
            return;
        }

        try {
            $this->_customerAccountService->resetPassword($customerId, $resetPasswordToken, $password);
            $this->messageManager->addSuccess(__('Your password has been updated.'));
            $this->_redirect('*/*/login');
            return;
        } catch (\Exception $exception) {
            $this->messageManager->addError(__('There was an error saving the new password.'));
            $this->_redirect('*/*/createpassword', array('id' => $customerId, 'token' => $resetPasswordToken));
            return;
        }
    }

    /**
     * Forgot customer account information page
     *
     * @return void
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
        $customerId = $this->_getSession()->getCustomerId();
        $customerDataObject = $this->_customerAccountService->getCustomer($customerId);
        if (!empty($data)) {
            $customerDataObject = $this->_customerBuilder->mergeDataObjectWithArray($customerDataObject, $data);
        }
        $this->_getSession()->setCustomerData($customerDataObject);
        $this->_getSession()->setChangePassword($this->getRequest()->getParam('changepass') == 1);

        $this->_view->getLayout()->getBlock('head')->setTitle(__('Account Information'));
        $this->_view->getLayout()->getBlock('messages')->setEscapeMessageFlag(true);
        $this->_view->renderLayout();
    }

    /**
     * Change customer password action
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function editPostAction()
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            $this->_redirect('*/*/edit');
            return;
        }

        if ($this->getRequest()->isPost()) {
            $customerId = $this->_getSession()->getCustomerId();
            $customer = $this->_extractCustomer('customer_account_edit');
            $this->_customerBuilder->populate($customer);
            $this->_customerBuilder->setId($customerId);
            $customer = $this->_customerBuilder->create();

            if ($this->getRequest()->getParam('change_password')) {
                $currPass = $this->getRequest()->getPost('current_password');
                $newPass = $this->getRequest()->getPost('password');
                $confPass = $this->getRequest()->getPost('confirmation');

                if (strlen($newPass)) {
                    if ($newPass == $confPass) {
                        try {
                            $this->_customerAccountService->changePassword($customerId, $currPass, $newPass);
                        } catch (AuthenticationException $e) {
                            $this->messageManager->addError($e->getMessage());
                        } catch (\Exception $e) {
                            $this->messageManager->addException(
                                $e,
                                __('A problem was encountered trying to change password.')
                            );
                        }
                    } else {
                        $this->messageManager->addError(__('Confirm your new password'));
                    }
                } else {
                    $this->messageManager->addError(__('New password field cannot be empty.'));
                }
            }

            try {
                $this->_customerDetailsBuilder->setCustomer($customer);
                $this->_customerAccountService->updateCustomer($this->_customerDetailsBuilder->create());
            } catch (AuthenticationException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (InputException $e) {
                $this->messageManager->addException($e, __('Invalid input'));
            } catch (\Exception $e) {
                $this->messageManager->addException(
                    $e,
                    __('Cannot save the customer.') . $e->getMessage() . '<pre>' . $e->getTraceAsString() . '</pre>'
                );
            }

            if ($this->messageManager->getMessages()->getCount() > 0) {
                $this->_getSession()->setCustomerFormData($this->getRequest()->getPost());
                $this->_redirect('*/*/edit');
                return;
            }

            $this->messageManager->addSuccess(__('The account information has been saved.'));
            $this->_redirect('customer/account');
            return;
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
        return $this->_addressHelper->isVatValidationEnabled($store);
    }

    /**
     * @return \Magento\UrlInterface
     */
    protected function _createUrl()
    {
        return $this->_urlFactory->create();
    }

    /**
     * @param string $entityType
     * @param string $formCode
     * @return \Magento\Customer\Model\Metadata\Form
     */
    protected function _createForm($entityType, $formCode)
    {
        return $this->_formFactory->create($entityType, $formCode);
    }
}
