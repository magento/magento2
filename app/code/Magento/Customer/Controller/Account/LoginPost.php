<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Controller\Account;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Controller\AccountInterface;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\Redirect as ResultRedirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;

class LoginPost implements CsrfAwareActionInterface, HttpPostActionInterface, AccountInterface
{
    /**
     * @var AccountManagementInterface
     */
    protected $customerAccountManagement;

    /**
     * @var FormKeyValidator
     */
    protected $formKeyValidator;

    /**
     * @var AccountRedirect
     */
    protected $accountRedirect;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var PhpCookieManager
     */
    private $cookieMetadataManager;

    /**
     * @var CustomerUrl
     */
    private $customerUrl;
    /**
     * @var ScopeConfigInterface
     */
    private $config;
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var MessageManagerInterface
     */
    private $messageManager;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @param RequestInterface $request
     * @param Session $customerSession
     * @param AccountManagementInterface $customerAccountManagement
     * @param CustomerUrl $customerHelperData
     * @param Validator $formKeyValidator
     * @param AccountRedirect $accountRedirect
     * @param RedirectFactory $redirectFactory
     * @param ScopeConfigInterface $config
     * @param MessageManagerInterface $messageManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     */
    public function __construct(
        RequestInterface $request,
        Session $customerSession,
        AccountManagementInterface $customerAccountManagement,
        CustomerUrl $customerHelperData,
        Validator $formKeyValidator,
        AccountRedirect $accountRedirect,
        RedirectFactory $redirectFactory,
        ScopeConfigInterface $config,
        MessageManagerInterface $messageManager,
        CookieMetadataFactory $cookieMetadataFactory
    ) {
        $this->session = $customerSession;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->customerUrl = $customerHelperData;
        $this->formKeyValidator = $formKeyValidator;
        $this->accountRedirect = $accountRedirect;
        $this->config = $config;
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->redirectFactory = $redirectFactory;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->redirectFactory->create();
        $resultRedirect->setPath('*/*/');

        return new InvalidRequestException(
            $resultRedirect,
            [new Phrase('Invalid Form Key. Please refresh the page.')]
        );
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return null;
    }

    /**
     * Login post action
     *
     * @return ResultRedirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        if ($this->session->isLoggedIn() || !$this->formKeyValidator->validate($this->request)) {
            /** @var ResultRedirect $resultRedirect */
            $resultRedirect = $this->redirectFactory->create();
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }

        if ($this->request->isPost()) {
            $login = $this->request->getPost('login');
            if (!empty($login['username']) && !empty($login['password'])) {
                try {
                    $customer = $this->customerAccountManagement->authenticate($login['username'], $login['password']);
                    $this->session->setCustomerDataAsLoggedIn($customer);
                    if ($this->cookieMetadataManager->getCookie('mage-cache-sessid')) {
                        $metadata = $this->cookieMetadataFactory->createCookieMetadata();
                        $metadata->setPath('/');
                        $this->cookieMetadataManager->deleteCookie('mage-cache-sessid', $metadata);
                    }
                    $redirectUrl = $this->accountRedirect->getRedirectCookie();
                    if (!$this->isRedirectDashboardAfterLogin() && $redirectUrl) {
                        $this->accountRedirect->clearRedirectCookie();
                        $resultRedirect = $this->redirectFactory->create();
                        // URL is checked to be internal in $this->_redirect->success()
                        $resultRedirect->setUrl($this->_redirect->success($redirectUrl));
                        return $resultRedirect;
                    }
                } catch (EmailNotConfirmedException $e) {
                    $value = $this->customerUrl->getEmailConfirmationUrl($login['username']);
                    $message = __(
                        'This account is not confirmed. <a href="%1">Click here</a> to resend confirmation email.',
                        $value
                    );
                } catch (AuthenticationException $e) {
                    $message = __(
                        'The account sign-in was incorrect or your account is disabled temporarily. '
                        . 'Please wait and try again later.'
                    );
                } catch (LocalizedException $e) {
                    $message = $e->getMessage();
                } catch (\Exception $e) {
                    // PA DSS violation: throwing or logging an exception here can disclose customer password
                    $this->messageManager->addErrorMessage(
                        __('An unspecified error occurred. Please contact us for assistance.')
                    );
                } finally {
                    if (isset($message)) {
                        $this->messageManager->addErrorMessage($message);
                        $this->session->setUsername($login['username']);
                    }
                }
            } else {
                $this->messageManager->addErrorMessage(__('A login and a password are required.'));
            }
        }

        return $this->accountRedirect->getRedirect();
    }

    /**
     * @return bool
     */
    private function isRedirectDashboardAfterLogin(): bool
    {
        return $this->scopeConfig->isSetFlag('customer/startup/redirect_dashboard');
    }
}
