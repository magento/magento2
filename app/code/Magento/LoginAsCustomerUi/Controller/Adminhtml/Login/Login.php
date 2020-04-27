<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerUi\Controller\Adminhtml\Login;

use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Backend\App\Action;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\LoginAsCustomer\Api\CreateSecretInterface;

/**
 * Login as customer action
 * Generate secret key and forward to the storefront action
 *
 * This action can be executed via GET request when "Store View To Login In" is disabled, and POST when it is enabled
 */
class Login extends Action implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_LoginAsCustomerUi::login_button';

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    private $authSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Url
     */
    private $url;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\LoginAsCustomer\Model\Config
     */
    private $config;

    /**
     * @var CreateSecretInterface
     */
    private $createSecretProcessor;

    /**
     * Login constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Url $url
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Magento\LoginAsCustomer\Model\Config $config,
     * @param CreateSecretInterface $createSecretProcessor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Url $url,
        CustomerRepositoryInterface $customerRepository,
        \Magento\LoginAsCustomer\Model\Config $config,
        CreateSecretInterface $createSecretProcessor
    ) {
        parent::__construct($context);
        $this->authSession = $authSession;
        $this->storeManager = $storeManager;
        $this->url = $url;
        $this->customerRepository = $customerRepository;
        $this->config = $config;
        $this->createSecretProcessor = $createSecretProcessor;
    }

    /**
     * Login as customer
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if (!$this->config->isEnabled()) {
            $this->messageManager->addErrorMessage(__('Login As Customer is disabled.'));
            return $resultRedirect->setPath('customer/index/index');
        }

        $request = $this->getRequest();

        $customerId = (int) $request->getParam('customer_id');
        if (!$customerId) {
            $customerId = (int) $request->getParam('entity_id');
        }

        try {
            $customer = $this->customerRepository->getById($customerId);
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('Customer with this ID are no longer exist.'));
            return $resultRedirect->setPath('customer/index/index');
        }

        $customerStoreId = $request->getParam('store_id');
        if (!isset($customerStoreId) && $this->config->isManualChoiceEnabled()) {
            $this->messageManager->addNoticeMessage(__('Please select a Store View to login in.'));
            return $resultRedirect->setPath('loginascustomer/login/manual', ['entity_id' => $customerId ]);
        }


        $user = $this->authSession->getUser();
        $secret = $this->createSecretProcessor->execute($customerId, (int)$user->getId());

        $store = $this->storeManager->getStore();
        if (null === $store) {
            $store = $this->storeManager->getDefaultStoreView();
        }

        $redirectUrl = $this->url->setScope($store)
            ->getUrl('loginascustomer/login/index', ['secret' => $secret, '_nosid' => true]);

        return $resultRedirect->setUrl($redirectUrl);
    }
}
