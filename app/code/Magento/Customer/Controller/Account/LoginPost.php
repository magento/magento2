<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Account;

use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Url\DecoderInterface;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Core\App\Action\FormKeyValidator;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LoginPost extends \Magento\Customer\Controller\Account
{
    /** @var ScopeConfigInterface */
    protected $scopeConfig;

    /** @var StoreManagerInterface */
    protected $storeManager;

    /** @var AccountManagementInterface */
    protected $customerAccountManagement;

    /** @var DecoderInterface */
    protected $urlDecoder;

    /** @var CustomerUrl */
    protected $customerUrl;

    /** @var FormKeyValidator */
    protected $formKeyValidator;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param AccountManagementInterface $customerAccountManagement
     * @param DecoderInterface $urlDecoder
     * @param CustomerUrl $customerHelperData
     * @param FormKeyValidator $formKeyValidator
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        AccountManagementInterface $customerAccountManagement,
        DecoderInterface $urlDecoder,
        CustomerUrl $customerHelperData,
        FormKeyValidator $formKeyValidator
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->urlDecoder = $urlDecoder;
        $this->customerUrl = $customerHelperData;
        $this->formKeyValidator = $formKeyValidator;
        parent::__construct($context, $customerSession);
    }

    /**
     * Define target URL and redirect customer after logging in
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function loginPostRedirect()
    {
        $lastCustomerId = $this->_getSession()->getLastCustomerId();
        if (isset(
            $lastCustomerId
            ) && $this->_getSession()->isLoggedIn() && $lastCustomerId != $this->_getSession()->getId()
        ) {
            $this->_getSession()->unsBeforeAuthUrl()->setLastCustomerId($this->_getSession()->getId());
        }
        if (!$this->_getSession()->getBeforeAuthUrl() ||
            $this->_getSession()->getBeforeAuthUrl() == $this->storeManager->getStore()->getBaseUrl()
        ) {
            // Set default URL to redirect customer to
            $this->_getSession()->setBeforeAuthUrl($this->customerUrl->getAccountUrl());
            // Redirect customer to the last page visited after logging in
            if ($this->_getSession()->isLoggedIn()) {
                if (!$this->scopeConfig->isSetFlag(
                    CustomerUrl::XML_PATH_CUSTOMER_STARTUP_REDIRECT_TO_DASHBOARD,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
                ) {
                    $referer = $this->getRequest()->getParam(CustomerUrl::REFERER_QUERY_PARAM_NAME);
                    if ($referer) {
                        $referer = $this->urlDecoder->decode($referer);
                        if ($this->_url->isOwnOriginUrl()) {
                            $this->_getSession()->setBeforeAuthUrl($referer);
                        }
                    }
                } elseif ($this->_getSession()->getAfterAuthUrl()) {
                    $this->_getSession()->setBeforeAuthUrl($this->_getSession()->getAfterAuthUrl(true));
                }
            } else {
                $this->_getSession()->setBeforeAuthUrl($this->customerUrl->getLoginUrl());
            }
        } elseif ($this->_getSession()->getBeforeAuthUrl() == $this->customerUrl->getLogoutUrl()) {
            $this->_getSession()->setBeforeAuthUrl($this->customerUrl->getDashboardUrl());
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
     * Login post action
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        if ($this->_getSession()->isLoggedIn() || !$this->formKeyValidator->validate($this->getRequest())) {
            $this->_redirect('*/*/');
            return;
        }

        if ($this->getRequest()->isPost()) {
            $login = $this->getRequest()->getPost('login');
            if (!empty($login['username']) && !empty($login['password'])) {
                try {
                    $customer = $this->customerAccountManagement->authenticate($login['username'], $login['password']);
                    $this->_getSession()->setCustomerDataAsLoggedIn($customer);
                    $this->_getSession()->regenerateId();
                } catch (EmailNotConfirmedException $e) {
                    $value = $this->customerUrl->getEmailConfirmationUrl($login['username']);
                    $message = __(
                        'This account is not confirmed.' .
                        ' <a href="%1">Click here</a> to resend confirmation email.',
                        $value
                    );
                    $this->messageManager->addError($message);
                    $this->_getSession()->setUsername($login['username']);
                }
                catch (AuthenticationException $e) {
                    $message = __('Invalid login or password.');
                    $this->messageManager->addError($message);
                    $this->_getSession()->setUsername($login['username']);
                } catch (\Exception $e) {
                    // PA DSS violation: this exception log can disclose customer password
                    // $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                    $this->messageManager->addError(__('There was an error validating the login and password.'));
                }
            } else {
                $this->messageManager->addError(__('Login and password are required.'));
            }
        }

        $this->loginPostRedirect();
    }
}
