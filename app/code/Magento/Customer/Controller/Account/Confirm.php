<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Controller\Account;

use Exception;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Controller\AccountInterface;
use Magento\Customer\Helper\Address;
use Magento\Customer\Helper\Address as AddressHelper;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Controller\Result\Redirect as ResultRedirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use Magento\Framework\UrlFactory;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Confirm implements HttpGetActionInterface, AccountInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var AccountManagementInterface
     */
    protected $customerAccountManagement;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var AddressHelper
     */
    protected $addressHelper;

    /**
     * @var UrlInterface
     */
    protected $urlModel;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var PhpCookieManager
     */
    private $cookieManager;
    /**
     * @var RedirectInterface
     */
    private $redirect;
    /**
     * @var UrlFactory
     */
    private $urlFactory;
    /**
     * @var MessageManagerInterface
     */
    private $messageManager;
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @param RequestInterface $request
     * @param Session $customerSession
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param PhpCookieManager $cookieManager
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param AccountManagementInterface $customerAccountManagement
     * @param CustomerRepositoryInterface $customerRepository
     * @param Address $addressHelper
     * @param UrlFactory $urlFactory
     * @param RedirectInterface $redirect
     * @param RedirectFactory $redirectFactory
     * @param MessageManagerInterface $messageManager
     */
    public function __construct(
        RequestInterface $request,
        Session $customerSession,
        CookieMetadataFactory $cookieMetadataFactory,
        PhpCookieManager $cookieManager,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        AccountManagementInterface $customerAccountManagement,
        CustomerRepositoryInterface $customerRepository,
        Address $addressHelper,
        UrlFactory $urlFactory,
        RedirectInterface $redirect,
        RedirectFactory $redirectFactory,
        MessageManagerInterface $messageManager
    ) {
        $this->session = $customerSession;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->customerRepository = $customerRepository;
        $this->addressHelper = $addressHelper;
        $this->urlModel = $urlFactory->create();
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->cookieManager = $cookieManager;
        $this->redirect = $redirect;
        $this->urlFactory = $urlFactory;
        $this->messageManager = $messageManager;
        $this->request = $request;
        $this->redirectFactory = $redirectFactory;
    }

    /**
     * Confirm customer account by id and confirmation key
     *
     * @return ResultRedirect
     */
    public function execute()
    {
        /** @var ResultRedirect $resultRedirect */
        $resultRedirect = $this->redirectFactory->create();

        if ($this->session->isLoggedIn()) {
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }

        $customerId = $this->request->getParam('id', false);
        $key = $this->request->getParam('key', false);
        if (empty($customerId) || empty($key)) {
            $this->messageManager->addErrorMessage(__('Bad request.'));
            $url = $this->urlModel->getUrl('*/*/index', ['_secure' => true]);
            return $resultRedirect->setUrl($this->redirect->error($url));
        }

        try {
            $customerEmail = $this->customerRepository->getById($customerId)->getEmail();
            $customer = $this->customerAccountManagement->activate($customerEmail, $key);
            $this->session->setCustomerDataAsLoggedIn($customer);
            if ($this->cookieManager->getCookie('mage-cache-sessid')) {
                $metadata = $this->cookieMetadataFactory->createCookieMetadata();
                $metadata->setPath('/');
                $this->cookieManager->deleteCookie('mage-cache-sessid', $metadata);
            }
            $this->messageManager->addSuccessMessage($this->getSuccessMessage());
            $resultRedirect->setUrl($this->getSuccessRedirect());
            return $resultRedirect;
        } catch (StateException $e) {
            $this->messageManager->addExceptionMessage($e, __('This confirmation key is invalid or has expired.'));
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('There was an error confirming the account'));
        }

        $url = $this->urlModel->getUrl('*/*/index', ['_secure' => true]);
        return $resultRedirect->setUrl($this->redirect->error($url));
    }

    /**
     * Retrieve success message
     *
     * @return string
     */
    protected function getSuccessMessage()
    {
        if ($this->addressHelper->isVatValidationEnabled()) {
            $addressType = (string)$this->addressHelper->getTaxCalculationAddressType();
            return $this->getVatAddressTypeMessage($addressType);
        }

        return __('Thank you for registering with %1.', $this->storeManager->getStore()->getFrontendName());
    }

    /**
     * Retrieve success redirect URL
     *
     * @return string
     */
    protected function getSuccessRedirect()
    {
        $backUrl = $this->request->getParam('back_url', false);
        if (!$this->isDashboardRedirectAfterRegistration() && $this->session->getBeforeAuthUrl()) {
            $successUrl = $this->session->getBeforeAuthUrl(true);
        } else {
            $successUrl = $this->urlModel->getUrl('*/*/index', ['_secure' => true]);
        }

        return $this->redirect->success($backUrl ? $backUrl : $successUrl);
    }


    /**
     * Returns Phrase about VAT address change
     *
     * @param string $addressType
     * @return Phrase
     */
    private function getVatAddressTypeMessage(string $addressType): Phrase
    {
        return __(
            'If you are a registered VAT customer, please click <a href="%url">here</a> to enter your %type for proper VAT calculation.',
            [
                'url' => $this->urlModel->getUrl('customer/address/edit'),
                'type' => __($addressType . ' address')
            ]
        );
    }

    /**
     * @return bool
     */
    private function isDashboardRedirectAfterRegistration(): bool
    {
        return $this->scopeConfig->isSetFlag(
            Url::XML_PATH_CUSTOMER_STARTUP_REDIRECT_TO_DASHBOARD,
            ScopeInterface::SCOPE_STORE
        );
    }
}
