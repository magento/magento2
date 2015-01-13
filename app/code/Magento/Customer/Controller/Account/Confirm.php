<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Account;

use Magento\Customer\Model\Url;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Helper\Address;
use Magento\Framework\UrlFactory;
use Magento\Framework\Exception\StateException;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Confirm
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Confirm extends \Magento\Customer\Controller\Account
{
    /** @var ScopeConfigInterface */
    protected $scopeConfig;

    /** @var StoreManagerInterface */
    protected $storeManager;

    /** @var AccountManagementInterface  */
    protected $customerAccountManagement;

    /** @var CustomerRepositoryInterface  */
    protected $customerRepository;

    /** @var Address */
    protected $addressHelper;

    /** @var \Magento\Framework\UrlInterface */
    protected $urlModel;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param AccountManagementInterface $customerAccountManagement
     * @param CustomerRepositoryInterface $customerRepository
     * @param Address $addressHelper
     * @param UrlFactory $urlFactory
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        AccountManagementInterface $customerAccountManagement,
        CustomerRepositoryInterface $customerRepository,
        Address $addressHelper,
        UrlFactory $urlFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->customerRepository = $customerRepository;
        $this->addressHelper = $addressHelper;
        $this->urlModel = $urlFactory->create();
        parent::__construct($context, $customerSession);
    }

    /**
     * Confirm customer account by id and confirmation key
     *
     * @return void
     */
    public function execute()
    {
        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }
        try {
            $customerId = $this->getRequest()->getParam('id', false);
            $key = $this->getRequest()->getParam('key', false);
            if (empty($customerId) || empty($key)) {
                throw new \Exception(__('Bad request.'));
            }

            // log in and send greeting email
            $customerEmail = $this->customerRepository->getById($customerId)->getEmail();
            $customer = $this->customerAccountManagement->activate($customerEmail, $key);
            $this->_getSession()->setCustomerDataAsLoggedIn($customer);

            $this->messageManager->addSuccess($this->getSuccessMessage());
            $this->getResponse()->setRedirect($this->getSuccessRedirect());
            return;
        } catch (StateException $e) {
            $this->messageManager->addException($e, __('This confirmation key is invalid or has expired.'));
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('There was an error confirming the account'));
        }
        // die unhappy
        $url = $this->urlModel->getUrl('*/*/index', ['_secure' => true]);
        $this->getResponse()->setRedirect($this->_redirect->error($url));
        return;
    }

    /**
     * Retrieve success message
     *
     * @return string
     */
    protected function getSuccessMessage()
    {
        if ($this->addressHelper->isVatValidationEnabled()) {
            if ($this->addressHelper->getTaxCalculationAddressType() == Address::TYPE_SHIPPING) {
                // @codingStandardsIgnoreStart
                $message = __(
                    'If you are a registered VAT customer, please click <a href="%1">here</a> to enter you shipping address for proper VAT calculation',
                    $this->urlModel->getUrl('customer/address/edit')
                );
                // @codingStandardsIgnoreEnd
            } else {
                // @codingStandardsIgnoreStart
                $message = __(
                    'If you are a registered VAT customer, please click <a href="%1">here</a> to enter you billing address for proper VAT calculation',
                    $this->urlModel->getUrl('customer/address/edit')
                );
                // @codingStandardsIgnoreEnd
            }
        } else {
            $message = __('Thank you for registering with %1.', $this->storeManager->getStore()->getFrontendName());
        }
        return $message;
    }

    /**
     * Retrieve success redirect URL
     *
     * @return string
     */
    protected function getSuccessRedirect()
    {
        $backUrl = $this->getRequest()->getParam('back_url', false);
        $redirectToDashboard = $this->scopeConfig->isSetFlag(
            Url::XML_PATH_CUSTOMER_STARTUP_REDIRECT_TO_DASHBOARD,
            ScopeInterface::SCOPE_STORE
        );
        if (!$redirectToDashboard && $this->_getSession()->getBeforeAuthUrl()) {
            $successUrl = $this->_getSession()->getBeforeAuthUrl(true);
        } else {
            $successUrl = $this->urlModel->getUrl('*/*/index', ['_secure' => true]);
        }
        return $this->_redirect->success($backUrl ? $backUrl : $successUrl);
    }
}
